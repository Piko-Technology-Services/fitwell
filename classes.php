<?php
require __DIR__ . '/app/bootstrap.php';

$title = 'Aerobics & Group Fitness Classes';
$description = 'Explore Fitwell Studio classes: high-energy aerobics, low-impact aerobics, dance cardio, group fitness and weekend specials. Beginner friendly — book your class online.';
$canonical = 'classes.php';
$classes = published_classes();
$cats = cfg('categories', []);
$counts = [];
foreach ($classes as $c) {
    $counts[$c['category']] = ($counts[$c['category']] ?? 0) + 1;
}
$activeCat = isset($_GET['category'], $counts[$_GET['category']]) ? $_GET['category'] : 'all';

require APP_DIR . '/partials/header.php';
?>

<section class="page-hero">
  <div class="container page-hero__grid">
    <div class="hero-anim">
      <p class="eyebrow">Classes &amp; experiences</p>
      <h1 class="h1">Find the class<br>that <span class="accent">moves you.</span></h1>
    </div>
    <p class="lead hero-anim">Every Fitwell class is coached, social and built for real people. Choose your energy, pick a time and come as you are — we'll take care of the rest.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?= db_notice() ?>
    <?php if ($classes): ?>
      <div class="chips chips--scroll" data-filters role="group" aria-label="Filter classes by category" style="margin-bottom:clamp(48px,6vw,88px)">
        <a class="chip" href="<?= url('classes.php') ?>" data-filter="all" aria-pressed="<?= $activeCat === 'all' ? 'true' : 'false' ?>">All classes <span class="chip__count"><?= count($classes) ?></span></a>
        <?php foreach ($cats as $key => $label): if (empty($counts[$key])) continue; ?>
          <a class="chip" href="<?= url('classes.php?category=' . rawurlencode($key)) ?>" data-filter="<?= e($key) ?>" aria-pressed="<?= $activeCat === $key ? 'true' : 'false' ?>"><?= e($label) ?> <span class="chip__count"><?= $counts[$key] ?></span></a>
        <?php endforeach; ?>
      </div>

      <div class="class-list">
        <?php foreach ($classes as $i => $c): $href = url('class.php?c=' . rawurlencode($c['slug'])); ?>
          <article class="class-row" data-filter-item data-category="<?= e($c['category']) ?>"<?= ($activeCat !== 'all' && $c['category'] !== $activeCat) ? ' hidden' : '' ?>>
            <a class="class-row__media" href="<?= $href ?>" data-reveal="img" tabindex="-1" aria-hidden="true">
              <?= img($c['image'] ?: '', '(min-width: 860px) 52vw, 100vw', ['alt' => ''], 1.25) ?>
              <span class="class-row__index"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?> / <?= e(category_label($c['category'])) ?></span>
            </a>
            <div class="class-row__body" data-reveal>
              <h2 class="h2"><a href="<?= $href ?>" style="text-decoration:none"><?= e($c['name']) ?></a></h2>
              <p class="lead" style="color:var(--text)"><?= e($c['summary']) ?></p>
              <ul class="facts">
                <li class="fact"><?= icon('clock') ?><?= (int) $c['duration_min'] ?> minutes</li>
                <li class="fact"><?= icon('level') ?><?= e($c['level']) ?></li>
                <?php if ($c['intensity']): ?><li class="fact"><?= icon('pulse') ?><?= e($c['intensity']) ?> intensity</li><?php endif; ?>
              </ul>
              <div class="btn-row">
                <a class="btn btn--primary" href="<?= url('class.php?c=' . rawurlencode($c['slug']) . '#sessions') ?>">Book class <?= icon('arrow') ?></a>
                <a class="link-arrow" href="<?= $href ?>">View class</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <div class="empty" data-filter-empty hidden>
        <div class="empty__icon"><?= icon('spark') ?></div>
        <h3>Nothing in this category yet</h3>
        <p>New sessions are added regularly — try another category.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section section--flush-top">
  <div class="container">
    <div class="cta-panel" data-reveal>
      <div>
        <h2>Not sure where to start?</h2>
        <p>Every class welcomes beginners. Pick a time that suits you — your instructor will guide you through every move.</p>
      </div>
      <div class="btn-row">
        <a class="btn btn--dark btn--lg" href="<?= url('timetable.php') ?>">See the timetable <?= icon('arrow') ?></a>
      </div>
    </div>
  </div>
</section>

<?php require APP_DIR . '/partials/footer.php'; ?>
