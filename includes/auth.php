<?php
/**
 * Sessions, login state, role checks and CSRF protection.
 * Include this at the top of every page (it pulls in db + helpers).
 */
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => BASE_URL === '' ? '/' : BASE_URL . '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

/** Logged-in user array (id, username, fullname, user_type) or null. */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return (current_user()['user_type'] ?? '') === 'admin';
}

/** Home page for a role. */
function home_for(string $userType): string
{
    return $userType === 'admin' ? url('admin/dashboard.php') : url('student/home.php');
}

/**
 * Check credentials. Returns the user row on success, null on failure.
 * Always runs password_verify so timing doesn't reveal valid usernames.
 */
function attempt_login(string $username, string $password): ?array
{
    $user = db_one(
        'SELECT id, username, password_hash, fullname, user_type FROM users WHERE username = ? LIMIT 1',
        's',
        [$username]
    );
    $hash = $user['password_hash'] ?? '$2y$10$wvszTmv6j1EH0jASehZOQeyPGCdxwd61nbjGul.2kT.G1uey0gg8i';
    if (!password_verify($password, $hash) || $user === null) {
        return null;
    }
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        db_exec('UPDATE users SET password_hash = ? WHERE id = ?', 'si',
            [password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    }
    return $user;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'        => (int) $user['id'],
        'username'  => $user['username'],
        'fullname'  => $user['fullname'],
        'user_type' => $user['user_type'],
    ];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Redirect to login if nobody is signed in. */
function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        redirect(url('login.php'));
    }
    return $user;
}

/** Only allow the given role; others go to their own home page. */
function require_role(string $role): array
{
    $user = require_login();
    if ($user['user_type'] !== $role) {
        redirect(home_for($user['user_type']));
    }
    return $user;
}

/** For student pages: the students row of the logged-in student. */
function current_student(): array
{
    $user = require_role('student');
    $student = db_one('SELECT * FROM students WHERE user_id = ? LIMIT 1', 'i', [$user['id']]);
    if ($student === null) {
        logout_user();
        redirect(url('login.php?error=noprofile'));
    }
    return $student;
}

// --- CSRF -----------------------------------------------------------------

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Hidden input to drop inside every POST form. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Call at the top of every POST handler. Stops the request on mismatch. */
function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('Your session expired. Go back, refresh the page and try again.');
    }
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}
