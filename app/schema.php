<?php
/** Database schema + optional sample content. */

function schema_statements(bool $mysql): array
{
    $id = $mysql ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $fk = $mysql ? 'INT UNSIGNED NOT NULL' : 'INTEGER NOT NULL';
    $tail = $mysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '';

    return [
        "CREATE TABLE IF NOT EXISTS users (
            id $id,
            name VARCHAR(80) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            phone VARCHAR(30) NULL,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT 'member',
            created_at DATETIME NOT NULL
        )$tail",
        "CREATE TABLE IF NOT EXISTS classes (
            id $id,
            slug VARCHAR(120) NOT NULL UNIQUE,
            name VARCHAR(120) NOT NULL,
            category VARCHAR(40) NOT NULL,
            summary VARCHAR(255) NOT NULL,
            description TEXT NULL,
            duration_min SMALLINT NOT NULL DEFAULT 45,
            level VARCHAR(40) NOT NULL DEFAULT 'All levels',
            intensity VARCHAR(40) NULL,
            image VARCHAR(255) NULL,
            is_published TINYINT NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL
        )$tail",
        "CREATE TABLE IF NOT EXISTS class_sessions (
            id $id,
            class_id $fk,
            starts_at DATETIME NOT NULL,
            duration_min SMALLINT NOT NULL,
            capacity SMALLINT NOT NULL,
            instructor VARCHAR(80) NULL,
            location VARCHAR(80) NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'scheduled',
            created_at DATETIME NOT NULL,
            FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
        )$tail",
        "CREATE TABLE IF NOT EXISTS bookings (
            id $id,
            session_id $fk,
            user_id $fk,
            status VARCHAR(20) NOT NULL DEFAULT 'confirmed',
            created_at DATETIME NOT NULL,
            cancelled_at DATETIME NULL,
            UNIQUE (session_id, user_id),
            FOREIGN KEY (session_id) REFERENCES class_sessions(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )$tail",
        media_table_sql($mysql),
        "CREATE TABLE IF NOT EXISTS login_attempts (
            id $id,
            email VARCHAR(190) NOT NULL,
            ip VARCHAR(45) NOT NULL,
            attempted_at DATETIME NOT NULL
        )$tail",
        $mysql ? "CREATE INDEX idx_sessions_start ON class_sessions (starts_at)" : "CREATE INDEX IF NOT EXISTS idx_sessions_start ON class_sessions (starts_at)",
        $mysql ? "CREATE INDEX idx_attempts ON login_attempts (email, ip, attempted_at)" : "CREATE INDEX IF NOT EXISTS idx_attempts ON login_attempts (email, ip, attempted_at)",
    ];
}

/** Uploaded class photos are stored in the database, so no folder permissions are needed. */
function media_table_sql(bool $mysql): string
{
    $id = $mysql ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $blob = $mysql ? 'MEDIUMBLOB' : 'BLOB';
    $tail = $mysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : '';
    return "CREATE TABLE IF NOT EXISTS media (
        id $id,
        mime VARCHAR(40) NOT NULL,
        data $blob NOT NULL,
        width INT NOT NULL DEFAULT 0,
        height INT NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL
    )$tail";
}

function create_schema(PDO $pdo, bool $mysql): void
{
    foreach (schema_statements($mysql) as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            // Index may already exist on re-run (MySQL has no IF NOT EXISTS for indexes)
            if (stripos($sql, 'CREATE INDEX') !== 0) {
                throw $e;
            }
        }
    }
}

