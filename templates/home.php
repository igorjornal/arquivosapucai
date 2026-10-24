<?php
$nl2p = fn (string $text) => implode('', array_map(fn ($p) => '<p>' . e(trim($p)) . '</p>', array_filter(preg_split("/\n\s*\n/", $text))));
?>
<?php if ($slides): ?>
<section class="hero" aria-label="Destaques">
  <div class="slider" data-slider>
    <?php foreach ($slides as $i => $slide): ?>
      <div class="slide<?= $i === 0 ? ' is-active' : '' ?>" data-slide>
        <?php if ($slide['link']): ?><a href="<?= e($slide['link']) ?>"><?php endif; ?>
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

<section class="sec sec-blog" id="blog">
  <div class="wrap">
    <div class="sec-head">
      <h2 class="sec-title"><?= e($home['blog_title']) ?></h2>
      <a class="btn btn-red" href="/blog">VER TODAS</a>
    </div>
    <?php if ($posts): ?>
      <div class="<?= e(cards_class()) ?>">
        <?php foreach ($posts as $post) partial('post-card', ['post' => $post]); ?>
      </div>
    <?php else: ?>
      <p class="empty">Os primeiros posts chegam em breve.</p>
    <?php endif; ?>
  </div>
</section>

<section class="sec sec-arquivo" id="arquivo">
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

<section class="sec sec-playlists" id="playlists">
  <div class="wrap">
    <h2 class="sec-title center"><?= e($home['playlists_title']) ?></h2>
    <div class="sec-intro"><?= $nl2p($home['playlists_intro']) ?></div>
    <div class="playlists">
      <?php foreach ($home['playlists'] as $pl): ?>
        <a class="playlist" href="<?= e($pl['link'] ?: '#') ?>"<?= $pl['link'] ? ' target="_blank" rel="noopener"' : '' ?>>
          <img src="<?= e(media_url($pl['image'])) ?>" alt="" loading="lazy">
          <span><?= e($pl['title']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="sec-intro"><?= $nl2p($home['playlists_outro']) ?></div>
  </div>
</section>

<section class="sec sec-acervo" id="acervo">
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

<?php partial('contact', ['home' => $home]); ?>
