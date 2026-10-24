<?php $cats = array_filter(categories_with_posts(), fn ($c) => $c['total'] > 0); ?>
<nav class="cat-bar" aria-label="Categorias do blog">
  <ul>
    <li><a href="/blog"<?= empty($active) ? ' aria-current="page"' : '' ?>>Todos os posts</a></li>
    <?php foreach ($cats as $c): ?>
      <li><a href="/blog/categoria/<?= e($c['slug']) ?>"<?= ($active ?? null) === $c['slug'] ? ' aria-current="page"' : '' ?>><?= e($c['name']) ?></a></li>
    <?php endforeach; ?>
  </ul>
  <form class="cat-search" action="/busca" method="get" role="search">
    <label class="sr" for="busca-blog">Buscar no blog</label>
    <input id="busca-blog" type="search" name="q" placeholder="Buscar" value="<?= e($search ?? '') ?>">
  </form>
</nav>
