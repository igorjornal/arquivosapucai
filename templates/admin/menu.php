<div class="a-head"><h1>Menu</h1></div>
<p class="a-muted">Os itens aparecem no topo do site e no rodapé, nesta ordem. Para levar a uma seção da home, use <code>/#arquivo</code>, <code>/#playlists</code>, <code>/#acervo</code> ou <code>/#contato</code>.</p>
<div class="a-menu-list">
<?php foreach ($items as $i => $it): ?>
  <div class="a-card a-menu-item">
    <form method="post" class="a-menu-form">
      <?= csrf_field() ?><input type="hidden" name="acao" value="salvar"><input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
      <label>Nome<input name="label" value="<?= e($it['label']) ?>" required></label>
      <label>Endereço<input name="url" value="<?= e($it['url']) ?>" required></label>
      <label class="a-check"><input type="checkbox" name="new_tab" value="1"<?= $it['new_tab'] ? ' checked' : '' ?>> Nova aba</label>
      <button class="a-btn a-btn-sm a-btn-ghost">Salvar</button>
    </form>
    <div class="a-order">
      <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="mover"><input type="hidden" name="id" value="<?= (int) $it['id'] ?>"><input type="hidden" name="direcao" value="cima"><button class="a-icon-btn" title="Subir"<?= $i === 0 ? ' disabled' : '' ?>>↑</button></form>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="mover"><input type="hidden" name="id" value="<?= (int) $it['id'] ?>"><input type="hidden" name="direcao" value="baixo"><button class="a-icon-btn" title="Descer"<?= $i === count($items) - 1 ? ' disabled' : '' ?>>↓</button></form>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int) $it['id'] ?>"><button class="a-icon-btn a-danger-text" title="Remover" data-confirm="Remover “<?= e($it['label']) ?>” do menu?">×</button></form>
    </div>
  </div>
<?php endforeach; ?>
</div>
<form method="post" class="a-card">
  <?= csrf_field() ?><input type="hidden" name="acao" value="salvar">
  <h2>Adicionar item</h2>
  <div class="a-row-2">
    <label>Nome<input name="label" required placeholder="Guia 2027"></label>
    <label>Endereço<input name="url" required placeholder="/blog/categoria/guia-dos-enredos" list="menu-sugestoes"></label>
  </div>
  <datalist id="menu-sugestoes">
    <option value="/">Home</option><option value="/blog">Blog</option><option value="/busca">Busca</option>
    <?php foreach ($categories as $c): ?><option value="/blog/categoria/<?= e($c['slug']) ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
  </datalist>
  <label class="a-check"><input type="checkbox" name="new_tab" value="1"> Abrir em nova aba</label>
  <button class="a-btn">Adicionar ao menu</button>
</form>
