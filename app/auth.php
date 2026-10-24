<?php
declare(strict_types=1);

function current_user(): ?array
{
    static $user = false;
    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['user_id'])) {
            $user = db_one('SELECT * FROM users WHERE id = ? AND active = 1', [(int) $_SESSION['user_id']]);
        }
    }
    return $user;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        redirect('/admin/login?volta=' . urlencode($_SERVER['REQUEST_URI'] ?? '/admin'));
    }
    return $user;
}

function require_admin(): array
{
    $user = require_login();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        exit('Apenas administradores podem acessar esta área.');
    }
    return $user;
}

function too_many_login_attempts(): bool
{
    $since = date('Y-m-d H:i:s', time() - 15 * 60);
    return (int) db_val('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND created_at > ?', [client_ip(), $since]) >= 8;
}

function attempt_login(string $email, string $password): bool
{
    $user = db_one('SELECT * FROM users WHERE email = ? AND active = 1', [mb_strtolower($email)]);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        db_insert('login_attempts', ['ip' => client_ip(), 'email' => mb_substr($email, 0, 190), 'created_at' => now()]);
        return false;
    }
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        db_update('users', (int) $user['id'], ['password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    db_update('users', (int) $user['id'], ['last_login_at' => now()]);
    db_exec('DELETE FROM login_attempts WHERE ip = ?', [client_ip()]);
    return true;
}

function logout(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}
