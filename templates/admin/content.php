<div class="a-head"><h1>Conteúdo do site</h1></div>
<nav class="a-tabs">
  <?php foreach (['site' => 'Site e redes', 'home' => 'Textos da home', 'rodape' => 'Rodapé', 'blog' => 'Blog e comentários'] as $k => $label): ?>
    <a href="/admin/conteudo?aba=<?= $k ?>"<?= $tab === $k ? ' aria-current="page"' : '' ?>><?= $label ?></a>
  <?php endforeach; ?>
</nav>
<form method="post">
  <?= csrf_field() ?><input type="hidden" name="aba" value="<?= e($tab) ?>">

  <?php if ($tab === 'site'): ?>
    <div class="a-cols">
      <section class="a-card">
        <h2>Identidade</h2>
        <label>Nome do site<input name="title" value="<?= e($site['title']) ?>" required></label>
        <label>Frase curta<input name="tagline" value="<?= e($site['tagline']) ?>"></label>
        <label>Descrição (rodapé e Google)<textarea name="description" rows="3"><?= e($site['description']) ?></textarea></label>
        <label>E-mail de contato<input type="email" name="email" value="<?= e($site['email']) ?>"></label>
        <?= image_field('logo', $site['logo'], 'Logo') ?>
        <small class="a-help">A imagem de fundo do cabeçalho fica em <a href="/admin/aparencia#g-cabecalho">Aparência → Cabeçalho e logo</a>.</small>
        <?= image_field('share_image', $site['share_image'], 'Imagem padrão de compartilhamento', 'Aparece no WhatsApp e redes quando a página não tem imagem própria.') ?>
      </section>
      <section class="a-card">
        <h2>Redes sociais</h2>
        <p class="a-muted">Deixe vazio o que não usar: o ícone some do site.</p>
        <?php foreach (['youtube' => 'YouTube', 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'x' => 'X (Twitter)', 'tiktok' => 'TikTok', 'linkedin' => 'LinkedIn'] as $net => $label): ?>
          <label><?= $label ?><input name="socials[<?= $net ?>]" value="<?= e($site['socials'][$net] ?? '') ?>" placeholder="https://"></label>
        <?php endforeach; ?>
      </section>
    </div>

  <?php elseif ($tab === 'home'): ?>
    <nav class="a-toc" aria-label="Nesta página">
      <a href="#g-avisos">Avisos</a><a href="#g-blog">Blog</a><a href="#g-arquivo">O Arquivo</a><a href="#g-playlists">Playlists</a><a href="#g-acervo">Acervo e História</a><a href="#g-contato">Contato</a>
    </nav>
    <p class="a-help">Os banners do topo ficam em <a href="/admin/banners">Banners do topo</a>. A ordem das seções fica em <a href="/admin/secoes">Seções da home</a>. As cores ficam em <a href="/admin/aparencia">Aparência</a>.</p>

    <section class="a-group" id="g-avisos">
      <h2 class="a-group-title">Faixa de avisos</h2>
      <section class="a-card">
        <p class="a-muted">Um aviso ou vários, um atrás do outro. Link é opcional. Sem nenhum aviso, a faixa some.</p>
        <?php $avisos = ticker_items(); $avisos[] = ['text' => '', 'link' => '']; $avisos[] = ['text' => '', 'link' => '']; ?>
        <div class="a-ticker-list" data-ticker-list>
          <?php foreach ($avisos as $i => $av): ?>
            <div class="a-row-2 a-ticker-row">
              <label>Aviso <?= $i + 1 ?><input name="ticker_items[<?= $i ?>][text]" value="<?= e($av['text']) ?>" maxlength="200"></label>
              <label><span>Link <small>(opcional)</small></span><input name="ticker_items[<?= $i ?>][link]" value="<?= e($av['link'] ?? '') ?>"></label>
              <label class="a-check a-span-2"><input type="checkbox" name="ticker_items[<?= $i ?>][new_tab]" value="1"<?= !empty($av['new_tab']) ? ' checked' : '' ?>> Abrir em nova aba</label>
            </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="a-btn a-btn-sm a-btn-ghost" data-add-ticker>+ Mais um aviso</button>
        <small class="a-help" style="margin-top:8px">Para apagar um aviso, deixe o texto vazio e salve.</small>
      </section>
      <section class="a-card"><h3>Fonte dos avisos</h3><?= type_fields(['ticker']) ?></section>
    </section>

    <section class="a-group" id="g-blog">
      <h2 class="a-group-title">Blog</h2>
      <section class="a-card">
        <div class="a-row-2">
          <label>Título da seção Blog<input name="blog_title" value="<?= e($home['blog_title']) ?>"></label>
          <label>Quantos posts mostrar na home<input type="number" name="blog_count" min="1" max="24" value="<?= (int) $home['blog_count'] ?>"></label>
          <label>Texto do botão que leva ao blog<input name="blog_button" value="<?= e($home['blog_button']) ?>" maxlength="40"></label>
        </div>
        <small class="a-help">Botão vazio: ele some.</small>
      </section>
      <section class="a-card"><h3>Fontes</h3><?= type_fields(['blog_title', 'blog_button']) ?></section>
    </section>

    <section class="a-group" id="g-arquivo">
      <h2 class="a-group-title">O Arquivo</h2>
      <section class="a-card">
        <label>Título<input name="arquivo_title" value="<?= e($home['arquivo_title']) ?>"></label>
        <label>Texto (linha em branco separa parágrafos)<textarea name="arquivo_text" rows="6"><?= e($home['arquivo_text']) ?></textarea></label>
        <div class="a-row-2">
          <label>Texto do botão<input name="arquivo_button" value="<?= e($home['arquivo_button']) ?>"></label>
          <label>Link do botão<input name="arquivo_link" value="<?= e($home['arquivo_link']) ?>"></label>
        </div>
        <label class="a-check"><input type="checkbox" name="arquivo_new_tab" value="1"<?= ($home['arquivo_new_tab'] ?? preg_match('#^https?://#', (string) $home['arquivo_link'])) ? ' checked' : '' ?>> Abrir o link em nova aba</label>
        <?= image_field('arquivo_image', $home['arquivo_image'], 'Imagem') ?>
        <?= render_to_string('admin/image-options', ['key' => 'arquivo_img', 'o' => image_opts($home['arquivo_img'] ?? null)]) ?>
      </section>
      <section class="a-card"><h3>Fontes</h3><?= type_fields(['arquivo_title', 'arquivo_text', 'arquivo_button']) ?></section>
    </section>

    <section class="a-group" id="g-playlists">
      <h2 class="a-group-title">Playlists</h2>
      <section class="a-card">
        <label>Título<input name="playlists_title" value="<?= e($home['playlists_title']) ?>"></label>
        <label>Texto antes das imagens<textarea name="playlists_intro" rows="4"><?= e($home['playlists_intro']) ?></textarea></label>
        <div class="a-playlists" data-repeater>
          <?php $lists = $home['playlists']; $lists[] = ['title' => '', 'image' => '', 'link' => '']; ?>
          <?php foreach ($lists as $i => $pl): ?>
            <fieldset class="a-playlist">
              <legend><?= $pl['title'] !== '' ? e($pl['title']) : 'Nova playlist' ?></legend>
              <label>Nome<input name="playlists[<?= $i ?>][title]" value="<?= e($pl['title']) ?>"></label>
              <label>Link da playlist no YouTube<input name="playlists[<?= $i ?>][link]" value="<?= e($pl['link']) ?>" placeholder="https://www.youtube.com/playlist?list=..."></label>
              <label class="a-check"><input type="checkbox" name="playlists[<?= $i ?>][new_tab]" value="1"<?= ($pl['new_tab'] ?? true) ? ' checked' : '' ?>> Abrir em nova aba</label>
              <?= image_field("playlists[{$i}][image]", $pl['image'], 'Imagem') ?>
              <?php if ($pl['title'] !== ''): ?><small class="a-help">Para remover, apague o nome e a imagem e salve.</small><?php endif; ?>
            </fieldset>
          <?php endforeach; ?>
        </div>
        <label>Texto depois das imagens<textarea name="playlists_outro" rows="3"><?= e($home['playlists_outro']) ?></textarea></label>
      </section>
      <section class="a-card"><h3>Fontes</h3><?= type_fields(['playlists_title', 'playlists_intro', 'playlists_names', 'playlists_outro']) ?></section>
    </section>

    <section class="a-group" id="g-acervo">
      <h2 class="a-group-title">Acervo e História</h2>
      <p class="a-muted">Duas colunas lado a lado (no celular, uma embaixo da outra). No menu, use <code>/#acervo</code> ou <code>/#historia</code> para descer até cada coluna, ou o endereço da página própria (<code>/acervo</code>, <code>/historia</code>).</p>
      <div class="a-cols">
        <?php foreach (['acervo' => 'Coluna 1: Acervo', 'historia' => 'Coluna 2: História'] as $k => $colLabel): ?>
          <section class="a-card">
            <h3><?= $colLabel ?></h3>
            <label class="a-check"><input type="checkbox" name="<?= $k ?>_show" value="1"<?= !empty($home[$k . '_show']) ? ' checked' : '' ?>> Mostrar esta coluna</label>
            <?= image_field($k . '_image', $home[$k . '_image'], 'Imagem') ?>
            <?= render_to_string('admin/image-options', ['key' => $k . '_img', 'o' => image_opts($home[$k . '_img'] ?? null, ['radius' => 16, 'ratio' => 'wide'])]) ?>
            <label>Título<input name="<?= $k ?>_title" value="<?= e($home[$k . '_title']) ?>"></label>
            <label>Texto (linha em branco separa parágrafos)<textarea name="<?= $k ?>_text" rows="4"><?= e($home[$k . '_text']) ?></textarea></label>
            <label>Texto do botão<input name="<?= $k ?>_button" value="<?= e($home[$k . '_button']) ?>"></label>
            <label>Link do botão<input name="<?= $k ?>_link" value="<?= e($home[$k . '_link']) ?>" placeholder="/<?= $k ?>"></label>
            <label class="a-check"><input type="checkbox" name="<?= $k ?>_new_tab" value="1"<?= !empty($home[$k . '_new_tab']) ? ' checked' : '' ?>> Abrir o link em nova aba</label>
            <small class="a-help" style="margin-top:4px">Botão ou link vazio: o botão some.</small>
          </section>
        <?php endforeach; ?>
      </div>
      <section class="a-card"><h3>Fontes</h3><?= type_fields(['acervo_title' => 'Acervo: título', 'acervo_text' => 'Acervo: texto', 'acervo_button' => 'Acervo: botão', 'historia_title' => 'História: título', 'historia_text' => 'História: texto', 'historia_button' => 'História: botão']) ?></section>
    </section>

    <section class="a-group" id="g-contato">
      <h2 class="a-group-title">Contato</h2>
      <section class="a-card">
        <label>Título<input name="contact_title" value="<?= e($home['contact_title']) ?>"></label>
        <label>Texto<input name="contact_text" value="<?= e($home['contact_text']) ?>"></label>
        <div class="a-label">Nomes dos campos do formulário</div>
        <div class="a-row-2">
          <?php foreach (['nome' => 'Campo 1 (nome)', 'sobrenome' => 'Campo 2 (sobrenome)', 'email' => 'Campo 3 (e-mail)', 'telefone' => 'Campo 4 (telefone)', 'mensagem' => 'Campo 5 (mensagem)'] as $f => $fl): ?>
            <label><small><?= $fl ?></small><input name="contact_labels[<?= $f ?>]" value="<?= e($home['contact_labels'][$f]) ?>" maxlength="60" required></label>
          <?php endforeach; ?>
          <label><small>Botão</small><input name="contact_button" value="<?= e($home['contact_button']) ?>" maxlength="40" required></label>
        </div>
      </section>
      <section class="a-card"><h3>Fontes</h3><?= type_fields(['contact_title', 'contact_text', 'contact_labels', 'contact_button']) ?></section>
    </section>

  <?php elseif ($tab === 'rodape'): ?>
    <nav class="a-toc" aria-label="Nesta página">
      <a href="#g-textos">Textos</a><a href="#g-fontes-rodape">Fontes</a>
    </nav>
    <section class="a-group" id="g-textos">
      <h2 class="a-group-title">Textos do rodapé</h2>
      <div class="a-cols">
        <section class="a-card">
          <h3>Coluna dos links</h3>
          <label>Título<input name="nav_title" value="<?= e($footer['nav_title']) ?>" maxlength="60" required></label>
          <label>Nome do link do RSS<input name="rss_label" value="<?= e($footer['rss_label']) ?>" maxlength="40"></label>
          <small class="a-help">Vazio: some. Os outros links vêm do <a href="/admin/menu">Menu</a>.</small>
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
      <p class="a-help">O título da 1ª coluna é o nome do site; o texto embaixo dele é a descrição. Os dois, o e-mail e as redes vêm da aba <a href="/admin/conteudo?aba=site">Site e redes</a>, porque também aparecem no Google e no WhatsApp. Os 3 títulos usam sempre a mesma fonte, para ficarem alinhados.</p>
    </section>
    <section class="a-group" id="g-fontes-rodape">
      <h2 class="a-group-title">Fontes do rodapé</h2>
      <section class="a-card"><?= type_fields(['footer_titles', 'footer_texts', 'footer_links', 'footer_news_button', 'footer_email', 'footer_word', 'footer_copyright', 'footer_author']) ?></section>
    </section>

  <?php else: ?>
    <section class="a-card">
      <h2>Blog e comentários</h2>
      <label>Posts por página no blog<input type="number" name="per_page" min="3" max="48" value="<?= (int) $blog['per_page'] ?>"></label>
      <label class="a-check"><input type="checkbox" name="comments_enabled" value="1"<?= $blog['comments_enabled'] ? ' checked' : '' ?>> Comentários ligados no site</label>
      <label class="a-check"><input type="checkbox" name="comments_moderate" value="1"<?= $blog['comments_moderate'] ? ' checked' : '' ?>> Comentários só aparecem depois de aprovados</label>
      <label class="a-check"><input type="checkbox" name="share_buttons" value="1"<?= $blog['share_buttons'] ? ' checked' : '' ?>> Botões de compartilhar nos posts</label>
      <p class="a-help">Layout dos cards (posts por linha, mosaico, linhas do título) fica em <a href="/admin/aparencia">Aparência</a>.</p>
    </section>
  <?php endif; ?>

  <div class="a-sticky-save"><button class="a-btn" type="submit">Salvar</button></div>
</form>
