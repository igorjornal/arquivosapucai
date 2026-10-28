<?php if (($home['playlists_layout'] ?? 'grid') === 'feature'):
    // Formato imagem + texto: imagem à esquerda (clicável), título, texto e botão à direita
    $si = split_image(image_opts($home['playlists_img'] ?? null, ['radius' => 16]));
    $link = (string) $home['playlists_link'];
    $align = in_array($home['playlists_align'] ?? 'left', ['left', 'center', 'right', 'justify'], true) ? $home['playlists_align'] : 'left'; ?>
<section class="sec sec-playlists" id="<?= e($section['anchor'] ?: 'playlists') ?>">
  <div class="wrap split<?= $home['playlists_image'] ? '' : ' split-noimg' ?>" style="<?= $si['vars'] ?>">
    <?php if ($home['playlists_image']): ?>
      <div class="split-media">
        <?php if ($link !== ''): ?><a href="<?= e($link) ?>"<?= link_target($link, $home['playlists_new_tab'] ?? null) ?> aria-label="<?= e($home['playlists_title']) ?>"><?php endif; ?>
        <img style="<?= $si['style'] ?>" src="<?= e(media_url($home['playlists_image'])) ?>" alt="<?= e($home['playlists_title']) ?>" loading="lazy">
        <?php if ($link !== ''): ?></a><?php endif; ?>
      </div>
    <?php endif; ?>
    <div class="split-text" style="text-align:<?= $align ?>">
      <h2 class="sec-title"<?= type_style('playlists_title') ?>><?= e($home['playlists_title']) ?></h2>
      <div<?= type_style('playlists_intro') ?>><?= $nl2p($home['playlists_intro']) ?></div>
      <?php if ($home['playlists_button'] !== '' && $link !== ''): ?>
        <a class="btn"<?= type_style('playlists_button') ?> href="<?= e($link) ?>"<?= link_target($link, $home['playlists_new_tab'] ?? null) ?>><?= e($home['playlists_button']) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php else: ?>
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
<?php endif; ?>
