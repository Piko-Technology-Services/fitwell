<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$st = db()->prepare('SELECT s.*, c.name AS class_name FROM class_sessions s JOIN classes c ON c.id = s.class_id WHERE s.id = ?');
$st->execute([$id]);
$session = $st->fetch();
if (!$session) {
    flash('error', 'Session not found.');
    redirect('admin/sessions.php');
}
$self = 'admin/session.php?id=' . $id;
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'cancel_booking') {
        [$ok, $msg] = cancel_booking(0, (int) ($_POST['booking_id'] ?? 0), true);
        flash($ok ? 'success' : 'error', $ok ? 'Booking cancelled — the spot is available again.' : $msg);
        redirect($self);
    }
    if ($action === 'update') {
        $capacity = (int) ($_POST['capacity'] ?? 0);
        $st = db()->prepare("SELECT COUNT(*) FROM bookings WHERE session_id = ? AND status = 'confirmed'");
        $st->execute([$id]);
        $booked = (int) $st->fetchColumn();
        if ($capacity < max(1, $booked)) {
            $errors['capacity'] = "Capacity can't be lower than the $booked current bookings.";
        } else {
            db()->prepare('UPDATE class_sessions SET capacity = ?, instructor = ?, location = ? WHERE id = ?')
                ->execute([$capacity, trim((string) $_POST['instructor']) ?: null, trim((string) $_POST['location']) ?: null, $id]);
            flash('success', 'Session updated.');
            redirect($self);
        }
    }
}

$st = db()->prepare("SELECT b.id, b.status, b.created_at, b.cancelled_at, u.name, u.email, u.phone FROM bookings b JOIN users u ON u.id = b.user_id WHERE b.session_id = ? ORDER BY b.status = 'confirmed' DESC, b.created_at");
$st->execute([$id]);
$bookings = $st->fetchAll();
$confirmed = count(array_filter($bookings, function ($b) { return $b['status'] === 'confirmed'; }));

$title = 'Session · Admin';
require APP_DIR . '/partials/header.php';
require APP_DIR . '/partials/admin-bar.php';
?>
<section class="section section--tight">
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb"><a href="<?= url('admin/sessions.php') ?>">Sessions</a><span aria-hidden="true">/</span><span><?= e($session['class_name']) ?></span></nav>
    <div class="account-head" style="margin-bottom:32px">
      <div>
        <h1 class="h2"><?= e($session['class_name']) ?></h1>
        <p class="lead muted" style="margin:10px 0 0"><?= e(fmt_day($session['starts_at'], true)) ?> · <?= e(fmt_time($session['starts_at'])) ?> · <?= (int) $session['duration_min'] ?> min <?= $session['status'] !== 'scheduled' ? '<span class="badge badge--danger">Cancelled</span>' : '' ?></p>
      </div>
      <div class="stat" style="min-width:200px"><strong><?= $confirmed ?> / <?= (int) $session['capacity'] ?></strong><span>Confirmed bookings</span></div>
    </div>

    <div class="admin-grid">
      <div class="panel">
        <h2 class="panel__title">Session details</h2>
        <form class="form" method="post" action="<?= url($self) ?>" novalidate>
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="return" value="<?= e($self) ?>">
          <div class="field<?= isset($errors['capacity']) ? ' has-error' : '' ?>"><label class="label" for="capacity">Capacity</label><input class="input" id="capacity" name="capacity" type="number" min="1" max="500" value="<?= (int) $session['capacity'] ?>"><?= field_error($errors, 'capacity') ?></div>
          <div class="field"><label class="label" for="instructor">Instructor</label><input class="input" id="instructor" name="instructor" value="<?= e($session['instructor'] ?? '') ?>"></div>
          <div class="field"><label class="label" for="location">Location</label><input class="input" id="location" name="location" value="<?= e($session['location'] ?? '') ?>"></div>
          <button class="btn btn--primary btn--block" type="submit"><span>Save</span></button>
        </form>
      </div>

      <div>
        <h2 class="h3" style="margin-bottom:16px">Attendees</h2>
        <?php if ($bookings): ?>
          <div class="table-scroll">
            <table class="table">
              <thead><tr><th>Member</th><th>Contact</th><th>Status</th><th><span class="sr-only">Actions</span></th></tr></thead>
              <tbody>
                <?php foreach ($bookings as $b): ?>
                  <tr>
                    <td><strong><?= e($b['name']) ?></strong><br><span class="muted" style="font-size:.85rem">Booked <?= e(date('j M, H:i', strtotime($b['created_at']))) ?></span></td>
                    <td><a href="mailto:<?= e($b['email']) ?>"><?= e($b['email']) ?></a><?= $b['phone'] ? '<br><span class="muted">' . e($b['phone']) . '</span>' : '' ?></td>
                    <td><?= $b['status'] === 'confirmed' ? '<span class="badge badge--success">Confirmed</span>' : '<span class="badge">Cancelled</span>' ?></td>
                    <td><div class="actions"><?php if ($b['status'] === 'confirmed'): ?>
                      <form method="post" action="<?= url($self) ?>" data-confirm-form><?= csrf_field() ?><input type="hidden" name="action" value="cancel_booking"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="booking_id" value="<?= (int) $b['id'] ?>"><input type="hidden" name="return" value="<?= e($self) ?>"><button class="btn btn--quiet btn--sm" type="submit" data-confirm-label="Confirm">Cancel</button></form>
                    <?php endif; ?></div></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="empty"><div class="empty__icon"><?= icon('users') ?></div><h3>No bookings yet</h3><p>Members who book this session will appear here.</p></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php require APP_DIR . '/partials/footer.php'; ?>
