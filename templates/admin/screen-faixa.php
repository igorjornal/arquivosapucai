<div class="a-head"><h1>Faixa de avisos</h1><a class="a-btn a-btn-ghost" href="/" target="_blank" rel="noopener">Ver o site ↗</a></div>
<form method="post">
  <?= csrf_field() ?>
  <div class="a-live" data-live-preview="faixa"><iframe title="Prévia do topo do site" tabindex="-1"></iframe></div>
  <small class="a-help" style="margin:4px 0 14px">Prévia: é o topo do site de verdade, na largura de um computador, e muda na hora enquanto você mexe. No site, só depois de salvar.</small>
  <section class="a-group" id="g-avisos">
    <h2 class="a-group-title">1. Avisos</h2>
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
  </section>
  <section class="a-group" id="g-aparencia-faixa">
    <h2 class="a-group-title">2. Como a faixa aparece</h2>
      <section class="a-card"><div class="a-label">Cores</div><?= color_fields('faixa') ?></section>
      <section class="a-card" data-sep-box>
        <h3>Movimento e separador</h3>
        <div class="a-row-2">
          <label><span>Velocidade: <output data-range-out><?= (int) $home['ticker_speed'] ?></output> <small>(1 = bem devagar, 10 = rápida)</small></span>
            <input type="range" name="ticker_speed" min="1" max="10" value="<?= (int) $home['ticker_speed'] ?>" data-range>
          </label>
          <label><span>Espaço entre aviso e separador: <output data-range-out><?= (int) $home['ticker_gap'] ?></output> px</span>
            <input type="range" name="ticker_gap" min="0" max="80" step="2" value="<?= (int) $home['ticker_gap'] ?>" data-range>
          </label>
        </div>
        <label>Separador entre os avisos
          <select name="ticker_sep" data-sep-select><?= select_options(['flag' => 'Bandeirinha', 'star' => 'Estrela ★', 'dot' => 'Ponto •', 'diamond' => 'Losango ◆', 'dash' => 'Traço —', 'bar' => 'Barra |', 'none' => 'Nenhum (só espaço)', 'text' => 'Texto ou emoji meu', 'image' => 'Imagem da biblioteca'], (string) $home['ticker_sep']) ?></select>
        </label>
        <div data-sep-only="text"><label>Texto ou emoji do separador<input name="ticker_sep_text" value="<?= e($home['ticker_sep_text']) ?>" maxlength="30" placeholder="🥁"></label></div>
        <div data-sep-only="image"><?= image_field('ticker_sep_image', $home['ticker_sep_image'], 'Imagem do separador', 'Fica do tamanho da letra da faixa. Imagem quadrada ou PNG sem fundo fica melhor.') ?></div>
        <small class="a-help">Estrela, ponto, losango, traço e barra usam a cor do texto da faixa. A faixa continua parando quando o leitor passa o mouse em cima.</small>
      </section>
      <section class="a-card">
        <h3>Espessura da faixa</h3>
        <label><span>Espaço em cima e embaixo do texto: <output data-range-out><?= (int) $home['ticker_pad'] ?></output> px</span>
          <input type="range" name="ticker_pad" min="0" max="40" value="<?= (int) $home['ticker_pad'] ?>" data-range>
        </label>
        <small class="a-help">A faixa fica com a altura da letra mais esse espaço. Letra maior pede espaço menor, e vice-versa.</small>
      </section>
      <section class="a-card"><h3>Fonte dos avisos</h3><?= type_fields(['ticker']) ?></section>
  </section>
  <div class="a-sticky-save"><button class="a-btn" type="submit">Salvar faixa de avisos</button></div>
</form>
