<?php $lb = $home['contact_labels']; $lbStyle = type_style('contact_labels'); ?>
<section class="sec sec-contact" id="<?= e($anchor ?? 'contato') ?>">
  <div class="wrap narrow">
    <h2 class="sec-title center"<?= type_style('contact_title') ?>><?= e($home['contact_title']) ?></h2>
    <?php if ($home['contact_text']): ?><p class="center"<?= type_style('contact_text') ?>><?= e($home['contact_text']) ?></p><?php endif; ?>
    <?php foreach (take_flash_group('contact') as $f): ?>
      <p class="notice notice-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></p>
    <?php endforeach; ?>
    <form class="contact-form" action="/contato" method="post">
      <?= csrf_field() ?><?= spam_fields() ?>
      <div class="row">
        <label<?= $lbStyle ?>><?= e($lb['nome']) ?><input name="nome" required maxlength="120" value="<?= e(old('nome')) ?>" autocomplete="given-name"></label>
        <label<?= $lbStyle ?>><?= e($lb['sobrenome']) ?><input name="sobrenome" maxlength="120" value="<?= e(old('sobrenome')) ?>" autocomplete="family-name"></label>
      </div>
      <div class="row">
        <label<?= $lbStyle ?>><?= e($lb['email']) ?><input type="email" name="email" required maxlength="190" value="<?= e(old('email')) ?>" autocomplete="email"></label>
        <label<?= $lbStyle ?>><?= e($lb['telefone']) ?><input type="tel" name="telefone" maxlength="40" value="<?= e(old('telefone')) ?>" autocomplete="tel"></label>
      </div>
      <label<?= $lbStyle ?>><?= e($lb['mensagem']) ?><textarea name="mensagem" required rows="5" maxlength="5000"><?= e(old('mensagem')) ?></textarea></label>
      <button class="btn btn-dark" type="submit"<?= type_style('contact_button') ?>><?= e($home['contact_button']) ?></button>
    </form>
  </div>
</section>
<?php forget_old(); ?>
