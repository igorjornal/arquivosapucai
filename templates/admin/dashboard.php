<div class="a-head">
  <h1>Olá, <?= e(explode(' ', $user['name'])[0]) ?>!</h1>
  <a class="a-btn" href="/admin/posts/novo">+ Escrever post</a>
</div>
<?php if (is_test_host()): ?>
  <p class="notice notice-info"><strong>Site de teste escondido do Google.</strong> Enquanto o site estiver em <code><?= e($_SERVER['HTTP_HOST']) ?></code>, o Google é avisado para não guardar nada. No endereço oficial, isso se desliga sozinho.</p>
<?php endif; ?>
<div class="a-stats">
  <?php foreach ($stats as [$label, $value, $link]): ?>
    <a class="a-stat" href="<?= e($link) ?>"><strong><?= $value ?></strong><span><?= e($label) ?></span></a>
  <?php endforeach; ?>
</div>
<div class="a-cols">
  <section class="a-card">
    <h2>Editados recentemente</h2>
    <?php if (!$recent): ?><p class="a-muted">Nenhum post ainda.</p><?php endif; ?>
    <ul class="a-list">
      <?php foreach ($recent as $p): ?>
        <li><a href="/admin/posts/<?= (int) $p['id'] ?>"><?= e($p['title']) ?></a> <?php partial_status($p); ?></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <section class="a-card">
    <h2>Mais lidos</h2>
    <?php if (!$popular): ?><p class="a-muted">Ainda sem leituras.</p><?php endif; ?>
    <ul class="a-list">
      <?php foreach ($popular as $p): ?>
        <li><a href="/blog/<?= e($p['slug']) ?>" target="_blank" rel="noopener"><?= e($p['title']) ?></a> <span class="a-muted"><?= (int) $p['views'] ?> leituras</span></li>
      <?php endforeach; ?>
    </ul>
  </section>
</div>
