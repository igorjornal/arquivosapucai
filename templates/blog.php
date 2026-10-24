<section class="sec sec-blog blog-page">
  <div class="wrap">
    <?php partial('category-bar', ['active' => $activeCategory, 'search' => $search ?? null]); ?>
    <?php if (!empty($author)): ?>
      <div class="author-head">
        <img class="avatar avatar-lg" src="<?= e(avatar_url($author['avatar'])) ?>" alt="" width="96" height="96">
        <div>
          <h1 class="sec-title"><?= e($author['name']) ?></h1>
          <?php if ($author['bio']): ?><p><?= nl2br(e($author['bio'])) ?></p><?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <h1 class="sec-title"><?= e($heading) ?></h1>
      <?php if (!empty($intro)): ?><p class="sec-lead"><?= e($intro) ?></p><?php endif; ?>
    <?php endif; ?>

    <?php if ($posts): ?>
      <div class="cards">
        <?php foreach ($posts as $post) partial('post-card', ['post' => $post]); ?>
      </div>
    <?php elseif (isset($search) && $search === ''): ?>
      <form class="big-search" action="/busca" method="get" role="search">
        <input type="search" name="q" placeholder="Escola, enredo, ano, carnavalesco…" aria-label="Buscar" autofocus>
        <button class="btn" type="submit">Buscar</button>
      </form>
    <?php else: ?>
      <p class="empty">Nenhum post encontrado por aqui.</p>
    <?php endif; ?>

    <?php if ($pagination['pages'] > 1):
        $sep = str_contains($baseLink, '?') ? '&' : '?'; ?>
      <nav class="pager" aria-label="Páginas">
        <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
          <a href="<?= e($baseLink . ($i > 1 ? $sep . 'pagina=' . $i : '')) ?>"<?= $i === $pagination['page'] ? ' aria-current="page"' : '' ?>><?= $i ?></a>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>
