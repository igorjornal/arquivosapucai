<?php if ($pagination['pages'] > 1):
    $sep = str_contains($baseLink, '?') ? '&' : '?'; ?>
  <nav class="pager" aria-label="Páginas">
    <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
      <a href="<?= e($baseLink . ($i > 1 ? $sep . 'pagina=' . $i : '')) ?>"<?= $i === $pagination['page'] ? ' aria-current="page"' : '' ?>><?= $i ?></a>
    <?php endfor; ?>
  </nav>
<?php endif; ?>
