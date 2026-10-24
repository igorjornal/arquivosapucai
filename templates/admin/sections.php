<div class="a-head">
  <h1>Seções da home</h1>
  <a class="a-btn a-btn-ghost" href="/" target="_blank" rel="noopener">Ver a home ↗</a>
</div>
<p class="a-muted">A home mostra as seções nesta ordem, de cima para baixo. Use ↑ ↓ para mudar a ordem e o olho para esconder sem apagar.</p>
<div class="a-sections">
<?php foreach ($sections as $i => $s):
    $t = $types[$s['type']]; ?>
  <div class="a-card a-section-row<?= $s['active'] ? '' : ' a-off' ?>">
    <div class="a-order">
      <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="mover"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><input type="hidden" name="direcao" value="cima"><button class="a-icon-btn" title="Subir"<?= $i === 0 ? ' disabled' : '' ?>>↑</button></form>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="mover"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><input type="hidden" name="direcao" value="baixo"><button class="a-icon-btn" title="Descer"<?= $i === count($sections) - 1 ? ' disabled' : '' ?>>↓</button></form>
    </div>
    <div class="a-section-info">
      <a class="a-strong" href="/admin/secoes/<?= (int) $s['id'] ?>"><?= e($s['label']) ?></a>
      <span class="a-muted"><?= e($t['label']) ?><?= $s['anchor'] ? ' · âncora <code>/#' . e($s['anchor']) . '</code>' : '' ?></span>
      <?php if (!$s['active']): ?><span class="a-pill">Escondida</span><?php endif; ?>
    </div>
    <div class="a-row-tight">
      <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="alternar"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><button class="a-btn a-btn-sm a-btn-ghost"><?= $s['active'] ? '👁 Esconder' : '👁 Mostrar' ?></button></form>
      <a class="a-btn a-btn-sm a-btn-ghost" href="/admin/secoes/<?= (int) $s['id'] ?>">Editar</a>
      <?php if (!$t['builtin']): ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="duplicar"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><button class="a-btn a-btn-sm a-btn-ghost">Duplicar</button></form>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><button class="a-btn a-btn-sm a-btn-danger" data-confirm="Apagar a seção “<?= e($s['label']) ?>”?">Apagar</button></form>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
</div>

<h2 class="a-section-title">Adicionar seção</h2>
<div class="a-add-sections">
  <?php foreach ($types as $key => $t): if ($t['builtin']) continue; ?>
    <form method="post" class="a-card a-add-card">
      <?= csrf_field() ?><input type="hidden" name="acao" value="adicionar"><input type="hidden" name="tipo" value="<?= e($key) ?>">
      <strong><?= e($t['label']) ?></strong>
      <span class="a-muted"><?= e($t['help']) ?></span>
      <button class="a-btn a-btn-sm">+ Adicionar</button>
    </form>
  <?php endforeach; ?>
</div>
