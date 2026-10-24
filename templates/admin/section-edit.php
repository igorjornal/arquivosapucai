<?php
$d = $s['data'];
$editTexts = [
    'hero'      => ['/admin/banners', 'Banners do topo'],
    'blog'      => ['/admin/conteudo?aba=home', 'Conteúdo do site → Textos da home'],
    'arquivo'   => ['/admin/conteudo?aba=home', 'Conteúdo do site → Textos da home'],
    'playlists' => ['/admin/conteudo?aba=home', 'Conteúdo do site → Textos da home'],
    'acervo'    => ['/admin/conteudo?aba=home', 'Conteúdo do site → Textos da home'],
    'contact'   => ['/admin/conteudo?aba=home', 'Conteúdo do site → Textos da home'],
];
?>
<p><a href="/admin/secoes">← Seções da home</a></p>
<div class="a-head"><h1><?= e($s['label']) ?></h1></div>
<form method="post">
  <?= csrf_field() ?>
  <div class="a-cols a-cols-form">
    <section class="a-card">
      <h2>Geral</h2>
      <label>Nome no painel<input name="label" value="<?= e($s['label']) ?>" maxlength="120"></label>
      <label>Âncora para o menu <small>(opcional)</small><input name="anchor" value="<?= e($s['anchor']) ?>" placeholder="ex.: guia-2027"></label>
      <small class="a-help">Com a âncora “guia-2027”, um item de menu com endereço <code>/#guia-2027</code> rola até esta seção.</small>
      <label class="a-check"><input type="checkbox" name="active" value="1"<?= $s['active'] ? ' checked' : '' ?>> Mostrar na home</label>
      <?php if (!$builtin): ?>
        <h3>Cores desta seção</h3>
        <?php foreach (['bg' => 'Fundo', 'title_color' => 'Título', 'text_color' => 'Texto'] as $k => $label): ?>
          <label class="a-color"><input type="color" name="<?= $k ?>" value="<?= e(strtolower($d[$k])) ?>" data-color><span><?= $label ?></span><code data-color-code><?= e(strtoupper($d[$k])) ?></code></label>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

    <section class="a-card">
      <h2>Conteúdo</h2>
      <?php if ($builtin): ?>
        <p>Os textos e imagens desta seção ficam em <a href="<?= e($editTexts[$s['type']][0]) ?>"><?= e($editTexts[$s['type']][1]) ?></a>. As cores ficam em <a href="/admin/aparencia">Aparência</a>.</p>
      <?php else: ?>
        <label>Título <small>(opcional)</small><input name="title" value="<?= e($d['title']) ?>"></label>

        <?php if ($s['type'] === 'text_image'): ?>
          <label>Texto <small>(linha em branco separa parágrafos)</small><textarea name="text" rows="7"><?= e($d['text']) ?></textarea></label>
          <div class="a-row-2">
            <label>Texto do botão <small>(opcional)</small><input name="button_text" value="<?= e($d['button_text']) ?>"></label>
            <label>Link do botão<input name="button_link" value="<?= e($d['button_link']) ?>"></label>
          </div>
          <label class="a-check"><input type="checkbox" name="button_new_tab" value="1"<?= ($d['button_new_tab'] ?? preg_match('#^https?://#', (string) $d['button_link'])) ? ' checked' : '' ?>> Abrir o link em nova aba</label>
          <?= image_field('image', $d['image'], 'Imagem') ?>
          <div class="a-label">Imagem do lado</div>
          <div class="a-seg">
            <label><input type="radio" name="image_side" value="left"<?= $d['image_side'] !== 'right' ? ' checked' : '' ?>><span>Esquerdo</span></label>
            <label><input type="radio" name="image_side" value="right"<?= $d['image_side'] === 'right' ? ' checked' : '' ?>><span>Direito</span></label>
          </div>

        <?php elseif ($s['type'] === 'category_posts'): ?>
          <label>Categoria
            <select name="category_id" required><option value="">Escolha…</option><?= select_options(array_column($categories, 'name', 'id'), (string) $d['category_id']) ?></select>
          </label>
          <div class="a-row-2">
            <label>Quantos posts<input type="number" name="count" min="1" max="24" value="<?= (int) $d['count'] ?>"></label>
            <label>Texto do botão <small>(vazio = sem botão)</small><input name="button_text" value="<?= e($d['button_text']) ?>"></label>
          </div>
          <small class="a-help">Se o título ficar vazio, aparece o nome da categoria. Se a categoria não tiver posts, a seção não aparece.</small>

        <?php elseif ($s['type'] === 'rich_text'): ?>
          <label class="a-check"><input type="checkbox" name="narrow" value="1"<?= $d['narrow'] ? ' checked' : '' ?>> Coluna estreita (melhor para leitura)</label>
          <textarea name="html" data-editor><?= e($d['html']) ?></textarea>

        <?php elseif ($s['type'] === 'video'): ?>
          <label>Link do vídeo no YouTube<input name="url" value="<?= e($d['url']) ?>" placeholder="https://www.youtube.com/watch?v=..."></label>
          <label>Texto <small>(opcional)</small><textarea name="text" rows="4"><?= e($d['text']) ?></textarea></label>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  </div>
  <div class="a-sticky-save"><button class="a-btn" type="submit">Salvar seção</button> <a class="a-btn a-btn-ghost" href="/#<?= e($s['anchor'] ?: ($builtin ? $s['type'] : 'secao-' . $s['id'])) ?>" target="_blank" rel="noopener">Ver na home ↗</a></div>
</form>
