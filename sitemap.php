<?php
/** XML sitemap — served at /sitemap.xml (see .htaccess). Lists public pages and every published class. */
require __DIR__ . '/app/bootstrap.php';

$pages = [
    ''               => ['1.0', 'weekly'],
    'classes.php'    => ['0.9', 'weekly'],
    'timetable.php'  => ['0.9', 'daily'],
    'membership.php' => ['0.8', 'monthly'],
    'corporate.php'  => ['0.8', 'monthly'],
    'about.php'      => ['0.7', 'monthly'],
    'register.php'   => ['0.6', 'yearly'],
    'privacy.php'    => ['0.2', 'yearly'],
    'terms.php'      => ['0.2', 'yearly'],
];
$today = date('Y-m-d');

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($pages as $path => [$prio, $freq]) {
    $file = ROOT_DIR . '/' . ($path === '' ? 'index.php' : $path);
    $mod = is_file($file) ? date('Y-m-d', filemtime($file)) : $today;
    printf("  <url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%s</priority></url>\n",
        e(abs_url(url($path))), $mod, $freq, $prio);
}
foreach (published_classes() as $c) {
    printf("  <url><loc>%s</loc><lastmod>%s</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>\n",
        e(abs_url(url('class.php?c=' . rawurlencode($c['slug'])))), $today);
}
echo "</urlset>\n";
