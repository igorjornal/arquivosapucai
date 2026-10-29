<div class="a-head"><h1>Configurações</h1></div>
<form method="post">
  <?= csrf_field() ?>
    <div class="a-cols">
      <section class="a-card">
        <h2>Identidade</h2>
        <label>Nome do site<input name="title" value="<?= e($site['title']) ?>" required></label>
        <label>Frase curta<input name="tagline" value="<?= e($site['tagline']) ?>"></label>
        <label>Descrição (rodapé e Google)<textarea name="description" rows="3"><?= e($site['description']) ?></textarea></label>
        <label>E-mail de contato<input type="email" name="email" value="<?= e($site['email']) ?>"></label>
        <small class="a-help">O logo e a imagem do topo ficam em <a href="/admin/cabecalho">Cabeçalho e menu</a>.</small>
        <?= image_field('share_image', $site['share_image'], 'Imagem padrão de compartilhamento', 'Aparece no WhatsApp e redes quando a página não tem imagem própria.') ?>
      </section>
      <section class="a-card" id="g-redes">
        <h2>Redes sociais</h2>
        <p class="a-muted">Aparecem no topo e no rodapé. Deixe vazio o que não usar: o ícone some do site.</p>
        <?php foreach (['youtube' => 'YouTube', 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'x' => 'X (Twitter)', 'tiktok' => 'TikTok', 'linkedin' => 'LinkedIn'] as $net => $label): ?>
          <label><?= $label ?><input name="socials[<?= $net ?>]" value="<?= e($site['socials'][$net] ?? '') ?>" placeholder="https://"></label>
        <?php endforeach; ?>
      </section>
    </div>
    <div class="a-cols">
      <section class="a-card" id="g-avisos">
        <h2>Avisos por e-mail</h2>
        <p class="a-muted">Para onde o site manda um aviso quando acontece algo. Deixe vazio para não receber aquele aviso. Tudo também fica guardado no painel.</p>
        <?php foreach (['contact' => ['Mensagem pelo formulário de contato', 'Mensagens'], 'newsletter' => ['Novo cadastro na newsletter do rodapé', 'Newsletter'], 'comments' => ['Comentário esperando aprovação', 'Comentários']] as $k => [$label, $where]): ?>
          <label><?= $label ?><input type="email" name="notify[<?= $k ?>]" value="<?= e(notify_to($k)) ?>" placeholder="seu@email.com"></label>
          <small class="a-help">Fica guardado também em Painel → <?= $where ?>.</small>
        <?php endforeach; ?>
        <button class="a-btn a-btn-ghost a-btn-sm" type="submit" name="acao" value="teste_email">Salvar e mandar e-mail de teste</button>
      </section>
      <section class="a-card" id="g-medicao">
        <h2>Medição de visitas e anúncios</h2>
        <p class="a-muted">Cole só o número. Vazio = desligado. Com algum preenchido, aparece para o leitor o aviso de cookies, e a medição só começa se ele aceitar (LGPD).</p>
        <label>Número do Pixel da Meta (Facebook/Instagram)<input name="meta_pixel" value="<?= e(setting('tracking')['meta_pixel'] ?? '') ?>" inputmode="numeric" placeholder="ex.: 123456789012345"></label>
        <small class="a-help">Fica no Gerenciador de Eventos da Meta. Só números.</small>
        <label>Código do Google Analytics<input name="ga4" value="<?= e(setting('tracking')['ga4'] ?? '') ?>" placeholder="ex.: G-AB12CD34EF"></label>
        <small class="a-help">Fica no Google Analytics, em Administrador → Fluxos de dados. Começa com “G-”.</small>
        <label>Texto do aviso de cookies<textarea name="cookie_text" rows="2" maxlength="300"><?= e(setting('tracking')['cookie_text'] ?? '') ?></textarea></label>
      </section>
    </div>

  <div class="a-sticky-save"><button class="a-btn" type="submit">Salvar configurações</button></div>
</form>
