<?php
require __DIR__ . '/app/bootstrap.php';

$title = 'Timetable';
$description = 'Fitwell Studio class timetable: see upcoming aerobics, dance cardio and group fitness sessions, check live availability and book your spot online.';
$canonical = 'timetable.php';

$maxWeeks = max(1, (int) ceil(cfg('booking_window_days', 28) / 7));
$week = max(0, min($maxWeeks - 1, (int) ($_GET['w'] ?? 0)));
$start = strtotime('today +' . ($week * 7) . ' days');
$end = strtotime('+7 days', $start);
$from = $week === 0 ? now_sql() : date('Y-m-d H:i:s', $start);
$sessions = upcoming_sessions($from, date('Y-m-d H:i:s', $end));
$byDay = group_by_day($sessions);
$return = 'timetable.php' . ($week ? '?w=' . $week : '');

// Default to the first day that has classes (week view shows all if JS is off)
$initial = $byDay ? array_key_first($byDay) : 'all';

require APP_DIR . '/partials/header.php';
?>

<section class="page-hero" style="padding-bottom:clamp(40px,5vw,72px)">
  <div class="container page-hero__grid">
    <div class="hero-anim">
      <p class="eyebrow">Timetable</p>
      <h1 class="h1">Pick a time.<br><span class="accent">Show up. Shine.</span></h1>
    </div>
    <div class="hero-anim">
      <p class="lead">Reserve your spot in seconds. Bookings can be cancelled up to <?= (int) cfg('cancel_cutoff_hours', 2) ?> hours before class<?= current_user() ? '' : ' — just log in or create a free account to book' ?>.</p>
    </div>
  </div>
</section>

<div class="tt-toolbar">
  <div class="container tt-toolbar__inner">
    <div class="week-nav" aria-label="Change week">
      <?php if ($week > 0): ?><a href="<?= url('timetable.php' . ($week - 1 ? '?w=' . ($week - 1) : '')) ?>" aria-label="Previous week"><?= icon('arrow-left') ?></a><?php else: ?><span aria-hidden="true"><?= icon('arrow-left') ?></span><?php endif; ?>
      <?php if ($week < $maxWeeks - 1): ?><a href="<?= url('timetable.php?w=' . ($week + 1)) ?>" aria-label="Next week"><?= icon('arrow') ?></a><?php else: ?><span aria-hidden="true"><?= icon('arrow') ?></span><?php endif; ?>
    </div>
    <div class="days" data-days data-initial="<?= e($initial) ?>" role="group" aria-label="Choose a day">
      <button class="day-btn day-btn--all" type="button" data-day="all" aria-pressed="<?= $initial === 'all' ? 'true' : 'false' ?>"><strong>All week</strong></button>
      <?php for ($d = 0; $d < 7; $d++): $ts = strtotime("+$d days", $start); $key = date('Y-m-d', $ts); $has = isset($byDay[$key]); ?>
        <button class="day-btn<?= $has ? '' : ' is-empty' ?>" type="button" data-day="<?= $key ?>" aria-pressed="<?= $initial === $key ? 'true' : 'false' ?>" aria-label="<?= e(date('l j F', $ts)) ?><?= $has ? ', ' . count($byDay[$key]) . ' classes' : ', no classes' ?>">
          <small><?= e($key === date('Y-m-d') ? 'Today' : date('D', $ts)) ?></small>
          <strong><?= date('j', $ts) ?></strong>
          <i aria-hidden="true"></i>
        </button>
      <?php endfor; ?>
    </div>
  </div>
</div>

<section class="section" style="padding-top:clamp(40px,5vw,72px)">
  <div class="container container--narrow">
    <?= db_notice() ?>
    <p class="muted" style="margin-bottom:32px;font-size:.95rem"><?= e(date('j M', $start)) ?> – <?= e(date('j M Y', strtotime('-1 day', $end))) ?></p>

    <?php foreach ($byDay as $day => $rows): ?>
      <div class="tt-day" data-day-block="<?= e($day) ?>">
        <div class="tt-day__head">
          <h2 class="tt-day__title"><?= e(relative_day($day)) ?><span><?= e(date('j F', strtotime($day))) ?></span></h2>
          <span class="tt-day__count"><?= count($rows) ?> <?= count($rows) === 1 ? 'class' : 'classes' ?></span>
        </div>
        <ul class="sessions">
          <?php foreach ($rows as $s) echo render_session_row($s, $return); ?>
        </ul>
      </div>
    <?php endforeach; ?>

    <div class="empty" data-day-empty<?= $byDay ? ' hidden' : '' ?>>
      <div class="empty__icon"><?= icon('calendar') ?></div>
      <h3><?= $byDay ? 'Rest day' : 'No classes scheduled this week' ?></h3>
      <p><?= $byDay ? 'No classes on this day. Choose another day to find your next session.' : 'New sessions are published regularly. Try the next week or check back soon.' ?></p>
      <?php if (!$byDay && $week < $maxWeeks - 1): ?><a class="btn btn--dark btn--sm" href="<?= url('timetable.php?w=' . ($week + 1)) ?>">Next week <?= icon('arrow') ?></a><?php endif; ?>
    </div>
  </div>
</section>

<?php require APP_DIR . '/partials/footer.php'; ?>
