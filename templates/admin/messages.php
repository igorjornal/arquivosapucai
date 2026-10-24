<div class="a-head"><h1>Mensagens do contato</h1></div>
<?php if (!$messages): ?>
  <div class="a-card a-empty"><p>Nenhuma mensagem ainda.</p></div>
<?php else: ?>
<div class="a-table-wrap">
<table class="a-table">
  <thead><tr><th>De</th><th>Mensagem</th><th>Data</th></tr></thead>
  <tbody>
  <?php foreach ($messages as $m): ?>
    <tr class="<?= $m['read_at'] ? '' : 'a-unread' ?>">
      <td><a class="a-strong" href="/admin/mensagens/<?= (int) $m['id'] ?>"><?= e(trim($m['first_name'] . ' ' . $m['last_name'])) ?></a><br><span class="a-muted"><?= e($m['email']) ?></span></td>
      <td><a href="/admin/mensagens/<?= (int) $m['id'] ?>"><?= e(text_excerpt($m['message'], 110)) ?></a></td>
      <td class="a-nowrap"><?= e(date('d/m/Y H:i', strtotime($m['created_at']))) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?= admin_pager($pagination, '/admin/mensagens') ?>
<?php endif; ?>
