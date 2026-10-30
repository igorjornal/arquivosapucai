<?= render_to_string('admin/posts-tabs', ['current' => 'posts']) ?>
<div class="a-head">
  <h1>Posts</h1>
  <a class="a-btn" href="/admin/posts/novo">+ Escrever post</a>
</div>
<form class="a-filters" method="get">
  <nav class="a-tabs">
    <?php foreach (['' => 'Todos', 'publicados' => 'Publicados', 'agendados' => 'Agendados', 'rascunhos' => 'Rascunhos'] as $k => $label): ?>
      <a href="/admin/posts<?= $k ? '?status=' . $k : '' ?>"<?= $status === $k ? ' aria-current="page"' : '' ?>><?= $label ?></a>
    <?php endforeach; ?>
  </nav>
  <input type="hidden" name="status" value="<?= e($status) ?>">
  <select name="categoria" onchange="this.form.submit()" aria-label="Categoria">
    <option value="">Todas as categorias</option>
    <?= select_options(array_column($categories, 'name', 'id'), (string) $category) ?>
  </select>
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar pelo título" aria-label="Buscar">
</form>

<?php if (!$posts): ?>
  <div class="a-card a-empty"><p>Nenhum post por aqui.</p><a class="a-btn" href="/admin/posts/novo">Escrever o primeiro</a></div>
<?php else: ?>
<div class="a-table-wrap">
<table class="a-table">
  <thead><tr><th>Título</th><th>Situação</th><th>Categoria</th><th>Autor</th><th>Data</th><th class="num">Leituras</th><th class="num">Coment.</th></tr></thead>
  <tbody>
  <?php foreach ($posts as $p): ?>
    <tr>
      <td class="a-title-cell">
        <a class="a-strong" href="/admin/posts/<?= (int) $p['id'] ?>"><?= e($p['title']) ?></a>
        <div class="a-row-actions">
          <a href="/admin/posts/<?= (int) $p['id'] ?>">Editar</a>
          <a href="/blog/<?= e($p['slug']) ?><?= $p['status'] === 'draft' || strtotime((string) $p['published_at']) > time() ? '?previa=1' : '' ?>" target="_blank" rel="noopener">Ver</a>
        </div>
      </td>
      <td><?php partial_status($p); ?><?= $p['featured'] ? ' <span class="a-pill">Destaque</span>' : '' ?></td>
      <td><?= e($p['category_name'] ?? '—') ?></td>
      <td><?= $p['author_name'] !== null ? e($p['author_name']) : '<span class="a-muted">Sem autor (escolha na tela do post)</span>' ?></td>
      <td class="a-nowrap"><?= $p['published_at'] ? e(date('d/m/Y H:i', strtotime($p['published_at']))) : '—' ?></td>
      <td class="num"><?= (int) $p['views'] ?></td>
      <td class="num"><?= (int) $p['comments_count'] ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?= admin_pager($pagination, '/admin/posts?' . http_build_query(array_filter(['status' => $status, 'q' => $q, 'categoria' => $category ?: null]))) ?>
<?php endif; ?>
