<div class="a-head"><h1>Rodapé</h1><a class="a-btn a-btn-ghost" href="/" target="_blank" rel="noopener">Ver o site ↗</a></div>
<form method="post">
  <?= csrf_field() ?>
  <nav class="a-toc" aria-label="Nesta página"><a href="#g-textos">Textos</a><a href="#g-aparencia-rodape">Cores e fontes</a></nav>
    <section class="a-group" id="g-textos">
      <h2 class="a-group-title">Textos do rodapé</h2>
      <div class="a-cols">
        <section class="a-card">
          <h3>Coluna dos links</h3>
          <label>Título<input name="nav_title" value="<?= e($footer['nav_title']) ?>" maxlength="60" required></label>
          <label>Nome do link do RSS<input name="rss_label" value="<?= e($footer['rss_label']) ?>" maxlength="40"></label>
          <small class="a-help">Vazio: some. Os outros links são os do menu, em <a href="/admin/cabecalho#g-itens">Cabeçalho e menu</a>.</small>
          <h3>Linha de baixo</h3>
          <label>Linha do ©<input name="copyright" value="<?= e($footer['copyright']) ?>" maxlength="200"></label>
          <small class="a-help"><code>{ano}</code> vira o ano atual sozinho. Hoje aparece: “<?= e(str_replace('{ano}', date('Y'), $footer['copyright'])) ?>”.</small>
          <label>Link “Área do autor”<input name="author_label" value="<?= e($footer['author_label']) ?>" maxlength="40"></label>
          <small class="a-help">Abre o painel em outra aba. Vazio: o link some.</small>
        </section>
        <section class="a-card">
          <h3>Newsletter</h3>
          <label>Título<input name="news_title" value="<?= e($news['title']) ?>"></label>
          <label>Texto<textarea name="news_text" rows="3"><?= e($news['text']) ?></textarea></label>
          <label>Texto dentro do campo<input name="news_placeholder" value="<?= e($news['placeholder']) ?>" maxlength="60"></label>
          <label>Botão<input name="news_button" value="<?= e($news['button']) ?>" maxlength="40" required></label>
          <label>Palavra antes do e-mail<input name="email_label" value="<?= e($footer['email_label']) ?>" maxlength="40" placeholder="Contato:"></label>
          <small class="a-help">Aparece em destaque antes do e-mail, por exemplo “Contato:”. O e-mail vem da aba Site e redes.</small>
          <h3>Texto grande ao fundo</h3>
          <label class="a-check"><input type="checkbox" name="show_word" value="1"<?= $footer['show_word'] ? ' checked' : '' ?>> Mostrar</label>
          <label>Texto<input name="word" value="<?= e($footer['word']) ?>" maxlength="40"></label>
          <small class="a-help">O tamanho se ajusta sozinho para caber numa linha.</small>
        </section>
      </div>
      <p class="a-help">O título da 1ª coluna é o nome do site; o texto embaixo dele é a descrição. Os dois, o e-mail e as redes ficam em <a href="/admin/configuracoes">Configurações</a>, porque também aparecem no Google e no WhatsApp. Os 3 títulos usam sempre a mesma fonte, para ficarem alinhados.</p>
    </section>
    <section class="a-group" id="g-aparencia-rodape">
      <h2 class="a-group-title">Como o rodapé aparece</h2>
      <section class="a-card"><div class="a-label">Cores</div><div class="a-colors-inline"><?= color_fields('rodape') ?></div></section>
      <h3>Fontes e tamanhos</h3>
      <section class="a-card"><?= type_fields(['footer_titles', 'footer_texts', 'footer_links', 'footer_news_button', 'footer_email', 'footer_word', 'footer_copyright', 'footer_author']) ?></section>
    </section>

  <div class="a-sticky-save"><button class="a-btn" type="submit">Salvar rodapé</button></div>
</form>
