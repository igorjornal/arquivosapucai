<?php
$cardStyle = $post['card_bg'] ? ' style="--card-bg:' . e($post['card_bg']) . '"' : '';
$ratio = ratio_css($post['card_ratio'] ?: setting('blog')['card_ratio']);
$mediaStyle = ' style="aspect-ratio:' . ($ratio ?? 'auto') . '"';
?>
<article class="card"<?= $cardStyle ?>>
  <a class="card-link" href="<?= e(post_url($post)) ?>">
    <div class="card-media<?= $ratio ? '' : ' is-original' ?>"<?= $mediaStyle ?>>
      <?php if ($post['cover_image']): ?>
        <img src="<?= e(media_url($post['cover_image'])) ?>" alt="<?= e($post['cover_alt'] ?: $post['title']) ?>" loading="lazy">
      <?php endif; ?>
    </div>
    <div class="card-body">
      <div class="card-meta">
        <img class="avatar" src="<?= e(avatar_url($post['author_avatar'])) ?>" alt="" width="28" height="28">
        <span><?= e($post['author_name']) ?><br><time datetime="<?= e(date('c', strtotime($post['published_at']))) ?>"><?= e(date_short($post['published_at'])) ?></time></span>
      </div>
      <?php if ($post['category_name']): ?><span class="chip"><?= e($post['category_name']) ?></span><?php endif; ?>
      <h3 class="card-title"><?= e($post['title']) ?></h3>
      <p class="card-excerpt"><?= e(post_excerpt($post, 700)) ?></p>
    </div>
  </a>
</article>
