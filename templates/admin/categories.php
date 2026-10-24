<div class="a-head"><h1>Categorias</h1></div>
<div class="a-cols a-cols-form">
  <form class="a-card" method="post" action="/admin/categorias">
    <?= csrf_field() ?>
    <h2><?= $editing ? 'Editar categoria' : 'Nova categoria' ?></h2>
    <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
    <label>Nome<input name="name" value="<?= e($editing['name'] ?? '') ?>" required maxlength="120"></label>
    <label>Endereço <small>(opcional)</small><input name="slug" value="<?= e($editing['slug'] ?? '') ?>" placeholder="gerado pelo nome"></label>
    <label>Descrição <small>(aparece no topo da página da categoria)</small><textarea name="description" rows="3"><?= e($editing['description'] ?? '') ?></textarea></label>
    <label>Ordem na barra de categorias<input type="number" name="sort_order" value="<?= (int) ($editing['sort_order'] ?? 0) ?>"></label>
    <div class="a-row-tight">
      <button class="a-btn"><?= $editing ? 'Salvar' : 'Criar categoria' ?></button>
      <?php if ($editing): ?><a class="a-btn a-btn-ghost" href="/admin/categorias">Cancelar</a><?php endif; ?>
    </div>
  </form>
  <div class="a-table-wrap">
    <table class="a-table">
      <thead><tr><th>Nome</th><th>Endereço</th><th class="num">Posts</th><th class="num">Ordem</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($categories as $c): ?>
        <tr>
          <td><a class="a-strong" href="/admin/categorias?editar=<?= (int) $c['id'] ?>"><?= e($c['name']) ?></a></td>
          <td><a class="a-muted" href="/blog/categoria/<?= e($c['slug']) ?>" target="_blank">/<?= e($c['slug']) ?></a></td>
          <td class="num"><?= (int) $c['total'] ?></td>
          <td class="num"><?= (int) $c['sort_order'] ?></td>
          <td class="num a-nowrap">
            <a href="/admin/categorias?editar=<?= (int) $c['id'] ?>">Editar</a>
            <form class="a-inline" method="post" action="/admin/categorias/<?= (int) $c['id'] ?>/excluir"><?= csrf_field() ?><button class="a-link a-danger-text" data-confirm="Excluir a categoria “<?= e($c['name']) ?>”? Os posts ficam sem categoria.">Excluir</button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
