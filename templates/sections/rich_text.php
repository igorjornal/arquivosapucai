<?php $d = $section['data']; if (trim((string) $d['html']) === '' && $d['title'] === '') return; ?>
<section class="sec sec-custom" id="<?= e($section['anchor'] ?: 'secao-' . $section['id']) ?>"<?= section_style($d) ?>>
  <div class="wrap<?= $d['narrow'] ? ' narrow' : '' ?>">
    <?php if ($d['title'] !== ''): ?><h2 class="sec-title"><?= e($d['title']) ?></h2><?php endif; ?>
    <div class="post-content section-content"><?= $d['html'] /* sanitizado ao salvar */ ?></div>
  </div>
</section>
