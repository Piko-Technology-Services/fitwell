<?php
/**
 * Classes, sessions and bookings — all booking rules are enforced here,
 * server-side, inside a transaction.
 */

function published_classes(?string $category = null): array
{
    if (!db_ready()) {
        return [];
    }
    $sql = 'SELECT * FROM classes WHERE is_published = 1';
    $args = [];
    if ($category) {
        $sql .= ' AND category = ?';
        $args[] = $category;
    }
    $sql .= ' ORDER BY sort_order, name';
    $st = db()->prepare($sql);
    $st->execute($args);
    return $st->fetchAll();
}

function find_class(string $slug): ?array
{
    if (!db_ready()) {
        return null;
    }
    $st = db()->prepare('SELECT * FROM classes WHERE slug = ? AND is_published = 1');
    $st->execute([$slug]);
    return $st->fetch() ?: null;
}

/**
 * Upcoming scheduled sessions with booked counts and (if logged in)
 * the current member's booking.
 */
function upcoming_sessions(string $from, string $to, ?int $classId = null, ?int $limit = null): array
{
    if (!db_ready()) {
        return [];
    }
    $uid = current_user()['id'] ?? 0;
    $sql = "SELECT s.*, c.name AS class_name, c.slug AS class_slug, c.level, c.category, c.image,
                   (SELECT COUNT(*) FROM bookings b WHERE b.session_id = s.id AND b.status = 'confirmed') AS booked,
                   (SELECT b2.id FROM bookings b2 WHERE b2.session_id = s.id AND b2.user_id = ? AND b2.status = 'confirmed') AS my_booking_id
            FROM class_sessions s
            JOIN classes c ON c.id = s.class_id
            WHERE s.status = 'scheduled' AND c.is_published = 1
              AND s.starts_at >= ? AND s.starts_at < ?";
    $args = [$uid, $from, $to];
    if ($classId) {
        $sql .= ' AND s.class_id = ?';
        $args[] = $classId;
    }
    $sql .= ' ORDER BY s.starts_at, c.name';
    if ($limit) {
        $sql .= ' LIMIT ' . (int) $limit;
    }
    $st = db()->prepare($sql);
    $st->execute($args);
    return $st->fetchAll();
}

function next_session(): ?array
{
    $rows = upcoming_sessions(now_sql(), date('Y-m-d H:i:s', strtotime('+14 days')), null, 1);
    return $rows[0] ?? null;
}

function spots_left(array $s): int
{
    return max(0, (int) $s['capacity'] - (int) $s['booked']);
}

/** Availability label + state for a session row. */
function availability(array $s): array
{
    $left = spots_left($s);
    if (!empty($s['my_booking_id'])) {
        return ['booked', "You're booked"];
    }
    if ($left === 0) {
        return ['full', 'Class full'];
    }
    if ($left <= max(3, (int) ceil($s['capacity'] * 0.2))) {
        return ['low', $left === 1 ? '1 spot left' : $left . ' spots left'];
    }
    return ['open', $left . ' spots available'];
}

function can_cancel(string $startsAt): bool
{
    return strtotime($startsAt) - time() >= cfg('cancel_cutoff_hours', 2) * 3600;
}

/**
 * Book a session. Returns [bool success, string message].
 * Guards: session exists & scheduled, class published, in the future,
 * not already booked, capacity not exceeded (row lock on MySQL).
 */
