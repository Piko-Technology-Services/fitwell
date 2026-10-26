<?php
require __DIR__ . '/app/bootstrap.php';

$title = 'Corporate Wellness';
$bodyClass = 'has-dark-hero';
$description = 'Corporate wellness programmes from Fitwell Studio: group fitness sessions, team wellness days and ongoing wellbeing programmes that build connection and an active workplace culture.';
$email = cfg('company.corporate_email') ?: cfg('company.email');
$mailto = $email ? 'mailto:' . $email . '?subject=' . rawurlencode('Corporate wellness enquiry') . '&body=' . rawurlencode("Hello Fitwell team,\n\nOrganisation:\nApproximate team size:\nWhat we're interested in:\nBest contact number:\n\nThank you") : null;

require APP_DIR . '/partials/header.php';
?>

<section class="page-hero page-hero--purple page-hero--image">
  <div class="container">
    <div class="page-hero__grid">
      <div class="hero-anim">
        <p class="eyebrow eyebrow--light">For organisations</p>
        <h1 class="h1">A stronger team<br>starts with <span style="color:var(--orange)">wellbeing.</span></h1>
      </div>
      <div class="hero-anim">
        <p class="lead">Bring your people together through engaging group fitness and corporate wellness experiences designed around your organisation.</p>
        <div class="btn-row" style="margin-top:32px">
          <a class="btn btn--primary btn--lg" href="#enquire">Make an enquiry <?= icon('arrow') ?></a>
          <?php if (whatsapp_url()): ?><a class="btn btn--whatsapp btn--lg" href="<?= e(whatsapp_url("Hi Fitwell! I'm interested in corporate wellness for my organisation.")) ?>" target="_blank" rel="noopener"><?= whatsapp_icon() ?> WhatsApp us</a><?php endif; ?>
        </div>
      </div>
    </div>
    <div class="page-hero__media hero-anim"><?= img('corporate-outdoor', '100vw', ['loading' => 'eager', 'fetchpriority' => 'high'], 2.4, 2000) ?></div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div class="section-head__text" data-reveal>
        <p class="eyebrow">Why it matters</p>
        <h2 class="h2">Wellbeing your people will actually show up for.</h2>
        <p class="lead">Group exercise is one of the simplest ways to connect colleagues, lift energy and make healthy habits stick — because it's more fun together.</p>
      </div>
    </div>
    <div class="features">
      <article class="feature" data-reveal><div class="feature__icon"><?= icon('users') ?></div><h3>Team connection</h3><p>Shared, upbeat sessions break down silos and give colleagues a reason to cheer each other on.</p></article>
      <article class="feature" data-reveal style="--d:.08s"><div class="feature__icon"><?= icon('pulse') ?></div><h3>Active workplace culture</h3><p>Make regular movement part of how your organisation works — not an afterthought.</p></article>
      <article class="feature" data-reveal style="--d:.16s"><div class="feature__icon"><?= icon('spark') ?></div><h3>Engaging group experiences</h3><p>Inclusive, music-led formats that work for every fitness level across your team.</p></article>
    </div>
  </div>
</section>

