<div class="a-head"><h1>Página do Blog</h1><a class="a-btn a-btn-ghost" href="/blog" target="_blank" rel="noopener">Ver a página ↗</a></div>
<p class="a-muted"><a href="/admin/paginas">← Páginas</a></p>
<form method="post">
  <?= csrf_field() ?>
  <section class="a-card">
    <p class="a-muted">É a página <code>/blog</code>, com a lista de todos os posts. Ela é montada sozinha com os posts; aqui você muda o título e o texto de cima.</p>
    <label>Título<input name="page_title" value="<?= e($blog['page_title']) ?>" maxlength="120" required></label>
    <label>Texto embaixo do título (opcional)<textarea name="page_intro" rows="3" maxlength="600"><?= e($blog['page_intro']) ?></textarea></label>
    <details class="a-adv"><summary>Fonte e tamanho do título</summary><?= type_fields(['blog_page_title']) ?></details>
    <small class="a-help">Quantos posts por vez, cards, coluna do post e comentários ficam em <a href="/admin/blog-posts">Blog e posts</a>. As páginas de categoria usam o nome da categoria como título.</small>
  </section>
  <div class="a-sticky-save"><button class="a-btn" type="submit">Salvar página do Blog</button></div>
</form>
