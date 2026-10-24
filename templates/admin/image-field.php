<?php /* Campo de imagem com pré-visualização e seletor da biblioteca. Variáveis: $name, $value, $label, $help */ ?>
<div class="a-image-field" data-image-field>
  <span class="a-label"><?= e($label) ?></span>
  <div class="a-image-preview" data-preview<?= $value ? '' : ' hidden' ?>>
    <img src="<?= e(media_url($value)) ?>" alt="">
  </div>
  <input type="hidden" name="<?= e($name) ?>" value="<?= e($value) ?>" data-value>
  <div class="a-row-tight">
    <button type="button" class="a-btn a-btn-ghost a-btn-sm" data-pick>Escolher imagem</button>
    <button type="button" class="a-link" data-clear<?= $value ? '' : ' hidden' ?>>Remover</button>
  </div>
  <?php if (!empty($help)): ?><small class="a-help"><?= e($help) ?></small><?php endif; ?>
</div>
