<div class="a-head"><h1>Comentários</h1></div>
<nav class="a-tabs">
  <?php foreach (['pending' => 'Pendentes', 'approved' => 'Aprovados', 'spam' => 'Spam'] as $k => $label): ?>
    <a href="/admin/comentarios?status=<?= $k ?>"<?= $status === $k ? ' aria-current="page"' : '' ?>><?= $label ?> <span class="a-muted">(<?= (int) ($counts[$k] ?? 0) ?>)</span></a>
  <?php endforeach; ?>
</nav>
<?php if (!$comments): ?>
  <div class="a-card a-empty"><p><?= $status === 'pending' ? 'Nenhum comentário esperando aprovação. 🎉' : 'Nada por aqui.' ?></p></div>
<?php endif; ?>
<?php foreach ($comments as $c): ?>
  <article class="a-card a-comment">
    <header>
      <strong><?= e($c['author_name']) ?></strong>
      <a class="a-muted" href="mailto:<?= e($c['author_email']) ?>"><?= e($c['author_email']) ?></a>
      <span class="a-muted">· <?= e(date('d/m/Y H:i', strtotime($c['created_at']))) ?></span>
    </header>
    <p class="a-muted">Em <a href="/blog/<?= e($c['post_slug']) ?>#comentario-<?= (int) $c['id'] ?>" target="_blank" rel="noopener"><?= e($c['post_title']) ?></a><?= $c['parent_name'] ? ' · respondendo a ' . e($c['parent_name']) : '' ?></p>
    <blockquote><?= nl2br(e($c['content'])) ?></blockquote>
    <div class="a-row-tight">
      <?php foreach (['aprovar' => 'Aprovar', 'pendente' => 'Voltar para pendente', 'spam' => 'É spam', 'excluir' => 'Excluir'] as $action => $label):
          if (($action === 'aprovar' && $status === 'approved') || ($action === 'pendente' && $status === 'pending') || ($action === 'spam' && $status === 'spam')) continue; ?>
        <form method="post" action="/admin/comentarios/<?= (int) $c['id'] ?>/<?= $action ?>">
          <?= csrf_field() ?><input type="hidden" name="voltar" value="<?= e($status) ?>">
          <button class="a-btn a-btn-sm <?= $action === 'aprovar' ? '' : ($action === 'excluir' ? 'a-btn-danger' : 'a-btn-ghost') ?>"<?= $action === 'excluir' ? ' data-confirm="Excluir este comentário e as respostas dele?"' : '' ?>><?= $label ?></button>
        </form>
      <?php endforeach; ?>
      <button type="button" class="a-btn a-btn-sm a-btn-ghost" data-toggle-target="#resp-<?= (int) $c['id'] ?>">Responder</button>
    </div>
    <form id="resp-<?= (int) $c['id'] ?>" class="a-reply" method="post" action="/admin/comentarios/<?= (int) $c['id'] ?>/responder" hidden>
      <?= csrf_field() ?><input type="hidden" name="voltar" value="<?= e($status) ?>">
      <label>Sua resposta (aparece com o seu nome e já aprova o comentário)<textarea name="resposta" rows="3" required></textarea></label>
      <button class="a-btn a-btn-sm">Publicar resposta</button>
    </form>
  </article>
<?php endforeach; ?>
<?= admin_pager($pagination, '/admin/comentarios?status=' . $status) ?>
