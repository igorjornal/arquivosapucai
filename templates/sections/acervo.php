<?php
// Duas colunas lado a lado (no celular, uma embaixo da outra). Cada uma tem a própria âncora: #acervo e #historia.
$cols = array_values(array_filter(['acervo', 'historia'], fn ($k) => !empty($home[$k . '_show'])));
$anchor = in_array($section['anchor'], ['acervo', 'historia'], true) ? '' : (string) $section['anchor'];
?>
<?php if ($cols): ?>
<section class="sec sec-acervo"<?= $anchor !== '' ? ' id="' . e($anchor) . '"' : '' ?>>
  <div class="wrap acervo-cols<?= count($cols) === 1 ? ' is-single' : '' ?>">
    <?php foreach ($cols as $k): ?>
      <div class="acervo-col" id="<?= $k ?>">
        <?php if ($home[$k . '_image']): ?>
          <div class="acervo-media"><img style="<?= image_opts_style(image_opts($home[$k . '_img'] ?? null, ['radius' => 16, 'ratio' => 'wide'])) ?>" src="<?= e(media_url($home[$k . '_image'])) ?>" alt="<?= e($home[$k . '_title']) ?>" loading="lazy"></div>
        <?php endif; ?>
        <h2 class="sec-title"<?= type_style($k . '_title') ?>><?= e($home[$k . '_title']) ?></h2>
        <div<?= type_style($k . '_text') ?>><?= $nl2p($home[$k . '_text']) ?></div>
        <?php if ($home[$k . '_button'] !== '' && $home[$k . '_link'] !== ''): ?>
          <a class="btn btn-dark"<?= type_style($k . '_button') ?> href="<?= e($home[$k . '_link']) ?>"<?= link_target((string) $home[$k . '_link'], $home[$k . '_new_tab'] ?? null) ?>><?= e($home[$k . '_button']) ?></a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
