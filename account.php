<?php
require __DIR__ . '/app/bootstrap.php';

$user = require_login('account.php');
$upcoming = member_bookings((int) $user['id'], true);
$past = member_bookings((int) $user['id'], false, 5);
$initials = '';
foreach (array_slice(preg_split('/\s+/', trim($user['name'])), 0, 2) as $part) {
    $initials .= mb_strtoupper(mb_substr($part, 0, 1));
}

$title = 'My Account';
$noindex = true;
require APP_DIR . '/partials/header.php';
?>
<section class="page-hero" style="padding-bottom:clamp(40px,5vw,72px)">
  <div class="container account-head">
    <div class="hero-anim">
      <p class="eyebrow">My account</p>
      <h1 class="h1">Hi, <?= e(explode(' ', $user['name'])[0]) ?>.</h1>
      <p class="lead muted" style="margin-top:16px"><?= $upcoming ? 'You have ' . count($upcoming) . ' upcoming ' . (count($upcoming) === 1 ? 'class' : 'classes') . '. Keep the momentum going.' : 'Ready for your next class?' ?></p>
    </div>
    <div class="btn-row hero-anim">
      <a class="btn btn--primary" href="<?= url('timetable.php') ?>">Book a class <?= icon('arrow') ?></a>
      <?php if ($user['role'] === 'admin'): ?><a class="btn btn--ghost" href="<?= url('admin/index.php') ?>">Studio admin</a><?php endif; ?>
    </div>
  </div>
</section>

<section class="section section--tight">
  <div class="container account-grid">
    <div>
      <h2 class="h3" style="margin-bottom:20px">Upcoming bookings</h2>
      <?php if ($upcoming): ?>
        <ul class="bookings">
          <?php foreach ($upcoming as $b): $cancelled = $b['status'] !== 'scheduled'; ?>
            <li class="booking" id="b-<?= (int) $b['booking_id'] ?>">
              <div class="booking__img"><?= img($b['image'] ?: '', '92px', ['alt' => ''], 1, 480) ?></div>
              <div>
                <p class="booking__when"><?= e(relative_day($b['starts_at'])) ?> · <?= e(fmt_day($b['starts_at'])) ?> · <?= e(fmt_time($b['starts_at'])) ?></p>
                <h3 class="booking__name"><a href="<?= url('class.php?c=' . rawurlencode($b['class_slug'])) ?>" style="text-decoration:none"><?= e($b['class_name']) ?></a></h3>
                <p class="booking__meta"><?= (int) $b['duration_min'] ?> min<?= $b['location'] ? ' · ' . e($b['location']) : '' ?><?= $b['instructor'] ? ' · with ' . e($b['instructor']) : '' ?></p>
              </div>
              <div class="booking__action">
                <?php if ($cancelled): ?>
                  <span class="badge badge--danger">Session cancelled by studio</span>
                <?php elseif (can_cancel($b['starts_at'])): ?>
                  <form method="post" action="<?= url('cancel.php') ?>" data-confirm-form>
                    <?= csrf_field() ?>
                    <input type="hidden" name="booking_id" value="<?= (int) $b['booking_id'] ?>">
                    <input type="hidden" name="return" value="account.php">
                    <button class="btn btn--quiet btn--sm" type="submit" data-confirm-label="Confirm cancel">Cancel booking</button>
                  </form>
                <?php else: ?>
                  <span class="badge badge--success"><?= icon('check') ?> Booked</span>
                <?php endif; ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
        <p class="field__help" style="margin-top:16px">You can cancel up to <?= (int) cfg('cancel_cutoff_hours', 2) ?> hours before a class starts.</p>
      <?php else: ?>
        <div class="empty">
          <div class="empty__icon"><?= icon('calendar') ?></div>
          <h3>No upcoming classes yet</h3>
          <p>Your next session is just a tap away. Browse the timetable and reserve your spot.</p>
          <a class="btn btn--primary" href="<?= url('timetable.php') ?>">Browse timetable <?= icon('arrow') ?></a>
        </div>
      <?php endif; ?>

      <?php if ($past): ?>
        <h2 class="h3" style="margin:56px 0 20px">Recent classes</h2>
        <ul class="bookings">
          <?php foreach ($past as $b): ?>
            <li class="booking booking--past">
              <div class="booking__img"><?= img($b['image'] ?: '', '92px', ['alt' => ''], 1, 480) ?></div>
              <div>
                <p class="booking__when"><?= e(fmt_day($b['starts_at'])) ?> · <?= e(fmt_time($b['starts_at'])) ?></p>
                <h3 class="booking__name"><?= e($b['class_name']) ?></h3>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <aside class="panel panel--off">
      <div style="display:flex;gap:16px;align-items:center;margin-bottom:12px">
        <span class="avatar" aria-hidden="true"><?= e($initials) ?></span>
        <div><h2 class="h3" style="margin:0"><?= e($user['name']) ?></h2><p class="muted mb-0" style="font-size:.9rem"><?= $user['role'] === 'admin' ? 'Studio administrator' : 'Fitwell member' ?></p></div>
      </div>
      <dl class="profile-list">
        <div><dt>Email</dt><dd><?= e($user['email']) ?></dd></div>
        <div><dt>Phone</dt><dd><?= $user['phone'] ? e($user['phone']) : '<span class="muted">Not added</span>' ?></dd></div>
        <div><dt>Member since</dt><dd><?= e(date('j F Y', strtotime($user['created_at']))) ?></dd></div>
      </dl>
      <form method="post" action="<?= url('logout.php') ?>" style="margin-top:20px">
        <?= csrf_field() ?>
        <button class="btn btn--ghost btn--block" type="submit"><?= icon('logout') ?> Log out</button>
      </form>
    </aside>
  </div>
</section>
<?php require APP_DIR . '/partials/footer.php'; ?>
