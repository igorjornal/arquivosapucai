<?php /* Abas de Posts. Variável: $current (posts | categorias | tags) */ ?>
<nav class="a-tabs a-tabs-top">
  <?php foreach (['posts' => ['/admin/posts', 'Posts'], 'categorias' => ['/admin/categorias', 'Categorias'], 'tags' => ['/admin/tags', 'Tags']] as $k => [$href, $label]): ?>
    <a href="<?= $href ?>"<?= $current === $k ? ' aria-current="page"' : '' ?>><?= $label ?></a>
  <?php endforeach; ?>
</nav>
