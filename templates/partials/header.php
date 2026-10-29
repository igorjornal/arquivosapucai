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
    <?php $mn = setting('appearance')['menu'] ?? []; $socials = array_filter(setting('site')['socials']); ?>
    <nav class="main-nav menu-align-<?= e(in_array($mn['align'] ?? '', ['logo', 'center', 'right'], true) ? $mn['align'] : 'right') ?> fx-<?= e(in_array($mn['effect'] ?? '', ['line', 'grow', 'pill', 'color'], true) ? $mn['effect'] : 'line') ?>" id="menu-principal" aria-label="Menu principal">
      <?php $menuStyle = type_style('menu'); ?>
      <ul>
        <?php foreach (menu_items() as $item):
            $active = $item['url'] === $current || ($item['url'] !== '/' && !str_contains($item['url'], '#') && str_starts_with((string) $current, $item['url'])); ?>
          <li><a href="<?= e($item['url']) ?>"<?= $menuStyle ?><?= $active ? ' aria-current="page"' : '' ?><?= $item['new_tab'] ? ' target="_blank" rel="noopener"' : '' ?>><?= e($item['label']) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <div class="header-tools">
        <?php foreach ($socials as $net => $link): ?>
          <a class="header-social" href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($net)) ?>"><?= social_icon($net) ?></a>
        <?php endforeach; ?>
        <form class="nav-search" action="/busca" method="get" role="search" id="busca-topo-form"<?= ($_GET['q'] ?? '') === '' ? ' hidden' : '' ?>>
          <label class="sr" for="busca-topo">Buscar</label>
          <input id="busca-topo" type="search" name="q" placeholder="Buscar no Arquivo" value="<?= e($_GET['q'] ?? '') ?>">
          <button type="submit">Buscar</button>
          <button class="nav-search-close" type="button" aria-label="Fechar a busca" data-search-close>×</button>
        </form>
        <button class="search-toggle" type="button" aria-label="Buscar no site" aria-controls="busca-topo-form" aria-expanded="<?= ($_GET['q'] ?? '') === '' ? 'false' : 'true' ?>" data-search-toggle><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></button>
      </div>
    </nav>
  </div>
</header>
<?php $ticker = ticker_items(); if ($ticker):
    // Repete a sequência até encher a largura e emenda sem pulo
    $reps = max(2, (int) ceil(6 / count($ticker)) * 2); ?>
<?php $tkHome = setting('home'); $sepHtml = ticker_separator(); ?>
<div class="ticker" aria-label="Avisos">
  <div class="ticker-track" data-speed="<?= max(1, min(10, (int) ($tkHome['ticker_speed'] ?? 4))) ?>" style="animation-duration: <?= max(25, 8 * count($ticker) * $reps / 2) ?>s;--ticker-gap:<?= max(0, min(80, (int) ($tkHome['ticker_gap'] ?? 22))) ?>px;<?= e(type_css('ticker')) ?>">
    <?php for ($r = 0; $r < $reps; $r++): foreach ($ticker as $item): ?>
      <?php if ($item['link'] ?? ''): ?>
        <a href="<?= e($item['link']) ?>"<?= $r ? ' tabindex="-1" aria-hidden="true"' : '' ?><?= link_target((string) $item['link'], $item['new_tab'] ?? null) ?>><?= e($item['text']) ?></a>
      <?php else: ?>
        <span<?= $r ? ' aria-hidden="true"' : '' ?>><?= e($item['text']) ?></span>
      <?php endif; ?>
      <?= $sepHtml ?>
    <?php endforeach; endfor; ?>
  </div>
</div>
<?php endif; ?>