<section class="section section--off" id="programmes">
  <div class="container">
    <div class="section-head">
      <div class="section-head__text" data-reveal>
        <p class="eyebrow">Programmes</p>
        <h2 class="h2">Flexible formats, built around you.</h2>
        <p class="lead">Every organisation is different. We'll shape the format, timing and frequency to fit your people and your calendar.</p>
      </div>
    </div>
    <div class="class-list" style="gap:clamp(48px,6vw,96px)">
      <article class="class-row">
        <div class="class-row__media" data-reveal="img"><?= img('corporate-outdoor', '(min-width: 860px) 52vw, 100vw', [], 1.25) ?><span class="class-row__index">01 / Team sessions</span></div>
        <div class="class-row__body" data-reveal>
          <h3 class="h2">Group fitness for teams</h3>
          <p>Energising aerobics and group workouts for your colleagues — a regular weekly slot or a one-off session to kick off a new quarter.</p>
          <ul class="checklist"><li><?= icon('check') ?><span>Suitable for mixed fitness levels</span></li><li><?= icon('check') ?><span>Morning, lunchtime or after-work timings</span></li></ul>
        </div>
      </article>
      <article class="class-row">
        <div class="class-row__media" data-reveal="img"><?= img('corporate-outdoor', '(min-width: 860px) 52vw, 100vw', [], 1.25) ?><span class="class-row__index">02 / Wellness events</span></div>
        <div class="class-row__body" data-reveal>
          <h3 class="h2">Wellness days &amp; team events</h3>
          <p>Make your next team day memorable with a high-energy dance fitness or group session that gets everyone moving and laughing together.</p>
          <ul class="checklist"><li><?= icon('check') ?><span>Great for away days and celebrations</span></li><li><?= icon('check') ?><span>Designed for groups of different sizes</span></li></ul>
        </div>
      </article>
      <article class="class-row">
        <div class="class-row__media" data-reveal="img"><?= img('corporate-outdoor', '(min-width: 860px) 52vw, 100vw', [], 1.25) ?><span class="class-row__index">03 / Ongoing programmes</span></div>
        <div class="class-row__body" data-reveal>
          <h3 class="h2">Ongoing wellbeing programmes</h3>
          <p>Build consistency with a recurring programme that helps your people make movement a lasting habit — planned together with your HR or people team.</p>
          <ul class="checklist"><li><?= icon('check') ?><span>Planned around your organisation's goals</span></li><li><?= icon('check') ?><span>Regular check-ins on what's working</span></li></ul>
        </div>
      </article>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head"><div class="section-head__text" data-reveal><p class="eyebrow">How it works</p><h2 class="h2">From first call to first class.</h2></div></div>
    <ol class="steps">
      <li class="step" data-reveal><h3>Tell us about your team</h3><p>Share your goals, team size, location and the times that work best.</p></li>
      <li class="step" data-reveal style="--d:.08s"><h3>We design the programme</h3><p>We'll recommend a format and schedule that fits your people and budget.</p></li>
      <li class="step" data-reveal style="--d:.16s"><h3>Move together</h3><p>Our instructors deliver engaging sessions — you watch the energy grow.</p></li>
    </ol>
  </div>
</section>

<section class="section section--flush-top" id="enquire">
  <div class="container">
    <div class="enquire" data-reveal>
      <div>
        <p class="eyebrow eyebrow--light">Corporate enquiries</p>
        <h2 class="h2" style="margin-bottom:20px">Let's build something great for your people.</h2>
        <p>Tell us a little about your organisation and we'll come back to you with ideas and next steps.</p>
        <div class="btn-row" style="margin-top:16px">
          <?php if (whatsapp_url()): ?><a class="btn btn--whatsapp btn--lg" href="<?= e(whatsapp_url("Hi Fitwell! I'm interested in corporate wellness for my organisation.")) ?>" target="_blank" rel="noopener"><?= whatsapp_icon() ?> Chat on WhatsApp</a><?php endif; ?>
          <?php if ($mailto): ?><a class="btn btn--outline-light btn--lg" href="<?= e($mailto) ?>">Email our corporate team</a><?php endif; ?>
        </div>
      </div>
      <div class="contact-lines">
        <?php if (whatsapp_url()): ?><a class="contact-line" href="<?= e(whatsapp_url("Hi Fitwell! I'm interested in corporate wellness for my organisation.")) ?>" target="_blank" rel="noopener"><?= whatsapp_icon() ?><span>WhatsApp <?= e(cfg('company.whatsapp_display')) ?></span></a><?php endif; ?>
        <?php if ($email): ?>
          <a class="contact-line" href="<?= e($mailto) ?>"><?= icon('mail') ?><span><?= e($email) ?></span></a>
        <?php else: ?>
          <div class="contact-line"><?= icon('mail') ?><span><?= placeholder('Corporate enquiries email') ?></span></div>
        <?php endif; ?>
        <?php if (cfg('company.phone')): ?>
          <a class="contact-line" href="tel:<?= e(preg_replace('/[^\d+]/', '', cfg('company.phone'))) ?>"><?= icon('phone') ?><span><?= e(cfg('company.phone')) ?></span></a>
        <?php else: ?>
          <div class="contact-line"><?= icon('phone') ?><span><?= placeholder('Phone number') ?></span></div>
        <?php endif; ?>
        <div class="contact-line"><?= icon('pin') ?><span><?= info('company.address', 'Studio address') ?></span></div>
      </div>
    </div>
  </div>
</section>

<?php require APP_DIR . '/partials/footer.php'; ?>
