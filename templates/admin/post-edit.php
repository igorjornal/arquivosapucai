<?php
$isScheduled = $post['status'] === 'published' && $post['published_at'] && strtotime((string) $post['published_at']) > time();
$isLive = $post['status'] === 'published' && !$isScheduled;
$publishedLocal = $post['published_at'] ? date('Y-m-d\TH:i', strtotime((string) $post['published_at'])) : '';
$ratios = array_map(fn ($r) => $r['label'], image_ratios());
[$posX, $posY] = array_map('intval', explode(' ', str_replace('%', '', (string) $post['cover_position'])) + [50, 50]);
?>
<form class="a-post-form" method="post" action="<?= $post['id'] ? '/admin/posts/' . (int) $post['id'] : '/admin/posts/novo' ?>" data-post-form>
  <?= csrf_field() ?>
  <div class="a-head">
    <h1><?= e($title) ?></h1>
    <div class="a-row-tight">
      <?php if ($post['id']): ?>
        <a class="a-btn a-btn-ghost" href="/blog/<?= e($post['slug']) ?>?previa=1" target="_blank" rel="noopener">Pré-visualizar ↗</a>
      <?php endif; ?>
      <button class="a-btn a-btn-ghost" type="submit" name="acao" value="rascunho">Salvar rascunho</button>
      <button class="a-btn" type="submit" name="acao" value="publicar"><?= $isLive ? 'Atualizar' : 'Publicar' ?></button>
    </div>
  </div>
  <?php if ($errors): ?><div class="notice notice-error"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>

  <div class="a-editor-grid">
    <div class="a-editor-main">
      <label class="a-title-input">
        <span class="sr">Título</span>
        <input name="title" value="<?= e($post['title']) ?>" placeholder="Título do post" required maxlength="255" data-title>
      </label>
      <div class="a-slug">
        <span>Endereço: /blog/</span><input name="slug" value="<?= e($post['slug']) ?>" placeholder="gerado-pelo-titulo" data-slug aria-label="Endereço do post">
      </div>
      <textarea name="content" id="post-content" data-editor><?= e($post['content']) ?></textarea>

      <section class="a-card">
        <h2>Resumo</h2>
        <label>Descrição que aparece no card e no Google
          <textarea name="excerpt" rows="3" maxlength="600" placeholder="Se ficar vazio, o site usa o começo do texto."><?= e($post['excerpt']) ?></textarea>
        </label>
      </section>

      <details class="a-card">
        <summary><h2>Google e redes sociais (opcional)</h2></summary>
        <label>Título para o Google<input name="meta_title" value="<?= e($post['meta_title']) ?>" maxlength="255" placeholder="Se vazio, usa o título do post"></label>
        <label>Descrição para o Google<textarea name="meta_description" rows="2" maxlength="300" placeholder="Se vazio, usa o resumo"><?= e($post['meta_description']) ?></textarea></label>
      </details>
    </div>

    <aside class="a-editor-side">
      <section class="a-card">
        <h2>Publicação</h2>
        <p class="a-muted">Situação:
          <?php if ($post['id']) partial_status($post); else echo '<span class="a-pill a-pill-draft">Novo</span>'; ?>
        </p>
        <label>Data e hora de publicação
          <input type="datetime-local" name="published_at" value="<?= e($publishedLocal) ?>">
        </label>
        <small class="a-help">Escolha uma data futura e clique em Publicar para <strong>agendar</strong>. Vazio = publica agora.</small>
        <?php if ($isScheduled): ?>
          <button class="a-btn a-btn-ghost a-btn-sm" type="submit" name="acao" value="publicar_agora">Publicar agora</button>
        <?php endif; ?>
        <label class="a-check"><input type="checkbox" name="featured" value="1"<?= $post['featured'] ? ' checked' : '' ?>> Fixar em destaque (aparece primeiro)</label>
        <label class="a-check"><input type="checkbox" name="allow_comments" value="1"<?= $post['allow_comments'] ? ' checked' : '' ?>> Aceitar comentários</label>
        <?php if (current_user()['role'] === 'admin'): ?>
          <label>Autor
            <select name="author_id"><?= select_options(array_column($authors, 'name', 'id'), (string) $post['author_id']) ?></select>
          </label>
        <?php endif; ?>
      </section>

      <section class="a-card">
        <h2>Categoria e tags</h2>
        <label>Categoria
          <select name="category_id">
            <option value="">Sem categoria</option>
            <?= select_options(array_column($categories, 'name', 'id'), (string) $post['category_id']) ?>
          </select>
        </label>
        <small class="a-help"><a href="/admin/categorias" target="_blank">Criar ou editar categorias ↗</a></small>
        <label>Tags <input name="tags" value="<?= e($tags) ?>" placeholder="mangueira, 1988, joãosinho trinta"></label>
        <small class="a-help">Separe por vírgula.</small>
      </section>

      <section class="a-card" data-cover>
        <h2>Imagem de capa</h2>
        <?= image_field('cover_image', $post['cover_image'], 'Imagem', 'Aparece no topo do post e no card do blog.') ?>
        <label>Texto alternativo <input name="cover_alt" value="<?= e($post['cover_alt']) ?>" placeholder="Descreva a foto (acessibilidade)"></label>
        <h3>Dentro do post</h3>
        <label>Formato <select name="cover_ratio" data-cover-ratio><?= select_options($ratios, $post['cover_ratio']) ?></select></label>
        <label><span>Tamanho: <output data-cover-width-out><?= (int) $post['cover_width'] ?>%</output></span>
          <input type="range" name="cover_width" min="20" max="100" step="5" value="<?= (int) $post['cover_width'] ?>" data-cover-width>
        </label>
        <div class="a-label">Alinhamento</div>
        <div class="a-seg" role="radiogroup">
          <?php foreach (['left' => 'Esquerda', 'center' => 'Centro', 'right' => 'Direita'] as $k => $label): ?>
            <label><input type="radio" name="cover_align" value="<?= $k ?>"<?= $post['cover_align'] === $k ? ' checked' : '' ?> data-cover-align><span><?= $label ?></span></label>
          <?php endforeach; ?>
        </div>
        <div class="a-focus" data-focus-wrap>
          <div class="a-label">Ponto do recorte <small>(clique na foto)</small></div>
          <div class="a-focus-img" data-focus-area>
            <img src="<?= e(media_url($post['cover_image'])) ?>" alt="" data-focus-img>
            <span class="a-focus-dot" data-focus-dot style="left:<?= $posX ?>%;top:<?= $posY ?>%"></span>
          </div>
          <input type="hidden" name="cover_position" value="<?= e($post['cover_position']) ?>" data-focus-value>
        </div>
        <div class="a-cover-preview" data-cover-preview><div class="a-label">Prévia</div><img src="<?= e(media_url($post['cover_image'])) ?>" alt=""></div>
      </section>

      <section class="a-card">
        <h2>Card do blog</h2>
        <label>Formato da foto no card
          <select name="card_ratio"><option value="">Usar o padrão do site</option><?= select_options($ratios, $post['card_ratio']) ?></select>
        </label>
        <label class="a-check"><input type="checkbox" name="card_bg_custom" value="1"<?= $post['card_bg'] ? ' checked' : '' ?> data-toggle-target="#card-bg-row"> Cor de fundo própria para este card</label>
        <div id="card-bg-row"<?= $post['card_bg'] ? '' : ' hidden' ?>><input type="color" name="card_bg" value="<?= e($post['card_bg'] ?: '#F1F0EC') ?>" aria-label="Cor do card"></div>
      </section>

      <?php if ($post['id']): ?>
      <section class="a-card a-danger">
        <h2>Excluir</h2>
        <p class="a-muted">Apaga o post e os comentários dele. Não dá para desfazer.</p>
        <button class="a-btn a-btn-danger a-btn-sm" type="submit" form="delete-post" data-confirm="Excluir este post de vez?">Excluir post</button>
      </section>
      <?php endif; ?>
    </aside>
  </div>
</form>
<?php if ($post['id']): ?>
<form id="delete-post" method="post" action="/admin/posts/<?= (int) $post['id'] ?>/excluir"><?= csrf_field() ?></form>
<?php endif; ?>
