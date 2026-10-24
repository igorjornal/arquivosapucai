<?php /* Opções de exibição de uma imagem. Variáveis: $key, $o (de image_opts) */ ?>
<fieldset class="a-img-opts">
  <legend>Como a imagem aparece</legend>
  <div class="a-row-2">
    <label>Tamanho: <output data-range-out><?= $o['width'] ?></output>%
      <input type="range" name="<?= e($key) ?>[width]" min="20" max="100" step="5" value="<?= $o['width'] ?>" data-range>
    </label>
    <label>Cantos
      <select name="<?= e($key) ?>[radius]"><?= select_options(['0' => 'Retos', '8' => 'Levemente arredondados', '16' => 'Arredondados', '32' => 'Bem arredondados', '999' => 'Redondo (use com quadrada)'], (string) $o['radius']) ?></select>
    </label>
    <label>Formato
      <select name="<?= e($key) ?>[ratio]"><?= select_options(array_map(fn ($r) => $r['label'], image_ratios()), $o['ratio']) ?></select>
    </label>
    <label>Alinhamento
      <select name="<?= e($key) ?>[align]"><?= select_options(['left' => 'Esquerda', 'center' => 'Centro', 'right' => 'Direita'], $o['align']) ?></select>
    </label>
  </div>
</fieldset>
