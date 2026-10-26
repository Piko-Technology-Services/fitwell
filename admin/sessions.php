<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require_admin();

$classes = db()->query('SELECT id, name, duration_min, is_published FROM classes ORDER BY sort_order, name')->fetchAll();
$errors = [];
$form = ['class_id' => '', 'date' => date('Y-m-d', strtotime('+1 day')), 'time' => '18:00', 'duration_min' => '', 'capacity' => 20, 'instructor' => '', 'location' => 'Main Studio', 'repeat' => 1];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'cancel' || $action === 'reinstate') {
        db()->prepare('UPDATE class_sessions SET status = ? WHERE id = ?')->execute([$action === 'cancel' ? 'cancelled' : 'scheduled', $id]);
        flash('success', $action === 'cancel' ? 'Session cancelled. Booked members will see it marked as cancelled in their account.' : 'Session reinstated.');
        redirect('admin/sessions.php');
    }
    if ($action === 'delete') {
        $st = db()->prepare("SELECT COUNT(*) FROM bookings WHERE session_id = ? AND status = 'confirmed'");
        $st->execute([$id]);
        if ((int) $st->fetchColumn() > 0) {
            flash('error', 'This session has bookings — cancel it instead of deleting it.');
        } else {
            db()->prepare('DELETE FROM class_sessions WHERE id = ?')->execute([$id]);
            flash('success', 'Session deleted.');
        }
        redirect('admin/sessions.php');
    }

    if ($action === 'create') {
        $form = array_merge($form, array_intersect_key($_POST, $form));
        $classId = (int) $form['class_id'];
        $class = null;
        foreach ($classes as $c) { if ((int) $c['id'] === $classId) { $class = $c; } }
        $start = strtotime($form['date'] . ' ' . $form['time']);
        $capacity = (int) $form['capacity'];
        $duration = (int) ($form['duration_min'] ?: ($class['duration_min'] ?? 0));
        $repeat = max(1, min(12, (int) $form['repeat']));

        if (!$class) { $errors['class_id'] = 'Choose a class.'; }
        if (!$start || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $form['date']) || !preg_match('/^\d{2}:\d{2}$/', $form['time'])) { $errors['date'] = 'Enter a valid date and time.'; }
        elseif ($start <= time()) { $errors['date'] = 'Sessions must be in the future.'; }
        if ($capacity < 1 || $capacity > 500) { $errors['capacity'] = 'Capacity should be between 1 and 500.'; }
        if ($duration < 10 || $duration > 240) { $errors['duration_min'] = 'Duration should be 10–240 minutes.'; }

        if (!$errors) {
            $ins = db()->prepare("INSERT INTO class_sessions (class_id, starts_at, duration_min, capacity, instructor, location, status, created_at) VALUES (?,?,?,?,?,?,'scheduled',?)");
            for ($i = 0; $i < $repeat; $i++) {
                $ts = strtotime('+' . ($i * 7) . ' days', $start);
                $ins->execute([$classId, date('Y-m-d H:i:s', $ts), $duration, $capacity, trim($form['instructor']) ?: null, trim($form['location']) ?: null, now_sql()]);
            }
            flash('success', sprintf('%d %s of %s scheduled.', $repeat, $repeat === 1 ? 'session' : 'weekly sessions', $class['name']));
            redirect('admin/sessions.php');
        }
    }
}

$showPast = !empty($_GET['past']);
$sql = "SELECT s.*, c.name AS class_name, (SELECT COUNT(*) FROM bookings b WHERE b.session_id = s.id AND b.status = 'confirmed') AS booked
        FROM class_sessions s JOIN classes c ON c.id = s.class_id WHERE s.starts_at " . ($showPast ? '<' : '>=') . " ? ORDER BY s.starts_at " . ($showPast ? 'DESC' : 'ASC') . " LIMIT 120";
$st = db()->prepare($sql);
$st->execute([now_sql()]);
$sessions = $st->fetchAll();

