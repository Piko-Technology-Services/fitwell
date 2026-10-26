<?php
/** Reusable view components. */

function render_session_row(array $s, string $return, bool $showDate = false): string
{
    [$state, $label] = availability($s);
    $user = current_user();
    $id = (int) $s['id'];
    $meta = [e($s['level'] ?: 'All levels')];
    if (!empty($s['location'])) {
        $meta[] = e($s['location']);
    }
    if (!empty($s['instructor'])) {
        $meta[] = 'with ' . e($s['instructor']);
    }
    $back = $return . (strpos($return, '#') === false ? '#s-' . $id : '');

    ob_start(); ?>
    <li class="session session--<?= $state ?>" id="s-<?= $id ?>" data-day="<?= e(date('Y-m-d', strtotime($s['starts_at']))) ?>">
      <div class="session__time">
        <?php if ($showDate): ?><span class="session__date"><?= e(relative_day($s['starts_at'])) ?></span><?php endif; ?>
        <strong><?= e(fmt_time($s['starts_at'])) ?></strong>
        <span><?= (int) $s['duration_min'] ?> min</span>
      </div>
      <div class="session__info">
        <a class="session__name" href="<?= url('class.php?c=' . rawurlencode($s['class_slug'])) ?>"><?= e($s['class_name']) ?></a>
        <p class="session__meta"><?= implode('<span aria-hidden="true"> · </span>', $meta) ?></p>
      </div>
      <p class="session__avail session__avail--<?= $state ?>"><span class="dot" aria-hidden="true"></span><?= e($label) ?></p>
      <div class="session__action">
        <?php if ($state === 'booked'): ?>
          <?php if (can_cancel($s['starts_at'])): ?>
            <form method="post" action="<?= url('cancel.php') ?>" data-confirm-form>
              <?= csrf_field() ?>
              <input type="hidden" name="booking_id" value="<?= (int) $s['my_booking_id'] ?>">
              <input type="hidden" name="return" value="<?= e($back) ?>">
              <button class="btn btn--quiet btn--sm" type="submit" data-confirm-label="Confirm cancel">Cancel booking</button>
            </form>
          <?php else: ?>
            <span class="badge badge--success"><?= icon('check') ?> Booked</span>
          <?php endif; ?>
        <?php elseif ($state === 'full'): ?>
          <button class="btn btn--disabled btn--sm" type="button" disabled>Full</button>
        <?php elseif (!$user): ?>
          <a class="btn btn--dark btn--sm" href="<?= url('login.php?next=' . rawurlencode($back)) ?>">Book <span class="sr-only"><?= e($s['class_name']) ?> at <?= e(fmt_time($s['starts_at'])) ?></span></a>
        <?php else: ?>
          <form method="post" action="<?= url('book.php') ?>" data-loading-form>
            <?= csrf_field() ?>
            <input type="hidden" name="session_id" value="<?= $id ?>">
            <input type="hidden" name="return" value="<?= e($back) ?>">
            <button class="btn btn--dark btn--sm" type="submit">Book <span class="sr-only"><?= e($s['class_name']) ?> at <?= e(fmt_time($s['starts_at'])) ?></span></button>
          </form>
        <?php endif; ?>
      </div>
    </li>
    <?php
    return ob_get_clean();
}

/** Group sessions by Y-m-d. */
function group_by_day(array $sessions): array
{
    $out = [];
    foreach ($sessions as $s) {
        $out[date('Y-m-d', strtotime($s['starts_at']))][] = $s;
    }
    return $out;
}

function class_tile(array $c, string $variant = '', string $sizes = '(min-width: 900px) 40vw, 100vw', float $ratio = 0.8): string
{
    $href = url('class.php?c=' . rawurlencode($c['slug']));
    ob_start(); ?>
    <article class="class-tile <?= e($variant) ?>" data-reveal data-category="<?= e($c['category']) ?>">
      <a class="class-tile__link" href="<?= $href ?>">
        <div class="class-tile__media"><?= img($c['image'] ?: '', $sizes, ['alt' => ''], $ratio) ?></div>
        <div class="class-tile__body">
          <p class="class-tile__cat"><?= e(category_label($c['category'])) ?></p>
          <h3 class="class-tile__name"><?= e($c['name']) ?></h3>
          <p class="class-tile__summary"><?= e($c['summary']) ?></p>
          <ul class="class-tile__meta">
            <li><?= icon('clock') ?><?= (int) $c['duration_min'] ?> min</li>
            <li><?= icon('level') ?><?= e($c['level']) ?></li>
          </ul>
          <span class="class-tile__cta">View class <?= icon('arrow') ?></span>
        </div>
      </a>
    </article>
    <?php
    return ob_get_clean();
}

function field_error(array $errors, string $key): string
{
    return isset($errors[$key]) ? '<p class="field__error" id="' . e($key) . '-error">' . icon('x') . e($errors[$key]) . '</p>' : '';
}

function db_notice(): string
{
    if (db_ready()) {
        return '';
    }
    return '<div class="notice notice--setup"><strong>Booking isn\'t connected yet.</strong> Run <a href="' . url('install.php') . '">install.php</a> to connect the database and create the classes and timetable.</div>';
}

/**
 * JSON-LD structured data for search engines.
 * Home: the business (HealthClub) + WebSite. Inner pages: breadcrumb trail.
 * Pages can pass extra blocks through $structured.
 */
function structured_data(string $canonical, string $title, array $extra = []): array
{
    $home = abs_url(url(''));
    $blocks = [];
    if ($canonical === '' || $canonical === 'about.php') {
        $org = [
            '@context' => 'https://schema.org',
            '@type'    => 'HealthClub',
            '@id'      => $home . '#business',
            'name'     => 'FITWELL STUDIO LIMITED',
            'alternateName' => 'Fitwell Studio',
            'slogan'   => 'Move Together. Live Better.',
            'description' => cfg('seo.default_description'),
            'url'      => $home,
            'logo'     => abs_url(asset('img/logo-mark.png')),
            'image'    => abs_url(asset('img/og-default.jpg')),
        ];
        if (cfg('company.whatsapp')) {
            $org['telephone'] = cfg('company.phone') ?: cfg('company.whatsapp');
            $org['contactPoint'] = [[
                '@type' => 'ContactPoint',
                'telephone' => cfg('company.whatsapp'),
                'contactType' => 'customer service',
                'availableLanguage' => ['English'],
            ]];
        }
        if (cfg('company.email')) {
            $org['email'] = cfg('company.email');
        }
        if (cfg('company.address')) {
            $org['address'] = ['@type' => 'PostalAddress', 'streetAddress' => cfg('company.address')];
        }
        $same = array_values(array_filter(cfg('social', [])));
        if ($same) {
            $org['sameAs'] = $same;
        }
        $blocks[] = $org;
    }
    if ($canonical === '') {
        $blocks[] = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => cfg('seo.site_name', 'Fitwell Studio'),
            'url' => $home,
        ];
    } else {
        $crumbs = [['Home', $home]];
        if (strpos($canonical, 'class.php') === 0) {
            $crumbs[] = ['Classes', abs_url(url('classes.php'))];
        }
        $crumbs[] = [$title, abs_url(url($canonical))];
        $items = [];
        foreach ($crumbs as $i => $c) {
            $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $c[0], 'item' => $c[1]];
        }
        $blocks[] = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items];
    }
    return array_merge($blocks, $extra);
}
