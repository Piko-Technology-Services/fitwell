<?php
/** robots.txt — served at /robots.txt (see .htaccess) so the sitemap URL is always correct. */
require __DIR__ . '/app/bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
$b = base_path();
echo "User-agent: *\n";
echo "Allow: /\n";
foreach (['/admin/', '/app/', '/account.php', '/login.php', '/book.php', '/cancel.php', '/logout.php', '/install.php'] as $p) {
    echo 'Disallow: ' . $b . $p . "\n";
}
echo "\nSitemap: " . abs_url(url('sitemap.xml')) . "\n";
