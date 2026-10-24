<?php
$user = current_user();
$isAdmin = $user['role'] === 'admin';
$counts = pending_counts();
$here = strtok($_SERVER['REQUEST_URI'] ?? '/admin', '?');
$nav = [
    ['Início', '/admin', 'home', true],
    ['Posts', '/admin/posts', 'doc', true],
    ['Imagens', '/admin/midia', 'image', true],
    ['Comentários', '/admin/comentarios', 'chat', true, $counts['comments']],
    ['Mensagens', '/admin/mensagens', 'mail', true, $counts['messages']],
    ['Newsletter', '/admin/newsletter', 'send', true],
    ['Categorias', '/admin/categorias', 'folder', true],
    ['Tags', '/admin/tags', 'tag', true],
    ['—'],
    ['Conteúdo do site', '/admin/conteudo', 'edit', $isAdmin],
    ['Páginas', '/admin/paginas', 'doc', $isAdmin],
    ['Seções da home', '/admin/secoes', 'layers', $isAdmin],
    ['Banners do topo', '/admin/banners', 'slides', $isAdmin],
    ['Menu', '/admin/menu', 'menu', $isAdmin],
    ['Aparência', '/admin/aparencia', 'palette', $isAdmin],
    ['Autores', '/admin/autores', 'users', $isAdmin],
];
$icons = [
    'home' => '<path d="M3 11 12 4l9 7v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
    'doc' => '<path d="M6 3h8l5 5v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm7 1.5V9h4.5M8 13h8M8 17h6"/>',
    'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 16-5-5-9 9"/>',
    'chat' => '<path d="M4 5h16v11H9l-5 4z"/>',
    'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
    'send' => '<path d="M21 3 10 14M21 3l-7 18-4-7-7-4z"/>',
    'folder' => '<path d="M3 6a1 1 0 0 1 1-1h5l2 2h9a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/>',
    'tag' => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8.5" r="1.5"/>',
    'edit' => '<path d="M4 20h4L19 9l-4-4L4 16zM14 6l4 4"/>',
    'layers' => '<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>',
    'slides' => '<rect x="3" y="6" width="18" height="12" rx="2"/><path d="M8 21h8"/>',
    'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
    'palette' => '<path d="M12 3a9 9 0 1 0 0 18c1.1 0 1.5-.8 1.5-1.5 0-1.4-1-1.7-1-3 0-1 .8-1.5 1.8-1.5H17a4 4 0 0 0 4-4c0-4.4-4-8-9-8z"/><circle cx="7.5" cy="11" r="1"/><circle cx="10" cy="7" r="1"/><circle cx="15" cy="7.5" r="1"/>',
    'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.7-4 3.3-6 6.5-6s5.8 2 6.5 6M16 4.5a3.5 3.5 0 0 1 0 7M18 14c2 .7 3.2 2.6 3.5 6"/>',
];
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Painel') ?> | Painel do Arquivo</title>
<meta name="robots" content="noindex">
<meta name="csrf" content="<?= e(csrf_token()) ?>">
<link rel="icon" href="<?= e(asset('img/favicon.png')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin">
<aside class="a-side" id="a-side">
  <a class="a-brand" href="/admin"><img src="<?= e(media_url(setting('site')['logo'])) ?>" alt="" width="42" height="42"><span>Painel do<br>Arquivo</span></a>
  <nav aria-label="Painel">
    <?php foreach ($nav as $item):
        if ($item[0] === '—') { echo '<hr>'; continue; }
        if (!$item[3]) continue;
        $active = $item[1] === '/admin' ? $here === '/admin' : str_starts_with((string) $here, $item[1]); ?>
      <a href="<?= e($item[1]) ?>"<?= $active ? ' aria-current="page"' : '' ?>>
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" stroke-linecap="round" aria-hidden="true"><?= $icons[$item[2]] ?></svg>
        <span><?= e($item[0]) ?></span>
        <?php if (!empty($item[4])): ?><b class="a-badge"><?= (int) $item[4] ?></b><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="a-side-foot">
    <a class="a-me" href="/admin/perfil"><img src="<?= e(avatar_url($user['avatar'])) ?>" alt="" width="32" height="32"><span><?= e($user['name']) ?><small><?= $isAdmin ? 'Administrador' : 'Autor' ?></small></span></a>
    <a href="/" target="_blank" rel="noopener">Ver o site ↗</a>
    <form method="post" action="/admin/sair"><?= csrf_field() ?><button class="a-link" type="submit">Sair</button></form>
  </div>
</aside>
<div class="a-top">
  <button class="a-burger" type="button" aria-controls="a-side" aria-expanded="false">☰ Menu</button>
  <a href="/admin" class="a-top-brand">Painel do Arquivo</a>
</div>
<main class="a-main">
  <?php
  // Botão Voltar: em telas internas, volta para a lista de onde veio
  $parts = explode('/', trim((string) $here, '/'));
  $backTo = count($parts) > 2 ? '/' . implode('/', array_slice($parts, 0, 2)) : null;
  if (!$backTo && isset($_GET['editar'])) {
      $backTo = $here;
  }
  ?>
  <?php if ($backTo): ?>
    <a class="a-back" href="<?= e($backTo) ?>">← Voltar</a>
  <?php endif; ?>
  <div class="a-toasts" aria-live="polite">
  <?php foreach (take_flashes() as $f): ?>
    <div class="a-toast a-toast-<?= e($f['type']) ?>" role="status">
      <span class="a-toast-icon" aria-hidden="true"><?= $f['type'] === 'success' ? '✓' : '!' ?></span>
      <span><?= e($f['message']) ?></span>
      <button type="button" aria-label="Fechar">×</button>
    </div>
  <?php endforeach; ?>
  </div>
  <?= $content ?>
</main>
<div class="a-modal" id="media-modal" hidden>
  <div class="a-modal-box" role="dialog" aria-modal="true" aria-labelledby="media-modal-title">
    <div class="a-modal-head">
      <h2 id="media-modal-title">Escolher imagem</h2>
      <button type="button" class="a-modal-close" data-close aria-label="Fechar">×</button>
    </div>
    <div class="a-modal-upload">
      <label class="sr" for="media-folder">Pasta</label>
      <select id="media-folder" data-media-folder style="width:auto"><option value="0">🗂 Todas as pastas</option><option value="-1">📄 Sem pasta</option></select>
      <label class="a-btn a-btn-ghost">Enviar do computador<input type="file" accept="image/*" data-media-upload hidden></label>
      <span class="a-muted" data-media-status>JPG, PNG, WebP ou GIF. O site reduz o tamanho sozinho.</span>
    </div>
    <div class="a-media-grid" data-media-grid></div>
    <button type="button" class="a-btn a-btn-ghost" data-media-more hidden>Carregar mais</button>
  </div>
</div>
<script src="<?= e(asset('js/admin.js')) ?>"></script>
<?php if (!empty($editorAssets)): ?>
<script src="<?= e(asset('vendor/tinymce/tinymce.min.js')) ?>"></script>
<script>window.ARQUIVO_ASSETS = { fonts: <?= json_encode(asset('css/fonts.css')) ?>, site: <?= json_encode(asset('css/site.css')) ?>, vars: <?= json_encode(appearance_css()) ?> };</script>
<script src="<?= e(asset('js/editor.js')) ?>"></script>
<?php endif; ?>
</body>
</html>
