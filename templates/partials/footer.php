<?php
$site = setting('site');
$news = setting('newsletter');
$socials = array_filter($site['socials']);
$names = ['youtube' => 'YouTube', 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'x' => 'X', 'tiktok' => 'TikTok', 'linkedin' => 'LinkedIn'];
?>
<footer class="site-footer">
  <div class="wrap footer-grid">
    <div class="footer-brand">
      <a class="footer-logo" href="/"><img src="<?= e(media_url($site['logo'])) ?>" alt="" width="72" height="72"><span><?= e($site['title']) ?></span></a>
      <p><?= e($site['description']) ?></p>
      <?php if ($socials): ?>
        <ul class="socials">
          <?php foreach ($socials as $net => $link): ?>
            <li><a href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e($names[$net] ?? $net) ?>"><?= social_icon($net) ?></a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
    <nav class="footer-nav" aria-label="Rodapé">
      <h2>Navegue</h2>
      <ul>
        <?php foreach (menu_items() as $item): ?>
          <li><a href="<?= e($item['url']) ?>"><?= e($item['label']) ?></a></li>
        <?php endforeach; ?>
        <li><a href="/feed.xml">RSS</a></li>
      </ul>
    </nav>
    <div class="footer-news" id="newsletter">
      <h2><?= e($news['title']) ?></h2>
      <p><?= e($news['text']) ?></p>
      <?php foreach (take_flash_group('newsletter') as $f): ?>
        <p class="notice notice-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></p>
      <?php endforeach; ?>
      <form class="news-form" action="/newsletter" method="post">
        <?= csrf_field() ?><?= spam_fields() ?>
        <label class="sr" for="news-email">Seu e-mail</label>
        <input id="news-email" type="email" name="email" placeholder="Seu melhor e-mail" required>
        <button class="btn" type="submit">Quero receber</button>
      </form>
      <p class="footer-contact"><a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a></p>
    </div>
  </div>
  <div class="footer-word" aria-hidden="true">SAPUCAÍ</div>
  <div class="wrap footer-bottom">
    <span>© <?= date('Y') ?> <?= e($site['title']) ?>. Todos os direitos reservados.</span>
    <a href="/admin">Área do autor</a>
  </div>
</footer>
