<div class="a-head"><h1>Banners do topo</h1></div>
<p class="a-muted">O carrossel da home. Tamanho ideal: 1728 × 568 pixels (ou qualquer imagem bem larga).</p>
<?php foreach ($slides as $i => $s): ?>
  <div class="a-card a-banner">
    <img class="a-banner-img" src="<?= e(media_url($s['image'])) ?>" alt="">
    <form method="post" class="a-banner-form">
      <?= csrf_field() ?><input type="hidden" name="acao" value="salvar"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
      <?= image_field('image', $s['image'], 'Imagem') ?>
      <label>Descrição da imagem (acessibilidade)<input name="alt" value="<?= e($s['alt']) ?>"></label>
      <label>Link ao clicar <small>(opcional)</small><input name="link" value="<?= e($s['link']) ?>" placeholder="/blog/guia-dos-enredos-2027"></label>
      <label class="a-check"><input type="checkbox" name="active" value="1"<?= $s['active'] ? ' checked' : '' ?>> Mostrar no site</label>
      <button class="a-btn a-btn-sm">Salvar</button>
    </form>
    <div class="a-order">
      <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="mover"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><input type="hidden" name="direcao" value="cima"><button class="a-icon-btn" title="Subir"<?= $i === 0 ? ' disabled' : '' ?>>↑</button></form>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="mover"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><input type="hidden" name="direcao" value="baixo"><button class="a-icon-btn" title="Descer"<?= $i === count($slides) - 1 ? ' disabled' : '' ?>>↓</button></form>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><button class="a-icon-btn a-danger-text" title="Remover" data-confirm="Remover este banner?">×</button></form>
    </div>
  </div>
<?php endforeach; ?>
<form method="post" class="a-card">
  <?= csrf_field() ?><input type="hidden" name="acao" value="salvar">
  <h2>Adicionar banner</h2>
  <?= image_field('image', '', 'Imagem') ?>
  <label>Descrição da imagem<input name="alt"></label>
  <label>Link ao clicar <small>(opcional)</small><input name="link"></label>
  <input type="hidden" name="active" value="1">
  <button class="a-btn">Adicionar</button>
</form>
