<?= render_to_string('admin/posts-tabs', ['current' => 'tags']) ?>
<div class="a-head"><h1>Tags</h1></div>
<p class="a-muted">As tags são criadas ao escrever o post (campo “Tags”). Aqui você renomeia ou apaga.</p>
<?php if (!$tags): ?>
  <div class="a-card a-empty"><p>Nenhuma tag ainda.</p></div>
<?php else: ?>
<div class="a-table-wrap">
<table class="a-table">
  <thead><tr><th>Tag</th><th class="num">Posts</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($tags as $t): ?>
    <tr>
      <td>
        <form class="a-inline-edit" method="post" action="/admin/tags">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
          <input name="name" value="<?= e($t['name']) ?>" aria-label="Nome da tag"><button class="a-btn a-btn-sm a-btn-ghost">Renomear</button>
        </form>
      </td>
      <td class="num"><?= (int) $t['total'] ?></td>
      <td class="num"><form method="post" action="/admin/tags/<?= (int) $t['id'] ?>/excluir"><?= csrf_field() ?><button class="a-link a-danger-text" data-confirm="Excluir a tag #<?= e($t['name']) ?>?">Excluir</button></form></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
