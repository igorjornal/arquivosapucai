<?php
$site = setting('site');
$current = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$headerBg = media_url($site['header_bg']);
?>
<header class="site-header"<?php if ($headerBg): ?> style="--header-image:url('<?= e($headerBg) ?>')"<?php endif; ?>>
  <div class="wrap header-inner">
    <a class="logo" href="/" aria-label="<?= e($site['title']) ?> — página inicial">
      <img src="<?= e(media_url($site['logo'])) ?>" alt="<?= e($site['title']) ?>" width="96" height="96">
    </a>
    <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="menu-principal">
      <span></span><span></span><span></span><span class="sr">Abrir menu</span>
    </button>
    <nav class="main-nav" id="menu-principal" aria-label="Menu principal">
      <ul>
        <?php foreach (menu_items() as $item):
            $active = $item['url'] === $current || ($item['url'] !== '/' && !str_contains($item['url'], '#') && str_starts_with((string) $current, $item['url'])); ?>
          <li><a href="<?= e($item['url']) ?>"<?= $active ? ' aria-current="page"' : '' ?><?= $item['new_tab'] ? ' target="_blank" rel="noopener"' : '' ?>><?= e($item['label']) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <form class="nav-search" action="/busca" method="get" role="search">
        <label class="sr" for="busca-topo">Buscar</label>
        <input id="busca-topo" type="search" name="q" placeholder="Buscar no Arquivo" value="<?= e($_GET['q'] ?? '') ?>">
        <button type="submit" aria-label="Buscar"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></button>
      </form>
    </nav>
  </div>
</header>
<?php if (setting('home')['ticker'] !== ''): ?>
<div class="ticker" aria-label="Aviso">
  <div class="ticker-track">
    <?php for ($i = 0; $i < 4; $i++): ?>
      <span<?= $i ? ' aria-hidden="true"' : '' ?>><?= e(setting('home')['ticker']) ?></span>
      <img src="<?= e(asset('img/ticker-icone.svg')) ?>" alt="" width="18" height="18">
    <?php endfor; ?>
  </div>
</div>
<?php endif; ?>
