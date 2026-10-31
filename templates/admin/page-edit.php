<?php $live = $page['status'] === 'published'; ?>
<form class="a-post-form" method="post" action="<?= $page['id'] ? '/admin/paginas/' . (int) $page['id'] : '/admin/paginas/nova' ?>" data-post-form>
  <?= csrf_field() ?>
  <div class="a-head">
    <h1><?= e($title) ?></h1>
    <div class="a-row-tight">
      <?php if ($page['id']): ?>
        <a class="a-btn a-btn-ghost" href="/<?= e($page['slug']) ?>?previa=1" target="_blank" rel="noopener">Pré-visualizar ↗</a>
      <?php endif; ?>
      <button class="a-btn a-btn-ghost" type="submit" name="acao" value="rascunho">Salvar rascunho</button>
      <button class="a-btn" type="submit" name="acao" value="publicar"><?= $live ? 'Atualizar' : 'Publicar' ?></button>
    </div>
  </div>
  <?php if ($errors): ?><div class="notice notice-error"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>
  <div class="a-editor-grid">
    <div class="a-editor-main">
      <label class="a-title-input"><span class="sr">Título</span>
        <input name="title" value="<?= e($page['title']) ?>" placeholder="Título da página" required maxlength="255" data-title>
      </label>
      <div class="a-slug"><span>Endereço: /</span><input name="slug" value="<?= e($page['slug']) ?>" placeholder="gerado-pelo-titulo" data-slug aria-label="Endereço da página"></div>
      <label>Subtítulo <small>(opcional, aparece embaixo do título)</small><input name="subtitle" value="<?= e($page['subtitle']) ?>" maxlength="500"></label>
      <textarea name="content" data-editor><?= e($page['content']) ?></textarea>
      <details class="a-card">
        <summary><h2>Google e redes sociais</h2></summary>
        <label>Título para o Google<input name="meta_title" value="<?= e($page['meta_title']) ?>" maxlength="255" placeholder="Se vazio, usa o título da página"></label>
        <label>Descrição para o Google<textarea name="meta_description" rows="2" maxlength="300" placeholder="1 ou 2 frases com as palavras que as pessoas pesquisam"><?= e($page['meta_description']) ?></textarea></label>
      </details>
      <div id="playlists-decadas">
      <?php if ($page['id'] && (int) $page['id'] === $plPageId): ?>
        <?= render_to_string('admin/playlist-decades-field', ['pl' => $pl]) ?>
      <?php elseif (!$plPageId): ?>
        <label class="a-check a-card"><input type="checkbox" name="use_playlists" value="1"> Mostrar nesta página as playlists por década (depois de salvar, o quadro para cadastrar as décadas aparece aqui)</label>
      <?php endif; ?>
      </div>
    </div>
    <aside class="a-editor-side">
      <section class="a-card">
        <h2>Situação</h2>
        <p><?= $live ? '<span class="a-pill a-pill-pub">Publicada</span>' : '<span class="a-pill a-pill-draft">Rascunho</span> <span class="a-muted">só você vê</span>' ?></p>
      </section>
      <section class="a-card">
        <h2>Topo da página</h2>
        <?= image_field('banner_image', $page['banner_image'], 'Imagem da faixa do topo (opcional)') ?>
        <?php $bpos = $page['banner_position'] ?: '50% 50%'; [$bx, $by] = array_map('intval', explode(' ', str_replace('%', '', $bpos)) + [50, 50]); ?>
        <div class="a-focus" data-banner-focus<?= $page['banner_image'] ? '' : ' hidden' ?>>
          <div class="a-label">Parte da foto que aparece na faixa <small>(clique na foto)</small></div>
          <div class="a-focus-img" data-focus-area>
            <img src="<?= e(media_url($page['banner_image'])) ?>" alt="" data-focus-img>
            <span class="a-focus-dot" data-focus-dot style="left:<?= $bx ?>%;top:<?= $by ?>%"></span>
          </div>
          <div class="a-label">Prévia da faixa</div>
          <div class="a-banner-preview"><img src="<?= e(media_url($page['banner_image'])) ?>" alt="" style="object-position:<?= e($bpos) ?>" data-banner-preview></div>
          <input type="hidden" name="banner_position" value="<?= e($bpos) ?>" data-focus-value>
        </div>
        <label class="a-check"><input type="checkbox" name="title_over_banner" value="1"<?= $page['title_over_banner'] ? ' checked' : '' ?>> Título por cima da imagem</label>
      </section>
      <section class="a-card">
        <h2>Layout</h2>
        <div class="a-seg">
          <label><input type="radio" name="width" value="narrow"<?= $page['width'] !== 'wide' ? ' checked' : '' ?>><span>Estreita (leitura)</span></label>
          <label><input type="radio" name="width" value="wide"<?= $page['width'] === 'wide' ? ' checked' : '' ?>><span>Larga</span></label>
        </div>
        <h3>Cores <small class="a-muted">(sem marcar = cores padrão do site)</small></h3>
        <?php foreach (['bg_color' => ['Fundo', '#FFFFFF'], 'title_color' => ['Título', '#1E0D06'], 'text_color' => ['Texto', '#2B211D']] as $k => [$label, $def]): ?>
          <label class="a-color">
            <input type="checkbox" name="<?= $k ?>_on" value="1"<?= $page[$k] ? ' checked' : '' ?>>
            <input type="color" name="<?= $k ?>" value="<?= e(strtolower($page[$k] ?: $def)) ?>" data-color>
            <span><?= $label ?></span><code data-color-code><?= e(strtoupper($page[$k] ?: $def)) ?></code>
          </label>
        <?php endforeach; ?>
      </section>
      <?php if ($page['id']): ?>
      <section class="a-card a-danger">
        <h2>Excluir</h2>
        <button class="a-btn a-btn-danger a-btn-sm" type="submit" form="delete-page" data-confirm="Excluir esta página de vez?">Excluir página</button>
      </section>
      <?php endif; ?>
    </aside>
  </div>
</form>
<?php if ($page['id']): ?>
<form id="delete-page" method="post" action="/admin/paginas/<?= (int) $page['id'] ?>/excluir"><?= csrf_field() ?></form>
<?php endif; ?>
