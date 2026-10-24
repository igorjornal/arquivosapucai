<div class="a-head">
  <h1>Autores</h1>
  <a class="a-btn" href="/admin/autores/novo">+ Cadastrar autor</a>
</div>
<p class="a-muted"><strong>Autor</strong> escreve e edita os próprios posts, vê comentários e mensagens. <strong>Administrador</strong> pode tudo, inclusive aparência, conteúdo do site e autores.</p>
<div class="a-table-wrap">
<table class="a-table">
  <thead><tr><th>Nome</th><th>E-mail</th><th>Papel</th><th class="num">Posts</th><th>Último acesso</th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): ?>
    <tr class="<?= $u['active'] ? '' : 'a-off' ?>">
      <td><a class="a-person" href="/admin/autores/<?= (int) $u['id'] ?>"><img src="<?= e(avatar_url($u['avatar'])) ?>" alt="" width="32" height="32"><span class="a-strong"><?= e($u['name']) ?></span></a><?= $u['active'] ? '' : ' <span class="a-pill">Desativado</span>' ?></td>
      <td><?= e($u['email']) ?></td>
      <td><?= $u['role'] === 'admin' ? 'Administrador' : 'Autor' ?></td>
      <td class="num"><?= (int) $u['total'] ?></td>
      <td class="a-nowrap"><?= $u['last_login_at'] ? e(date('d/m/Y H:i', strtotime($u['last_login_at']))) : '—' ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
