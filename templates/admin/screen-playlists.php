<?php
$plPage = $pl['page_slug'] !== '' ? db_one('SELECT title, slug FROM pages WHERE slug = ?', [$pl['page_slug']]) : null;
$decades = $pl['decades'] ?: [['name' => 'Anos 80', 'items' => []]];
// Uma linha de playlist (ano, título, subtítulo, link)
$itemRow = function (string $d, string $i, array $it): string {
    $n = 'decades[' . $d . '][items][' . $i . ']';
    return '<div class="a-pl-item" data-pl-item>'
        . '<label>Ano (texto grande)<input name="' . $n . '[year]" value="' . e($it['year'] ?? '') . '" maxlength="20" placeholder="1980"></label>'
        . '<label>Título<input name="' . $n . '[title]" value="' . e($it['title'] ?? '') . '" maxlength="60" placeholder="Grupo Especial"></label>'
        . '<label>Subtítulo<input name="' . $n . '[subtitle]" value="' . e($it['subtitle'] ?? '') . '" maxlength="60" placeholder="Desfiles completos"></label>'
        . '<label>Link da playlist no YouTube<input name="' . $n . '[link]" value="' . e($it['link'] ?? '') . '" maxlength="255" placeholder="https://www.youtube.com/playlist?list=…"></label>'
        . '<span class="a-pl-tools"><button type="button" class="a-mini" data-pl-up aria-label="Subir">↑</button><button type="button" class="a-mini" data-pl-down aria-label="Descer">↓</button><button type="button" class="a-mini" data-pl-remove aria-label="Tirar">×</button></span>'
        . '</div>';
};
?>
<div class="a-head"><h1>Página de Playlists</h1><?php if ($plPage): ?><a class="a-btn a-btn-ghost" href="/<?= e($plPage['slug']) ?>" target="_blank" rel="noopener">Ver a página ↗</a><?php endif; ?></div>
<form method="post" data-pl-form>
  <?= csrf_field() ?>
  <section class="a-card">
    <p class="a-muted">As playlists aparecem em décadas que abrem e fecham com um clique, depois do texto da página. A primeira década começa aberta. A capa se monta sozinha com o ano.</p>
    <div class="a-row-2">
      <label>Página onde aparecem
        <select name="page_slug"><option value="">Nenhuma (escondidas)</option><?= select_options(array_column($pages, 'title', 'slug'), $pl['page_slug']) ?></select>
      </label>
      <label>Palavra depois do número (“8 playlists”)<input name="count_label" value="<?= e($pl['count_label']) ?>" maxlength="30"></label>
    </div>
    <small class="a-help">O título, a foto do topo e o texto de cima são da própria página, em <a href="/admin/paginas">Páginas</a>. Palavra vazia: o número some.</small>
  </section>

  <div data-pl-decades>
    <?php foreach ($decades as $di => $dec): $dk = 'd' . $di; ?>
      <section class="a-card a-pl-decade" data-pl-decade data-key="<?= $dk ?>">
        <div class="a-pl-dhead">
          <label>Nome da década<input name="decades[<?= $dk ?>][name]" value="<?= e($dec['name']) ?>" maxlength="60" placeholder="Anos 80"></label>
          <span class="a-pl-tools"><button type="button" class="a-mini" data-pl-up aria-label="Subir a década">↑</button><button type="button" class="a-mini" data-pl-down aria-label="Descer a década">↓</button><button type="button" class="a-mini" data-pl-remove aria-label="Apagar a década">×</button></span>
        </div>
        <div data-pl-items>
          <?php foreach ($dec['items'] as $ii => $it) echo $itemRow($dk, 'i' . $ii, $it); ?>
        </div>
        <button type="button" class="a-btn a-btn-ghost" data-pl-add-item>+ Playlist nesta década</button>
      </section>
    <?php endforeach; ?>
  </div>
  <p><button type="button" class="a-btn a-btn-ghost" data-pl-add-decade>+ Década</button></p>
  <template data-pl-item-tpl><?= $itemRow('__D__', '__I__', []) ?></template>
  <template data-pl-decade-tpl>
    <section class="a-card a-pl-decade" data-pl-decade data-key="__D__">
      <div class="a-pl-dhead">
        <label>Nome da década<input name="decades[__D__][name]" value="" maxlength="60" placeholder="Anos 90"></label>
        <span class="a-pl-tools"><button type="button" class="a-mini" data-pl-up aria-label="Subir a década">↑</button><button type="button" class="a-mini" data-pl-down aria-label="Descer a década">↓</button><button type="button" class="a-mini" data-pl-remove aria-label="Apagar a década">×</button></span>
      </div>
      <div data-pl-items></div>
      <button type="button" class="a-btn a-btn-ghost" data-pl-add-item>+ Playlist nesta década</button>
    </section>
  </template>

  <section class="a-card"><div class="a-label">Cores</div><div class="a-colors-inline"><?= color_fields('pl_pagina') ?></div></section>
  <div class="a-sticky-save"><button class="a-btn" type="submit">Salvar página de Playlists</button></div>
</form>
