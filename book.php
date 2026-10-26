<?php
require __DIR__ . '/app/bootstrap.php';

require_post();
verify_csrf();
$return = safe_next($_POST['return'] ?? null, 'timetable.php');
$user = require_login($return);

[$ok, $message] = book_session((int) $user['id'], (int) ($_POST['session_id'] ?? 0));
flash($ok ? 'success' : 'error', $message);
redirect($return);
