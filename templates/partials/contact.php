<section class="sec sec-contact" id="contato">
  <div class="wrap narrow">
    <h2 class="sec-title center"><?= e($home['contact_title']) ?></h2>
    <?php if ($home['contact_text']): ?><p class="center"><?= e($home['contact_text']) ?></p><?php endif; ?>
    <?php foreach (take_flash_group('contact') as $f): ?>
      <p class="notice notice-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></p>
    <?php endforeach; ?>
    <form class="contact-form" action="/contato" method="post">
      <?= csrf_field() ?><?= spam_fields() ?>
      <div class="row">
        <label>Nome<input name="nome" required maxlength="120" value="<?= e(old('nome')) ?>" autocomplete="given-name"></label>
        <label>Sobrenome<input name="sobrenome" maxlength="120" value="<?= e(old('sobrenome')) ?>" autocomplete="family-name"></label>
      </div>
      <div class="row">
        <label>E-mail<input type="email" name="email" required maxlength="190" value="<?= e(old('email')) ?>" autocomplete="email"></label>
        <label>Telefone<input type="tel" name="telefone" maxlength="40" value="<?= e(old('telefone')) ?>" autocomplete="tel"></label>
      </div>
      <label>Mensagem<textarea name="mensagem" required rows="5" maxlength="5000"><?= e(old('mensagem')) ?></textarea></label>
      <button class="btn btn-dark" type="submit">Enviar</button>
    </form>
  </div>
</section>
<?php forget_old(); ?>
