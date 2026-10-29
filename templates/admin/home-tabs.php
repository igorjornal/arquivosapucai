<?php /* Abas da Página inicial. Variável: $current (textos | secoes | banners) */ ?>
<nav class="a-tabs">
  <?php foreach (['textos' => ['/admin/pagina-inicial', 'Textos, imagens e cores das seções'], 'secoes' => ['/admin/secoes', 'Ordem das seções e seções extras'], 'banners' => ['/admin/banners', 'Banners do topo']] as $k => [$href, $label]): ?>
    <a href="<?= $href ?>"<?= $current === $k ? ' aria-current="page"' : '' ?>><?= $label ?></a>
  <?php endforeach; ?>
</nav>
