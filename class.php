<?php
require __DIR__ . '/app/bootstrap.php';

$class = find_class((string) ($_GET['c'] ?? ''));
if (!$class) {
    http_response_code(404);
    $title = 'Class not found';
    $noindex = true;
    require APP_DIR . '/partials/header.php'; ?>
    <section class="page-hero"><div class="container">
      <p class="eyebrow">404</p>
      <h1 class="h1">That class has<br><span class="accent">moved on.</span></h1>
      <p class="lead" style="margin:24px 0 32px">It may have been renamed or retired. Explore what's on now.</p>
      <a class="btn btn--primary" href="<?= url('classes.php') ?>">Browse classes <?= icon('arrow') ?></a>
    </div></section>
    <?php require APP_DIR . '/partials/footer.php';
    exit;
}

$title = $class['name'];
$description = $class['summary'] . ' ' . (int) $class['duration_min'] . '-minute ' . strtolower(category_label($class['category'])) . ' class, ' . strtolower($class['level']) . '. Book online at Fitwell Studio.';
$canonical = 'class.php?c=' . rawurlencode($class['slug']);
$ogImage = share_image($class['image'] ?: null);
$ogImageAlt = $class['name'] . ' at Fitwell Studio';
$bodyClass = 'has-dark-hero';
$sessions = upcoming_sessions(now_sql(), date('Y-m-d 00:00:00', strtotime('+' . (int) cfg('booking_window_days', 28) . ' days')), (int) $class['id'], 12);
$byDay = group_by_day($sessions);
$return = 'class.php?c=' . rawurlencode($class['slug']);
$others = array_values(array_filter(published_classes(), function ($c) use ($class) { return $c['id'] !== $class['id']; }));

require APP_DIR . '/partials/header.php';
?>

<section class="class-hero">
  <div class="class-hero__media"><?= img($class['image'] ?: '', '100vw', ['loading' => 'eager', 'fetchpriority' => 'high'], 1.8, 2000) ?></div>
  <div class="container hero-anim">
    <nav class="breadcrumb" aria-label="Breadcrumb"><a href="<?= url('classes.php') ?>">Classes</a><span aria-hidden="true">/</span><span><?= e(category_label($class['category'])) ?></span></nav>
    <h1 class="display" style="font-size:clamp(2.8rem,1.6rem + 5.4vw,6.4rem)"><?= e($class['name']) ?></h1>
    <ul class="facts">
      <li class="fact"><?= icon('clock') ?><?= (int) $class['duration_min'] ?> minutes</li>
      <li class="fact"><?= icon('level') ?><?= e($class['level']) ?></li>
      <?php if ($class['intensity']): ?><li class="fact"><?= icon('pulse') ?><?= e($class['intensity']) ?> intensity</li><?php endif; ?>
    </ul>
    <a class="btn btn--primary btn--lg" href="#sessions">Book this class <?= icon('arrow') ?></a>
  </div>
</section>

<section class="section">
  <div class="container detail">
    <div class="detail__text" data-reveal>
      <p class="eyebrow">About the class</p>
      <p class="lead"><?= e($class['summary']) ?></p>
      <?php foreach (preg_split("/\n\s*\n/", trim((string) $class['description'])) as $para): if (trim($para) === '') continue; ?>
        <p><?= nl2br(e(trim($para))) ?></p>
      <?php endforeach; ?>
      <?php if (whatsapp_url()): ?>
      <a class="wa-inline" href="<?= e(whatsapp_url("Hi Fitwell! I have a question about the " . $class['name'] . " class.")) ?>" target="_blank" rel="noopener"><span class="wa-inline__icon"><?= whatsapp_icon() ?></span><span><strong>Questions about this class?</strong><br>Chat with us on WhatsApp</span><?= icon('arrow') ?></a>
      <?php endif; ?>
      <h2 class="h3" style="margin-top:40px">Before you come</h2>
      <ul class="checklist">
        <li><?= icon('check') ?><span>Comfortable workout clothes and supportive trainers</span></li>
        <li><?= icon('check') ?><span>A water bottle and a small towel</span></li>
        <li><?= icon('check') ?><span>Arrive a few minutes early if it's your first class</span></li>
        <li><?= icon('check') ?><span>Let your instructor know about any injuries</span></li>
      </ul>
    </div>

    <div id="sessions" data-reveal style="--d:.1s">
      <div class="tt-day__head" style="border:0;padding:0;margin-bottom:8px">
        <h2 class="h3">Upcoming sessions</h2>
        <a class="link-arrow" href="<?= url('timetable.php') ?>">Full timetable <?= icon('arrow') ?></a>
      </div>
      <?php if ($byDay): foreach ($byDay as $day => $rows): ?>
        <div class="tt-day" style="margin-top:28px">
          <div class="tt-day__head"><h3 class="tt-day__title"><?= e(relative_day($day)) ?><span><?= e(date('j F', strtotime($day))) ?></span></h3></div>
          <ul class="sessions"><?php foreach ($rows as $s) echo render_session_row($s, $return); ?></ul>
        </div>
      <?php endforeach; else: ?>
        <div class="empty" style="margin-top:24px">
          <div class="empty__icon"><?= icon('calendar') ?></div>
          <h3>No sessions scheduled yet</h3>
          <p>We're planning the next dates for this class. Take a look at what else is on this week.</p>
          <a class="btn btn--dark btn--sm" href="<?= url('timetable.php') ?>">See timetable</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if ($others): ?>
<section class="section section--off">
  <div class="container">
    <div class="section-head">
      <div class="section-head__text"><p class="eyebrow">Keep exploring</p><h2 class="h2">You might also love</h2></div>
      <a class="link-arrow" href="<?= url('classes.php') ?>">All classes <?= icon('arrow') ?></a>
    </div>
    <div class="class-grid class-grid--even" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr))">
      <?php foreach (array_slice($others, 0, 3) as $c) echo class_tile($c, 'class-tile--even', '(min-width: 900px) 33vw, 100vw', 0.9); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require APP_DIR . '/partials/footer.php'; ?>
