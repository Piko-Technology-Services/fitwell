<?php
require __DIR__ . '/app/bootstrap.php';

$title = 'Fitwell Studio — Aerobics, Group Fitness & Corporate Wellness';
$canonical = '';
$classes = published_classes();
$next = next_session();
$preview = upcoming_sessions(now_sql(), date('Y-m-d 00:00:00', strtotime('+3 days')), null, 8);
$previewDays = group_by_day($preview);
$plans = cfg('memberships', []);

require APP_DIR . '/partials/header.php';
?>

<?php /* ============ HERO ============ */ ?>
<section class="hero" aria-labelledby="hero-title">
  <div class="container hero__inner">
    <div class="hero-anim">
      <p class="eyebrow">Aerobics · Group Fitness · Corporate Wellness</p>
      <h1 class="display hero__title" id="hero-title"><span>Move better.</span><span class="accent">Live better.</span></h1>
      <p class="hero__sub">Find your rhythm. Build your strength. Discover your community.</p>
      <p class="hero__text">Experience energising aerobics and group fitness sessions designed to bring people together through movement, consistency and wellbeing.</p>
      <div class="btn-row">
        <a class="btn btn--primary btn--lg" href="<?= url('classes.php') ?>">Explore Classes <?= icon('arrow') ?></a>
        <a class="btn btn--ghost btn--lg" href="<?= url('register.php') ?>">Join Fitwell</a>
      </div>
      <ul class="hero__meta">
        <li><?= icon('users') ?>Group classes for every level</li>
        <li><?= icon('calendar') ?>Book online in seconds</li>
      </ul>
    </div>

    <div class="hero__media">
      <span class="hero__orb" aria-hidden="true"></span>
      <span class="hero__ring" aria-hidden="true"></span>
      <div class="hero__img-main"><?= img('corporate-outdoor', '(min-width: 900px) 46vw, 100vw', ['loading' => 'eager', 'fetchpriority' => 'high'], 0.8) ?></div>
      <div class="hero__img-small"><?= img('pair', '(min-width: 900px) 18vw, 38vw', [], 0.9, 800) ?></div>
      <?php if ($next): ?>
        <a class="next-card" href="<?= url('timetable.php#s-' . (int) $next['id']) ?>">
          <span class="next-card__label"><span class="live" aria-hidden="true"></span>Next class</span>
          <span class="next-card__name"><?= e($next['class_name']) ?></span>
          <span class="next-card__meta"><?= e(relative_day($next['starts_at'])) ?> · <?= e(fmt_time($next['starts_at'])) ?> · <?= e(availability($next)[1]) ?></span>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php /* ============ TICKER ============ */ ?>
<div class="ticker" aria-hidden="true">
  <div class="ticker__track">
    <?php for ($g = 0; $g < 2; $g++): ?>
      <div class="ticker__group">
        <?php foreach (['Aerobics', 'Dance Fitness', 'Group Fitness', 'Corporate Wellness', 'Move Together', 'Live Better'] as $word): ?>
          <span class="ticker__item"><?= e($word) ?><span class="ticker__dot"></span></span>
        <?php endforeach; ?>
      </div>
    <?php endfor; ?>
  </div>
</div>

