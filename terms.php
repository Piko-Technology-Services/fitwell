<?php
require __DIR__ . '/app/bootstrap.php';
$title = 'Terms & Conditions';
$description = 'Terms for using the Fitwell Studio website, member accounts and online class bookings.';
require APP_DIR . '/partials/header.php';
?>
<section class="page-hero" style="padding-bottom:clamp(40px,5vw,64px)">
  <div class="container"><p class="eyebrow">Legal</p><h1 class="h1">Terms &amp; conditions</h1></div>
</section>
<section class="section section--tight">
  <div class="container prose">
    <div class="notice notice--setup"><strong>Draft for review.</strong> This is a starting template based on how online booking works on this site. Please have it reviewed and completed before launch.</div>
    <h2>Your account</h2>
    <p>You are responsible for keeping your login details secure and for bookings made from your account. Please provide accurate contact details.</p>
    <h2>Bookings</h2>
    <ul>
      <li>Bookings are subject to availability. Each session has a limited number of places.</li>
      <li>You may cancel a booking from your account up to <?= (int) cfg('cancel_cutoff_hours', 2) ?> hours before the session starts.</li>
      <li>Fitwell may occasionally need to change or cancel a session. We will do our best to let you know as early as possible.</li>
    </ul>
    <h2>Health and safety</h2>
    <p>Please consult a medical professional before starting a new exercise programme if you have any health concerns, and tell your instructor about any injuries or conditions before class.</p>
    <h2>Membership</h2>
    <p>Membership options, prices and payment terms are confirmed by the studio.</p>
    <h2>Contact</h2>
    <p>Questions about these terms? Contact us at <?= info('company.email', 'Email address') ?>.</p>
  </div>
</section>
<?php require APP_DIR . '/partials/footer.php'; ?>
