<?php
declare(strict_types=1);

// Instalador: rode uma vez. Cria o config.php, as tabelas, o primeiro administrador
// e o conteúdo inicial. Depois de instalado, ele se bloqueia sozinho.

define('ROOT', __DIR__);
define('APP_VERSION', 'install');
$configFile = ROOT . '/config.php';
$lockFile = ROOT . '/storage/installed.lock';

function h($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

if (is_file($lockFile)) {
    http_response_code(403);
    exit('O site já está instalado. Para reinstalar, apague o arquivo storage/installed.lock pelo Gerenciador de Arquivos.');
}

$errors = [];
$cli = PHP_SAPI === 'cli';
$data = $cli ? cli_args($argv) : $_POST;

function cli_args(array $argv): array
{
    $out = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (preg_match('/^--([a-z_]+)=(.*)$/', $arg, $m)) {
            $out[$m[1]] = $m[2];
        }
    }
    return $out;
}

if ($cli || ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $db = [
        'host'     => trim($data['db_host'] ?? 'localhost'),
        'name'     => trim($data['db_name'] ?? ''),
        'user'     => trim($data['db_user'] ?? ''),
        'password' => (string) ($data['db_pass'] ?? ''),
    ];
    $admin = [
        'name'     => trim($data['admin_name'] ?? ''),
        'email'    => mb_strtolower(trim($data['admin_email'] ?? '')),
        'password' => (string) ($data['admin_pass'] ?? ''),
    ];
    $baseUrl = rtrim(trim($data['base_url'] ?? ''), '/');

    if ($db['name'] === '' || $db['user'] === '') $errors[] = 'Informe o nome e o usuário do banco.';
    if ($admin['name'] === '') $errors[] = 'Informe o seu nome.';
    if (!filter_var($admin['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Informe um e-mail válido para o login.';
    if (strlen($admin['password']) < 10) $errors[] = 'A senha do painel precisa ter pelo menos 10 caracteres.';

    if (!$errors) {
        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $db['host'], $db['name']),
                $db['user'],
                $db['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $pdo->exec(file_get_contents(ROOT . '/database/schema.sql'));
        } catch (PDOException $e) {
            $errors[] = 'Não consegui conectar ao banco. Confira os dados. (' . $e->getMessage() . ')';
        }
    }

    if (!$errors) {
        $config = [
            'db'           => $db,
            'base_url'     => $baseUrl,
            'timezone'     => 'America/Sao_Paulo',
            'debug'        => false,
            'notify_email' => $admin['email'],
        ];
        $php = "<?php\n// Gerado pelo instalador em " . date('d/m/Y H:i') . "\nreturn " . var_export($config, true) . ";\n";
        if (file_put_contents($configFile, $php) === false) {
            $errors[] = 'Não consegui gravar o config.php. Verifique as permissões da pasta.';
        }
    }

    if (!$errors) {
        require ROOT . '/app/bootstrap.php';
        require ROOT . '/database/seed.php';
        $adminId = (int) db_val('SELECT id FROM users WHERE email = ?', [$admin['email']]);
        if (!$adminId) {
            $adminId = db_insert('users', [
                'name'          => $admin['name'],
                'slug'          => unique_slug('users', $admin['name']),
                'email'         => $admin['email'],
                'password_hash' => password_hash($admin['password'], PASSWORD_DEFAULT),
                'role'          => 'admin',
                'avatar'        => $data['admin_avatar'] ?? null,
                'active'        => 1,
                'created_at'    => now(),
            ]);
        }
        seed_initial_content($adminId);
        seed_home_sections();
        save_setting_raw('schema_version', 2);
        if (!is_dir(ROOT . '/storage')) mkdir(ROOT . '/storage', 0755, true);
        file_put_contents($lockFile, date('c'));
        if ($cli) {
            echo "Instalado. Admin: {$admin['email']}\n";
            exit(0);
        }
        header('Location: /admin/login?instalado=1');
        exit;
    }
    if ($cli) {
        fwrite(STDERR, implode("\n", $errors) . "\n");
        exit(1);
    }
}

$requirements = [
    'PHP 8.1 ou mais novo'  => version_compare(PHP_VERSION, '8.1', '>='),
    'Extensão PDO MySQL'    => extension_loaded('pdo_mysql'),
    'Extensão GD (imagens)' => extension_loaded('gd'),
    'Extensão mbstring'     => extension_loaded('mbstring'),
    'Extensão intl'         => extension_loaded('intl'),
    'Pasta uploads gravável' => is_writable(ROOT . '/uploads'),
    'Pasta storage gravável' => is_writable(ROOT . '/storage'),
    'Pasta do site gravável (para criar o config.php)' => is_writable(ROOT),
];
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$suggestedUrl = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '');
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Instalar o Arquivo Sapucaí</title>
<meta name="robots" content="noindex">
<style>
body{margin:0;font-family:system-ui,sans-serif;background:#20063A;color:#1E0D06}
main{max-width:640px;margin:40px auto;background:#FDFCF0;border-radius:14px;padding:32px}
h1{margin-top:0}h2{font-size:18px;margin:28px 0 8px}
label{display:block;margin:12px 0 4px;font-weight:600;font-size:14px}
input{width:100%;padding:10px 12px;border:1px solid #c9c4ad;border-radius:8px;font-size:15px;box-sizing:border-box}
small{color:#6b5f58}
button{margin-top:24px;background:#CDF50D;border:0;border-radius:6px;padding:14px 24px;font-weight:700;font-size:16px;cursor:pointer}
.err{background:#FDE2E2;color:#7A1010;padding:12px;border-radius:8px}
ul.req{list-style:none;padding:0}ul.req li{padding:3px 0}
</style>
</head>
<body>
<main>
  <h1>Instalar o Arquivo Sapucaí</h1>
  <p>Preencha uma vez. Os dados do banco estão no hPanel da Hostinger, na área de Bancos de Dados MySQL.</p>
  <ul class="req">
    <?php foreach ($requirements as $label => $ok): ?><li><?= $ok ? '✅' : '❌' ?> <?= h($label) ?></li><?php endforeach; ?>
  </ul>
  <?php if ($errors): ?><div class="err"><?= implode('<br>', array_map('h', $errors)) ?></div><?php endif; ?>
  <form method="post">
    <h2>Banco de dados</h2>
    <label>Servidor do banco</label><input name="db_host" value="<?= h($_POST['db_host'] ?? 'localhost') ?>">
    <label>Nome do banco</label><input name="db_name" value="<?= h($_POST['db_name'] ?? '') ?>" required>
    <label>Usuário do banco</label><input name="db_user" value="<?= h($_POST['db_user'] ?? '') ?>" required>
    <label>Senha do banco</label><input name="db_pass" type="password">
    <h2>Endereço do site</h2>
    <label>Endereço</label><input name="base_url" value="<?= h($_POST['base_url'] ?? $suggestedUrl) ?>">
    <small>Ex.: https://novo.arquivosapucai.com.br (teste) ou https://www.arquivosapucai.com.br</small>
    <h2>Seu acesso ao painel</h2>
    <label>Seu nome</label><input name="admin_name" value="<?= h($_POST['admin_name'] ?? '') ?>" required>
    <label>E-mail de login</label><input name="admin_email" type="email" value="<?= h($_POST['admin_email'] ?? '') ?>" required>
    <label>Senha do painel</label><input name="admin_pass" type="password" minlength="10" required>
    <small>Pelo menos 10 caracteres.</small>
    <button type="submit">Instalar</button>
  </form>
</main>
</body>
</html>
