<div class="a-head">
  <h1>Páginas</h1>
  <a class="a-btn" href="/admin/paginas/nova">+ Nova página</a>
</div>
<p class="a-muted">Páginas fixas do site, com endereço próprio (ex.: <code>/acervo</code>, <code>/historia</code>). Boas para o Google. Para aparecer no menu, adicione em <a href="/admin/cabecalho#g-itens">Cabeçalho e menu → Itens do menu</a>.</p>
<?php if (!$pages): ?>
  <div class="a-card a-empty"><p>Nenhuma página ainda.</p><a class="a-btn" href="/admin/paginas/nova">Criar a primeira</a></div>
<?php else: ?>
<div class="a-table-wrap">
<table class="a-table">
  <thead><tr><th>Título</th><th>Endereço</th><th>Situação</th><th>Atualizada</th></tr></thead>
  <tbody>
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
<?php endif; ?>
