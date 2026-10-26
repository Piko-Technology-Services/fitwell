<?php
require __DIR__ . '/app/bootstrap.php';
http_response_code(404);
$title = 'Page not found';
$description = 'The page you were looking for has moved. Explore Fitwell Studio classes and the timetable.';
$noindex = true;
require APP_DIR . '/partials/header.php';
?>
<section class="page-hero">
  <div class="container">
    <p class="eyebrow">404</p>
    <h1 class="h1">This page has<br><span class="accent">moved on.</span></h1>
    <p class="lead muted" style="margin:24px 0 32px;max-width:46ch">The link may be old or mistyped. Let's get you back to the good stuff.</p>
    <div class="btn-row">
      <a class="btn btn--primary" href="<?= url('') ?>">Back to home <?= icon('arrow') ?></a>
      <a class="btn btn--ghost" href="<?= url('timetable.php') ?>">See the timetable</a>
    </div>
  </div>
</section>
<?php require APP_DIR . '/partials/footer.php'; ?>
