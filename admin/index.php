<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require_admin();

$weekEnd = date('Y-m-d H:i:s', strtotime('+7 days'));
$st = db()->prepare("SELECT COUNT(*) FROM class_sessions WHERE status = 'scheduled' AND starts_at >= ? AND starts_at < ?");
$st->execute([now_sql(), $weekEnd]);
$sessionsWeek = (int) $st->fetchColumn();
$st = db()->prepare("SELECT COUNT(*) FROM bookings b JOIN class_sessions s ON s.id = b.session_id WHERE b.status = 'confirmed' AND s.status = 'scheduled' AND s.starts_at >= ? AND s.starts_at < ?");
$st->execute([now_sql(), $weekEnd]);
$bookingsWeek = (int) $st->fetchColumn();
$members = (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'member'")->fetchColumn();

$st = db()->prepare("SELECT s.*, c.name AS class_name,
        (SELECT COUNT(*) FROM bookings b WHERE b.session_id = s.id AND b.status = 'confirmed') AS booked
        FROM class_sessions s JOIN classes c ON c.id = s.class_id
        WHERE s.starts_at >= ? AND s.starts_at < ? ORDER BY s.starts_at LIMIT 40");
$st->execute([now_sql(), date('Y-m-d H:i:s', strtotime('+14 days'))]);
$sessions = $st->fetchAll();

$title = 'Studio admin';
require APP_DIR . '/partials/header.php';
require APP_DIR . '/partials/admin-bar.php';
?>
<section class="section section--tight">
  <div class="container">
    <div class="account-head" style="margin-bottom:32px">
      <div><p class="eyebrow">Overview</p><h1 class="h2">Next 7 days</h1></div>
      <div class="btn-row"><a class="btn btn--primary" href="<?= url('admin/sessions.php#new') ?>">Schedule a session <?= icon('arrow') ?></a></div>
    </div>
    <div class="stat-row">
      <div class="stat"><strong><?= $sessionsWeek ?></strong><span>Scheduled sessions</span></div>
      <div class="stat"><strong><?= $bookingsWeek ?></strong><span>Confirmed bookings</span></div>
      <div class="stat"><strong><?= $members ?></strong><span>Registered members</span></div>
    </div>

    <h2 class="h3" style="margin:48px 0 16px">Upcoming sessions (14 days)</h2>
    <?php if ($sessions): ?>
      <div class="table-scroll">
        <table class="table">
          <thead><tr><th>When</th><th>Class</th><th>Booked</th><th>Status</th><th><span class="sr-only">Actions</span></th></tr></thead>
          <tbody>
            <?php foreach ($sessions as $s): ?>
              <tr>
                <td><strong><?= e(fmt_day($s['starts_at'])) ?></strong><br><span class="muted"><?= e(fmt_time($s['starts_at'])) ?> · <?= (int) $s['duration_min'] ?> min</span></td>
                <td><?= e($s['class_name']) ?><?= $s['instructor'] ? '<br><span class="muted">' . e($s['instructor']) . '</span>' : '' ?></td>
                <td><?= (int) $s['booked'] ?> / <?= (int) $s['capacity'] ?><div class="meter"><span style="width:<?= min(100, round($s['booked'] / max(1, $s['capacity']) * 100)) ?>%"></span></div></td>
                <td><?= $s['status'] === 'scheduled' ? ((int) $s['booked'] >= (int) $s['capacity'] ? '<span class="badge badge--orange">Full</span>' : '<span class="badge badge--success">Open</span>') : '<span class="badge badge--danger">Cancelled</span>' ?></td>
                <td><div class="actions"><a class="btn btn--ghost btn--sm" href="<?= url('admin/session.php?id=' . (int) $s['id']) ?>">Bookings</a></div></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty"><div class="empty__icon"><?= icon('calendar') ?></div><h3>No sessions in the next two weeks</h3><p>Schedule sessions so members can start booking.</p><a class="btn btn--dark btn--sm" href="<?= url('admin/sessions.php#new') ?>">Schedule a session</a></div>
    <?php endif; ?>
  </div>
</section>
<?php require APP_DIR . '/partials/footer.php'; ?>
