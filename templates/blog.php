<section class="sec sec-blog blog-page">
  <div class="wrap">
    <?php if (!empty($headingKey)): ?>
      <?php // Página principal do Blog: box de abertura em cima, depois a barra de categorias ?>
      <header class="blog-box">
        <h1<?= type_style('blog_page_title') ?>><?= e($heading) ?></h1>
        <?php if (!empty($intro)): ?><p<?= type_style('blog_page_intro') ?>><?= nl2br(e($intro)) ?></p><?php endif; ?>
      </header>
    <?php endif; ?>
    <?php partial('category-bar', ['active' => $activeCategory, 'search' => $search ?? null]); ?>
    <?php if (!empty($headingKey)): /* o título já está no box de abertura */ ?>
    <?php elseif (!empty($author)): ?>
      <div class="author-head">
        <img class="avatar avatar-lg" src="<?= e(avatar_url($author['avatar'])) ?>" alt="" width="96" height="96">
        <div>
          <h1 class="sec-title"><?= e($author['name']) ?></h1>
          <?php if ($author['bio']): ?><p><?= nl2br(e($author['bio'])) ?></p><?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <h1 class="sec-title"<?= !empty($headingKey) ? type_style($headingKey) : '' ?>><?= e($heading) ?></h1>
      <?php if (!empty($intro)): ?><p class="sec-lead"><?= e($intro) ?></p><?php endif; ?>
    <?php endif; ?>

    <?php if ($posts): ?>
      <div class="<?= e(cards_class()) ?>">
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

    <?php partial('pager', ['pagination' => $pagination, 'baseLink' => $baseLink]); ?>
  </div>
</section>
<?php partial('contact', ['home' => setting('home'), 'anchor' => 'contato']); ?>
