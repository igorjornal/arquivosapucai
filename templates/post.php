<?php
$blog = setting('blog');
$shareUrl = urlencode(absolute_url(post_url($post)));
$shareTitle = urlencode($post['title']);
$commentsOpen = $blog['comments_enabled'] && $post['allow_comments'];
$totalComments = array_sum(array_map('count', $comments));
?>
<div class="post-page">
  <div class="wrap post-wrap<?= $sidebar ? ' post-wrap-side' : '' ?>">
    <?php partial('category-bar', ['active' => $post['category_slug']]); ?>

    <div class="post-layout<?= $sidebar ? ' has-side' : '' ?>">
    <div class="post-main">
    <article class="post-box">
      <header class="post-head">
        <div class="post-meta">
          <a href="/blog/autor/<?= e($post['author_slug']) ?>"><img class="avatar" src="<?= e(avatar_url($post['author_avatar'])) ?>" alt="" width="36" height="36"></a>
          <span><a href="/blog/autor/<?= e($post['author_slug']) ?>"><?= e($post['author_name']) ?></a> · <time datetime="<?= e(date('c', strtotime((string) $post['published_at']))) ?>" title="<?= e(date_long($post['published_at'])) ?>"><?= e(date_relative($post['published_at'])) ?></time> · <?= reading_minutes($post['content']) ?> min de leitura</span>
        </div>
        <h1 class="post-title"<?= type_style('post_title') ?>><?= e($post['title']) ?></h1>
        <?php if ($post['status'] !== 'published' || strtotime((string) $post['published_at']) > time()): ?>
          <p class="notice notice-info">Pré-visualização: este post ainda não está publicado.</p>
        <?php endif; ?>
      </header>

      <?php if ($post['cover_image']): ?>
        <?php
        $coverAlign = in_array($post['cover_align'], ['left', 'center', 'right'], true) ? $post['cover_align'] : 'center';
        $coverWidth = max(20, min(100, (int) $post['cover_width']));
        $imgStyle = ratio_css($post['cover_ratio'])
            ? 'aspect-ratio:' . ratio_css($post['cover_ratio']) . ';object-fit:cover;object-position:' . e(preg_replace('/[^0-9% ]/', '', $post['cover_position']))
            : '';
        ?>
        <figure class="post-cover cover-<?= $coverAlign ?>" style="width:<?= $coverWidth ?>%">
          <img src="<?= e(media_url($post['cover_image'])) ?>" alt="<?= e($post['cover_alt'] ?: $post['title']) ?>"<?= $imgStyle ? ' style="' . $imgStyle . '"' : '' ?>>
        </figure>
      <?php endif; ?>

      <div class="post-content">
        <?= $post['content'] /* já sanitizado ao salvar */ ?>
      </div>

      <footer class="post-foot">
        <div class="post-tags">
          <?php if ($post['category_name']): ?><a class="chip" href="/blog/categoria/<?= e($post['category_slug']) ?>"><?= e($post['category_name']) ?></a><?php endif; ?>
          <?php foreach ($tags as $t): ?><a class="tag" href="/blog/tag/<?= e($t['slug']) ?>">#<?= e($t['name']) ?></a><?php endforeach; ?>
        </div>
        <?php if ($blog['share_buttons']): ?>
          <div class="share">
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" rel="noopener" aria-label="Compartilhar no Facebook"><?= social_icon('facebook') ?></a>
            <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" target="_blank" rel="noopener" aria-label="Compartilhar no X"><?= social_icon('x') ?></a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareUrl ?>" target="_blank" rel="noopener" aria-label="Compartilhar no LinkedIn"><?= social_icon('linkedin') ?></a>
            <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareUrl ?>" target="_blank" rel="noopener" aria-label="Compartilhar no WhatsApp"><svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.4.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.5-.3z"/></svg></a>
            <button type="button" data-copy="<?= e(absolute_url(post_url($post))) ?>" aria-label="Copiar link"><?= social_icon('link') ?></button>
          </div>
        <?php endif; ?>
      </footer>
    </article>

    <?php if ($commentsOpen || $totalComments): ?>
    <section class="comments" id="comentarios">
      <h2><?= $totalComments ? $totalComments . ($totalComments === 1 ? ' comentário' : ' comentários') : 'Comentários' ?></h2>
      <?php foreach (take_flashes() as $f): ?>
        <p class="notice notice-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></p>
      <?php endforeach; ?>
      <?php
      $renderComments = function (int $parentId, int $depth) use (&$renderComments, $comments, $commentsOpen) {
          foreach ($comments[$parentId] ?? [] as $c): ?>
            <div class="comment" id="comentario-<?= (int) $c['id'] ?>">
              <div class="comment-avatar" aria-hidden="true"><?= e(gravatar_initials($c['author_name'])) ?></div>
              <div class="comment-body">
                <p class="comment-meta"><strong><?= e($c['author_name']) ?></strong> · <time datetime="<?= e(date('c', strtotime($c['created_at']))) ?>"><?= e(date_relative($c['created_at'])) ?></time></p>
                <p><?= nl2br(e($c['content'])) ?></p>
                <?php if ($commentsOpen && $depth < 2): ?><button class="link-btn" type="button" data-reply="<?= (int) $c['id'] ?>" data-reply-name="<?= e($c['author_name']) ?>">Responder</button><?php endif; ?>
                <?php $renderComments((int) $c['id'], $depth + 1); ?>
              </div>
            </div>
          <?php endforeach;
      };
      $renderComments(0, 0);
      ?>
      <?php if ($commentsOpen): ?>
        <form class="comment-form" action="<?= e(post_url($post)) ?>/comentar" method="post">
          <?= csrf_field() ?><?= spam_fields() ?>
          <input type="hidden" name="resposta_a" value="" data-reply-field>
          <h3>Deixe seu comentário <span class="reply-to" data-reply-label hidden></span></h3>
          <div class="row">
            <label>Nome<input name="nome" required maxlength="120" value="<?= e(old('nome')) ?>" autocomplete="name"></label>
            <label><span>E-mail <small>(não aparece no site)</small></span><input type="email" name="email" required maxlength="190" value="<?= e(old('email')) ?>" autocomplete="email"></label>
          </div>
          <label>Comentário<textarea name="comentario" required rows="5" maxlength="5000"><?= e(old('comentario')) ?></textarea></label>
          <button class="btn btn-dark" type="submit">Publicar comentário</button>
        </form>
      <?php endif; ?>
      <?php forget_old(); ?>
    </section>
    <?php endif; ?>
    </div>

    <?php if ($sidebar): ?>
    <aside class="post-side" aria-label="<?= e($blog['sidebar_title']) ?>">
      <h2><?= e($blog['sidebar_title']) ?></h2>
      <ul>
        <?php foreach ($sidebar as $s): ?>
          <li><a href="<?= e(post_url($s)) ?>">
            <?php if ($s['cover_image']): ?><img src="<?= e(media_url($s['cover_image'])) ?>" alt="" loading="lazy"><?php else: ?><span class="side-noimg" aria-hidden="true"></span><?php endif; ?>
            <span><strong><?= e($s['title']) ?></strong><time datetime="<?= e(date('c', strtotime((string) $s['published_at']))) ?>"><?= e(date_short($s['published_at'])) ?></time></span>
          </a></li>
        <?php endforeach; ?>
      </ul>
      <a class="side-all" href="/blog">Ver todos os posts →</a>
    </aside>
    <?php endif; ?>
    </div>

    <?php if ($related): ?>
    <section class="recent">
      <div class="sec-head"><h2><?= e($blog['related_title']) ?></h2><a href="/blog">Ver tudo</a></div>
      <div class="cards cards-3">
        <?php foreach ($related as $r) partial('post-card', ['post' => $r]); ?>
      </div>
    </section>
    <?php endif; ?>
  </div>
</div>
