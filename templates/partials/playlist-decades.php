<?php /* Página de Playlists: cada década é uma barra que abre e fecha (a primeira começa aberta). Variável: $pl */ ?>
<section class="pl-decades wrap" aria-label="Playlists por década">
  <?php foreach ($pl['decades'] as $i => $dec): $n = count($dec['items']); ?>
    <details class="pl-decade"<?= $i === 0 ? ' open' : '' ?>>
      <summary><span class="pl-dname"><?= e($dec['name']) ?></span><?php if ($pl['count_label'] !== '' && $n): ?><small><?= $n ?> <?= e($n === 1 ? preg_replace('/s$/u', '', $pl['count_label']) : $pl['count_label']) ?></small><?php endif; ?><span class="pl-toggle" aria-hidden="true"></span></summary>
      <?php if ($n): ?>
        <div class="pl-grid">
          <?php foreach ($dec['items'] as $it):
              // O ano encolhe conforme o tamanho do texto, para nunca encostar na borda
              $len = max(4, mb_strlen($it['year']));
              $size = round(min(19, 76 / $len), 1); ?>
            <a class="pl-cap" href="<?= e($it['link'] ?: '#') ?>"<?= $it['link'] ? ' target="_blank" rel="noopener"' : '' ?> aria-label="<?= e(trim($it['year'] . ' ' . $it['title'] . ' ' . $it['subtitle'])) ?>">
              <span class="pl-play" aria-hidden="true"></span>
              <strong class="pl-year" style="font-size:<?= $size ?>cqi"><?= e($it['year']) ?></strong>
              <?php if ($it['title'] !== ''): ?><span class="pl-title"><?= e($it['title']) ?></span><?php endif; ?>
              <?php if ($it['subtitle'] !== ''): ?><span class="pl-sub"><?= e($it['subtitle']) ?></span><?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="pl-empty">Em breve.</p>
      <?php endif; ?>
    </details>
  <?php endforeach; ?>
</section>
