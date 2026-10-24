<section class="sec sec-playlists" id="<?= e($section['anchor'] ?: 'playlists') ?>">
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
