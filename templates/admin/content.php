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
        <?= image_field('header_bg', $site['header_bg'], 'Imagem de fundo do cabeçalho') ?>
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
    <section class="a-card">
      <h2>Faixa corrida (avisos)</h2>
      <p class="a-muted">Um aviso ou vários, um atrás do outro. Link é opcional. Sem nenhum aviso, a faixa some.</p>
      <?php $avisos = ticker_items(); $avisos[] = ['text' => '', 'link' => '']; $avisos[] = ['text' => '', 'link' => '']; ?>
      <div class="a-ticker-list" data-ticker-list>
        <?php foreach ($avisos as $i => $av): ?>
          <div class="a-row-2 a-ticker-row">
            <label>Aviso <?= $i + 1 ?><input name="ticker_items[<?= $i ?>][text]" value="<?= e($av['text']) ?>" maxlength="200"></label>
            <label>Link <small>(opcional)</small><input name="ticker_items[<?= $i ?>][link]" value="<?= e($av['link'] ?? '') ?>"></label>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="a-btn a-btn-sm a-btn-ghost" data-add-ticker>+ Mais um aviso</button>
      <small class="a-help" style="margin-top:8px">Para apagar um aviso, deixe o texto vazio e salve.</small>
      <h2 style="margin-top:22px">Blog</h2>
      <div class="a-row-2">
        <label>Título da seção Blog<input name="blog_title" value="<?= e($home['blog_title']) ?>"></label>
        <label>Quantos posts mostrar na home<input type="number" name="blog_count" min="1" max="24" value="<?= (int) $home['blog_count'] ?>"></label>
      </div>
      <p class="a-help">Os banners do topo ficam em <a href="/admin/banners">Banners do topo</a>. A ordem das seções fica em <a href="/admin/secoes">Seções da home</a>.</p>
    </section>
    <section class="a-card">
      <h2>O Arquivo</h2>
      <label>Título<input name="arquivo_title" value="<?= e($home['arquivo_title']) ?>"></label>
      <label>Texto (linha em branco separa parágrafos)<textarea name="arquivo_text" rows="6"><?= e($home['arquivo_text']) ?></textarea></label>
      <div class="a-row-2">
        <label>Texto do botão<input name="arquivo_button" value="<?= e($home['arquivo_button']) ?>"></label>
        <label>Link do botão<input name="arquivo_link" value="<?= e($home['arquivo_link']) ?>"></label>
      </div>
      <?= image_field('arquivo_image', $home['arquivo_image'], 'Imagem') ?>
    </section>
    <section class="a-card">
      <h2>Playlists</h2>
      <label>Título<input name="playlists_title" value="<?= e($home['playlists_title']) ?>"></label>
      <label>Texto antes das imagens<textarea name="playlists_intro" rows="4"><?= e($home['playlists_intro']) ?></textarea></label>
      <div class="a-playlists" data-repeater>
        <?php $lists = $home['playlists']; $lists[] = ['title' => '', 'image' => '', 'link' => '']; ?>
        <?php foreach ($lists as $i => $pl): ?>
          <fieldset class="a-playlist">
            <legend><?= $pl['title'] !== '' ? e($pl['title']) : 'Nova playlist' ?></legend>
            <label>Nome<input name="playlists[<?= $i ?>][title]" value="<?= e($pl['title']) ?>"></label>
            <label>Link da playlist no YouTube<input name="playlists[<?= $i ?>][link]" value="<?= e($pl['link']) ?>" placeholder="https://www.youtube.com/playlist?list=..."></label>
            <?= image_field("playlists[{$i}][image]", $pl['image'], 'Imagem') ?>
            <?php if ($pl['title'] !== ''): ?><small class="a-help">Para remover, apague o nome e a imagem e salve.</small><?php endif; ?>
          </fieldset>
        <?php endforeach; ?>
      </div>
      <label>Texto depois das imagens<textarea name="playlists_outro" rows="3"><?= e($home['playlists_outro']) ?></textarea></label>
    </section>
    <section class="a-card">
      <h2>Acervo e História</h2>
      <?= image_field('acervo_image', $home['acervo_image'], 'Imagem') ?>
      <div class="a-row-2">
        <div>
          <label>Título 1<input name="acervo_title" value="<?= e($home['acervo_title']) ?>"></label>
          <label>Texto 1<textarea name="acervo_text" rows="4"><?= e($home['acervo_text']) ?></textarea></label>
        </div>
        <div>
          <label>Título 2<input name="historia_title" value="<?= e($home['historia_title']) ?>"></label>
          <label>Texto 2<textarea name="historia_text" rows="4"><?= e($home['historia_text']) ?></textarea></label>
        </div>
      </div>
      <div class="a-row-2">
        <label>Texto do botão<input name="acervo_button" value="<?= e($home['acervo_button']) ?>"></label>
        <label>Link do botão<input name="acervo_link" value="<?= e($home['acervo_link']) ?>"></label>
      </div>
    </section>
    <section class="a-card">
      <h2>Contato</h2>
      <label>Título<input name="contact_title" value="<?= e($home['contact_title']) ?>"></label>
      <label>Texto<input name="contact_text" value="<?= e($home['contact_text']) ?>"></label>
    </section>

  <?php elseif ($tab === 'rodape'): ?>
    <div class="a-cols">
      <section class="a-card">
        <h2>Texto grande ao fundo</h2>
        <label class="a-check"><input type="checkbox" name="show_word" value="1"<?= $footer['show_word'] ? ' checked' : '' ?>> Mostrar</label>
        <label>Texto<input name="word" value="<?= e($footer['word']) ?>" maxlength="40"></label>
        <small class="a-help">O tamanho se ajusta sozinho para caber numa linha.</small>
      </section>
      <section class="a-card">
        <h2>Newsletter</h2>
        <label>Título<input name="news_title" value="<?= e($news['title']) ?>"></label>
        <label>Texto<textarea name="news_text" rows="3"><?= e($news['text']) ?></textarea></label>
      </section>
    </div>
    <p class="a-help">Logo, descrição, e-mail e redes do rodapé vêm da aba <a href="/admin/conteudo?aba=site">Site e redes</a>. Os links vêm do <a href="/admin/menu">Menu</a>. As cores ficam em <a href="/admin/aparencia">Aparência</a>.</p>

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
