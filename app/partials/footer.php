<?php $footerClasses = published_classes(); ?>
</main>

<footer class="site-footer">
  <div class="container">
    <div class="site-footer__top">
      <div class="site-footer__brand">
        <img src="<?= asset('img/logo-horizontal.png') ?>" alt="Fitwell Studio Limited" width="220" height="50" loading="lazy">
        <p class="site-footer__statement">Energising aerobics and group fitness for everyone — and wellbeing programmes that bring teams together.</p>
        <p class="site-footer__slogan">Move Together. <span>Live Better.</span></p>
      </div>

      <div class="site-footer__cols">
        <div>
          <h2 class="site-footer__title">Explore</h2>
          <ul>
            <li><a href="<?= url('classes.php') ?>">Classes</a></li>
            <li><a href="<?= url('timetable.php') ?>">Timetable</a></li>
            <li><a href="<?= url('membership.php') ?>">Membership</a></li>
            <li><a href="<?= url('about.php') ?>">About</a></li>
            <li><a href="<?= url('login.php') ?>">Member Login</a></li>
          </ul>
        </div>
        <div>
          <h2 class="site-footer__title">Classes</h2>
          <ul>
            <?php if ($footerClasses): foreach (array_slice($footerClasses, 0, 5) as $fc): ?>
              <li><a href="<?= url('class.php?c=' . rawurlencode($fc['slug'])) ?>"><?= e($fc['name']) ?></a></li>
            <?php endforeach; else: ?>
              <li><a href="<?= url('classes.php') ?>">All classes</a></li>
            <?php endif; ?>
          </ul>
        </div>
        <div>
          <h2 class="site-footer__title">For organisations</h2>
          <ul>
            <li><a href="<?= url('corporate.php') ?>">Corporate Wellness</a></li>
            <li><a href="<?= url('corporate.php#enquire') ?>">Make an enquiry</a></li>
          </ul>
        </div>
        <div>
          <h2 class="site-footer__title">Contact</h2>
          <ul class="site-footer__contact">
            <?php if (whatsapp_url()): ?><li><?= whatsapp_icon('icon') ?><span><a href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener">WhatsApp <?= e(cfg('company.whatsapp_display', cfg('company.whatsapp'))) ?></a></span></li><?php endif; ?>
            <li><?= icon('pin') ?><span><?= info('company.address', 'Studio address') ?></span></li>
            <li><?= icon('phone') ?><span><?= info('company.phone', 'Phone number') ?></span></li>
            <li><?= icon('mail') ?><span><?php if (cfg('company.email')): ?><a href="mailto:<?= e(cfg('company.email')) ?>"><?= e(cfg('company.email')) ?></a><?php else: ?><?= placeholder('Email address') ?><?php endif; ?></span></li>
            <li><?= icon('clock') ?><span><?= info('company.hours', 'Opening hours') ?></span></li>
          </ul>
        </div>
      </div>
    </div>

    <div class="site-footer__bottom">
      <ul class="social" aria-label="Social media">
        <?php $anySocial = false; foreach (cfg('social', []) as $name => $link): if ($link): $anySocial = true; ?>
          <li><a href="<?= e($link) ?>" rel="noopener" target="_blank"><?= e($name) ?></a></li>
        <?php endif; endforeach; if (!$anySocial): ?>
          <li><?= placeholder('Social media links') ?></li>
        <?php endif; ?>
      </ul>
      <ul class="legal">
        <li><a href="<?= url('privacy.php') ?>">Privacy Policy</a></li>
        <li><a href="<?= url('terms.php') ?>">Terms &amp; Conditions</a></li>
        <li>&copy; <?= date('Y') ?> FITWELL STUDIO LIMITED</li>
      </ul>
    </div>
  </div>
</footer>

<?php if (whatsapp_url() && strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/admin/') === false): ?>
<a class="wa-float" href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener" aria-label="Chat with Fitwell on WhatsApp (opens WhatsApp)">
  <?= whatsapp_icon('wa-float__icon') ?><span class="wa-float__label">Chat with us</span>
</a>
<?php endif; ?>
</body>
</html>
