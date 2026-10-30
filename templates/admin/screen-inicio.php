<div class="a-head"><h1>Página inicial</h1><a class="a-btn a-btn-ghost" href="/" target="_blank" rel="noopener">Ver o site ↗</a></div>
<?= render_to_string('admin/home-tabs', ['current' => 'textos']) ?>
<form method="post">
  <?= csrf_field() ?>
  <nav class="a-toc" aria-label="Nesta página">
    <a href="#g-blog">Blog</a><a href="#g-arquivo">O Arquivo</a><a href="#g-playlists">Playlists</a><a href="#g-acervo">Acervo e História</a><a href="#g-contato">Contato</a>
  </nav>
    <section class="a-card">
      <label><span>Espaço entre a imagem e o texto: <output data-range-out><?= (int) ($home['split_gap'] ?? 40) ?></output> px</span>
        <input type="range" name="split_gap" min="0" max="120" step="4" value="<?= (int) ($home['split_gap'] ?? 40) ?>" data-range>
      </label>
      <small class="a-help">Vale igual para O Arquivo, Playlists (em “Imagem + texto”) e as seções “Texto + imagem”, para ficarem uniformes. No celular a imagem fica em cima do texto.</small>
    </section>
    <section class="a-group" id="g-blog">
      <h2 class="a-group-title">Blog</h2>
      <section class="a-card">
        <div class="a-row-2">
          <label>Título da seção Blog<input name="blog_title" value="<?= e($home['blog_title']) ?>"></label>
          <label>Quantos posts por vez<input type="number" name="per_page" min="1" max="48" value="<?= (int) $blog['per_page'] ?>"></label>
          <label>Texto do botão que leva ao blog<input name="blog_button" value="<?= e($home['blog_button']) ?>" maxlength="40"></label>
        </div>
        <small class="a-help">Botão vazio: ele some. “Quantos posts por vez” vale aqui e na página do blog (é o mesmo número de Blog e posts). Se houver mais posts, aparecem embaixo os números das páginas.</small>
      </section>
      <section class="a-card"><div class="a-label">Cores</div><div class="a-colors-inline"><?= color_fields('blog') ?></div></section>
      <details class="a-adv"><summary>Fontes e tamanhos deste bloco</summary><?= type_fields(['blog_title', 'blog_button']) ?></details>
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
      <section class="a-card"><div class="a-label">Cores</div><div class="a-colors-inline"><?= color_fields('arquivo') ?></div></section>
      <details class="a-adv"><summary>Fontes e tamanhos deste bloco</summary><?= type_fields(['arquivo_title', 'arquivo_text', 'arquivo_button']) ?></details>
    </section>

    <section class="a-group" id="g-playlists">
      <h2 class="a-group-title">Playlists</h2>
      <section class="a-card" data-layout-switch>
        <div class="a-label">Formato da seção</div>
        <div class="a-seg">
          <label><input type="radio" name="playlists_layout" value="grid"<?= $home['playlists_layout'] !== 'feature' ? ' checked' : '' ?>><span>Grade de playlists</span></label>
          <label><input type="radio" name="playlists_layout" value="feature"<?= $home['playlists_layout'] === 'feature' ? ' checked' : '' ?>><span>Imagem + texto</span></label>
        </div>
        <small class="a-help">O formato que não está em uso fica guardado: é só voltar e salvar.</small>
        <label>Título<input name="playlists_title" value="<?= e($home['playlists_title']) ?>"></label>
        <label><span data-layout-only="grid">Texto antes das imagens</span><span data-layout-only="feature">Texto</span><textarea name="playlists_intro" rows="4"><?= e($home['playlists_intro']) ?></textarea></label>
        <div data-layout-only="feature">
          <?= image_field('playlists_image', $home['playlists_image'], 'Imagem à esquerda (clicável)') ?>
          <?= render_to_string('admin/image-options', ['key' => 'playlists_img', 'o' => image_opts($home['playlists_img'] ?? null, ['radius' => 16])]) ?>
          <div class="a-row-2">
            <label>Texto do botão<input name="playlists_button" value="<?= e($home['playlists_button']) ?>" maxlength="60"></label>
            <label>Link do botão (e da imagem)<input name="playlists_link" value="<?= e($home['playlists_link']) ?>" placeholder="/playlists" list="lista-paginas"></label>
          </div>
          <datalist id="lista-paginas"><?php foreach ($pages as $pgItem): ?><option value="/<?= e($pgItem['slug']) ?>"><?= e($pgItem['title']) ?></option><?php endforeach; ?><option value="/blog">Blog</option></datalist>
          <small class="a-help">Clique no campo do link para escolher uma das suas páginas, ou cole um endereço (por exemplo, a playlist no YouTube). Botão vazio: some.</small>
          <label class="a-check"><input type="checkbox" name="playlists_new_tab" value="1"<?= !empty($home['playlists_new_tab']) ? ' checked' : '' ?>> Abrir o link em nova aba</label>
          <div class="a-label">Alinhamento do título e do texto</div>
          <div class="a-seg">
            <?php foreach (['left' => 'Esquerda', 'center' => 'Centro', 'right' => 'Direita', 'justify' => 'Justificado'] as $al => $alLabel): ?>
              <label><input type="radio" name="playlists_align" value="<?= $al ?>"<?= ($home['playlists_align'] ?? 'left') === $al ? ' checked' : '' ?>><span><?= $alLabel ?></span></label>
            <?php endforeach; ?>
          </div>
        </div>
        <div data-layout-only="grid">
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
        </div>
      </section>
      <section class="a-card"><div class="a-label">Cores</div><div class="a-colors-inline"><?= color_fields('playlists') ?></div></section>
      <details class="a-adv"><summary>Fontes e tamanhos deste bloco</summary><?= type_fields(['playlists_title', 'playlists_intro', 'playlists_names', 'playlists_outro', 'playlists_button']) ?>
        <small class="a-help" style="margin-top:10px">“Nomes em cima das imagens” e “Texto depois das imagens” só aparecem na grade; “Botão”, só em Imagem + texto.</small></details>
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
      <section class="a-card"><div class="a-label">Cores</div><div class="a-colors-inline"><?= color_fields('acervo') ?></div></section>
      <details class="a-adv"><summary>Fontes e tamanhos deste bloco</summary><?= type_fields(['acervo_title' => 'Acervo: título', 'acervo_text' => 'Acervo: texto', 'acervo_button' => 'Acervo: botão', 'historia_title' => 'História: título', 'historia_text' => 'História: texto', 'historia_button' => 'História: botão']) ?></details>
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
      <section class="a-card"><div class="a-label">Cores</div><div class="a-colors-inline"><?= color_fields('contato') ?></div></section>
      <details class="a-adv"><summary>Fontes e tamanhos deste bloco</summary><?= type_fields(['contact_title', 'contact_text', 'contact_labels', 'contact_button']) ?></details>
    </section>

  <div class="a-sticky-save"><button class="a-btn" type="submit">Salvar página inicial</button></div>
</form>
