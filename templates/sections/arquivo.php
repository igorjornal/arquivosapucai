<?php $si = split_image(image_opts($home['arquivo_img'] ?? null)); ?>
<section class="sec sec-arquivo" id="<?= e($section['anchor'] ?: 'arquivo') ?>">
  <div class="wrap split" style="<?= $si['vars'] ?>">
    <?php if ($home['arquivo_image']): ?>
      <div class="split-media"><img style="<?= $si['style'] ?>" src="<?= e(media_url($home['arquivo_image'])) ?>" alt="Canal Arquivo Sapucaí no YouTube" loading="lazy"></div>
    <?php endif; ?>
    <div class="split-text">
      <h2 class="sec-title"<?= type_style('arquivo_title') ?>><?= e($home['arquivo_title']) ?></h2>
      <div<?= type_style('arquivo_text') ?>><?= $nl2p($home['arquivo_text']) ?></div>
      <?php if ($home['arquivo_button']): ?>
        <a class="btn"<?= type_style('arquivo_button') ?> href="<?= e($home['arquivo_link'] ?: '#') ?>"<?= link_target((string) $home['arquivo_link'], $home['arquivo_new_tab'] ?? null) ?>><?= e($home['arquivo_button']) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>
