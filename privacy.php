<?php
require __DIR__ . '/app/bootstrap.php';
$title = 'Privacy Policy';
$description = 'How FITWELL STUDIO LIMITED collects and uses information when you create an account and book classes on this website.';
require APP_DIR . '/partials/header.php';
?>
<section class="page-hero" style="padding-bottom:clamp(40px,5vw,64px)">
  <div class="container"><p class="eyebrow">Legal</p><h1 class="h1">Privacy policy</h1></div>
</section>
<section class="section section--tight">
  <div class="container prose">
    <div class="notice notice--setup"><strong>Draft for review.</strong> This is a starting template written for this website's features. Please have it reviewed and completed before launch.</div>
    <p>FITWELL STUDIO LIMITED (“Fitwell”, “we”) respects your privacy. This policy explains what information this website collects and how it is used.</p>
    <h2>Information we collect</h2>
    <ul>
      <li><strong>Account details</strong> — your name, email address, optional phone number and a securely hashed password.</li>
      <li><strong>Bookings</strong> — the classes and sessions you book or cancel.</li>
      <li><strong>Technical data</strong> — a session cookie needed to keep you logged in.</li>
    </ul>
    <h2>How we use it</h2>
    <ul>
      <li>To create and manage your member account.</li>
      <li>To manage class bookings and capacity, and contact you about sessions you have booked.</li>
      <li>To keep the website secure.</li>
    </ul>
    <h2>Sharing</h2>
    <p>We do not sell your personal information. Studio staff can see booking lists for the sessions they run.</p>
    <h2>Your choices</h2>
    <p>You can ask us to update or delete your account information by contacting us at <?= info('company.email', 'Email address') ?>.</p>
    <h2>Contact</h2>
    <p>FITWELL STUDIO LIMITED · <?= info('company.address', 'Registered address') ?></p>
  </div>
</section>
<?php require APP_DIR . '/partials/footer.php'; ?>