function book_session(int $userId, int $sessionId): array
{
    $pdo = db();
    try {
        $pdo->beginTransaction();
        $lock = is_mysql() ? ' FOR UPDATE' : '';
        $st = $pdo->prepare("SELECT s.*, c.name AS class_name, c.is_published FROM class_sessions s
                              JOIN classes c ON c.id = s.class_id WHERE s.id = ?" . $lock);
        $st->execute([$sessionId]);
        $s = $st->fetch();

        if (!$s || $s['status'] !== 'scheduled' || (int) $s['is_published'] !== 1) {
            $pdo->rollBack();
            return [false, 'That session is no longer available.'];
        }
        if (strtotime($s['starts_at']) <= time()) {
            $pdo->rollBack();
            return [false, 'That session has already started.'];
        }

        $st = $pdo->prepare('SELECT id, status FROM bookings WHERE session_id = ? AND user_id = ?' . $lock);
        $st->execute([$sessionId, $userId]);
        $existing = $st->fetch();
        if ($existing && $existing['status'] === 'confirmed') {
            $pdo->rollBack();
            return [false, "You're already booked into this session."];
        }

        $st = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE session_id = ? AND status = 'confirmed'");
        $st->execute([$sessionId]);
        if ((int) $st->fetchColumn() >= (int) $s['capacity']) {
            $pdo->rollBack();
            return [false, 'Sorry — this session is now full.'];
        }

        if ($existing) {
            $pdo->prepare("UPDATE bookings SET status = 'confirmed', created_at = ?, cancelled_at = NULL WHERE id = ?")
                ->execute([now_sql(), $existing['id']]);
        } else {
            $pdo->prepare("INSERT INTO bookings (session_id, user_id, status, created_at) VALUES (?, ?, 'confirmed', ?)")
                ->execute([$sessionId, $userId, now_sql()]);
        }
        $pdo->commit();
        return [true, sprintf("You're in! %s · %s at %s.", $s['class_name'], fmt_day($s['starts_at']), fmt_time($s['starts_at']))];
    } catch (PDOException $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Unique (session_id, user_id) violation = double submit
        if ($ex->getCode() === '23000' || stripos($ex->getMessage(), 'UNIQUE') !== false) {
            return [false, "You're already booked into this session."];
        }
        error_log('Fitwell booking error: ' . $ex->getMessage());
        return [false, 'We could not complete that booking. Please try again.'];
    }
}

function cancel_booking(int $userId, int $bookingId, bool $asAdmin = false): array
{
    $pdo = db();
    $sql = "SELECT b.*, s.starts_at, c.name AS class_name FROM bookings b
            JOIN class_sessions s ON s.id = b.session_id
            JOIN classes c ON c.id = s.class_id WHERE b.id = ?";
    $args = [$bookingId];
    if (!$asAdmin) {
        $sql .= ' AND b.user_id = ?';
        $args[] = $userId;
    }
    $st = $pdo->prepare($sql);
    $st->execute($args);
    $b = $st->fetch();
    if (!$b || $b['status'] !== 'confirmed') {
        return [false, 'That booking could not be found.'];
    }
    if (!$asAdmin && !can_cancel($b['starts_at'])) {
        return [false, sprintf('Bookings can be cancelled up to %d hours before the class. Please contact the studio.', cfg('cancel_cutoff_hours', 2))];
    }
    $pdo->prepare("UPDATE bookings SET status = 'cancelled', cancelled_at = ? WHERE id = ?")
        ->execute([now_sql(), $bookingId]);
    return [true, sprintf('Your booking for %s on %s has been cancelled.', $b['class_name'], fmt_day($b['starts_at']))];
}

function member_bookings(int $userId, bool $upcoming = true, int $limit = 50): array
{
    $op = $upcoming ? '>=' : '<';
    $order = $upcoming ? 'ASC' : 'DESC';
    $st = db()->prepare("SELECT b.id AS booking_id, b.created_at AS booked_at, s.*, c.name AS class_name, c.slug AS class_slug, c.image
                         FROM bookings b
                         JOIN class_sessions s ON s.id = b.session_id
                         JOIN classes c ON c.id = s.class_id
                         WHERE b.user_id = ? AND b.status = 'confirmed' AND s.starts_at $op ?
                         ORDER BY s.starts_at $order LIMIT " . (int) $limit);
    $st->execute([$userId, now_sql()]);
    return $st->fetchAll();
}

function category_label(string $key): string
{
    return cfg('categories')[$key] ?? ucfirst($key);
}
