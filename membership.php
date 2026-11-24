<?php
require __DIR__ . '/app/bootstrap.php';

$title = 'Fitness Membership & Prices in Ndola';
$description = 'Fitwell Studio prices in Ndola: a K100 trial session, 12 group sessions (3 days a week) for K650, K1,200 with personal coaching, or 12 private sessions with your own coach for K1,500.';
$plans = cfg('memberships', []);

// Comparison rows: one value per plan, in the same order as 'memberships' in app/config.php
// (true = included, false = not included, text = shown as written)
$compare = [
    'Group classes'                     => ['1 session', '12 sessions', '12 sessions', false],
    'Training days'                     => [false, '3 days a week', '3 days a week', false],
    'Personal coaching'                 => [false, false, '1 session', '12 private sessions'],
    'Dedicated coach'                   => [false, false, false, true],
    'Aerobics, dance & group fitness'   => [true, true, true, false],
    'Member account'                    => [true, true, true, true],
];

$structured = ld_memberships();
require APP_DIR . '/partials/header.php';
?>

<section class="page-hero">
  <div class="container page-hero__grid">
    <div class="hero-anim">
      <p class="eyebrow">Membership</p>
      <h1 class="h1">Your next chapter<br><span class="accent">starts here.</span></h1>
    </div>
    <div class="hero-anim">
      <p class="lead">Whether you're trying your very first class or ready to make movement a habit, there's a simple way to join the Fitwell community.</p>
    </div>
  </div>
</section>

<section class="section section--tight" style="background:var(--off);padding-top:0">
  <div class="container">
    <div class="plans">
      <?php foreach ($plans as $i => $p): ?>
        <article class="plan<?= $p['featured'] ? ' plan--featured' : '' ?>" data-reveal style="--d:<?= $i * .08 ?>s">
          <?php if ($p['featured']): ?><span class="plan__flag">Most popular</span><?php endif; ?>
          <h2 class="plan__name"><?= e($p['name']) ?></h2>
          <p class="plan__tagline"><?= e($p['tagline']) ?></p>
          <div class="price"><?= price_label($p) ?><span class="price__period"><?= e($p['period']) ?></span></div>
          <ul class="plan__list">
            <?php foreach ($p['benefits'] as $b): ?><li><?= icon('check') ?><?= e($b) ?></li><?php endforeach; ?>
          </ul>
          <a class="btn <?= $p['featured'] ? 'btn--primary' : 'btn--ghost' ?> btn--block" href="<?= url('register.php') ?>">Become a Member</a>
        </article>
      <?php endforeach; ?>
    </div>
    <?php if (whatsapp_url()): ?><div class="center" style="margin-top:40px"><a class="btn btn--whatsapp" href="<?= e(whatsapp_url("Hi Fitwell! I'd like to know more about membership options and prices.")) ?>" target="_blank" rel="noopener"><?= whatsapp_icon() ?> Ask about membership on WhatsApp</a></div><?php endif; ?>
    <p class="center muted" style="margin:24px auto 0;font-size:.95rem;max-width:60ch">Create your free account today to book classes online. Payments aren't taken on the website — the studio will confirm membership details with you.</p>
  </div>
</section>

<section class="section">
  <div class="container container--narrow">
    <div class="section-head"><div class="section-head__text" data-reveal><p class="eyebrow">Compare</p><h2 class="h2">What's included</h2></div></div>
    <div class="table-scroll" data-reveal>
      <table class="compare">
        <thead><tr><th scope="col">Feature</th><?php foreach ($plans as $p): ?><th scope="col"><?= e($p['name']) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php foreach ($compare as $label => $vals): ?>
            <tr>
              <th scope="row" style="font-weight:500;font-family:var(--font-body);font-size:.975rem;text-align:left"><?= e($label) ?></th>
              <?php foreach ($vals as $v): ?>
                <td><?php if ($v === true): ?><?= icon('check') ?><span class="sr-only">Included</span><?php elseif ($v === false): ?><span class="no" aria-hidden="true">—</span><span class="sr-only">Not included</span><?php else: ?><?= e($v) ?><?php endif; ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<section class="section section--off">
  <div class="container split">
    <div class="split__media" data-reveal="img"><?= img('kneeling', '(min-width: 900px) 45vw, 100vw', [], 0.87) ?></div>
    <div data-reveal>
      <p class="eyebrow">Joining is simple</p>
      <h2 class="h2" style="margin-bottom:28px">Three steps to your first class.</h2>
      <ol class="steps" style="grid-template-columns:1fr;gap:28px">
        <li class="step"><h3>Create your account</h3><p>Sign up in under a minute with just your name, email and a password.</p></li>
        <li class="step"><h3>Choose a session</h3><p>Browse the timetable and reserve the class and time that suit you.</p></li>
        <li class="step"><h3>Come and move</h3><p>Arrive a few minutes early — we'll welcome you and help you settle in.</p></li>
      </ol>
    </div>
  </div>
</section>

<section class="section">
  <div class="container container--narrow">
    <div class="section-head"><div class="section-head__text" data-reveal><p class="eyebrow">Questions</p><h2 class="h2">Good to know</h2></div></div>
    <div class="faq" data-reveal>
      <details><summary>Do I need any experience?</summary><p>Not at all. Classes are coached for mixed abilities, and instructors offer easier and harder options so you can work at your own level.</p></details>
      <details><summary>How do I book a class?</summary><p>Create a free account, open the timetable and tap “Book” on any session with spaces available. Your upcoming bookings appear in your account.</p></details>
      <details><summary>Can I cancel a booking?</summary><p>Yes — you can cancel from your account up to <?= (int) cfg('cancel_cutoff_hours', 2) ?> hours before the class starts, which frees your spot for someone else.</p></details>
      <details><summary>What should I bring?</summary><p>Comfortable workout clothes, supportive trainers, a water bottle and a small towel.</p></details>
      <details><summary>Do you offer programmes for companies?</summary><p>Yes. Our corporate wellness programmes bring teams together through group fitness. <a href="<?= url('corporate.php') ?>">Explore Corporate Wellness</a>.</p></details>
    </div>
  </div>
</section>

<section class="section section--flush-top">
  <div class="container">
    <div class="cta-panel" data-reveal>
      <div><h2>Ready when you are.</h2><p>Create your free account and book your first session today.</p></div>
      <div class="btn-row"><a class="btn btn--dark btn--lg" href="<?= url('register.php') ?>">Become a Member <?= icon('arrow') ?></a></div>
    </div>
  </div>
</section>

<?php require APP_DIR . '/partials/footer.php'; ?>
