<?php $hd = $a['header'] ?? ['overlay' => 100, 'gradient' => true]; $mn = $a['menu'] ?? ['align' => 'right', 'gap' => 30, 'effect' => 'line']; ?>
<div class="a-head"><h1>Cabeçalho e menu</h1><a class="a-btn a-btn-ghost" href="/" target="_blank" rel="noopener">Ver o site ↗</a></div>
<form method="post">
  <?= csrf_field() ?>
  <div class="a-live" data-live-preview="cabecalho"><iframe title="Prévia do topo do site" tabindex="-1"></iframe></div>
  <small class="a-help" style="margin:4px 0 14px">Prévia: é o topo do site de verdade, na largura de um computador, e muda na hora enquanto você mexe. No site, só depois de salvar.</small>
  <nav class="a-toc" aria-label="Nesta página"><a href="#g-logo">Logo</a><a href="#g-fundo">Fundo</a><a href="#g-itens">Itens do menu</a><a href="#g-aparencia-menu">Como o menu aparece</a><a href="#g-redes">Redes e busca</a></nav>

  <section class="a-group" id="g-logo">
    <h2 class="a-group-title">1. Logo e tamanho</h2>
    <section class="a-card">
      <div class="a-row-2">
        <?= image_field('logo', $site['logo'], 'Logo') ?>
        <div>
          <label><span>Tamanho do logo: <output data-range-out><?= (int) ($a['sizes']['logo'] ?? 96) ?></output> px</span>
            <input type="range" name="size_logo" min="40" max="220" step="4" value="<?= (int) ($a['sizes']['logo'] ?? 96) ?>" data-range>
          </label>
          <label><span>Altura do cabeçalho: <output data-range-out><?= (int) ($a['sizes']['header'] ?? 120) ?></output> px</span>
            <input type="range" name="size_header" min="60" max="320" step="10" value="<?= (int) ($a['sizes']['header'] ?? 120) ?>" data-range>
          </label>
          <small class="a-help">No celular os dois ficam um pouco menores, sozinhos.</small>
        </div>
      </div>
    </section>
  </section>

  <section class="a-group" id="g-fundo">
    <h2 class="a-group-title">2. Fundo</h2>
    <section class="a-card">
      <div class="a-row-2">
        <?= image_field('header_image', $site['header_bg'], 'Imagem atrás do logo e do menu', 'Clique em “Remover” para deixar só a cor.') ?>
        <div>
          <?= color_fields('cabecalho_fundo') ?>
          <label><span>Força da cor por cima da imagem: <output data-range-out><?= (int) $hd['overlay'] ?></output>%</span>
            <input type="range" name="header_overlay" min="0" max="100" step="5" value="<?= (int) $hd['overlay'] ?>" data-range>
          </label>
          <small class="a-help">0% mostra só a imagem; 100% cobre com a cor.</small>
          <label class="a-check"><input type="checkbox" name="header_gradient" value="1"<?= !empty($hd['gradient']) ? ' checked' : '' ?>> Em degradê (a imagem aparece mais à direita)</label>
        </div>
      </div>
    </section>
  </section>

  <section class="a-group" id="g-itens">
    <h2 class="a-group-title">3. Itens do menu</h2>
    <section class="a-card">
      <p class="a-muted">Os itens aparecem no topo e no rodapé, nesta ordem. Para descer até uma parte da página inicial, use <code>/#arquivo</code>, <code>/#playlists</code>, <code>/#acervo</code>, <code>/#historia</code> ou <code>/#contato</code>. Para uma página, o endereço dela, como <code>/acervo</code>.</p>
      <div class="a-menu-rows" data-menu-rows>
        <?php foreach ($items as $i => $it): ?>
          <div class="a-menu-row" data-menu-row>
            <input type="hidden" name="menu[<?= $i ?>][id]" value="<?= (int) $it['id'] ?>"><input type="hidden" name="menu[<?= $i ?>][remove]" value="" data-remove>
            <label><span class="sr">Nome</span><input name="menu[<?= $i ?>][label]" value="<?= e($it['label']) ?>" placeholder="Nome"></label>
            <label><span class="sr">Endereço</span><input name="menu[<?= $i ?>][url]" value="<?= e($it['url']) ?>" placeholder="Endereço" list="menu-sugestoes"></label>
            <label class="a-check"><input type="checkbox" name="menu[<?= $i ?>][new_tab]" value="1"<?= $it['new_tab'] ? ' checked' : '' ?>> Nova aba</label>
            <div class="a-order"><button type="button" class="a-icon-btn" title="Subir" data-move="up">↑</button><button type="button" class="a-icon-btn" title="Descer" data-move="down">↓</button><button type="button" class="a-icon-btn a-danger-text" title="Remover" data-remove-row>×</button></div>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="a-btn a-btn-sm" data-add-menu>+ Adicionar item</button>
      <small class="a-help" style="margin-top:8px">A ordem e os itens novos ou removidos só valem depois de salvar.</small>
      <datalist id="menu-sugestoes">
        <option value="/">Home</option><option value="/blog">Blog</option><option value="/busca">Busca</option>
        <?php foreach ($pages as $pg): ?><option value="/<?= e($pg['slug']) ?>">Página: <?= e($pg['title']) ?></option><?php endforeach; ?>
        <?php foreach ($categories as $c): ?><option value="/blog/categoria/<?= e($c['slug']) ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
      </datalist>
    </section>
  </section>

  <section class="a-group" id="g-aparencia-menu">
    <h2 class="a-group-title">4. Como o menu aparece</h2>
    <section class="a-card">
      <div class="a-row-2">
        <div><div class="a-label">Cores</div><?= color_fields('cabecalho_menu') ?></div>
        <div>
          <label>Efeito ao passar o mouse
            <select name="menu_effect"><?= select_options(['line' => 'Risco embaixo', 'grow' => 'Risco que cresce do meio', 'pill' => 'Fundo arredondado', 'color' => 'Só troca de cor'], $mn['effect']) ?></select>
          </label>
          <small class="a-help">No “Fundo arredondado”, o texto em cima do fundo fica com a cor do fundo do cabeçalho, para nunca sumir.</small>
          <div class="a-label">Posição do menu</div>
          <div class="a-seg">
            <?php foreach (['logo' => 'Colado no logo', 'center' => 'Centro', 'right' => 'Direita'] as $k => $l): ?>
              <label><input type="radio" name="menu_align" value="<?= $k ?>"<?= $mn['align'] === $k ? ' checked' : '' ?>><span><?= $l ?></span></label>
            <?php endforeach; ?>
          </div>
          <label><span>Espaço entre os itens: <output data-range-out><?= (int) $mn['gap'] ?></output> px</span>
            <input type="range" name="menu_gap" min="0" max="80" step="2" value="<?= (int) $mn['gap'] ?>" data-range>
          </label>
        </div>
      </div>
      <h3>Fonte do menu</h3>
      <?= type_fields(['menu']) ?>
    </section>
  </section>

  <section class="a-group" id="g-redes">
    <h2 class="a-group-title">5. Redes e busca</h2>
    <section class="a-card">
      <p class="a-muted" style="margin-top:0">Os ícones das redes e a lupinha da busca aparecem sozinhos no topo. Os endereços das redes ficam em <a href="/admin/configuracoes#g-redes">Configurações</a>, porque também aparecem no rodapé.</p>
      <?= color_fields('cabecalho_redes') ?>
    </section>
    <details class="a-adv"><summary>Ajustes avançados</summary><?= color_fields('cabecalho_avancado') ?></details>
  </section>
  <div class="a-sticky-save"><button class="a-btn" type="submit">Salvar cabeçalho e menu</button></div>
</form>
