<?php $ratios = array_map(fn ($r) => $r['label'], image_ratios()); ?>
<div class="a-head"><h1>Blog e posts</h1><a class="a-btn a-btn-ghost" href="/blog" target="_blank" rel="noopener">Ver o blog ↗</a></div>
<form method="post">
  <?= csrf_field() ?>
  <nav class="a-toc" aria-label="Nesta página"><a href="#g-cards">Cards dos posts</a><a href="#g-post">Página do post</a><a href="#g-comentarios">Blog e comentários</a></nav>
  <section class="a-group" id="g-cards">
    <h2 class="a-group-title">1. Cards dos posts</h2>
    <section class="a-card">
      <p class="a-muted">Vale para a seção Blog da home e para a página do blog.</p>
      <label>Posts por linha
        <select name="cards_per_row"><?= select_options([2 => '2 por linha', 3 => '3 por linha', 4 => '4 por linha', 5 => '5 por linha'], (string) $blog['cards_per_row']) ?></select>
      </label>
      <div class="a-label">Arrumação</div>
      <div class="a-seg">
        <label><input type="radio" name="cards_layout" value="grid"<?= $blog['cards_layout'] === 'grid' ? ' checked' : '' ?>><span>Grade reta</span></label>
        <label><input type="radio" name="cards_layout" value="masonry"<?= $blog['cards_layout'] === 'masonry' ? ' checked' : '' ?>><span>Mosaico</span></label>
      </div>
      <small class="a-help">Mosaico: cada card acompanha o tamanho da foto (fica melhor com o formato “Original”).</small>
      <label>Formato padrão da foto do card
        <select name="card_ratio"><?= select_options($ratios, $blog['card_ratio']) ?></select>
      </label>
      <small class="a-help">Cada post pode ter uma exceção, na tela do post.</small>
      <div class="a-row-2">
        <label>Linhas do título
          <select name="card_title_lines"><?= select_options([0 => 'Sem limite', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6'], (string) $blog['card_title_lines']) ?></select>
        </label>
        <label>Linhas da descrição
          <select name="card_excerpt_lines"><?= select_options([0 => 'Sem limite', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8'], (string) $blog['card_excerpt_lines']) ?></select>
        </label>
      </div>
      <small class="a-help">A descrição é o “Resumo” do post. Se ele estiver vazio, usa o começo do texto.</small>
    </section>
    <section class="a-card"><div class="a-label">Cores dos cards</div><div class="a-colors-inline"><?= color_fields('cards') ?></div></section>
  </section>
  <section class="a-group" id="g-post">
    <h2 class="a-group-title">2. Página do post</h2>
    <section class="a-card">
      <label class="a-check"><input type="checkbox" name="post_sidebar" value="1"<?= $blog['post_sidebar'] ? ' checked' : '' ?>> Coluna à direita com os últimos posts do site</label>
      <div class="a-row-2">
        <label>Título da coluna<input name="sidebar_title" value="<?= e($blog['sidebar_title']) ?>" maxlength="60"></label>
        <label>Quantos posts na coluna
          <select name="sidebar_count"><?= select_options([3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8'], (string) $blog['sidebar_count']) ?></select>
        </label>
      </div>
      <small class="a-help">No celular a coluna desce para depois do texto.</small>
      <details class="a-adv"><summary>Fontes e tamanhos da coluna</summary><?= type_fields(['side_title', 'side_posts']) ?></details>
      <label>Título dos posts embaixo do texto<input name="related_title" value="<?= e($blog['related_title']) ?>" maxlength="60"></label>
      <small class="a-help">Mostra os posts escolhidos em “Posts relacionados”, na tela de cada post. Se nenhum for escolhido, mostra os da mesma categoria.</small>
    </section>
    <section class="a-card"><div class="a-label">Cores</div><div class="a-colors-inline"><?= color_fields('post') ?></div></section>
    <section class="a-card"><h3>Título dos posts e das páginas</h3><?= type_fields(['post_title']) ?></section>
  </section>
  <section class="a-group" id="g-comentarios">
    <h2 class="a-group-title">3. Blog e comentários</h2>
    <section class="a-card">
      <label>Quantos posts por vez<input type="number" name="per_page" min="1" max="48" value="<?= (int) $blog['per_page'] ?>"></label>
      <small class="a-help">Vale na página do blog e na seção Blog da página inicial (é o mesmo número nas duas telas).</small>
      <div class="a-label">Cores dos números das páginas (1, 2, 3…)</div><div class="a-colors-inline"><?= color_fields('paginas') ?></div>
      <label class="a-check"><input type="checkbox" name="comments_enabled" value="1"<?= $blog['comments_enabled'] ? ' checked' : '' ?>> Comentários ligados no site</label>
      <label class="a-check"><input type="checkbox" name="comments_moderate" value="1"<?= $blog['comments_moderate'] ? ' checked' : '' ?>> Comentários só aparecem depois de aprovados</label>
      <label class="a-check"><input type="checkbox" name="share_buttons" value="1"<?= $blog['share_buttons'] ? ' checked' : '' ?>> Botões de compartilhar nos posts</label>
    </section>
  </section>
  <div class="a-sticky-save"><button class="a-btn" type="submit">Salvar blog e posts</button></div>
</form>
