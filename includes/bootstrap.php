<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

require_once __DIR__ . '/../config.php';

function escape(string|int|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!is_string($submittedToken) || !hash_equals(csrf_token(), $submittedToken)) {
        http_response_code(419);
        exit('Your form session expired. Go back, refresh the page, and try again.');
    }
}

function require_role(string $role): void
{
    if (!in_array($role, ['student', 'admin'], true)) {
        http_response_code(500);
        exit('Invalid access role configuration.');
    }

    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        header('Location: ../login.php');
        exit;
    }

    $userId = filter_var($_SESSION['user']['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($userId === false) {
        unset($_SESSION['user']);
        header('Location: ../login.php');
        exit;
    }

    global $mysqli;
    $statement = $mysqli->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
    $statement->bind_param('i', $userId);
    $statement->execute();
    $account = $statement->get_result()->fetch_assoc();
    if (!$account) {
        unset($_SESSION['user']);
        header('Location: ../login.php');
        exit;
    }

    $_SESSION['user']['role'] = $account['role'];
    if ($account['role'] !== $role) {
        http_response_code(403);
        exit('You do not have permission to view this page.');
    }
}

function set_flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $flash;
}
