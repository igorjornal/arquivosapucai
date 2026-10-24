<?php
$folderName = '';
foreach ($folders as $f) {
    if ((int) $f['id'] === $current) {
        $folderName = $f['name'];
    }
}
$here = '/admin/midia' . ($current ? '?pasta=' . $current : '');
?>
<div class="a-head"><h1>Imagens<?= $folderName !== '' ? ' <span class="a-muted">/ ' . e($folderName) . '</span>' : ($current === -1 ? ' <span class="a-muted">/ Sem pasta</span>' : '') ?></h1></div>

<div class="a-media-layout">
  <aside class="a-folders a-card">
    <h2>Pastas</h2>
    <nav>
      <a href="/admin/midia"<?= $current === 0 ? ' aria-current="page"' : '' ?>>🗂 Todas <span class="a-muted"><?= $counts['all'] ?></span></a>
      <a href="/admin/midia?pasta=-1"<?= $current === -1 ? ' aria-current="page"' : '' ?>>📄 Sem pasta <span class="a-muted"><?= $counts['none'] ?></span></a>
      <?php foreach ($folders as $f): ?>
        <a href="/admin/midia?pasta=<?= (int) $f['id'] ?>"<?= $current === (int) $f['id'] ? ' aria-current="page"' : '' ?>>📁 <?= e($f['name']) ?> <span class="a-muted"><?= (int) $f['total'] ?></span></a>
      <?php endforeach; ?>
    </nav>
    <form method="post" action="<?= e($here) ?>" class="a-folder-new" data-no-protect>
      <?= csrf_field() ?><input type="hidden" name="acao" value="criar_pasta">
      <label class="sr" for="nova-pasta">Nova pasta</label>
      <input id="nova-pasta" name="nome" placeholder="Nova pasta (ex.: Desfiles 1987)" maxlength="120" required>
      <button class="a-btn a-btn-sm">+ Criar</button>
    </form>
    <?php if ($current > 0): ?>
      <form method="post" action="<?= e($here) ?>" class="a-folder-new" data-no-protect>
        <?= csrf_field() ?><input type="hidden" name="acao" value="renomear_pasta"><input type="hidden" name="folder_id" value="<?= $current ?>">
        <label class="sr" for="renomear">Renomear pasta</label>
        <input id="renomear" name="nome" value="<?= e($folderName) ?>" maxlength="120" required>
        <button class="a-btn a-btn-sm a-btn-ghost">Renomear</button>
      </form>
      <form method="post" action="<?= e($here) ?>">
        <?= csrf_field() ?><input type="hidden" name="acao" value="excluir_pasta"><input type="hidden" name="folder_id" value="<?= $current ?>">
        <button class="a-link a-danger-text" data-confirm="Excluir a pasta “<?= e($folderName) ?>”? As imagens NÃO são apagadas: vão para “Sem pasta”.">Excluir esta pasta</button>
      </form>
    <?php endif; ?>
  </aside>

  <div>
    <form class="a-card a-upload" method="post" action="<?= e($here) ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="folder_id" value="<?= $current > 0 ? $current : '' ?>">
      <label class="a-drop" data-drop>
        <input type="file" name="arquivos[]" accept="image/*" multiple required onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()">
        <strong>Clique ou arraste imagens aqui</strong>
        <span class="a-muted"><?= $current > 0 ? 'Vão direto para a pasta “' . e($folderName) . '”. ' : '' ?>JPG, PNG, WebP ou GIF, até 15 MB. Fotos grandes são reduzidas e convertidas para WebP sozinhas.</span>
      </label>
    </form>

    <?php if (!$items): ?>
      <p class="a-muted">Nenhuma imagem aqui ainda.</p>
    <?php else: ?>
    <form method="post" action="<?= e($here) ?>" id="mover-form" class="a-move-bar" data-no-protect>
      <?= csrf_field() ?><input type="hidden" name="acao" value="mover">
      <label class="a-check"><input type="checkbox" data-select-all> Marcar todas</label>
      <span class="a-muted" data-selected-count>0 marcadas</span>
      <label class="sr" for="destino">Mover para</label>
      <select id="destino" name="destino">
        <option value="0">Mover para: Sem pasta</option>
        <?php foreach ($folders as $f): ?><option value="<?= (int) $f['id'] ?>">Mover para: <?= e($f['name']) ?></option><?php endforeach; ?>
      </select>
      <button class="a-btn a-btn-sm">Mover marcadas</button>
    </form>
    <div class="a-gallery">
      <?php foreach ($items as $m): ?>
        <figure class="a-gallery-item">
          <label class="a-gallery-check"><input type="checkbox" name="ids[]" value="<?= (int) $m['id'] ?>" form="mover-form" data-media-check><span class="sr">Marcar</span></label>
          <a href="<?= e(media_url($m['path'])) ?>" target="_blank" rel="noopener"><img src="<?= e(media_url($m['path'])) ?>" alt="" loading="lazy"></a>
          <figcaption>
            <span title="<?= e($m['original_name']) ?>"><?= e($m['original_name'] ?: basename($m['path'])) ?></span>
            <small class="a-muted"><?= (int) $m['width'] ?>×<?= (int) $m['height'] ?> · <?= number_format($m['size'] / 1024, 0, ',', '.') ?> KB</small>
            <span class="a-row-tight">
              <button type="button" class="a-link" data-copy-text="<?= e(media_url($m['path'])) ?>">Copiar endereço</button>
              <form method="post" action="/admin/midia/<?= (int) $m['id'] ?>/excluir"><?= csrf_field() ?><button class="a-link a-danger-text" data-confirm="Excluir esta imagem? Se ela estiver em algum post, vai sumir de lá.">Excluir</button></form>
            </span>
          </figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
    <?= admin_pager($pagination, $here) ?>
    <?php endif; ?>
  </div>
</div>
