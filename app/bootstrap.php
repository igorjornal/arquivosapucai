<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('APP_VERSION', '0.1.0');

$configFile = ROOT . '/config.php';
if (!is_file($configFile)) {
    header('Location: /install.php');
    exit;
}

$GLOBALS['config'] = require $configFile;

date_default_timezone_set(config('timezone', 'America/Sao_Paulo'));
error_reporting(E_ALL);
ini_set('display_errors', config('debug') ? '1' : '0');
mb_internal_encoding('UTF-8');

require ROOT . '/app/helpers.php';
require ROOT . '/app/db.php';
require ROOT . '/app/settings.php';
require ROOT . '/app/auth.php';
require ROOT . '/app/sanitizer.php';
require ROOT . '/app/view.php';
require ROOT . '/app/content.php';

session_name('arquivo_sess');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => is_https(),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

function config(string $key, $default = null)
{
    $value = $GLOBALS['config'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}
