<div class="a-head">
  <h1>Páginas</h1>
  <a class="a-btn" href="/admin/paginas/nova">+ Nova página</a>
</div>
<p class="a-muted">Páginas fixas do site, com endereço próprio (ex.: <code>/acervo</code>, <code>/historia</code>). Boas para o Google. Para aparecer no menu, adicione em <a href="/admin/cabecalho#g-itens">Cabeçalho e menu → Itens do menu</a>.</p>
<div class="a-table-wrap">
<table class="a-table">
  <thead><tr><th>Título</th><th>Endereço</th><th>Situação</th><th>Atualizada</th></tr></thead>
  <tbody>
    <tr>
      <td class="a-title-cell"><a class="a-strong" href="/admin/paginas/blog"><?= e(setting('blog')['page_title']) ?></a> <span class="a-pill">Lista dos posts</span>
        <div class="a-row-actions"><a href="/admin/paginas/blog">Editar</a><a href="/blog" target="_blank" rel="noopener">Ver</a></div></td>
      <td><code>/blog</code></td>
      <td><span class="a-pill a-pill-pub">Publicada</span></td>
      <td class="a-nowrap">—</td>
    </tr>
  <?php foreach ($pages as $pg): ?>
    <tr>
      <td class="a-title-cell"><a class="a-strong" href="/admin/paginas/<?= (int) $pg['id'] ?>"><?= e($pg['title']) ?></a>
        <div class="a-row-actions"><a href="/admin/paginas/<?= (int) $pg['id'] ?>">Editar</a><a href="/<?= e($pg['slug']) ?><?= $pg['status'] === 'published' ? '' : '?previa=1' ?>" target="_blank" rel="noopener">Ver</a></div></td>
      <td><code>/<?= e($pg['slug']) ?></code></td>
      <td><?= $pg['status'] === 'published' ? '<span class="a-pill a-pill-pub">Publicada</span>' : '<span class="a-pill a-pill-draft">Rascunho</span>' ?></td>
      <td class="a-nowrap"><?= e(date('d/m/Y H:i', strtotime($pg['updated_at']))) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
