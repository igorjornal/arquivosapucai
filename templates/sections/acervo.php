<section class="sec sec-acervo" id="<?= e($section['anchor'] ?: 'acervo') ?>">
  <div class="wrap">
    <?php if ($home['acervo_image']): ?>
      <div class="acervo-media"><img src="<?= e(media_url($home['acervo_image'])) ?>" alt="" loading="lazy"></div>
    <?php endif; ?>
    <div class="acervo-cols">
      <div>
        <h2 class="sec-title"><?= e($home['acervo_title']) ?></h2>
        <?= $nl2p($home['acervo_text']) ?>
      </div>
      <div id="historia">
        <h2 class="sec-title"><?= e($home['historia_title']) ?></h2>
        <?= $nl2p($home['historia_text']) ?>
      </div>
    </div>
    <?php if ($home['acervo_button']): ?>
      <div class="center"><a class="btn btn-dark" href="<?= e($home['acervo_link'] ?: '#') ?>"><?= e($home['acervo_button']) ?></a></div>
    <?php endif; ?>
  </div>
</section>
