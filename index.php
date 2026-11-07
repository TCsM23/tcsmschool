<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

$user = current_user();
redirect($user ? home_for($user['user_type']) : url('login.php'));
