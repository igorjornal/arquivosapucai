<div class="a-head">
  <h1>Newsletter <span class="a-muted">(<?= $total ?> inscritos)</span></h1>
  <?php if ($total): ?><a class="a-btn" href="/admin/newsletter/exportar">Baixar lista (Excel/CSV)</a><?php endif; ?>
</div>
<p class="a-muted">A lista pode ser importada em ferramentas de envio de e-mail, como Mailchimp, Brevo ou o e-mail marketing da Hostinger.</p>
<?php if (current_user()['role'] === 'admin'): ?>
<details class="a-card">
  <summary><h2>Textos do cadastro (rodapé do site)</h2></summary>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="acao" value="textos">
    <label>Título<input name="news_title" value="<?= e($news['title']) ?>"></label>
    <label>Texto<textarea name="news_text" rows="2"><?= e($news['text']) ?></textarea></label>
    <button class="a-btn">Salvar textos</button>
  </form>
</details>
<?php endif; ?>
<?php if (!$subscribers): ?>
  <div class="a-card a-empty"><p>Ninguém se inscreveu ainda.</p></div>
<?php else: ?>
<div class="a-table-wrap">
<table class="a-table">
  <thead><tr><th>E-mail</th><th>Data</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($subscribers as $s): ?>
    <tr>
      <td><?= e($s['email']) ?></td>
      <td class="a-nowrap"><?= e(date('d/m/Y H:i', strtotime($s['created_at']))) ?></td>
      <td class="num"><form method="post" action="/admin/newsletter/<?= (int) $s['id'] ?>/excluir"><?= csrf_field() ?><button class="a-link a-danger-text" data-confirm="Remover este e-mail da lista?">Remover</button></form></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?= admin_pager($pagination, '/admin/newsletter') ?>
<?php endif; ?>
