<section class="sec sec-arquivo" id="<?= e($section['anchor'] ?: 'arquivo') ?>">
  <div class="wrap split">
    <?php if ($home['arquivo_image']): ?>
      <div class="split-media"><img src="<?= e(media_url($home['arquivo_image'])) ?>" alt="Canal Arquivo Sapucaí no YouTube" loading="lazy"></div>
    <?php endif; ?>
    <div class="split-text">
      <h2 class="sec-title"><?= e($home['arquivo_title']) ?></h2>
      <?= $nl2p($home['arquivo_text']) ?>
      <?php if ($home['arquivo_button']): ?>
        <a class="btn" href="<?= e($home['arquivo_link'] ?: '#') ?>"<?= $home['arquivo_link'] && str_starts_with($home['arquivo_link'], 'http') ? ' target="_blank" rel="noopener"' : '' ?>><?= e($home['arquivo_button']) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>
