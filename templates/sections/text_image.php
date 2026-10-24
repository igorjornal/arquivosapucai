<?php $d = $section['data']; ?>
<section class="sec sec-custom" id="<?= e($section['anchor'] ?: 'secao-' . $section['id']) ?>"<?= section_style($d) ?>>
  <div class="wrap split<?= $d['image_side'] === 'right' ? ' split-reverse' : '' ?><?= $d['image'] ? '' : ' split-noimg' ?>">
    <?php if ($d['image']): ?>
      <div class="split-media"><img src="<?= e(media_url($d['image'])) ?>" alt="" loading="lazy"></div>
    <?php endif; ?>
    <div class="split-text">
      <?php if ($d['title'] !== ''): ?><h2 class="sec-title"><?= e($d['title']) ?></h2><?php endif; ?>
      <?= $nl2p((string) $d['text']) ?>
      <?php if ($d['button_text'] !== '' && $d['button_link'] !== ''): ?>
        <a class="btn" href="<?= e($d['button_link']) ?>"<?= str_starts_with($d['button_link'], 'http') ? ' target="_blank" rel="noopener"' : '' ?>><?= e($d['button_text']) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>
