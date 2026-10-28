<?php $ratios = array_map(fn ($r) => $r['label'], image_ratios()); ?>
<div class="a-head">
  <h1>Aparência</h1>
  <a class="a-btn a-btn-ghost" href="/" target="_blank" rel="noopener">Ver o site ↗</a>
</div>
<nav class="a-toc" aria-label="Nesta página">
  <a href="#g-fontes">Fontes</a><a href="#g-cards">Cards do blog</a><a href="#g-cabecalho">Cabeçalho e logo</a><a href="#g-cores">Cores</a>
</nav>
<form method="post">
  <?= csrf_field() ?>
  <section class="a-group" id="g-fontes">
    <h2 class="a-group-title">Fontes do site</h2>
    <p class="a-muted">É o padrão do site inteiro. Todo texto que estiver em “Usar o padrão do site” segue o que for escolhido aqui. A fonte de cada texto da home, do rodapé, do menu e dos avisos pode ser trocada junto do próprio texto.</p>
    <div class="a-type-grid">
      <?php $sizes = base_font_sizes(); $weights = base_font_weights(); foreach (['heading' => ['Títulos', 'Títulos das seções da home', 24, 80], 'body' => ['Texto corrido', 'Textos da home e dos posts', 13, 24], 'ui' => ['Menus, botões e detalhes', 'Botões, campos e textos pequenos', 11, 22]] as $role => [$label, $where, $min, $max]): ?>
        <fieldset class="a-type">
          <legend><?= $label ?></legend>
          <label>Fonte<select name="fonts[<?= $role ?>]" data-font-select><?= select_options(array_combine(font_choices(), font_choices()), $a['fonts'][$role]) ?></select></label>
          <p class="a-font-sample" data-size-sample style="font-family:'<?= e($a['fonts'][$role]) ?>';font-size:<?= min($sizes[$role], 40) ?>px<?= isset($weights[$role]) ? ';font-weight:' . $weights[$role] : '' ?>">Mangueira 1988</p>
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
    <section class="a-card">
      <h3>Título dos posts e das páginas</h3>
      <?= type_fields(['post_title']) ?>
    </section>
  </section>

  <section class="a-group" id="g-cards">
    <h2 class="a-group-title">Cards do blog</h2>
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
  </section>

  <section class="a-group" id="g-cabecalho">
    <h2 class="a-group-title">Cabeçalho e logo</h2>
    <div class="a-header-preview" data-header-preview data-logo="<?= e(media_url($site['logo'])) ?>">
      <img src="<?= e(media_url($site['logo'])) ?>" alt="" data-hp-logo>
      <span class="a-hp-menu" data-hp-menu><?php foreach (array_slice(menu_items(), 0, 5) as $mi): ?><span><?= e($mi['label']) ?></span><?php endforeach; ?></span>
    </div>
    <small class="a-help" style="margin:-6px 0 14px">Prévia: muda na hora enquanto você mexe na imagem, na força da cor, no degradê, nos tamanhos e nas cores do cabeçalho. Só vale no site depois de salvar.</small>
    <section class="a-card">
      <div class="a-row-2">
        <label><span>Tamanho do logo: <output data-range-out><?= (int) ($a['sizes']['logo'] ?? 96) ?></output> px</span>
          <input type="range" name="size_logo" min="40" max="220" step="4" value="<?= (int) ($a['sizes']['logo'] ?? 96) ?>" data-range>
        </label>
        <label><span>Altura do cabeçalho: <output data-range-out><?= (int) ($a['sizes']['header'] ?? 120) ?></output> px</span>
          <input type="range" name="size_header" min="60" max="320" step="10" value="<?= (int) ($a['sizes']['header'] ?? 120) ?>" data-range>
        </label>
      </div>
      <small class="a-help">No celular os dois ficam um pouco menores, automaticamente.</small>
      <h3>Imagem de fundo</h3>
      <?= image_field('header_image', $site['header_bg'], 'Imagem atrás do logo e do menu', 'Clique em “Remover” para deixar só a cor.') ?>
      <?php $hd = $a['header'] ?? ['overlay' => 100, 'gradient' => true]; ?>
      <label><span>Força da cor por cima da imagem: <output data-range-out><?= (int) $hd['overlay'] ?></output>%</span>
        <input type="range" name="header_overlay" min="0" max="100" step="5" value="<?= (int) $hd['overlay'] ?>" data-range>
      </label>
      <small class="a-help">0% mostra só a imagem; 100% cobre com a cor. A cor é o “Fundo do cabeçalho”, em Cores, mais abaixo.</small>
      <label class="a-check"><input type="checkbox" name="header_gradient" value="1"<?= !empty($hd['gradient']) ? ' checked' : '' ?>> Em degradê (cor mais forte à esquerda, a imagem aparece mais à direita)</label>
    </section>
  </section>

  <section class="a-group" id="g-cores">
  <h2 class="a-group-title">Cores</h2>
  <div class="a-color-groups">
    <?php foreach ($groups as $group => $fields): ?>
      <section class="a-card">
        <h3><?= e($group) ?></h3>
        <?php foreach ($fields as $key => $label): ?>
          <label class="a-color">
            <input type="color" name="colors[<?= e($key) ?>]" value="<?= e(strtolower($a['colors'][$key])) ?>" data-color>
            <span><?= e($label) ?></span>
            <code data-color-code><?= e(strtoupper($a['colors'][$key])) ?></code>
          </label>
        <?php endforeach; ?>
      </section>
    <?php endforeach; ?>
  </div>
  </section>
  <div class="a-sticky-save">
    <button class="a-btn" type="submit">Salvar aparência</button>
    <button class="a-btn a-btn-ghost" type="submit" name="acao" value="restaurar" data-confirm="Voltar as cores, as fontes do site, os tamanhos e o título dos posts para o padrão original? As fontes escolhidas em cada texto da home e do rodapé continuam.">Restaurar padrão</button>
  </div>
</form>
