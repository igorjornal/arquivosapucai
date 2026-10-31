<?php
$style = [];
foreach (['bg_color' => '--pg-bg', 'text_color' => '--pg-text', 'title_color' => '--pg-title'] as $col => $var) {
    if ($page[$col]) {
        $style[] = $var . ':' . $page[$col];
    }
}
$isDraft = $page['status'] !== 'published';
?>
<article class="static-page"<?= $style ? ' style="' . e(implode(';', $style)) . '"' : '' ?>>
  <?php if ($page['banner_image']): ?>
    <header class="page-banner<?= $page['title_over_banner'] ? ' has-title' : '' ?>">
      <img src="<?= e(media_url($page['banner_image'])) ?>" alt="" style="object-position:<?= e($page['banner_position'] ?? '50% 50%') ?>">
      <?php if ($page['title_over_banner']): ?>
        <div class="wrap page-banner-text">
          <h1 class="post-title"<?= type_style('post_title') ?>><?= e($page['title']) ?></h1>
          <?php if ($page['subtitle']): ?><p class="page-subtitle"><?= e($page['subtitle']) ?></p><?php endif; ?>
        </div>
      <?php endif; ?>
    </header>
  <?php endif; ?>
  <div class="wrap<?= $page['width'] === 'narrow' ? ' narrow' : '' ?> page-body">
    <a class="back-link" href="/" data-back>← Voltar</a>
    <?php if ($isDraft): ?><p class="notice notice-info">Pré-visualização: esta página ainda não está publicada.</p><?php endif; ?>
    <?php if (!$page['banner_image'] || !$page['title_over_banner']): ?>
      <h1 class="post-title"<?= type_style('post_title') ?>><?= e($page['title']) ?></h1>
      <?php if ($page['subtitle']): ?><p class="page-subtitle"><?= e($page['subtitle']) ?></p><?php endif; ?>
    <?php endif; ?>
    <div class="post-content"><?= $page['content'] /* sanitizado ao salvar */ ?></div>
  </div>
  <?php $pl = setting('playlist_page'); if ($pl['decades'] && (int) $page['id'] === playlists_page_id()) partial('playlist-decades', ['pl' => $pl]); ?>
</article>
<?php partial('contact', ['home' => setting('home'), 'anchor' => 'contato']); ?>
