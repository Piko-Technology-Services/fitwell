<?php
/**
 * Copy this file to  app/config.local.php  on the live server (Hostinger)
 * and fill in the values from hPanel → Databases → MySQL Databases.
 * Anything set here overrides app/config.php, and this file is never
 * replaced when you upload a new version of the website.
 */
return [
    'db' => [
        'driver' => 'mysql',
        'host'   => 'localhost',
        'port'   => 3306,
        'name'   => 'u000000000_fitwell',   // Hostinger database name
        'user'   => 'u000000000_fitwell',   // Hostinger database user
        'pass'   => 'CHANGE-ME',            // database password
    ],
    // 'site_url' => 'https://yourdomain.com',
];
