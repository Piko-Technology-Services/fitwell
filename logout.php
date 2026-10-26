<?php
require __DIR__ . '/app/bootstrap.php';

// Logout must be a POST with a valid token (prevents logout via link/CSRF)
require_post();
verify_csrf();

$_SESSION = [];
session_regenerate_id(true);
flash('success', 'You\'ve been logged out. See you in class soon!');
redirect('');
