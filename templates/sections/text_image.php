<?php $d = $section['data']; ?>
<section class="sec sec-custom" id="<?= e($section['anchor'] ?: 'secao-' . $section['id']) ?>"<?= section_style($d) ?>>
  <div class="wrap split<?= $d['image_side'] === 'right' ? ' split-reverse' : '' ?><?= $d['image'] ? '' : ' split-noimg' ?>">
    <?php if ($d['image']): ?>
      <div class="split-media"><img style="<?= image_opts_style(image_opts($d['img'] ?? null)) ?>" src="<?= e(media_url($d['image'])) ?>" alt="" loading="lazy"></div>
    <?php endif; ?>
    <div class="split-text">
      <?php if ($d['title'] !== ''): ?><h2 class="sec-title"><?= e($d['title']) ?></h2><?php endif; ?>
      <?= $nl2p((string) $d['text']) ?>
      <?php if ($d['button_text'] !== '' && $d['button_link'] !== ''): ?>
        <a class="btn" href="<?= e($d['button_link']) ?>"<?= link_target($d['button_link'], $d['button_new_tab'] ?? null) ?>><?= e($d['button_text']) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>
