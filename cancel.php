<?php
require __DIR__ . '/app/bootstrap.php';

require_post();
verify_csrf();
$return = safe_next($_POST['return'] ?? null, 'account.php');
$user = require_login($return);

[$ok, $message] = cancel_booking((int) $user['id'], (int) ($_POST['booking_id'] ?? 0));
flash($ok ? 'success' : 'error', $message);
redirect($return);
