<h1>Olá, <?= e(explode(' ', $user['name'])[0]) ?>!</h1>
<p>Este é o painel do Arquivo Sapucaí. As telas de posts, editor, comentários e aparência chegam na próxima etapa.</p>
<div class="a-stats">
  <?php foreach ($stats as $label => $value): ?>
    <div class="a-stat"><strong><?= $value ?></strong><span><?= e($label) ?></span></div>
  <?php endforeach; ?>
</div>