<?php /* ============ BRAND INTRO ============ */ ?>
<section class="section" aria-labelledby="intro-title">
  <div class="container">
    <div class="intro">
      <div class="intro__copy" data-reveal>
        <p class="eyebrow">Why Fitwell</p>
        <h2 class="h2 intro__title" id="intro-title">More than a workout. <em>A community.</em></h2>
        <p class="lead">There's a moment in every great class when the music lifts, the room moves as one and you forget you were ever nervous to walk in.</p>
        <p>That's what we build at Fitwell. Our aerobics and group sessions are led with energy and care, so first-timers feel welcome and regulars keep finding new ways to grow. You'll learn names, share the hard reps, laugh through the last song — and leave feeling better than when you arrived.</p>
        <a class="link-arrow" href="<?= url('about.php') ?>">Our story <?= icon('arrow') ?></a>
      </div>
      <div class="intro__media">
        <figure data-reveal="img"><?= img('kneeling', '(min-width: 900px) 26vw, 55vw', [], 0.75, 1200) ?></figure>
        <figure data-reveal="img" style="--d:.15s"><?= img('pair', '(min-width: 900px) 22vw, 45vw', [], 0.71, 1200) ?></figure>
        <div class="intro__badge" aria-hidden="true">
          <svg viewBox="0 0 120 120"><defs><path id="circ" d="M60,60 m-46,0 a46,46 0 1,1 92,0 a46,46 0 1,1 -92,0"/></defs><text font-family="Montserrat" font-weight="700" font-size="10.5" letter-spacing="3.2" fill="#201C26"><textPath href="#circ">MOVE TOGETHER · LIVE BETTER · </textPath></text></svg>
          <img src="<?= asset('img/logo-mark.png') ?>" alt="" width="44" height="44" style="width:44px;height:auto">
        </div>
      </div>
    </div>

    <div class="pillars">
      <div class="pillar" data-reveal><p class="pillar__num">01</p><h3>Find your rhythm</h3><p>Music-led aerobics and dance sessions that make it easy to keep moving and hard to stop smiling.</p></div>
      <div class="pillar" data-reveal style="--d:.1s"><p class="pillar__num">02</p><h3>Build your strength</h3><p>Progressive group workouts with clear coaching, so you get stronger at your own pace.</p></div>
      <div class="pillar" data-reveal style="--d:.2s"><p class="pillar__num">03</p><h3>Discover your community</h3><p>Familiar faces, shared goals and a studio where showing up is always celebrated.</p></div>
    </div>
  </div>
</section>

<?php /* ============ CLASSES ============ */ ?>
<section class="section section--off" aria-labelledby="classes-title">
  <div class="container">
    <div class="section-head">
      <div class="section-head__text" data-reveal>
        <p class="eyebrow">Classes &amp; experiences</p>
        <h2 class="h2" id="classes-title">Choose how you want to move.</h2>
        <p class="lead">From high-energy aerobics to beginner-friendly sessions, every class is coached, social and designed to leave you buzzing.</p>
      </div>
      <a class="link-arrow" href="<?= url('classes.php') ?>" data-reveal>View all classes <?= icon('arrow') ?></a>
    </div>

    <?= db_notice() ?>
    <?php if ($classes): ?>
      <div class="class-grid">
        <?php foreach (array_slice($classes, 0, 5) as $i => $c): ?>
          <?= class_tile($c, '', $i === 0 ? '(min-width: 900px) 56vw, 100vw' : '(min-width: 900px) 40vw, 100vw', $i === 0 ? 0.85 : 1.25) ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php /* ============ TIMETABLE PREVIEW ============ */ ?>
<section class="section" aria-labelledby="tt-title">
  <div class="container tt-preview">
    <div class="tt-preview__aside" data-reveal>
      <p class="eyebrow">This week</p>
      <h2 class="h2" id="tt-title">Your next class is closer than you think.</h2>
      <p>Pick a time that suits you, reserve your spot in seconds and just turn up ready to move.</p>
      <div class="btn-row">
        <a class="btn btn--dark" href="<?= url('timetable.php') ?>">Full timetable <?= icon('arrow') ?></a>
      </div>
    </div>
    <div data-reveal style="--d:.1s">
      <?php if ($previewDays): foreach ($previewDays as $day => $rows): ?>
        <div class="tt-day">
          <div class="tt-day__head">
            <h3 class="tt-day__title"><?= e(relative_day($day)) ?><span><?= e(date('j F', strtotime($day))) ?></span></h3>
            <span class="tt-day__count"><?= count($rows) ?> <?= count($rows) === 1 ? 'class' : 'classes' ?></span>
          </div>
          <ul class="sessions">
            <?php foreach ($rows as $s) echo render_session_row($s, 'index.php'); ?>
          </ul>
        </div>
      <?php endforeach; else: ?>
        <div class="empty">
          <div class="empty__icon"><?= icon('calendar') ?></div>
          <h3>New sessions are on the way</h3>
          <p>The next few days are still being scheduled. Check the full timetable for upcoming classes.</p>
          <a class="btn btn--dark btn--sm" href="<?= url('timetable.php') ?>">See timetable</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php /* ============ MEMBERSHIP ============ */ ?>
