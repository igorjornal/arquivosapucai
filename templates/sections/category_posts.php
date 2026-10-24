<?php
$d = $section['data'];
$cat = (int) $d['category_id'] ? db_one('SELECT * FROM categories WHERE id = ?', [(int) $d['category_id']]) : null;
$posts = $cat ? latest_posts(max(1, min(24, (int) $d['count'])), 0, ['category_id' => $cat['id']]) : [];
if (!$posts) return;
?>
<section class="sec sec-blog sec-custom" id="<?= e($section['anchor'] ?: 'secao-' . $section['id']) ?>"<?= section_style($d) ?>>
  <div class="wrap">
    <div class="sec-head">
      <h2 class="sec-title"><?= e($d['title'] !== '' ? $d['title'] : $cat['name']) ?></h2>
      <?php if ($d['button_text'] !== ''): ?><a class="btn btn-red" href="/blog/categoria/<?= e($cat['slug']) ?>"><?= e($d['button_text']) ?></a><?php endif; ?>
    </div>
    <div class="<?= e(cards_class()) ?>">
      <?php foreach ($posts as $post) partial('post-card', ['post' => $post]); ?>
    </div>
  </div>
</section>
