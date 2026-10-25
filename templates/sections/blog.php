<?php $posts = latest_posts((int) $home['blog_count']); ?>
<section class="sec sec-blog" id="<?= e($section['anchor'] ?: 'blog') ?>">
  <div class="wrap">
    <div class="sec-head">
      <h2 class="sec-title"<?= type_style('blog_title') ?>><?= e($home['blog_title']) ?></h2>
      <?php if ($home['blog_button'] !== ''): ?><a class="btn btn-red" href="/blog"<?= type_style('blog_button') ?>><?= e($home['blog_button']) ?></a><?php endif; ?>
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
