<?php $d = $section['data']; $id = youtube_id((string) $d['url']); if (!$id) return; ?>
<section class="sec sec-custom sec-video" id="<?= e($section['anchor'] ?: 'secao-' . $section['id']) ?>"<?= section_style($d) ?>>
  <div class="wrap">
    <?php if ($d['title'] !== ''): ?><h2 class="sec-title center"><?= e($d['title']) ?></h2><?php endif; ?>
    <?php if ($d['text'] !== ''): ?><div class="sec-intro center"><?= $nl2p((string) $d['text']) ?></div><?php endif; ?>
    <div class="video-feature">
      <iframe src="https://www.youtube-nocookie.com/embed/<?= e($id) ?>" title="<?= e($d['title'] ?: 'Vídeo') ?>" loading="lazy" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
  </div>
</section>
