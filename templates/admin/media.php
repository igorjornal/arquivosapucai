<div class="a-head"><h1>Imagens</h1></div>
<form class="a-card a-upload" method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <label class="a-drop" data-drop>
    <input type="file" name="arquivos[]" accept="image/*" multiple required onchange="this.form.submit()">
    <strong>Clique ou arraste imagens aqui</strong>
    <span class="a-muted">JPG, PNG, WebP ou GIF, até 15 MB. Fotos grandes são reduzidas e convertidas para WebP sozinhas.</span>
  </label>
</form>
<?php if (!$items): ?>
  <p class="a-muted">Nenhuma imagem enviada ainda.</p>
<?php else: ?>
<div class="a-gallery">
  <?php foreach ($items as $m): ?>
    <figure class="a-gallery-item">
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
<?= admin_pager($pagination, '/admin/midia') ?>
<?php endif; ?>
