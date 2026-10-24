<?php $isSelf = $u['id'] && (int) $u['id'] === (int) current_user()['id']; ?>
<?php if ($adminMode): ?><p><a href="/admin/autores">← Todos os autores</a></p><?php endif; ?>
<div class="a-head"><h1><?= e($title) ?></h1></div>
<?php if ($errors): ?><div class="notice notice-error"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>
<form class="a-cols a-cols-form" method="post" autocomplete="off">
  <?= csrf_field() ?>
  <section class="a-card">
    <h2>Dados</h2>
    <label>Nome (aparece nos posts)<input name="name" value="<?= e($u['name']) ?>" required maxlength="120"></label>
    <label>E-mail (usado no login)<input type="email" name="email" value="<?= e($u['email']) ?>" required maxlength="190"></label>
    <label>Minibiografia (aparece na página do autor)<textarea name="bio" rows="4" maxlength="1500"><?= e($u['bio']) ?></textarea></label>
    <?= image_field('avatar', $u['avatar'], 'Foto', 'Quadrada fica melhor.') ?>
  </section>
  <section class="a-card">
    <h2><?= $u['id'] ? 'Trocar senha' : 'Senha inicial' ?></h2>
    <?php if (!$adminMode): ?>
      <label>Senha atual<input type="password" name="current_password" autocomplete="current-password"></label>
    <?php endif; ?>
    <label><?= $u['id'] ? 'Nova senha (deixe vazio para manter)' : 'Senha' ?><input type="password" name="password" minlength="10" autocomplete="new-password"></label>
    <small class="a-help">Pelo menos 10 caracteres.</small>
    <?php if ($adminMode): ?>
      <h2>Permissões</h2>
      <?php if ($isSelf): ?>
        <p class="a-muted">Você é administrador. Para evitar perder o acesso, não dá para tirar a sua própria permissão.</p>
      <?php else: ?>
        <label>Papel
          <select name="role"><?= select_options(['author' => 'Autor', 'admin' => 'Administrador'], $u['role']) ?></select>
        </label>
        <label class="a-check"><input type="checkbox" name="active" value="1"<?= $u['active'] ? ' checked' : '' ?>> Acesso ativo (desmarque para bloquear o login sem apagar os posts)</label>
      <?php endif; ?>
    <?php endif; ?>
    <button class="a-btn" type="submit">Salvar</button>
  </section>
</form>
