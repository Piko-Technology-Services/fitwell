<?php
/** @var string $title  @var string $description  @var string $bodyClass */
$title = $title ?? 'Fitwell Studio';
$description = $description ?? cfg('seo.default_description');
$bodyClass = $bodyClass ?? '';
$user = current_user();
$page = current_page();
$navItems = [
    'index.php'      => 'Home',
    'classes.php'    => 'Classes',
    'timetable.php'  => 'Timetable',
    'membership.php' => 'Membership',
    'corporate.php'  => 'Corporate Wellness',
    'about.php'      => 'About',
];
$activeMap = ['class.php' => 'classes.php'];
$active = $activeMap[$page] ?? $page;
if (strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/admin/') !== false) {
    $active = '';
}
$flashes = take_flashes();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
  $siteName = cfg('seo.site_name', 'Fitwell Studio');
  $fullTitle = $title . (str_starts($title, 'Fitwell') ? '' : ' · ' . $siteName);
  if (!isset($canonical)) {
      $canonical = $page === 'index.php' ? '' : $page;
  }
  $canonicalUrl = abs_url(url($canonical));
  $ogImage = $ogImage ?? share_image();
  $ogImageAlt = $ogImageAlt ?? 'Fitwell Studio — Move Together. Live Better.';
  $noindex = !empty($noindex) || strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/admin/') !== false;
?>
<title><?= e($fullTitle) ?></title>
<meta name="description" content="<?= e($description) ?>">
<meta name="robots" content="<?= $noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large' ?>">
<?php if (!$noindex): ?><link rel="canonical" href="<?= e($canonicalUrl) ?>"><?php endif; ?>
<meta name="theme-color" content="#F36C21">
<meta name="author" content="FITWELL STUDIO LIMITED">

<!-- Social sharing (WhatsApp, Facebook, LinkedIn, X) -->
<meta property="og:type" content="<?= e($ogType ?? 'website') ?>">
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:locale" content="<?= e(cfg('seo.locale', 'en_GB')) ?>">
<meta property="og:title" content="<?= e($fullTitle) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:url" content="<?= e($canonicalUrl) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<?php if (strpos($ogImage, 'https://') === 0): ?><meta property="og:image:secure_url" content="<?= e($ogImage) ?>"><?php endif; ?>
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= e($ogImageAlt) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($fullTitle) ?>">
<meta name="twitter:description" content="<?= e($description) ?>">
<meta name="twitter:image" content="<?= e($ogImage) ?>">
<meta name="twitter:image:alt" content="<?= e($ogImageAlt) ?>">
<?php if (cfg('seo.twitter')): ?><meta name="twitter:site" content="<?= e(cfg('seo.twitter')) ?>"><?php endif; ?>

<link rel="icon" type="image/png" sizes="192x192" href="<?= asset('img/favicon.png') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= asset('img/favicon-32.png') ?>">
<link rel="apple-touch-icon" href="<?= asset('img/apple-touch-icon.png') ?>">
<link rel="manifest" href="<?= url('site.webmanifest') ?>">
<?php foreach (structured_data($canonical, $title, $structured ?? []) as $ld): ?>
<script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<?php endforeach; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://images.unsplash.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/fitwell.css') ?>">
<script>document.documentElement.classList.add('js');</script>
<script src="<?= asset('js/fitwell.js') ?>" defer></script>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header" data-header>
  <div class="container site-header__inner">
    <a class="brand" href="<?= url('') ?>" aria-label="Fitwell Studio Limited — home">
      <img class="brand__dark" src="<?= asset('img/logo-horizontal.png') ?>" alt="Fitwell Studio Limited" width="200" height="46">
      <img class="brand__light" src="<?= asset('img/logo-horizontal-light.png') ?>" alt="" width="200" height="46" aria-hidden="true">
    </a>

    <nav class="nav" aria-label="Main">
      <ul class="nav__list">
        <?php foreach ($navItems as $href => $label): ?>
          <li><a class="nav__link<?= $active === $href ? ' is-active' : '' ?>" href="<?= url($href === 'index.php' ? '' : $href) ?>"<?= $active === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="site-header__actions">
      <?php if ($user): ?>
        <a class="nav__login" href="<?= url($user['role'] === 'admin' ? 'admin/index.php' : 'account.php') ?>"><?= icon('user') ?><span><?= e(explode(' ', $user['name'])[0]) ?></span></a>
      <?php else: ?>
        <a class="nav__login" href="<?= url('login.php') ?>">Member Login</a>
      <?php endif; ?>
      <a class="btn btn--primary btn--sm" href="<?= url('timetable.php') ?>">Book a Class</a>
      <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-menu" data-menu-toggle>
        <span class="sr-only">Open menu</span>
        <span class="menu-toggle__bars" aria-hidden="true"><span></span><span></span></span>
      </button>
    </div>
  </div>
</header>

<div class="mobile-menu" id="mobile-menu" data-mobile-menu hidden>
  <nav class="mobile-menu__nav" aria-label="Mobile">
    <?php $i = 0; foreach ($navItems as $href => $label): $i++; ?>
      <a class="mobile-menu__link<?= $active === $href ? ' is-active' : '' ?>" href="<?= url($href === 'index.php' ? '' : $href) ?>" style="--i:<?= $i ?>"><span class="mobile-menu__num">0<?= $i ?></span><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="mobile-menu__foot">
    <a class="btn btn--primary btn--block" href="<?= url('timetable.php') ?>">Book a Class <?= icon('arrow') ?></a>
    <?php if ($user): ?>
      <a class="btn btn--ghost btn--block" href="<?= url($user['role'] === 'admin' ? 'admin/index.php' : 'account.php') ?>">My account</a>
    <?php else: ?>
      <a class="btn btn--ghost btn--block" href="<?= url('login.php') ?>">Member Login</a>
    <?php endif; ?>
    <?php if (whatsapp_url()): ?><a class="btn btn--whatsapp btn--block" href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener"><?= whatsapp_icon() ?> Chat on WhatsApp</a><?php endif; ?>
    <p class="mobile-menu__slogan">Move Together. Live Better.</p>
  </div>
</div>

<?php if ($flashes): ?>
<div class="toasts" role="status" aria-live="polite">
  <?php foreach ($flashes as $flashItem): ?>
    <div class="toast toast--<?= e($flashItem['type']) ?>" data-toast>
      <span class="toast__icon"><?= icon($flashItem['type'] === 'success' ? 'check' : ($flashItem['type'] === 'error' ? 'x' : 'spark')) ?></span>
      <p><?= e($flashItem['message']) ?></p>
      <button type="button" class="toast__close" data-toast-close aria-label="Dismiss"><?= icon('x') ?></button>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<main id="main">
