<?php /* Fonte, tamanho e negrito de uma parte do site. Variáveis: $key, $label (opcional) */
$t = type_targets()[$key];
$d = type_default($key);
$c = type_choice($key);
$cur = $c ?? $d;
[$min, $max] = type_limits($t['base']);
$baseName = ['heading' => 'Títulos', 'body' => 'Texto corrido', 'ui' => 'Menus, botões e detalhes'][$t['base']];
?>
<fieldset class="a-type<?= !empty($bare) ? ' a-type-bare' : '' ?>" data-type-field>
  <legend<?= !empty($bare) ? ' class="sr"' : '' ?>><?= e($label ?? $t['label']) ?></legend>
  <label class="a-check"><input type="checkbox" name="type[<?= e($key) ?>][padrao]" value="1"<?= $c ? '' : ' checked' ?> data-type-default> Usar o padrão do site</label>
  <small class="a-help" data-type-summary<?= $c ? ' hidden' : '' ?>>Padrão: <?= e($d['font']) ?><?= $d['size'] ? ', ' . $d['size'] . ' px' : '' ?><?= ', ' . mb_strtolower(weight_choices()[$d['weight']]) ?> (vem de “<?= $baseName ?>” em <a href="/admin/aparencia">Aparência</a>)</small>
  <div data-type-custom<?= $c ? '' : ' hidden' ?>>
    <label>Fonte<select name="type[<?= e($key) ?>][font]" data-font-select><?= select_options(array_combine(font_choices(), font_choices()), $cur['font']) ?></select></label>
    <p class="a-font-sample" data-size-sample style="font-family:'<?= e(font_parts($cur['font'])['family']) ?>';font-stretch:<?= font_parts($cur['font'])['stretch'] ?>;font-weight:<?= $cur['weight'] ?><?= $cur['size'] ? ';font-size:' . min($cur['size'], 40) . 'px' : '' ?>">Mangueira 1988</p>
    <?php if ($t['size'] !== false): ?>
      <label><span>Tamanho: <output data-range-out><?= $cur['size'] ?></output> px</span>
        <input type="range" name="type[<?= e($key) ?>][size]" min="<?= $min ?>" max="<?= $max ?>" value="<?= $cur['size'] ?>" data-range>
      </label>
    <?php else: ?>
      <small class="a-help">O tamanho se ajusta sozinho para caber numa linha.</small>
    <?php endif; ?>
    <label>Peso (grossura da letra)<select name="type[<?= e($key) ?>][weight]" data-type-weight><?= select_options(weight_choices(), (string) $cur['weight']) ?></select></label>
  </div>
</fieldset>
