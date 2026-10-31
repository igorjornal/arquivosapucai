<div class="a-head"><h1>Contato</h1><a class="a-btn a-btn-ghost" href="/#contato" target="_blank" rel="noopener">Ver no site ↗</a></div>
<form method="post">
  <?= csrf_field() ?>
  <p class="a-muted">O formulário de contato aparece no fim da página inicial, das páginas e do blog (não dentro dos posts). O que você muda aqui vale em todos esses lugares. Para onde vão as mensagens fica em <a href="/admin/configuracoes">Configurações</a>.</p>
    <section class="a-group" id="g-contato">
      <h2 class="a-group-title">Textos, cores e fontes</h2>
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

  <div class="a-sticky-save"><button class="a-btn" type="submit">Salvar contato</button></div>
</form>
