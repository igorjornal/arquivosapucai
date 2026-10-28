<?php
$site = setting('site');
$news = setting('newsletter');
$footer = setting('footer');
$socials = array_filter($site['socials']);
$names = ['youtube' => 'YouTube', 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'x' => 'X', 'tiktok' => 'TikTok', 'linkedin' => 'LinkedIn'];
$linkStyle = type_style('footer_links');
$titleStyle = type_style('footer_titles');
$textStyle = type_style('footer_texts');
?>
<footer class="site-footer">
  <div class="wrap footer-grid">
    <div class="footer-brand">
      <h2 class="footer-title"<?= $titleStyle ?>><a href="/"><?= e($site['title']) ?></a></h2>
      <?php if ($site['description'] !== ''): ?><p class="footer-text"<?= $textStyle ?>><?= e($site['description']) ?></p><?php endif; ?>
      <?php if ($socials): ?>
        <ul class="socials">
          <?php foreach ($socials as $net => $link): ?>
            <li><a href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e($names[$net] ?? $net) ?>"><?= social_icon($net) ?></a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
    <nav class="footer-nav" aria-label="Rodapé">
      <h2 class="footer-title"<?= $titleStyle ?>><?= e($footer['nav_title']) ?></h2>
      <ul>
        <?php foreach (menu_items() as $item): ?>
          <li><a href="<?= e($item['url']) ?>"<?= $linkStyle ?><?= $item['new_tab'] ? ' target="_blank" rel="noopener"' : '' ?>><?= e($item['label']) ?></a></li>
        <?php endforeach; ?>
        <?php if ($footer['rss_label'] !== ''): ?><li><a href="/feed.xml"<?= $linkStyle ?>><?= e($footer['rss_label']) ?></a></li><?php endif; ?>
      </ul>
    </nav>
    <div class="footer-news" id="newsletter">
      <h2 class="footer-title"<?= $titleStyle ?>><?= e($news['title']) ?></h2>
      <?php if ($news['text'] !== ''): ?><p class="footer-text"<?= $textStyle ?>><?= e($news['text']) ?></p><?php endif; ?>
      <?php foreach (take_flash_group('newsletter') as $f): ?>
        <p class="notice notice-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></p>
      <?php endforeach; ?>
      <form class="news-form" action="/newsletter" method="post">
        <?= csrf_field() ?><?= spam_fields() ?>
        <label class="sr" for="news-email">Seu e-mail</label>
        <input id="news-email" type="email" name="email" placeholder="<?= e($news['placeholder']) ?>" required>
        <button class="btn" type="submit"<?= type_style('footer_news_button') ?>><?= e($news['button']) ?></button>
      </form>
      <?php if ($site['email'] !== ''): ?>
        <p class="footer-contact"<?= type_style('footer_email') ?>><?php if ($footer['email_label'] !== ''): ?><strong><?= e($footer['email_label']) ?></strong> <?php endif; ?><a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a></p>
      <?php endif; ?>
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
