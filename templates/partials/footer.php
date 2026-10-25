<?php
$site = setting('site');
$news = setting('newsletter');
$footer = setting('footer');
$socials = array_filter($site['socials']);
$names = ['youtube' => 'YouTube', 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'x' => 'X', 'tiktok' => 'TikTok', 'linkedin' => 'LinkedIn'];
$linkStyle = type_style('footer_links');
?>
<footer class="site-footer">
  <div class="wrap footer-grid">
    <div class="footer-brand">
      <a class="footer-logo" href="/"><img src="<?= e(media_url($site['logo'])) ?>" alt="" width="72" height="72"><span<?= type_style('footer_name') ?>><?= e($site['title']) ?></span></a>
      <p<?= type_style('footer_description') ?>><?= e($site['description']) ?></p>
      <?php if ($socials): ?>
        <ul class="socials">
          <?php foreach ($socials as $net => $link): ?>
            <li><a href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e($names[$net] ?? $net) ?>"><?= social_icon($net) ?></a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
    <nav class="footer-nav" aria-label="Rodapé">
      <?php if ($footer['nav_title'] !== ''): ?><h2<?= type_style('footer_nav_title') ?>><?= e($footer['nav_title']) ?></h2><?php endif; ?>
      <ul>
        <?php foreach (menu_items() as $item): ?>
          <li><a href="<?= e($item['url']) ?>"<?= $linkStyle ?><?= $item['new_tab'] ? ' target="_blank" rel="noopener"' : '' ?>><?= e($item['label']) ?></a></li>
        <?php endforeach; ?>
        <?php if ($footer['rss_label'] !== ''): ?><li><a href="/feed.xml"<?= $linkStyle ?>><?= e($footer['rss_label']) ?></a></li><?php endif; ?>
      </ul>
    </nav>
    <div class="footer-news" id="newsletter">
      <h2<?= type_style('footer_news_title') ?>><?= e($news['title']) ?></h2>
      <p<?= type_style('footer_news_text') ?>><?= e($news['text']) ?></p>
      <?php foreach (take_flash_group('newsletter') as $f): ?>
        <p class="notice notice-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></p>
      <?php endforeach; ?>
      <form class="news-form" action="/newsletter" method="post">
        <?= csrf_field() ?><?= spam_fields() ?>
        <label class="sr" for="news-email">Seu e-mail</label>
        <input id="news-email" type="email" name="email" placeholder="<?= e($news['placeholder']) ?>" required>
        <button class="btn" type="submit"<?= type_style('footer_news_button') ?>><?= e($news['button']) ?></button>
      </form>
      <p class="footer-contact"><a href="mailto:<?= e($site['email']) ?>"<?= type_style('footer_email') ?>><?= e($site['email']) ?></a></p>
    </div>
  </div>
  <?php if ($footer['show_word'] && $footer['word'] !== ''): ?>
    <div class="footer-word" aria-hidden="true"<?= type_style('footer_word') ?>><?= e($footer['word']) ?></div>
  <?php endif; ?>
  <div class="wrap footer-bottom">
    <span<?= type_style('footer_copyright') ?>><?= e(str_replace('{ano}', date('Y'), $footer['copyright'])) ?></span>
    <?php if ($footer['author_label'] !== ''): ?><a href="/admin" target="_blank" rel="noopener"<?= type_style('footer_author') ?>><?= e($footer['author_label']) ?></a><?php endif; ?>
  </div>
</footer>