/** Sample classes — clearly sample content, editable in Admin → Classes. */
function sample_classes(): array
{
    return [
        ['fitwell-aerobics', 'Fitwell Aerobics', 'aerobics', 'Our signature high-energy aerobics class — great music, simple choreography and a full-body cardio lift.', "A feel-good cardio session built around easy-to-follow combinations that grow as the class goes on. Expect a dynamic warm-up, a main aerobic block with options for every level and a calming cool-down.\n\nPerfect if you love to move to music and want to build stamina, coordination and confidence.", 50, 'All levels', 'Moderate–High', 'class-aerobics', 1],
        ['dance-cardio', 'Dance Cardio', 'dance', 'Dance-inspired moves, feel-good tracks and a workout that never feels like work.', "Learn fun, rhythmic routines inspired by a mix of dance styles while your heart rate climbs. No dance experience needed — your instructor breaks everything down so you can let go and enjoy it.\n\nCome for the workout, stay for the energy in the room.", 45, 'All levels', 'Moderate', 'class-dance', 2],
        ['total-body-group-fitness', 'Total Body Group Fitness', 'group-fitness', 'A coached circuit of strength and cardio intervals for the whole body.', "Work through timed stations using bodyweight and light equipment, alternating strength and cardio. Clear coaching and modifications mean you can push hard or take it steady.\n\nIdeal for building functional strength alongside friends.", 45, 'Intermediate', 'High', 'class-group', 3],
        ['low-impact-aerobics', 'Low-Impact Aerobics', 'aerobics', 'All the joy of aerobics with joint-friendly moves — perfect for beginners and returners.', "Gentle on the joints but still energising, this class keeps one foot on the floor while you build fitness, balance and mobility. A brilliant first class if you're new to Fitwell or easing back into exercise.", 45, 'Beginner friendly', 'Low–Moderate', 'class-lowimpact', 4],
        ['saturday-special', 'Saturday Special', 'special', 'A longer weekend session with a different theme each week — the perfect way to start your weekend.', "Our weekend celebration of movement. Each Saturday brings a special format, from extended dance sessions to partner workouts and themed classes.\n\nCheck the timetable for this week's theme.", 60, 'All levels', 'Varies', 'class-special', 5],
    ];
}

function seed_sample_content(PDO $pdo): array
{
    $now = now_sql();
    $ids = [];
    $ins = $pdo->prepare('INSERT INTO classes (slug, name, category, summary, description, duration_min, level, intensity, image, is_published, sort_order, created_at) VALUES (?,?,?,?,?,?,?,?,?,1,?,?)');
    $find = $pdo->prepare('SELECT id FROM classes WHERE slug = ?');
    foreach (sample_classes() as $c) {
        $find->execute([$c[0]]);
        $existing = $find->fetchColumn();
        if ($existing) {
            $ids[$c[0]] = (int) $existing;
            continue;
        }
        $ins->execute([$c[0], $c[1], $c[2], $c[3], $c[4], $c[5], $c[6], $c[7], $c[8], $c[9], $now]);
        $ids[$c[0]] = (int) $pdo->lastInsertId();
    }

    // Weekly pattern: weekday (1=Mon) => [time, class slug]
    $pattern = [
        1 => [['06:00', 'fitwell-aerobics'], ['18:00', 'dance-cardio']],
        2 => [['06:00', 'low-impact-aerobics'], ['18:00', 'total-body-group-fitness']],
        3 => [['06:00', 'fitwell-aerobics'], ['17:30', 'low-impact-aerobics'], ['18:30', 'dance-cardio']],
        4 => [['06:00', 'total-body-group-fitness'], ['18:00', 'fitwell-aerobics']],
        5 => [['06:00', 'dance-cardio'], ['17:30', 'total-body-group-fitness']],
        6 => [['08:00', 'saturday-special'], ['09:30', 'low-impact-aerobics']],
    ];
    $durations = [];
    foreach (sample_classes() as $c) {
        $durations[$c[0]] = $c[5];
    }
    $ins = $pdo->prepare('INSERT INTO class_sessions (class_id, starts_at, duration_min, capacity, instructor, location, status, created_at) VALUES (?,?,?,?,NULL,?,\'scheduled\',?)');
    $count = 0;
    for ($d = 0; $d < 21; $d++) {
        $ts = strtotime("today +$d days");
        $dow = (int) date('N', $ts);
        foreach ($pattern[$dow] ?? [] as [$time, $slug]) {
            $start = date('Y-m-d', $ts) . ' ' . $time . ':00';
            if (strtotime($start) <= time()) {
                continue;
            }
            $ins->execute([$ids[$slug], $start, $durations[$slug], 20, 'Main Studio', $now]);
            $count++;
        }
    }
    return ['classes' => count($ids), 'sessions' => $count];
}
