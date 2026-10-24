<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Entrar | <?= e(setting('site')['title']) ?></title>
<meta name="robots" content="noindex">
<link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin-login">
<main class="login-box">
  <img src="<?= e(media_url(setting('site')['logo'])) ?>" alt="" width="96" height="96">
  <h1>Painel do Arquivo</h1>
  <?php if ($installed): ?><p class="notice notice-success">Instalação concluída! Entre com o e-mail e a senha que você criou.</p><?php endif; ?>
  <?php if ($error): ?><p class="notice notice-error"><?= e($error) ?></p><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <label>E-mail<input type="email" name="email" required autocomplete="username" value="<?= e(input('email')) ?>"></label>
    <label>Senha<input type="password" name="senha" required autocomplete="current-password"></label>
    <button class="a-btn" type="submit">Entrar</button>
  </form>
  <p><a href="/">← Voltar para o site</a></p>
</main>
</body>
</html>
