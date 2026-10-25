<section class="sec sec-playlists" id="<?= e($section['anchor'] ?: 'playlists') ?>">
  <div class="wrap">
    <h2 class="sec-title center"<?= type_style('playlists_title') ?>><?= e($home['playlists_title']) ?></h2>
    <div class="sec-intro"<?= type_style('playlists_intro') ?>><?= $nl2p($home['playlists_intro']) ?></div>
    <div class="playlists"><?php $nameStyle = type_style('playlists_names'); ?>
      <?php foreach ($home['playlists'] as $pl): ?>
        <a class="playlist" href="<?= e($pl['link'] ?: '#') ?>"<?= $pl['link'] ? link_target($pl['link'], $pl['new_tab'] ?? true) : '' ?>>
          <img src="<?= e(media_url($pl['image'])) ?>" alt="" loading="lazy">
          <span<?= $nameStyle ?>><?= e($pl['title']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="sec-intro"<?= type_style('playlists_outro') ?>><?= $nl2p($home['playlists_outro']) ?></div>
  </div>
</section>
