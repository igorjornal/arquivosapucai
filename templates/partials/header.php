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
<?php $ticker = ticker_items(); if ($ticker):
    // Repete a sequência até encher a largura e emenda sem pulo
    $reps = max(2, (int) ceil(6 / count($ticker)) * 2); ?>
<div class="ticker" aria-label="Avisos">
  <div class="ticker-track" style="animation-duration: <?= max(25, 8 * count($ticker) * $reps / 2) ?>s">
    <?php for ($r = 0; $r < $reps; $r++): foreach ($ticker as $item): ?>
      <?php if ($item['link'] ?? ''): ?>
        <a href="<?= e($item['link']) ?>"<?= $r ? ' tabindex="-1" aria-hidden="true"' : '' ?><?= link_target((string) $item['link'], $item['new_tab'] ?? null) ?>><?= e($item['text']) ?></a>
      <?php else: ?>
        <span<?= $r ? ' aria-hidden="true"' : '' ?>><?= e($item['text']) ?></span>
      <?php endif; ?>
      <img src="<?= e(asset('img/ticker-icone.svg')) ?>" alt="" width="18" height="18">
    <?php endforeach; endfor; ?>
  </div>
</div>
<?php endif; ?>