$title = 'Sessions · Admin';
require APP_DIR . '/partials/header.php';
require APP_DIR . '/partials/admin-bar.php';
?>
<section class="section section--tight">
  <div class="container admin-grid">
    <div class="panel" id="new">
      <h1 class="panel__title">Schedule a session</h1>
      <?php if (!$classes): ?>
        <p class="muted">Create a class first, then schedule sessions for it.</p>
        <a class="btn btn--primary" href="<?= url('admin/classes.php') ?>">Create a class</a>
      <?php else: ?>
      <form class="form" method="post" action="<?= url('admin/sessions.php') ?>" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="return" value="admin/sessions.php">
        <div class="field<?= isset($errors['class_id']) ? ' has-error' : '' ?>"><label class="label" for="class_id">Class</label>
          <select class="select" id="class_id" name="class_id"><option value="">Choose a class…</option>
            <?php foreach ($classes as $c): ?><option value="<?= (int) $c['id'] ?>"<?= (string) $form['class_id'] === (string) $c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?> (<?= (int) $c['duration_min'] ?> min)<?= $c['is_published'] ? '' : ' — hidden' ?></option><?php endforeach; ?>
          </select><?= field_error($errors, 'class_id') ?></div>
        <div class="field__row">
          <div class="field<?= isset($errors['date']) ? ' has-error' : '' ?>"><label class="label" for="date">Date</label><input class="input" id="date" name="date" type="date" value="<?= e($form['date']) ?>" min="<?= date('Y-m-d') ?>"></div>
          <div class="field<?= isset($errors['date']) ? ' has-error' : '' ?>"><label class="label" for="time">Start time</label><input class="input" id="time" name="time" type="time" value="<?= e($form['time']) ?>"></div>
        </div>
        <?= field_error($errors, 'date') ?>
        <div class="field__row">
          <div class="field<?= isset($errors['capacity']) ? ' has-error' : '' ?>"><label class="label" for="capacity">Capacity</label><input class="input" id="capacity" name="capacity" type="number" min="1" max="500" value="<?= (int) $form['capacity'] ?>"><?= field_error($errors, 'capacity') ?></div>
          <div class="field<?= isset($errors['duration_min']) ? ' has-error' : '' ?>"><label class="label" for="duration_min">Duration <span class="opt">Default: class</span></label><input class="input" id="duration_min" name="duration_min" type="number" min="10" max="240" value="<?= e($form['duration_min']) ?>"><?= field_error($errors, 'duration_min') ?></div>
        </div>
        <div class="field__row">
          <div class="field"><label class="label" for="instructor">Instructor <span class="opt">Optional</span></label><input class="input" id="instructor" name="instructor" value="<?= e($form['instructor']) ?>"></div>
          <div class="field"><label class="label" for="location">Location <span class="opt">Optional</span></label><input class="input" id="location" name="location" value="<?= e($form['location']) ?>"></div>
        </div>
        <div class="field"><label class="label" for="repeat">Repeat weekly</label>
          <select class="select" id="repeat" name="repeat"><?php for ($i = 1; $i <= 12; $i++): ?><option value="<?= $i ?>"<?= (int) $form['repeat'] === $i ? ' selected' : '' ?>><?= $i === 1 ? 'Just once' : $i . ' weeks' ?></option><?php endfor; ?></select></div>
        <button class="btn btn--primary btn--block" type="submit"><span>Publish session</span></button>
      </form>
      <?php endif; ?>
    </div>

    <div>
      <div class="panel__title" style="margin-bottom:16px">
        <h2 class="h3"><?= $showPast ? 'Past sessions' : 'Upcoming sessions' ?></h2>
        <a class="link-arrow" style="font-size:.85rem" href="<?= url('admin/sessions.php' . ($showPast ? '' : '?past=1')) ?>"><?= $showPast ? 'Show upcoming' : 'Show past' ?></a>
      </div>
      <div class="table-scroll">
        <table class="table">
          <thead><tr><th>When</th><th>Class</th><th>Booked</th><th><span class="sr-only">Actions</span></th></tr></thead>
          <tbody>
          <?php foreach ($sessions as $s): ?>
            <tr>
              <td><strong><?= e(fmt_day($s['starts_at'])) ?></strong><br><span class="muted"><?= e(fmt_time($s['starts_at'])) ?></span></td>
              <td><?= e($s['class_name']) ?><?= $s['status'] !== 'scheduled' ? ' <span class="badge badge--danger">Cancelled</span>' : '' ?></td>
              <td><?= (int) $s['booked'] ?> / <?= (int) $s['capacity'] ?></td>
              <td><div class="actions">
                <a class="btn btn--ghost btn--sm" href="<?= url('admin/session.php?id=' . (int) $s['id']) ?>">Manage</a>
                <?php if (!$showPast): ?>
                  <form method="post" action="<?= url('admin/sessions.php') ?>" data-confirm-form><?= csrf_field() ?><input type="hidden" name="return" value="admin/sessions.php"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                    <?php if ($s['status'] === 'scheduled'): ?>
                      <input type="hidden" name="action" value="<?= (int) $s['booked'] ? 'cancel' : 'delete' ?>"><button class="btn btn--quiet btn--sm" type="submit" data-confirm-label="Confirm"><?= (int) $s['booked'] ? 'Cancel' : 'Delete' ?></button>
                    <?php else: ?>
                      <input type="hidden" name="action" value="reinstate"><button class="btn btn--quiet btn--sm" type="submit" data-confirm-label="Confirm">Reinstate</button>
                    <?php endif; ?>
                  </form>
                <?php endif; ?>
              </div></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$sessions): ?><tr><td colspan="4" class="muted">No sessions to show.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
<?php require APP_DIR . '/partials/footer.php'; ?>
