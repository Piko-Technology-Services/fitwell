<?php
require __DIR__ . '/app/bootstrap.php';

$title = 'About Us';
$description = 'The Fitwell Studio story: a welcoming aerobics and group fitness studio built on energy, inclusion and the power of moving together. Meet our values and community.';
$instructors = cfg('instructors', []);

require APP_DIR . '/partials/header.php';
?>

<section class="page-hero page-hero--image">
  <div class="container">
    <div class="page-hero__grid">
      <div class="hero-anim">
        <p class="eyebrow">About Fitwell</p>
        <h1 class="h1">Move together.<br><span class="accent">Live better.</span></h1>
      </div>
      <p class="lead hero-anim">Fitwell Studio exists for one simple reason: people feel better when they move — and even better when they move together.</p>
    </div>
  </div>
</section>

<section class="section">
  <div class="container split">
    <div data-reveal>
      <p class="eyebrow">Our story</p>
      <p class="statement">We started Fitwell to make fitness feel less like a chore and more like <em>the best part of your day.</em></p>
    </div>
    <div data-reveal style="--d:.1s">
      <h2 class="h3">What if we just got started?</h2>
      <p>We often think that sport is not for everyone.</p>
      <p class="lead" style="color:var(--text)"><em>Not at all! At Fitwell Studio, we want to welcome you in a warm and encouraging atmosphere. With our dynamic and fun classes, losing weight is a pleasure. We strive to be innovative, creative and ready to take you on board the Fitwell journey.</em></p>
      <p>Our specialty is aerobics and group fitness, but also diverse and rhythmic workouts, with varied sessions tailored to your goals.</p>
      <p>Come and share enjoyable moments with us.</p>
      <p>Whether you are joining your very first class or coming back after a long break, you will find a community that wants to see you thrive.</p>
      <p>We also offer our services to companies, supporting organizations during their team buildings and other events, for healthier and more connected teams thanks to our corporate wellness programs.</p>
      <p><em>Make the move, Embrace the Change.</em></p>
      <p>Come and join, your place is waiting for you.</p>
    </div>
  </div>
</section>

<section class="section section--off">
  <div class="container">
    <div class="section-head">
      <div class="section-head__text" data-reveal>
        <p class="eyebrow">What we stand for</p>
        <h2 class="h2">Our values</h2>
      </div>
    </div>
    <div class="values">
      <div class="value" data-reveal><?= icon('heart') ?><h3>Everyone belongs</h3><p>All ages, shapes and fitness levels. No judgement, just encouragement.</p></div>
      <div class="value" data-reveal style="--d:.06s"><?= icon('sun') ?><h3>Energy is contagious</h3><p>Great music, great coaching and a room full of people lifting each other up.</p></div>
      <div class="value" data-reveal style="--d:.12s"><?= icon('calendar') ?><h3>Consistency over perfection</h3><p>Small, regular steps create lasting change. We celebrate showing up.</p></div>
      <div class="value" data-reveal style="--d:.18s"><?= icon('users') ?><h3>Stronger together</h3><p>Community turns a workout into something you look forward to.</p></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div class="section-head__text" data-reveal>
        <p class="eyebrow">Our instructors</p>
        <h2 class="h2">Coaches who make every class count.</h2>
        <p class="lead">Fitwell instructors lead with energy, clear cues and genuine care — offering options for every level so nobody gets left behind.</p>
      </div>
    </div>
    <?php if ($instructors): ?>
      <div class="team">
        <?php foreach ($instructors as $i => $m): ?>
          <article class="member" data-reveal style="--d:<?= $i * .06 ?>s">
            <figure><?= img($m['image'] ?? 'coach', '(min-width: 900px) 25vw, 50vw', ['alt' => $m['name']], 0.8, 800) ?></figure>
            <h3><?= e($m['name']) ?></h3>
            <p class="muted" style="margin:4px 0 8px"><?= e($m['role'] ?? '') ?></p>
            <?php if (!empty($m['bio'])): ?><p class="muted"><?= e($m['bio']) ?></p><?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="split" style="align-items:center">
        <div class="split__media" data-reveal="img"><?= img('coach', '(min-width: 900px) 45vw, 100vw', [], 0.87) ?></div>
        <div class="team-empty" data-reveal>
          <div class="team-empty__avatars" aria-hidden="true"><span><?= icon('user') ?></span><span><?= icon('user') ?></span><span><?= icon('user') ?></span></div>
          <div>
            <h3 class="h3" style="margin-bottom:6px">Meet the team — coming soon</h3>
            <p><?= placeholder('Instructor profiles') ?> Add names, photos and short bios in <code>app/config.php</code> to show them here.</p>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section section--off">
  <div class="container">
    <div class="community__head" data-reveal>
      <p class="eyebrow">Community</p>
      <h2 class="h1"><span>Every move counts.</span><span class="accent">Every person belongs.</span></h2>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cta-panel" data-reveal>
      <div><h2>Come move with us.</h2><p>Your first class is the hardest step — and the best one. We'll be there to welcome you.</p></div>
      <div class="btn-row">
        <a class="btn btn--dark btn--lg" href="<?= url('timetable.php') ?>">Book a Class <?= icon('arrow') ?></a>
        <a class="btn btn--ghost btn--lg" href="<?= url('register.php') ?>">Join Fitwell</a>
      </div>
    </div>
  </div>
</section>

<?php require APP_DIR . '/partials/footer.php'; ?>
