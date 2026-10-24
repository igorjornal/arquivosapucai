<?php $ratios = array_map(fn ($r) => $r['label'], image_ratios()); ?>
<div class="a-head">
  <h1>Aparência</h1>
  <a class="a-btn a-btn-ghost" href="/" target="_blank" rel="noopener">Ver o site ↗</a>
</div>
<form method="post">
  <?= csrf_field() ?>
  <div class="a-cols">
    <section class="a-card">
      <h2>Fontes</h2>
      <?php foreach (['heading' => 'Títulos', 'body' => 'Texto corrido', 'ui' => 'Menus, botões e detalhes'] as $role => $label): ?>
        <label><?= $label ?>
          <select name="fonts[<?= $role ?>]" data-font-select><?= select_options(array_combine(font_choices(), font_choices()), $a['fonts'][$role]) ?></select>
        </label>
        <p class="a-font-sample" data-font-sample style="font-family:'<?= e($a['fonts'][$role]) ?>'<?= $role === 'heading' ? ';font-weight:800' : '' ?>">Arquivo Sapucaí — Mangueira 1988</p>
      <?php endforeach; ?>
      <small class="a-help">Syne, Bitter e Instrument Sans já vêm no site. As outras são carregadas do Google Fonts (gratuitas).</small>
    </section>
    <section class="a-card">
      <h2>Cards do blog</h2>
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
    </section>
  </div>

  <section class="a-card">
    <h2>Fonte e tamanho de partes do site</h2>
    <div class="a-type-grid">
      <?php foreach (['menu' => ['Menu do topo', 11, 28], 'post_title' => ['Título dos posts e páginas', 20, 90], 'ticker' => ['Faixa corrida (avisos)', 10, 28]] as $key => [$label, $min, $max]):
          $t = type_settings()[$key]; ?>
        <fieldset class="a-type">
          <legend><?= $label ?></legend>
          <label>Fonte<select name="type[<?= $key ?>][font]" data-font-select><?= select_options(array_combine(font_choices(), font_choices()), $t['font']) ?></select></label>
          <p class="a-font-sample" style="font-family:'<?= e($t['font']) ?>';font-size:<?= min($t['size'], 40) ?>px<?= $key === 'post_title' ? ';font-weight:800' : '' ?>" data-size-sample>Mangueira 1988</p>
          <label>Tamanho: <output data-range-out><?= $t['size'] ?></output> px
            <input type="range" name="type[<?= $key ?>][size]" min="<?= $min ?>" max="<?= $max ?>" value="<?= $t['size'] ?>" data-range>
          </label>
        </fieldset>
      <?php endforeach; ?>
    </div>
    <small class="a-help">No celular o título dos posts diminui sozinho se não couber.</small>
  </section>

  <section class="a-card">
    <h2>Cabeçalho e logo</h2>
    <div class="a-row-2">
      <label>Tamanho do logo: <output data-range-out><?= (int) ($a['sizes']['logo'] ?? 96) ?></output> px
        <input type="range" name="size_logo" min="40" max="220" step="4" value="<?= (int) ($a['sizes']['logo'] ?? 96) ?>" data-range>
      </label>
      <label>Altura do cabeçalho: <output data-range-out><?= (int) ($a['sizes']['header'] ?? 120) ?></output> px
        <input type="range" name="size_header" min="60" max="320" step="10" value="<?= (int) ($a['sizes']['header'] ?? 120) ?>" data-range>
      </label>
    </div>
    <small class="a-help">No celular os dois ficam um pouco menores, automaticamente.</small>
  </section>

  <h2 class="a-section-title">Cores</h2>
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
  <div class="a-sticky-save">
    <button class="a-btn" type="submit">Salvar aparência</button>
    <button class="a-btn a-btn-ghost" type="submit" name="acao" value="restaurar" data-confirm="Voltar todas as cores e fontes para o padrão original?">Restaurar padrão</button>
  </div>
</form>
