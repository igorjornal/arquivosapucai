<?php $slides = active_slides(); ?>
<?php if ($slides): ?>
<section class="hero" aria-label="Destaques">
  <div class="slider" data-slider>
    <?php foreach ($slides as $i => $slide): ?>
      <div class="slide<?= $i === 0 ? ' is-active' : '' ?>" data-slide>
        <?php if ($slide['link']): ?><a href="<?= e($slide['link']) ?>"<?= link_target($slide['link'], $slide['new_tab'] ?? null) ?>><?php endif; ?>
        <img src="<?= e(media_url($slide['image'])) ?>" alt="<?= e($slide['alt']) ?>"<?= $i ? ' loading="lazy"' : ' fetchpriority="high"' ?>>
        <?php if ($slide['link']): ?></a><?php endif; ?>
      </div>
    <?php endforeach; ?>
    <?php if (count($slides) > 1): ?>
      <button class="slider-btn prev" type="button" data-prev aria-label="Anterior">‹</button>
      <button class="slider-btn next" type="button" data-next aria-label="Próximo">›</button>
      <div class="slider-dots" data-dots></div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>
