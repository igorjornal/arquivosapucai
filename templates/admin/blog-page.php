<div class="a-head"><h1>Página do Blog</h1><a class="a-btn a-btn-ghost" href="/blog" target="_blank" rel="noopener">Ver a página ↗</a></div>
<p class="a-muted"><a href="/admin/paginas">← Páginas</a></p>
<form method="post">
  <?= csrf_field() ?>
  <section class="a-card">
    <h2>Box de abertura</h2>
    <p class="a-muted">É o quadro no topo da página <code>/blog</code>, acima da barra de categorias. Depois vêm os posts, montados sozinhos.</p>
    <label>Título<input name="page_title" value="<?= e($blog['page_title']) ?>" maxlength="120" required></label>
    <label>Texto (opcional)<textarea name="page_intro" rows="4" maxlength="1200"><?= e($blog['page_intro']) ?></textarea></label>
    <label><span>Folga dentro do box (altura): <output data-range-out><?= (int) ($blog['page_box_pad'] ?? 32) ?></output> px</span>
      <input type="range" name="page_box_pad" min="8" max="100" step="4" value="<?= (int) ($blog['page_box_pad'] ?? 32) ?>" data-range>
    </label>
    <div class="a-label">Cores</div><div class="a-colors-inline"><?= color_fields('pagina_blog') ?></div>
    <small class="a-help">O fundo da página vale também para as páginas de categoria, tag, autor e busca. As cores da seção Blog da página inicial continuam separadas, em Página inicial.</small>
    <details class="a-adv"><summary>Fontes e tamanhos do box</summary><?= type_fields(['blog_page_title' => 'Título', 'blog_page_intro' => 'Texto']) ?></details>
    <small class="a-help">Quantos posts por vez, cards, coluna do post e comentários ficam em <a href="/admin/blog-posts">Blog e posts</a>.</small>
  </section>
  <div class="a-sticky-save"><button class="a-btn" type="submit">Salvar página do Blog</button></div>
</form>