<section class="section section--off" aria-labelledby="plans-title">
  <div class="container">
    <div class="section-head">
      <div class="section-head__text" data-reveal>
        <p class="eyebrow">Membership</p>
        <h2 class="h2" id="plans-title">Your next chapter starts here.</h2>
        <p class="lead">Simple options, no complicated rules. Start with one class or commit to a month of momentum.</p>
      </div>
      <a class="link-arrow" href="<?= url('membership.php') ?>" data-reveal>Compare options <?= icon('arrow') ?></a>
    </div>
    <div class="plans">
      <?php foreach ($plans as $i => $p): ?>
        <article class="plan<?= $p['featured'] ? ' plan--featured' : '' ?>" data-reveal style="--d:<?= $i * .08 ?>s">
          <?php if ($p['featured']): ?><span class="plan__flag">Most popular</span><?php endif; ?>
          <h3 class="plan__name"><?= e($p['name']) ?></h3>
          <p class="plan__tagline"><?= e($p['tagline']) ?></p>
          <div class="price"><?= price_label($p) ?><span class="price__period"><?= e($p['period']) ?></span></div>
          <ul class="plan__list">
            <?php foreach ($p['benefits'] as $b): ?><li><?= icon('check') ?><?= e($b) ?></li><?php endforeach; ?>
          </ul>
          <a class="btn <?= $p['featured'] ? 'btn--primary' : 'btn--ghost' ?> btn--block" href="<?= url('register.php') ?>">Become a Member</a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php /* ============ CORPORATE ============ */ ?>
<section class="section corp" aria-labelledby="corp-title">
  <div class="container corp__grid">
    <div data-reveal>
      <p class="eyebrow eyebrow--light">Corporate wellness</p>
      <h2 class="h2" id="corp-title">A stronger team starts with wellbeing.</h2>
      <p class="corp__lead">Bring your people together through engaging group fitness and corporate wellness experiences designed around your organisation.</p>
      <div class="benefits">
        <div class="benefit"><span class="benefit__icon"><?= icon('users') ?></span><div><h3>Team connection</h3><p>Shared sessions that build trust and camaraderie across departments.</p></div></div>
        <div class="benefit"><span class="benefit__icon"><?= icon('pulse') ?></span><div><h3>Active workplace culture</h3><p>Make movement a normal, enjoyable part of the working week.</p></div></div>
        <div class="benefit"><span class="benefit__icon"><?= icon('spark') ?></span><div><h3>Engaging group experiences</h3><p>Fun, inclusive formats people genuinely look forward to.</p></div></div>
      </div>
      <a class="btn btn--light btn--lg" href="<?= url('corporate.php') ?>">Explore Corporate Wellness <?= icon('arrow') ?></a>
    </div>
    <div class="corp__media">
      <figure data-reveal="img"><?= img('hi5', '(min-width: 900px) 44vw, 100vw', [], 0.83) ?></figure>
      <span class="corp__tag">Designed around your team</span>
    </div>
  </div>
</section>

<?php /* ============ COMMUNITY ============ */ ?>
<section class="section" aria-labelledby="community-title">
  <div class="container">
    <div class="community__head" data-reveal>
      <p class="eyebrow">Our community</p>
      <h2 class="h1" id="community-title"><span>Every move counts.</span><span class="accent">Every person belongs.</span></h2>
      <p>You don't need to be an athlete to belong here. Beginners, returners and regulars train side by side — and everyone gets the same warm welcome.</p>
    </div>
  </div>
</section>

<?php /* ============ FINAL CTA ============ */ ?>
<section class="section section--flush-top" aria-labelledby="cta-title">
  <div class="container">
    <div class="cta-panel" data-reveal>
      <div>
        <h2 id="cta-title">Ready to find your fit?</h2>
        <p>Your journey starts with one session. Join the Fitwell community today.</p>
      </div>
      <div class="btn-row">
        <a class="btn btn--dark btn--lg" href="<?= url('timetable.php') ?>">Book Your First Class <?= icon('arrow') ?></a>
        <a class="btn btn--ghost btn--lg" href="<?= url('register.php') ?>">Become a Member</a>
        <?php if (whatsapp_url()): ?><a class="cta-panel__wa" href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener"><?= whatsapp_icon() ?> Questions? Chat with us on WhatsApp</a><?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php require APP_DIR . '/partials/footer.php'; ?>
