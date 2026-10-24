<p><a href="/admin/mensagens">← Todas as mensagens</a></p>
<article class="a-card">
  <h1><?= e(trim($msg['first_name'] . ' ' . $msg['last_name'])) ?></h1>
  <p class="a-muted">
    <a href="mailto:<?= e($msg['email']) ?>"><?= e($msg['email']) ?></a>
    <?php if ($msg['phone']): ?> · <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $msg['phone'])) ?>"><?= e($msg['phone']) ?></a><?php endif; ?>
    · <?= e(date('d/m/Y H:i', strtotime($msg['created_at']))) ?>
  </p>
  <div class="a-message"><?= nl2br(e($msg['message'])) ?></div>
  <div class="a-row-tight">
    <a class="a-btn" href="mailto:<?= e($msg['email']) ?>?subject=<?= rawurlencode('Re: contato pelo site Arquivo Sapucaí') ?>">Responder por e-mail</a>
    <form method="post" action="/admin/mensagens/<?= (int) $msg['id'] ?>/excluir"><?= csrf_field() ?><button class="a-btn a-btn-danger" data-confirm="Excluir esta mensagem?">Excluir</button></form>
  </div>
</article>
