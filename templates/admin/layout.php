<?php $user = current_user(); ?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Painel') ?> | Painel do Arquivo</title>
<meta name="robots" content="noindex">
<link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin">
<aside class="a-side">
  <a class="a-brand" href="/admin"><img src="<?= e(media_url(setting('site')['logo'])) ?>" alt="" width="40" height="40"> Painel</a>
  <nav>
    <a href="/admin">Início</a>
    <span class="a-soon">Posts <em>etapa 2</em></span>
    <span class="a-soon">Comentários <em>etapa 2</em></span>
    <span class="a-soon">Aparência <em>etapa 2</em></span>
    <a href="/" target="_blank" rel="noopener">Ver o site ↗</a>
  </nav>
  <form method="post" action="/admin/sair"><?= csrf_field() ?><button class="a-link" type="submit">Sair (<?= e($user['name'] ?? '') ?>)</button></form>
</aside>
<main class="a-main">
<?= $content ?>
</main>
</body>
</html>
