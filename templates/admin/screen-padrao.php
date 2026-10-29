<div class="a-head"><h1>Padrão do site</h1><a class="a-btn a-btn-ghost" href="/" target="_blank" rel="noopener">Ver o site ↗</a></div>
<form method="post">
  <?= csrf_field() ?>
  <nav class="a-toc" aria-label="Nesta página"><a href="#g-fontes">Fontes</a><a href="#g-botoes">Botões</a></nav>
  <section class="a-group" id="g-fontes">
    <h2 class="a-group-title">1. Fontes do site</h2>
    <p class="a-muted">É o padrão do site inteiro. Todo texto que estiver em “Usar o padrão do site” segue o que for escolhido aqui. Cada parte do site (cabeçalho, faixa, página inicial, rodapé) pode trocar a fonte de um texto na tela dela, em “Fontes e tamanhos”.</p>
    <div class="a-type-grid">
      <?php $sizes = base_font_sizes(); $weights = base_font_weights(); foreach (['heading' => ['Títulos', 'Títulos das seções da home', 24, 80], 'body' => ['Texto corrido', 'Textos da home e dos posts', 13, 24], 'ui' => ['Menus, botões e detalhes', 'Botões, campos e textos pequenos', 11, 22]] as $role => [$label, $where, $min, $max]): ?>
        <fieldset class="a-type">
          <legend><?= $label ?></legend>
          <label>Fonte<select name="fonts[<?= $role ?>]" data-font-select><?= select_options(array_combine(font_choices(), font_choices()), $a['fonts'][$role]) ?></select></label>
          <p class="a-font-sample" data-size-sample style="font-family:'<?= e(font_parts($a['fonts'][$role])['family']) ?>';font-stretch:<?= font_parts($a['fonts'][$role])['stretch'] ?>;font-size:<?= min($sizes[$role], 40) ?>px<?= isset($weights[$role]) ? ';font-weight:' . $weights[$role] : '' ?>">Mangueira 1988</p>
          <label><span>Tamanho: <output data-range-out><?= $sizes[$role] ?></output> px</span>
            <input type="range" name="font_sizes[<?= $role ?>]" min="<?= $min ?>" max="<?= $max ?>" value="<?= $sizes[$role] ?>" data-range>
          </label>
          <?php if (isset($weights[$role])): ?>
            <label>Peso (grossura da letra)<select name="font_weights[<?= $role ?>]" data-base-weight><?= select_options(weight_choices(), (string) $weights[$role]) ?></select></label>
          <?php endif; ?>
          <small class="a-help">Vale para: <?= $where ?>.</small>
        </fieldset>
      <?php endforeach; ?>
    </div>
    <small class="a-help" style="margin-top:12px">Syne, Bitter e Instrument Sans já vêm no site. As outras são carregadas do Google Fonts (gratuitas). No celular, os títulos grandes diminuem sozinhos.</small>
  </section>
  <section class="a-group" id="g-botoes">
    <h2 class="a-group-title">2. Botões</h2>
    <p class="a-muted">O site tem 3 tipos de botão. Cada um com as suas cores. Os botões que você cria dentro dos posts têm cores próprias, escolhidas na hora.</p>
    <div class="a-cols">
      <section class="a-card"><h3>Botão principal</h3><small class="a-help">O Arquivo, Playlists, newsletter, “Texto + imagem”, busca.</small><?= color_fields('botao_principal') ?></section>
      <section class="a-card"><h3>Botão “Ver todas”</h3><small class="a-help">Seção Blog e “Posts de uma categoria”.</small><?= color_fields('botao_todas') ?></section>
      <section class="a-card"><h3>Botão escuro</h3><small class="a-help">Acervo, História, Enviar do contato, Publicar comentário.</small><?= color_fields('botao_escuro') ?></section>
    </div>
  </section>
  <div class="a-sticky-save">
    <button class="a-btn" type="submit">Salvar padrão do site</button>
    <button class="a-btn a-btn-ghost" type="submit" name="acao" value="restaurar" data-confirm="Voltar as fontes do site e as cores dos botões ao padrão original? O resto (cabeçalho, faixa, página inicial, rodapé, blog) continua como está.">Restaurar padrão</button>
  </div>
</form>
