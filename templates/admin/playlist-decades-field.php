<?php /* Playlists por década, dentro da tela da página Playlists (Páginas). Variável: $pl */
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
<section class="a-card a-pl-block" data-pl-form>
  <input type="hidden" name="pl_block" value="1">
  <h2>Playlists por década</h2>
  <p class="a-muted">Aparecem depois do texto desta página, em décadas que abrem e fecham com um clique. A primeira década começa aberta. A capa se monta sozinha com o ano.</p>
  <label>Texto que vem depois do número<input name="count_label" value="<?= e($pl['count_label']) ?>" maxlength="30" placeholder="playlists"></label>
  <small class="a-help">Exemplo: com “playlists”, a barra mostra “Anos 80 · 8 playlists”. O número é contado sozinho. Vazio: o número some.</small>
  <div data-pl-decades>
    <?php foreach ($decades as $di => $dec): $dk = 'd' . $di; ?>
      <section class="a-pl-decade" data-pl-decade data-key="<?= $dk ?>">
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
    <section class="a-pl-decade" data-pl-decade data-key="__D__">
      <div class="a-pl-dhead">
        <label>Nome da década<input name="decades[__D__][name]" value="" maxlength="60" placeholder="Anos 90"></label>
        <span class="a-pl-tools"><button type="button" class="a-mini" data-pl-up aria-label="Subir a década">↑</button><button type="button" class="a-mini" data-pl-down aria-label="Descer a década">↓</button><button type="button" class="a-mini" data-pl-remove aria-label="Apagar a década">×</button></span>
      </div>
      <div data-pl-items></div>
      <button type="button" class="a-btn a-btn-ghost" data-pl-add-item>+ Playlist nesta década</button>
    </section>
  </template>

  <div class="a-label">Cores</div><div class="a-colors-inline"><?= color_fields('pl_pagina') ?></div>
  <small class="a-help">Tudo aqui é salvo junto com a página, no botão de salvar/publicar lá em cima.</small>
</section>
