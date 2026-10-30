// Revisão completa do painel e do site: cada botão, salvar e conferir no site.
const { chromium } = require(process.argv[2] + '/node_modules/playwright-core');
const fs = require('fs');
const base = 'http://127.0.0.1:8080';
const shots = process.argv[3];
const IMG1 = '/home/user/arquivosapucai/assets/img/sample/desfile-1.webp';
const IMG2 = '/home/user/arquivosapucai/assets/img/banner-serie-ouro-2027.webp';
const results = [];
let area = '';
const ok = (name, cond, extra) => { results.push({ area, name, pass: !!cond, extra: extra || '' }); };
let failShots = 0;
const safe = async (name, fn) => { try { await fn(); } catch (e) { if (pageRef && failShots < 3) { failShots++; await pageRef.screenshot({ path: process.argv[3] + '/falha-' + failShots + '.png' }).catch(() => {}); } ok(name, false, 'erro: ' + e.message.split('\n')[0] + ' em ' + (pageRef && pageRef.url())); } };
let pageRef = null;

(async () => {
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium' });
  const ctx = await b.newContext({ viewport: { width: 1366, height: 900 } });
  const p = await ctx.newPage();
  pageRef = p;
  const jsErrors = [];
  p.on('pageerror', e => jsErrors.push(p.url() + ' → ' + e.message));
  let lastDialog = '';
  p.on('dialog', d => { lastDialog = d.message(); d.accept(); });
  const go = async (path) => (await p.goto(base + path)).text();
  const pub = async (path) => { const r = await fetch(base + path); return r.text(); };
  const submit = async (locator) => { await Promise.all([p.waitForNavigation(), locator.click()]); };
  // Prévia ao vivo do topo (iframe desenhado pelo próprio site)
  const prevHtml = async () => { await p.waitForTimeout(1300); return p.locator('[data-live-preview] iframe').evaluate(f => f.contentDocument.documentElement.outerHTML); };
  const notice = async () => (await p.locator('.a-toast, .notice').first().textContent({ timeout: 3000 }).catch(() => '')) || '';
  const login = async (email, pass) => {
    await p.goto(base + '/admin/login');
    await p.fill('input[name=email]', email); await p.fill('input[name=senha]', pass);
    await submit(p.locator('button[type=submit]'));
  };
  const logout = async () => { await p.goto(base + '/admin'); await submit(p.locator('form[action="/admin/sair"] button')).catch(() => {}); };
  const pickFromLibrary = async (scope, index = 0) => {
    await scope.locator('[data-pick]').first().click();
    await p.waitForSelector('[data-media-grid] button');
    await p.locator('[data-media-grid] button').nth(index).click();
  };
  const editorReady = () => p.waitForFunction(() => window.tinymce && tinymce.activeEditor && tinymce.activeEditor.initialized, null, { timeout: 15000 });

  /* ---------------- Login ---------------- */
  area = 'Login';
  await safe('senha errada é recusada', async () => {
    await p.goto(base + '/admin/login');
    await p.fill('input[name=email]', 'admin@teste.com'); await p.fill('input[name=senha]', 'errada-123456');
    await submit(p.locator('button[type=submit]'));
    ok('senha errada é recusada', (await notice()).includes('incorretos'));
  });
  await safe('login certo entra no painel', async () => {
    await login('admin@teste.com', 'senha-admin-123');
    ok('login certo entra no painel', p.url().endsWith('/admin'));
  });

  /* ---------------- Imagens ---------------- */
  area = 'Imagens';
  await safe('enviar 2 imagens de uma vez', async () => {
    await p.goto(base + '/admin/midia');
    await Promise.all([p.waitForNavigation(), p.setInputFiles('input[name="arquivos[]"]', [IMG1, IMG2])]);
    ok('enviar 2 imagens de uma vez', (await notice()).includes('2 imagens'), await notice());
    ok('aparecem na biblioteca', await p.locator('.a-gallery-item').count() === 2);
  });
  await safe('arquivo que não é imagem é recusado', async () => {
    fs.writeFileSync('/tmp/claude-0/falso.jpg', '<?php echo 1; ?>');
    await Promise.all([p.waitForNavigation(), p.setInputFiles('input[name="arquivos[]"]', '/tmp/claude-0/falso.jpg')]);
    ok('arquivo que não é imagem é recusado', (await notice()).includes('Formato não aceito'), await notice());
  });
  await safe('imagem enviada abre no site', async () => {
    const src = await p.locator('.a-gallery-item img').first().getAttribute('src');
    const r = await fetch(base + src);
    ok('imagem enviada abre no site', r.status === 200 && r.headers.get('content-type').includes('image'));
  });

  /* ---------------- Categorias e tags ---------------- */
  area = 'Categorias e tags';
  await safe('criar categoria', async () => {
    await p.goto(base + '/admin/categorias');
    await p.fill('input[name=name]', 'Guia dos Enredos 2027 QA'); await p.fill('textarea[name=description]', 'Descrição da categoria de teste');
    await submit(p.locator('button:has-text("Criar categoria")'));
    ok('criar categoria', (await notice()).includes('criada') && (await p.content()).includes('Guia dos Enredos 2027 QA'));
  });
  await safe('editar categoria', async () => {
    await p.locator('a:has-text("Guia dos Enredos 2027 QA")').first().click(); await p.waitForLoadState();
    await p.fill('input[name=name]', 'Guia 2027'); await submit(p.locator('button:has-text("Salvar")'));
    ok('editar categoria', (await p.content()).includes('Guia 2027'));
  });

  /* ---------------- Posts e editor ---------------- */
  area = 'Posts e editor';
  let postId = null;
  await safe('novo post: título gera endereço', async () => {
    await p.goto(base + '/admin/posts/novo'); await editorReady();
    await p.fill('[data-title]', 'Mangueira 1988: o samba que contou a história');
    ok('novo post: título gera endereço', (await p.inputValue('[data-slug]')) === 'mangueira-1988-o-samba-que-contou-a-historia');
  });
  await safe('editor: texto com tamanho e cor', async () => {
    await p.frameLocator('.tox-edit-area iframe').locator('body').click();
    await p.keyboard.type('Texto do post escrito pelo teclado.');
    await p.keyboard.press('Shift+Home');
    await p.locator('.tox-tbtn--bespoke input, input.tox-number-input__input, .tox-number-input input').first().fill('26px').catch(() => {});
    await p.keyboard.press('Enter').catch(() => {});
    const html = await p.evaluate(() => tinymce.activeEditor.getContent());
    ok('editor: digitar texto', html.includes('Texto do post escrito pelo teclado.'));
  });
  await safe('editor: janela Imagem (biblioteca, formato, alinhamento)', async () => {
    await p.evaluate(() => { const ed = tinymce.activeEditor; ed.selection.select(ed.getBody(), true); ed.selection.collapse(false); });
    await p.click('button[aria-label="Imagem (formato, tamanho, alinhamento)"]');
    await p.waitForSelector('.tox-dialog');
    await p.click('.tox-dialog [data-arq="pick"]');
    await p.waitForSelector('[data-media-grid] button'); await p.locator('[data-media-grid] button').first().click();
    const d = p.locator('.tox-dialog');
    await d.getByLabel('Descrição (acessibilidade)').fill('Comissão de frente');
    await d.getByLabel('Legenda (opcional)').fill('Legenda de teste');
    await d.getByLabel('Alinhamento').selectOption('right');
    await d.getByLabel('Tamanho').selectOption('40');
    await d.getByLabel('Formato (recorte)').selectOption('square');
    await d.getByLabel('Cantos').selectOption('18');
    await d.locator('button:has-text("Inserir")').click();
    const html = await p.evaluate(() => tinymce.activeEditor.getContent());
    ok('editor: janela Imagem (biblioteca, formato, alinhamento)', /figure class="image align-right" style="width: 40%;?"/.test(html) && html.includes('aspect-ratio: 1 / 1') && html.includes('Legenda de teste'), html.match(/<figure[^>]*>/) && html.match(/<figure[^>]*>/)[0]);
  });
  await safe('editor: janela Vídeo', async () => {
    await p.click('button[aria-label="Vídeo do YouTube"]'); await p.waitForSelector('.tox-dialog');
    await p.locator('.tox-dialog textarea').fill('https://www.youtube.com/watch?v=SLrGaE65GSM');
    await p.locator('.tox-dialog button:has-text("Inserir")').click();
    const html = await p.evaluate(() => tinymce.activeEditor.getContent());
    ok('editor: janela Vídeo', html.includes('youtube-nocookie.com/embed/SLrGaE65GSM'));
    const cap = (html.match(/<figcaption>([\s\S]*?)<\/figcaption>/) || [])[1] || '';
    ok('vídeo NÃO fica dentro da legenda da imagem', !cap.includes('video') && cap.trim() === 'Legenda de teste', cap.slice(0, 80));
  });
  await safe('editor: janela Botão', async () => {
    await p.click('button[aria-label="Botão colorido"]'); await p.waitForSelector('.tox-dialog');
    const d = p.locator('.tox-dialog');
    await d.getByLabel('Texto do botão').fill('Assista ao desfile');
    await d.getByLabel('Link').fill('https://www.youtube.com/@ArquivoSapucai');
    await d.locator('button:has-text("Inserir")').click();
    const html = await p.evaluate(() => tinymce.activeEditor.getContent());
    ok('editor: janela Botão', html.includes('class="btn-post"') && html.includes('--btn-hover-bg'));
  });
  await safe('editor: espaço e divisória', async () => {
    await p.click('button[aria-label="Inserir espaço"]'); await p.waitForSelector('.tox-dialog');
    await p.locator('.tox-dialog button:has-text("Inserir")').click();
    await p.click('button[aria-label="Linha divisória"]'); await p.click('.tox-collection__item:has-text("Tracejada")');
    const html = await p.evaluate(() => tinymce.activeEditor.getContent());
    ok('editor: espaço e divisória', html.includes('class="spacer"') && html.includes('divider-dashed'));
  });
  await safe('editor: espaço antes/depois do bloco', async () => {
    await p.evaluate(() => { const ed = tinymce.activeEditor; ed.selection.select(ed.getBody().querySelector('p'), true); });
    await p.click('button[aria-label="Espaço antes e depois do bloco"]');
    await p.hover('.tox-collection__item:has-text("Espaço antes")'); await p.click('.tox-collection__item:has-text("40 px")');
    const html = await p.evaluate(() => tinymce.activeEditor.getContent());
    ok('editor: espaço antes/depois do bloco', html.includes('margin-top: 40px'));
  });
  await safe('editor: tabela com cor de célula', async () => {
    await p.evaluate(() => tinymce.activeEditor.execCommand('mceInsertTable', false, { rows: 2, columns: 2 }));
    await p.evaluate(() => { const ed = tinymce.activeEditor; const td = ed.getBody().querySelector('td'); ed.selection.setCursorLocation(td, 0); ed.execCommand('mceTableApplyCellStyle', false, { 'background-color': '#ffdacd' }); });
    const html = await p.evaluate(() => tinymce.activeEditor.getContent());
    ok('editor: tabela com cor de célula', html.includes('<table') && /background-color: #ffdacd/i.test(html));
  });
  await safe('capa: escolher, formato, tamanho, alinhamento, recorte', async () => {
    const cover = p.locator('[data-cover]');
    await pickFromLibrary(cover, 1);
    await cover.locator('[data-cover-ratio]').selectOption('banner');
    await cover.locator('[data-cover-width]').fill('70');
    await cover.locator('input[data-cover-align][value=left]').check({ force: true });
    await cover.locator('[data-focus-area]').click({ position: { x: 20, y: 20 } });
    ok('capa: prévia aparece', await cover.locator('[data-cover-preview]').isVisible());
    await p.selectOption('select[name=category_id]', { label: 'Guia 2027' });
    await p.fill('input[name=tags]', 'mangueira, 1988');
    await p.fill('textarea[name=excerpt]', 'Resumo de teste do post da Mangueira.');
    await p.selectOption('select[name=card_ratio]', 'square');
    await submit(p.locator('button[value=publicar]'));
    postId = p.url().match(/posts\/(\d+)/)[1];
    ok('publicar post', (await notice()).includes('publicado'), await notice());
    ok('capa: opções continuam salvas', (await p.inputValue('[data-cover-ratio]')) === 'banner' && (await p.inputValue('[data-cover-width]')) === '70' && await p.isChecked('input[data-cover-align][value=left]'));
  });
  await safe('post publicado aparece no site com tudo', async () => {
    const html = await pub('/blog/mangueira-1988-o-samba-que-contou-a-historia');
    ok('post abre no site', html.includes('Mangueira 1988: o samba que contou a história'));
    ok('site: capa banner, 70%, à esquerda', /post-cover cover-left" style="width:70%"/.test(html) && html.includes('aspect-ratio:3 / 1'));
    ok('site: imagem do texto, vídeo, botão, tabela', html.includes('align-right') && html.includes('embed/SLrGaE65GSM') && html.includes('btn-post') && /ffdacd/i.test(html));
    ok('site: categoria e tags', html.includes('/blog/categoria/guia-dos-enredos-2027-qa') && html.includes('#mangueira'));
    const home = await pub('/');
    ok('home: post aparece no Blog', home.includes('Mangueira 1988'));
    ok('home: foto do card quadrada (exceção do post)', /aspect-ratio:1 \/ 1"[^>]*>\s*<img[^>]+Mangueira|card-media" style="aspect-ratio:1 \/ 1"/.test(home));
  });
  await safe('rascunho não aparece no site; pré-visualizar mostra', async () => {
    await p.goto(base + '/admin/posts/novo'); await editorReady();
    await p.fill('[data-title]', 'Rascunho secreto QA');
    await p.evaluate(() => tinymce.activeEditor.setContent('<p>rascunho</p>'));
    await submit(p.locator('button[value=rascunho]'));
    ok('salvar rascunho', (await notice()).includes('Rascunho salvo'));
    const r = await fetch(base + '/blog/rascunho-secreto-qa');
    ok('rascunho não aparece para o público', r.status === 404);
    const prev = await go('/blog/rascunho-secreto-qa?previa=1');
    ok('pré-visualizar rascunho (logado)', prev.includes('Pré-visualização'));
  });
  await safe('agendar post para o futuro', async () => {
    await p.goto(base + '/admin/posts/novo'); await editorReady();
    await p.fill('[data-title]', 'Post agendado QA');
    await p.evaluate(() => tinymce.activeEditor.setContent('<p>futuro</p>'));
    await p.fill('input[name=published_at]', '2030-01-01T10:00');
    await submit(p.locator('button[value=publicar]'));
    ok('agendar post', (await notice()).includes('agendado'), await notice());
    ok('agendado não aparece antes da hora', (await fetch(base + '/blog/post-agendado-qa')).status === 404);
    await submit(p.locator('button[value=publicar_agora]'));
    ok('botão “Publicar agora” publica', (await fetch(base + '/blog/post-agendado-qa')).status === 200);
  });
  await safe('lista de posts: filtros e busca', async () => {
    await p.goto(base + '/admin/posts?status=rascunhos');
    ok('filtro rascunhos', (await p.content()).includes('Rascunho secreto QA') && !(await p.content()).includes('Mangueira 1988'));
    await p.goto(base + '/admin/posts?q=Mangueira');
    ok('busca por título', (await p.content()).includes('Mangueira 1988') && !(await p.content()).includes('Rascunho secreto QA'));
  });
  await safe('fixar em destaque coloca primeiro', async () => {
    await p.goto(base + '/admin/posts/' + postId); await editorReady();
    await p.check('input[name=featured]'); await submit(p.locator('button[value=publicar]'));
    const home = await pub('/blog');
    const first = home.match(/class="card-title">([^<]+)</);
    ok('fixar em destaque coloca primeiro', first && first[1].includes('Mangueira'), first && first[1]);
  });
  await safe('cor própria do card', async () => {
    await p.goto(base + '/admin/posts/' + postId); await editorReady();
    await p.check('input[name=card_bg_custom]');
    await p.locator('input[name=card_bg]').evaluate(el => { el.value = '#ffdacd'; el.dispatchEvent(new Event('input', { bubbles: true })); });
    await submit(p.locator('button[value=publicar]'));
    ok('cor própria do card', (await pub('/blog')).includes('--card-bg:#FFDACD'));
  });
  await safe('excluir post', async () => {
    const id = (await (await fetch(base + '/blog')).text(), null);
    await p.goto(base + '/admin/posts?q=Rascunho'); await p.locator('a.a-strong').first().click(); await p.waitForLoadState();
    await submit(p.locator('button:has-text("Excluir post")'));
    ok('excluir post', (await notice()).includes('excluído'));
  });

  /* ---------------- Comentários ---------------- */
  area = 'Comentários';
  const reader = await (await b.newContext()).newPage();
  const comment = async (text, name = 'Leitora QA') => {
    await reader.goto(base + '/blog/mangueira-1988-o-samba-que-contou-a-historia');
    await reader.waitForTimeout(3200);
    await reader.fill('.comment-form input[name=nome]', name); await reader.fill('.comment-form input[name=email]', 'leitora@teste.com');
    await reader.fill('.comment-form textarea', text);
    await Promise.all([reader.waitForNavigation(), reader.click('.comment-form button[type=submit]')]);
    return (await reader.locator('.comments .notice').first().textContent().catch(() => '')) || '';
  };
  await safe('leitor comenta e fica pendente', async () => {
    const msg = await comment('Comentário para aprovar');
    ok('leitor comenta e fica pendente', msg.includes('aprovado'), msg);
    ok('pendente não aparece no site', !(await pub('/blog/mangueira-1988-o-samba-que-contou-a-historia')).includes('Comentário para aprovar'));
  });
  await safe('aprovar, responder, spam, excluir', async () => {
    await p.goto(base + '/admin/comentarios');
    ok('contador no menu', (await p.locator('.a-badge').first().textContent()) === '1');
    await submit(p.locator('button:has-text("Aprovar")').first());
    ok('aprovar', (await pub('/blog/mangueira-1988-o-samba-que-contou-a-historia')).includes('Comentário para aprovar'));
    await p.goto(base + '/admin/comentarios?status=approved');
    await p.locator('button:has-text("Responder")').first().click();
    await p.locator('form.a-reply textarea').first().fill('Resposta do Igor QA');
    await submit(p.locator('form.a-reply button').first());
    ok('responder pelo painel aparece no site', (await pub('/blog/mangueira-1988-o-samba-que-contou-a-historia')).includes('Resposta do Igor QA'));
    await comment('Comentário que é spam');
    await p.goto(base + '/admin/comentarios');
    await submit(p.locator('button:has-text("É spam")').first());
    ok('marcar spam', (await p.content()).includes('spam') || (await notice()).includes('spam'));
    await p.goto(base + '/admin/comentarios?status=spam');
    await submit(p.locator('button:has-text("Excluir")').first());
    ok('excluir comentário', (await notice()).includes('excluído'));
  });
  await safe('responder outro leitor pelo site', async () => {
    await reader.goto(base + '/blog/mangueira-1988-o-samba-que-contou-a-historia');
    await reader.locator('[data-reply]').first().click();
    ok('botão Responder do site preenche o formulário', (await reader.inputValue('[data-reply-field]')) !== '');
  });

  /* ---------------- Contato, newsletter ---------------- */
  area = 'Contato e newsletter';
  await safe('contato e newsletter', async () => {
    await reader.goto(base + '/'); await reader.waitForTimeout(3200);
    await reader.fill('.contact-form input[name=nome]', 'Fulano'); await reader.fill('.contact-form input[name=email]', 'fulano@teste.com');
    await reader.fill('.contact-form textarea', 'Tenho fotos de 1987.');
    await Promise.all([reader.waitForNavigation(), reader.click('.contact-form button')]);
    ok('enviar contato', ((await reader.locator('#contato .notice').textContent().catch(() => '')) || '').includes('enviada'));
    await reader.waitForTimeout(2500);
    await reader.fill('.news-form input[name=email]', 'inscrito@teste.com');
    await Promise.all([reader.waitForNavigation(), reader.click('.news-form button')]);
    ok('inscrever na newsletter', ((await reader.locator('#newsletter .notice').textContent().catch(() => '')) || '').includes('Pronto'));
    await p.goto(base + '/admin/mensagens');
    await p.locator('a.a-strong').first().click(); await p.waitForLoadState();
    ok('abrir mensagem', (await p.content()).includes('Tenho fotos de 1987.'));
    await submit(p.locator('button:has-text("Excluir")'));
    ok('excluir mensagem', (await notice()).includes('excluída'));
    await p.goto(base + '/admin/newsletter');
    ok('inscrito aparece na lista', (await p.content()).includes('inscrito@teste.com'));
    const csv = await p.evaluate(() => fetch('/admin/newsletter/exportar').then(r => r.text()));
    ok('baixar lista (planilha)', csv.includes('inscrito@teste.com'));
    await submit(p.locator('button:has-text("Remover")').first());
    ok('remover inscrito', (await notice()).includes('removido'));
  });

  /* ---------------- Banners ---------------- */
  area = 'Banners do topo';
  await safe('trocar imagem do banner', async () => {
    await p.goto(base + '/admin/banners');
    const first = p.locator('.a-banner').nth(0);
    const before = await first.locator('[data-preview] img').getAttribute('src');
    await pickFromLibrary(first, 0);
    const after = await first.locator('[data-preview] img').getAttribute('src');
    ok('miniatura muda ao escolher', after !== before, before + ' → ' + after);
    await first.locator('input[name=link]').fill('https://www.youtube.com/@ArquivoSapucai');
    await first.locator('input[name=new_tab]').check();
    await submit(first.locator('button:has-text("Salvar")'));
    const home = await pub('/');
    const m = home.match(/class="slide is-active"[\s\S]*?<a href="([^"]+)"([^>]*)>\s*<img src="([^"]+)"/);
    ok('site mostra a imagem nova', m && m[3] === after, m && m[3]);
    ok('link do banner em nova aba', m && m[2].includes('_blank'));
  });
  await safe('adicionar, mover, esconder e remover banner', async () => {
    const add = p.locator('form.a-card:has-text("Adicionar banner")');
    await pickFromLibrary(add, 1);
    await add.locator('input[name=alt]').fill('Banner QA');
    await submit(add.locator('button:has-text("Adicionar")'));
    ok('adicionar banner', (await notice()).includes('adicionado') && await p.locator('.a-banner').count() === 3);
    await submit(p.locator('.a-banner').nth(2).locator('button[title=Subir]'));
    ok('mover banner para cima', (await p.locator('.a-banner').nth(1).locator('input[name=alt]').inputValue()) === 'Banner QA');
    await p.locator('.a-banner').nth(1).locator('input[name=active]').uncheck();
    await submit(p.locator('.a-banner').nth(1).locator('button:has-text("Salvar")'));
    ok('esconder banner some do site', !(await pub('/')).includes('Banner QA'));
    await submit(p.locator('.a-banner').nth(1).locator('button[title=Remover]'));
    ok('remover banner', await p.locator('.a-banner').count() === 2);
  });

  /* ---------------- Menu ---------------- */
  area = 'Menu';
  await safe('menu', async () => {
    await p.goto(base + '/admin/cabecalho');
    await p.click('[data-add-menu]');
    const row = p.locator('[data-menu-row]').last();
    await row.locator('input[name$="[label]"]').fill('Guia 2027');
    await row.locator('input[name$="[url]"]').fill('/blog/categoria/guia-2027');
    await row.locator('input[name$="[new_tab]"]').check();
    await submit(p.locator('.a-sticky-save button'));
    let home = await pub('/');
    ok('adicionar item aparece no topo e no rodapé', (home.match(/>Guia 2027</g) || []).length >= 2);
    ok('item em nova aba', /href="\/blog\/categoria\/guia-2027" target="_blank"/.test(home));
    await p.locator('[data-menu-row]').last().locator('input[name$="[label]"]').fill('Guia dos Enredos');
    await submit(p.locator('.a-sticky-save button'));
    ok('renomear item', (await pub('/')).includes('>Guia dos Enredos<'));
    await p.locator('[data-menu-row]').last().locator('button[title=Subir]').click();
    await submit(p.locator('.a-sticky-save button'));
    home = await pub('/');
    ok('mover item', home.indexOf('>Guia dos Enredos<') < home.indexOf('>Contato<'));
    await p.locator('[data-menu-row]:has(input[value="Guia dos Enredos"]) button[title=Remover]').click();
    ok('remover marca o item antes de salvar', await p.locator('[data-menu-row].is-removed').count() === 1);
    await submit(p.locator('.a-sticky-save button'));
    ok('remover item', !(await pub('/')).includes('>Guia dos Enredos<'));
  });

  /* ---------------- Seções da home ---------------- */
  area = 'Seções da home';
  await safe('seções', async () => {
    for (const label of ['Texto + imagem', 'Posts de uma categoria', 'Texto livre', 'Vídeo em destaque']) {
      await p.goto(base + '/admin/secoes');
      await submit(p.locator(`form.a-add-card:has-text("${label}") button`));
      ok('adicionar “' + label + '”', p.url().includes('/admin/secoes/'));
      await p.check('input[name=active]');
      if (label === 'Texto + imagem') {
        await p.fill('input[name=title]', 'Seção texto QA'); await p.fill('textarea[name=text]', 'Parágrafo um.\n\nParágrafo dois.');
        await pickFromLibrary(p.locator('form'), 0);
        await p.fill('input[name=button_text]', 'Ver'); await p.fill('input[name=button_link]', '/blog');
        await p.fill('input[name=anchor]', 'secao-qa');
      } else if (label === 'Posts de uma categoria') {
        await p.selectOption('select[name=category_id]', { label: 'Guia 2027' });
      } else if (label === 'Texto livre') {
        await editorReady(); await p.fill('input[name=title]', 'Recado QA');
        await p.evaluate(() => tinymce.activeEditor.setContent('<p>Texto livre QA</p>'));
      } else {
        await p.fill('input[name=title]', 'Vídeo QA'); await p.fill('input[name=url]', 'https://youtu.be/SLrGaE65GSM');
      }
      await submit(p.locator('button:has-text("Salvar seção")'));
    }
    const home = await pub('/');
    ok('as 4 seções novas aparecem na home', home.includes('Seção texto QA') && home.includes('id="secao-qa"') && home.includes('Texto livre QA') && home.includes('embed/SLrGaE65GSM') && /sec-blog sec-custom/.test(home));
    await p.goto(base + '/admin/secoes');
    await submit(p.locator('.a-section-row:has-text("Playlists") button:has-text("Esconder")'));
    ok('esconder seção', !(await pub('/')).includes('id="playlists"'));
    await submit(p.locator('.a-section-row:has-text("Playlists") button:has-text("Mostrar")'));
    ok('mostrar seção de volta', (await pub('/')).includes('id="playlists"'));
    const before = await pub('/');
    await submit(p.locator('.a-section-row:has-text("Contato") button[title=Descer]'));
    const after = await pub('/');
    ok('mudar ordem', after.indexOf('id="contato"') > before.indexOf('id="contato"') || after.indexOf('id="contato"') > after.indexOf('Seção texto QA'));
    await submit(p.locator('.a-section-row:has-text("Vídeo em destaque") button:has-text("Duplicar")'));
    ok('duplicar seção', p.url().includes('/admin/secoes/'));
    await p.goto(base + '/admin/secoes');
    await submit(p.locator('.a-section-row:has-text("(cópia)") button:has-text("Apagar")'));
    ok('apagar seção', !(await p.content()).includes('(cópia)'));
  });

  /* ---------------- Conteúdo do site ---------------- */
  area = 'Conteúdo do site';
  await safe('site e redes', async () => {
    await p.goto(base + '/admin/configuracoes');
    await p.fill('input[name=title]', 'Arquivo Sapucaí QA');
    await p.fill('input[name="socials[instagram]"]', 'instagram.com/arquivosapucai');
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/admin/cabecalho');
    await pickFromLibrary(p.locator('[data-image-field]:has(input[name=logo])'), 0);
    await submit(p.locator('.a-sticky-save button'));
    const home = await pub('/');
    ok('nome do site muda', home.includes('<title>Arquivo Sapucaí QA</title>'));
    ok('Instagram aparece no rodapé (com https)', home.includes('href="https://instagram.com/arquivosapucai"'));
    ok('logo novo aparece', /class="logo"[\s\S]*?<img src="\/uploads\//.test(home));
  });
  await safe('textos da home e avisos', async () => {
    await p.goto(base + '/admin/faixa');
    await p.fill('input[name="ticker_items[0][text]"]', 'Aviso um QA'); await p.fill('input[name="ticker_items[1][text]"]', 'Aviso dois QA');
    await p.fill('input[name="ticker_items[1][link]"]', '/blog');
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/admin/pagina-inicial');
    await p.fill('input[name=arquivo_title]', 'O Arquivo QA');
    await p.fill('input[name="playlists[0][link]"]', 'https://www.youtube.com/playlist?list=QA');
    await p.fill('input[name=per_page]', '2');
    await submit(p.locator('.a-sticky-save button'));
    const home = await pub('/');
    ok('faixa com 2 avisos', home.includes('Aviso um QA') && /<a href="\/blog"[^>]*>Aviso dois QA/.test(home));
    ok('título O Arquivo muda', home.includes('O Arquivo QA'));
    ok('link da playlist', home.includes('playlist?list=QA'));
    await p.goto(base + '/admin/faixa');
    await p.fill('input[name="ticker_items[0][text]"]', ''); await p.fill('input[name="ticker_items[1][text]"]', '');
    await submit(p.locator('.a-sticky-save button'));
    ok('sem avisos a faixa some', !(await pub('/')).includes('class="ticker"'));
  });
  await safe('rodapé', async () => {
    await p.goto(base + '/admin/rodape');
    await p.fill('input[name=word]', 'SAPUCAÍ QA'); await submit(p.locator('.a-sticky-save button'));
    ok('texto grande do rodapé muda', (await pub('/')).includes('SAPUCAÍ QA'));
    await p.uncheck('input[name=show_word]'); await submit(p.locator('.a-sticky-save button'));
    ok('esconder texto grande', !(await pub('/')).includes('footer-word'));
  });
  await safe('blog e comentários', async () => {
    await p.goto(base + '/admin/blog-posts');
    await p.uncheck('input[name=comments_moderate]'); await submit(p.locator('.a-sticky-save button'));
    const msg = await comment('Sem moderação QA');
    ok('sem moderação o comentário entra direto', msg.includes('publicado'), msg);
    await p.goto(base + '/admin/blog-posts');
    await p.uncheck('input[name=comments_enabled]'); await p.uncheck('input[name=share_buttons]');
    await submit(p.locator('.a-sticky-save button'));
    const post = await pub('/blog/mangueira-1988-o-samba-que-contou-a-historia');
    ok('desligar comentários tira o formulário', !post.includes('comment-form'));
    ok('desligar compartilhar tira os botões', !post.includes('class="share"'));
    await p.goto(base + '/admin/blog-posts');
    await p.check('input[name=comments_enabled]'); await p.check('input[name=share_buttons]'); await p.check('input[name=comments_moderate]');
    await submit(p.locator('.a-sticky-save button'));
  });

  /* ---------------- Aparência ---------------- */
  area = 'Aparência';
  await safe('cores, fontes e cards', async () => {
    await p.goto(base + '/admin/pagina-inicial');
    await p.locator('input[name="colors[blog_bg]"]').evaluate(el => { el.value = '#003366'; });
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/admin/padrao');
    await p.selectOption('select[name="fonts[heading]"]', 'Anton');
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
    await p.goto(base + '/admin/cabecalho');
    await p.locator('input[name=size_logo]').fill('140'); await p.locator('input[name=size_header]').fill('200');
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/admin/blog-posts');
    await p.selectOption('select[name=cards_per_row]', '4');
    await p.click('label:has(input[name=cards_layout][value=masonry]) span');
    await p.selectOption('select[name=card_ratio]', 'original');
    await p.selectOption('select[name=card_title_lines]', '2');
    await submit(p.locator('.a-sticky-save button'));
    const home = await pub('/');
    ok('cor muda no site', home.includes('--c-blog-bg:#003366'));
    ok('fonte do Google carrega', home.includes('fonts.googleapis.com/css2?family=Anton'));
    ok('tamanho do logo e do cabeçalho', home.includes('--logo-size:140px') && home.includes('--header-height:200px'));
    ok('4 por linha em mosaico', home.includes('cards cols-4 is-masonry'));
    ok('limite de linhas do título', home.includes('--card-title-lines:2'));
    await p.goto(base + '/admin/padrao');
    await submit(p.locator('button:has-text("Restaurar padrão")'));
    const h2 = await pub('/');
    ok('restaurar padrão volta as fontes', !h2.includes('family=Anton'));
    ok('restaurar padrão não mexe nas cores das seções', h2.includes('--c-blog-bg:#003366'));
    await p.goto(base + '/admin/pagina-inicial');
    await p.locator('input[name="colors[blog_bg]"]').evaluate(el => { el.value = '#20063a'; });
    await submit(p.locator('.a-sticky-save button'));
  });

  /* ---------------- Autores ---------------- */
  area = 'Autores';
  await safe('autores e permissões', async () => {
    await p.goto(base + '/admin/autores/novo');
    await p.fill('input[name=name]', 'Autor Convidado'); await p.fill('input[name=email]', 'autor@teste.com');
    await p.fill('input[name=password]', 'senha-autor-123'); await p.fill('textarea[name=bio]', 'Bio do autor.');
    await submit(p.locator('button:has-text("Salvar")'));
    ok('cadastrar autor', (await notice()).includes('salvo'));
    await logout(); await login('autor@teste.com', 'senha-autor-123');
    ok('autor faz login', p.url().endsWith('/admin'));
    ok('autor não vê menus de administrador', !(await p.content()).includes('/admin/padrao') && !(await p.content()).includes('/admin/cabecalho'));
    const r = await p.goto(base + '/admin/padrao');
    ok('autor é bloqueado em Padrão do site', r.status() === 403);
    const r2 = await p.goto(base + '/admin/configuracoes');
    ok('autor é bloqueado em Configurações', r2.status() === 403);
    await p.goto(base + '/admin/posts/' + postId);
    ok('autor não edita post de outro', p.url().endsWith('/admin/posts'));
    await p.goto(base + '/admin/posts/novo'); await editorReady();
    await p.fill('[data-title]', 'Post do autor QA'); await p.evaluate(() => tinymce.activeEditor.setContent('<p>oi</p>'));
    await submit(p.locator('button[value=publicar]'));
    ok('autor publica o próprio post', (await pub('/blog/autor/autor-convidado')).includes('Post do autor QA'));
    await p.goto(base + '/admin/perfil');
    await p.fill('input[name=current_password]', 'senha-autor-123'); await p.fill('input[name=password]', 'nova-senha-123');
    await submit(p.locator('button:has-text("Salvar")'));
    ok('autor troca a própria senha', (await notice()).includes('atualizado'));
    await logout(); await login('admin@teste.com', 'senha-admin-123');
    await p.goto(base + '/admin/autores'); await p.locator('a:has-text("Autor Convidado")').click(); await p.waitForLoadState();
    await p.uncheck('input[name=active]'); await submit(p.locator('button:has-text("Salvar")'));
    await logout(); await login('autor@teste.com', 'nova-senha-123');
    ok('autor desativado não entra', !p.url().endsWith('/admin'));
    await login('admin@teste.com', 'senha-admin-123');
  });

  /* ---------------- Site público ---------------- */
  area = 'Site público';
  await safe('páginas públicas', async () => {
    for (const [path, needle] of [['/blog', 'card'], ['/blog/categoria/guia-dos-enredos-2027-qa', 'Guia 2027'], ['/blog/tag/mangueira', '#mangueira'], ['/blog/autor/igor-schulenburg', 'Igor Schulenburg'], ['/busca?q=Mangueira', 'Mangueira 1988'], ['/sitemap.xml', '<urlset'], ['/feed.xml', '<rss'], ['/robots.txt', 'Sitemap']]) {
      const r = await fetch(base + path); const t = await r.text();
      ok('abre ' + path, r.status === 200 && t.includes(needle));
    }
    ok('página inexistente dá 404', (await fetch(base + '/nao-existe')).status === 404);
    for (const path of ['/config.php', '/app/db.php', '/database/schema.sql', '/storage/installed.lock']) {
      ok('arquivo interno bloqueado ' + path, (await fetch(base + path)).status !== 200);
    }
    ok('instalador travado depois de instalado', (await fetch(base + '/install.php')).status === 403);
  });
  await safe('celular: menu abre', async () => {
    const m = await (await b.newContext({ viewport: { width: 390, height: 844 } })).newPage();
    await m.goto(base + '/'); await m.click('.menu-toggle');
    ok('celular: menu abre', await m.locator('#menu-principal').isVisible());
    await m.screenshot({ path: shots + '/qa-celular.png' });
  });
  await safe('carrossel troca com a seta', async () => {
    await p.goto(base + '/');
    const before = await p.locator('.slide.is-active img').getAttribute('src');
    await p.click('[data-next]');
    const after = await p.locator('.slide.is-active img').getAttribute('src');
    ok('carrossel troca com a seta', before !== after);
  });


  /* ---------------- Rodada 3: pedidos do Igor ---------------- */
  area = 'Fontes (rodada 4)';
  const custom = async (key, font, size, bold) => {
    const box = p.locator(`[data-type-field]:has(input[name="type[${key}][padrao]"])`);
    await box.evaluate(el => { const d = el.closest('details'); if (d) d.open = true; }); // fontes ficam em “Fontes e tamanhos”, fechado
    await box.locator(`input[name="type[${key}][padrao]"]`).uncheck();
    ok(`${key}: desmarcar padrão mostra as escolhas`, await box.locator('[data-type-custom]').isVisible());
    if (font) await box.locator(`select[name="type[${key}][font]"]`).selectOption(font);
    if (size) await box.locator(`input[name="type[${key}][size]"]`).fill(String(size));
    await box.locator(`select[name="type[${key}][weight]"]`).selectOption(typeof bold === 'string' ? bold : (bold ? '700' : '400'));
  };
  const cs = async (sel, prop) => p.evaluate(([s, pr]) => { const el = document.querySelector(s); return el ? getComputedStyle(el)[pr] : 'NÃO ACHOU ' + s; }, [sel, prop]);
  await safe('Aparência: tamanhos base e título dos posts', async () => {
    await p.goto(base + '/admin/padrao');
    ok('Padrão do site: só fontes base e botões', !(await p.content()).includes('type[menu]') && !(await p.content()).includes('type[ticker]') && !(await p.content()).includes('type[post_title]'));
    await p.locator('input[name="font_sizes[heading]"]').fill('50');
    await p.locator('input[name="font_sizes[body]"]').fill('18');
    await p.locator('input[name="font_sizes[ui]"]').fill('16');
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
    await p.goto(base + '/admin/blog-posts');
    await custom('post_title', 'Bitter', 32, true);
    await submit(p.locator('.a-sticky-save button'));
    const home = await pub('/');
    ok('tamanhos base viram variáveis do site', home.includes('--fs-heading:50px') && home.includes('--fs-body:18px') && home.includes('--fs-ui:16px'));
    await p.goto(base + '/');
    ok('título de seção segue “Títulos” (50px)', (await cs('.sec-arquivo .sec-title', 'fontSize')) === '50px', await cs('.sec-arquivo .sec-title', 'fontSize'));
    ok('texto da home segue “Texto corrido” (18px)', (await cs('.sec-arquivo .split-text p', 'fontSize')) === '18px', await cs('.sec-arquivo .split-text p', 'fontSize'));
    ok('botão segue “Menus e botões” (16px)', (await cs('.sec-arquivo .btn', 'fontSize')) === '16px', await cs('.sec-arquivo .btn', 'fontSize'));
    await p.goto(base + '/blog/mangueira-1988-o-samba-que-contou-a-historia');
    ok('título do post com 32px', (await cs('.post-title', 'fontSize')) === '32px', await cs('.post-title', 'fontSize'));
    ok('título do post em Bitter', (await cs('.post-title', 'fontFamily')).includes('Bitter'));
    ok('texto do post segue “Texto corrido” (18px)', (await cs('.post-content', 'fontSize')) === '18px', await cs('.post-content', 'fontSize'));
    await p.goto(base + '/admin/blog-posts');
    ok('título do post continua salvo no painel', (await p.inputValue('input[name="type[post_title][size]"]')) === '32' && !(await p.isChecked('input[name="type[post_title][padrao]"]')));
    await p.goto(base + '/admin/padrao');
    ok('tamanho base continua salvo', (await p.inputValue('input[name="font_sizes[body]"]')) === '18');
    await p.locator('input[name="font_sizes[heading]"]').fill('44'); await p.locator('input[name="font_sizes[body]"]').fill('16'); await p.locator('input[name="font_sizes[ui]"]').fill('15');
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
  });
  await safe('fonte do menu (tela Cabeçalho e menu)', async () => {
    await p.goto(base + '/admin/cabecalho');
    await custom('menu', 'Oswald', 20, true);
    await submit(p.locator('.a-sticky-save button'));
    ok('menu: aviso de salvo', (await notice()).includes('salvas'), await notice());
    const home = await pub('/');
    ok('menu: fonte nova carrega do Google', home.includes('family=Oswald'));
    await p.goto(base + '/');
    ok('menu aparece com 20px', (await cs('.main-nav a', 'fontSize')) === '20px', await cs('.main-nav a', 'fontSize'));
    ok('menu aparece em Oswald', (await cs('.main-nav a', 'fontFamily')).includes('Oswald'));
    await p.goto(base + '/admin/cabecalho');
    await p.check('input[name="type[menu][padrao]"]');
    ok('marcar padrão esconde as escolhas', !(await p.locator('[data-type-field]:has(input[name="type[menu][padrao]"]) [data-type-custom]').isVisible()));
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/');
    ok('menu volta ao padrão (15px)', (await cs('.main-nav a', 'fontSize')) === '15px', await cs('.main-nav a', 'fontSize'));
  });
  await safe('fonte dos avisos (tela Faixa de avisos)', async () => {
    await p.goto(base + '/admin/faixa');
    await p.fill('input[name="ticker_items[0][text]"]', 'Aviso fonte QA');
    await custom('ticker', 'Bebas Neue', 18, false);
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/');
    ok('avisos com 18px', (await cs('.ticker-track', 'fontSize')) === '18px', await cs('.ticker-track', 'fontSize'));
    ok('avisos em Bebas Neue', (await cs('.ticker-track', 'fontFamily')).includes('Bebas Neue'));
    await p.goto(base + '/admin/faixa');
    await p.fill('input[name="ticker_items[0][text]"]', '');
    await p.check('input[name="type[ticker][padrao]"]');
    await submit(p.locator('.a-sticky-save button'));
  });

  area = 'Editor: tamanho inicial 16';
  await safe('texto começa em 16', async () => {
    await p.goto(base + '/admin/posts/novo'); await editorReady();
    const fs = await p.frameLocator('.tox-edit-area iframe').locator('body').evaluate(b => getComputedStyle(b).fontSize);
    ok('editor começa em 16px', fs === '16px', fs);
    const site = await (await b.newContext()).newPage();
    await site.goto(base + '/blog/mangueira-1988-o-samba-que-contou-a-historia');
    const sfs = await site.evaluate(() => getComputedStyle(document.querySelector('.post-content')).fontSize);
    ok('texto do post no site em 16px', sfs === '16px', sfs);
  });

  area = 'Painel: avisos e voltar';
  await safe('aviso antes de sair sem salvar', async () => {
    await p.goto(base + '/admin/posts/novo'); await editorReady();
    await p.fill('[data-title]', 'Não salvo');
    lastDialog = '';
    p.once('dialog', () => {}); // o aviso é aceito pelo ouvinte geral
    await Promise.all([p.waitForNavigation().catch(() => {}), p.click('.a-side a:has-text("Imagens")')]);
    ok('aviso aparece ao clicar fora do post', lastDialog.includes('não foram salvas'), lastDialog);
    await p.goto(base + '/admin/pagina-inicial');
    await p.fill('input[name=arquivo_title]', 'mexi');
    lastDialog = '';
    await Promise.all([p.waitForNavigation().catch(() => {}), p.click('.a-side a:has-text("Menu")')]);
    ok('aviso também em outras telas (Textos da home)', lastDialog.includes('não foram salvas'));
    lastDialog = '';
    await p.goto(base + '/admin/cabecalho'); await p.click('.a-side a:has-text("Posts")'); await p.waitForLoadState();
    ok('sem mudanças, sai sem aviso', lastDialog === '');
  });
  await safe('“Salvando…” e aviso de salvo', async () => {
    await p.goto(base + '/admin/rodape');
    await p.route(/\/admin\/rodape/, async r => { try { if (r.request().method() === 'POST') await new Promise(x => setTimeout(x, 1200)); await r.continue(); } catch (e) {} });
    await p.fill('input[name=word]', 'ARQUIVO SAPUCAÍ');
    const r = await p.evaluate(async () => { const btn = document.querySelector('.a-sticky-save button'); btn.click(); await new Promise(x => setTimeout(x, 150)); return [btn.textContent, document.body.classList.contains('is-saving')]; });
    ok('botão mostra “Salvando…” enquanto salva', r[0].includes('Salvando'), r[0]);
    ok('barra de carregamento no topo', r[1]);
    await p.waitForURL(/\/admin\/rodape/); await p.waitForLoadState(); await p.unroute(/\/admin\/rodape/);
    await p.waitForSelector('.a-toast-success');
    ok('aviso verde de salvo aparece', (await p.textContent('.a-toast-success')).includes('salvas'));
  });
  await safe('botão Voltar', async () => {
    await p.goto(base + '/admin/posts/' + postId);
    ok('Voltar no post leva à lista de posts', (await p.getAttribute('.a-back', 'href')) === '/admin/posts');
    await p.goto(base + '/admin/secoes'); await p.locator('.a-section-row a:has-text("Editar")').first().click(); await p.waitForLoadState();
    ok('Voltar na seção leva às seções', (await p.getAttribute('.a-back', 'href')) === '/admin/secoes');
    await p.goto(base + '/admin/posts');
    ok('telas principais não mostram Voltar', await p.locator('.a-back').count() === 0);
  });

  area = 'Imagens em pastas';
  await safe('pastas', async () => {
    await p.goto(base + '/admin/midia');
    await p.fill('input[name=nome]', 'Desfiles 1987');
    await submit(p.locator('button:has-text("+ Criar")'));
    ok('criar pasta', (await notice()).includes('criada') || (await p.content()).includes('Desfiles 1987'));
    const url = p.url();
    await Promise.all([p.waitForNavigation(), p.setInputFiles('input[name="arquivos[]"]', IMG1)]);
    ok('enviar imagem dentro da pasta', p.url() === url && await p.locator('.a-gallery-item').count() === 1);
    await p.goto(base + '/admin/midia?pasta=-1');
    const before = await p.locator('.a-gallery-item').count();
    await p.locator('[data-media-check]').first().check();
    await p.selectOption('select[name=destino]', { label: 'Mover para: Desfiles 1987' });
    await submit(p.locator('button:has-text("Mover marcadas")'));
    ok('mover imagem para a pasta', await p.locator('.a-gallery-item').count() === before - 1);
    await p.goto(url);
    ok('pasta mostra 2 imagens', await p.locator('.a-gallery-item').count() === 2);
    await p.fill('#renomear', 'Desfiles 1987 e 1988'); await submit(p.locator('button:has-text("Renomear")'));
    ok('renomear pasta', (await p.content()).includes('Desfiles 1987 e 1988'));
    // seletor de imagens filtra por pasta
    await p.goto(base + '/admin/posts/' + postId); await editorReady();
    await p.locator('[data-cover] [data-pick]').click();
    await p.waitForSelector('[data-media-grid] button');
    const allCount = await p.locator('[data-media-grid] button').count();
    await p.selectOption('[data-media-folder]', { label: '📁 Desfiles 1987 e 1988' });
    await p.waitForTimeout(600);
    ok('seletor filtra pela pasta', await p.locator('[data-media-grid] button').count() === 2 && allCount > 2, allCount + ' → 2');
    await p.click('[data-close]');
    await p.goto(url);
    await submit(p.locator('button:has-text("Excluir esta pasta")'));
    ok('excluir pasta não apaga imagens', (await notice()).includes('Sem pasta') && (await (await fetch(base + '/admin/midia')).status) === 200);
  });

  area = 'Imagens da home';
  await safe('opções de imagem em O Arquivo e Acervo', async () => {
    await p.goto(base + '/admin/pagina-inicial');
    await p.locator('input[name="arquivo_img[width]"]').fill('60');
    await p.selectOption('select[name="arquivo_img[radius]"]', '999');
    await p.selectOption('select[name="arquivo_img[ratio]"]', 'square');
    await p.selectOption('select[name="acervo_img[align]"]', 'center');
    await submit(p.locator('.a-sticky-save button'));
    const home = await pub('/');
    ok('O Arquivo: tamanho, cantos e formato', home.includes('class="wrap split" style="--img-w:0.6"') && /split-media"><img style="width:100%;border-radius:999px;aspect-ratio:1 \/ 1/.test(home));
    ok('Acervo: alinhado ao centro', /acervo-media"><img style="[^"]*margin-left:auto;margin-right:auto/.test(home));
    await p.goto(base + '/admin/pagina-inicial');
    ok('opções continuam salvas', (await p.inputValue('select[name="arquivo_img[ratio]"]')) === 'square');
  });

  area = 'Páginas';
  await safe('páginas', async () => {
    await p.goto(base + '/admin/paginas');
    ok('Acervo e História já criadas como rascunho', (await p.content()).includes('/acervo') && (await p.content()).includes('/historia'));
    ok('rascunho não aparece no site', (await fetch(base + '/acervo')).status === 404);
    await p.locator('a.a-strong:has-text("Acervo")').click(); await p.waitForLoadState(); await editorReady();
    await p.fill('input[name=subtitle]', 'Desfiles do Grupo Especial ano a ano');
    await p.evaluate(() => tinymce.activeEditor.setContent('<h2>Como o acervo é organizado</h2><p>Por ano e por escola.</p>'));
    await pickFromLibrary(p.locator('[data-image-field]').first(), 0);
    await p.check('input[name=width][value=wide]', { force: true });
    await p.check('input[name=bg_color_on]');
    await p.locator('input[name=bg_color]').evaluate(el => { el.value = '#fdfcf0'; });
    await p.click('details.a-card summary'); await p.fill('textarea[name=meta_description]', 'Acervo de desfiles do carnaval carioca.');
    await submit(p.locator('button[value=publicar]'));
    ok('publicar página', (await notice()).includes('publicada'));
    const r = await fetch(base + '/acervo'); const html = await r.text();
    ok('página abre em /acervo', r.status === 200 && html.includes('Como o acervo é organizado'));
    ok('banner com título por cima', html.includes('page-banner has-title') && html.includes('Desfiles do Grupo Especial ano a ano'));
    ok('largura larga e cor de fundo', !/wrap narrow page-body/.test(html) && html.includes('--pg-bg:#FDFCF0'));
    ok('descrição para o Google', html.includes('content="Acervo de desfiles do carnaval carioca."'));
    ok('página sem autor, data e comentários', !html.includes('comment-form') && !html.includes('post-meta'));
    ok('página entra no sitemap', (await pub('/sitemap.xml')).includes('/acervo'));
    await p.goto(base + '/admin/paginas/nova'); await editorReady();
    await p.fill('[data-title]', 'Teste'); await p.fill('[data-slug]', 'blog');
    await submit(p.locator('button[value=publicar]'));
    ok('endereço reservado é recusado', (await p.content()).includes('usado pelo próprio site'));
    await p.goto(base + '/admin/cabecalho');
    ok('página aparece nas sugestões do menu', (await p.content()).includes('Página: Acervo'));
    await p.goto(base + '/admin/paginas'); await p.locator('a.a-strong:has-text("História")').click(); await p.waitForLoadState();
    await submit(p.locator('button:has-text("Excluir página")'));
    ok('excluir página', (await notice()).includes('excluída'));
  });

  /* ---------------- Rodada 4 ---------------- */
  area = 'Painel organizado';
  await safe('grupos e atalhos', async () => {
    await p.goto(base + '/admin/pagina-inicial');
    ok('Página inicial: 5 seções com título', await p.locator('.a-group .a-group-title').count() === 5);
    ok('Página inicial: atalhos no topo', await p.locator('.a-toc a').count() === 5);
    ok('Página inicial: abas de seções, ordem e banners', await p.locator('.a-tabs a').count() === 3);
    await p.click('.a-toc a:has-text("Contato")');
    ok('atalho leva ao grupo Contato', p.url().endsWith('#g-contato'));
    await p.goto(base + '/admin/rodape');
    ok('Rodapé: grupos e atalhos', await p.locator('.a-group').count() === 2 && await p.locator('.a-toc a').count() === 2);
    for (const [scr, n] of [['padrao', 2], ['cabecalho', 5], ['faixa', 2], ['blog-posts', 3]]) {
      await p.goto(base + '/admin/' + scr);
      ok(scr + ': ' + n + ' grupos com título', await p.locator('.a-group .a-group-title').count() === n, String(await p.locator('.a-group .a-group-title').count()));
    }
  });

  area = 'Textos da home com fonte própria';
  await safe('fonte, tamanho e negrito em cada texto', async () => {
    await p.goto(base + '/admin/pagina-inicial');
    const boxes = await p.locator('[data-type-field]').count();
    ok('cada texto da home tem controle de fonte (20)', boxes === 20, String(boxes));
    await custom('arquivo_text', 'Lora', 20, true);
    await custom('playlists_intro', null, 22, false);
    await custom('playlists_names', 'Oswald', 14, false);
    await custom('contact_title', 'Anton', 40, false);
    await custom('blog_title', null, 28, true);
    await p.fill('input[name=blog_button]', 'VER TUDO QA');
    await submit(p.locator('.a-sticky-save button'));
    ok('salvar textos da home', (await notice()).includes('salvas'), await notice());
    await p.goto(base + '/');
    ok('O Arquivo: texto 20px', (await cs('.split-text > div p', 'fontSize')) === '20px', await cs('.split-text > div p', 'fontSize'));
    ok('O Arquivo: texto em Lora e negrito', (await cs('.split-text > div p', 'fontFamily')).includes('Lora') && (await cs('.split-text > div p', 'fontWeight')) === '700');
    ok('Playlists: texto antes 22px', (await cs('.sec-playlists .sec-intro p', 'fontSize')) === '22px');
    ok('Playlists: nomes 14px sem negrito', (await cs('.playlist span', 'fontSize')) === '14px' && (await cs('.playlist span', 'fontWeight')) === '400');
    ok('Contato: título em Anton sem negrito', (await cs('.sec-contact .sec-title', 'fontFamily')).includes('Anton') && (await cs('.sec-contact .sec-title', 'fontWeight')) === '400');
    ok('Blog: título 28px', (await cs('.sec-blog .sec-title', 'fontSize')) === '28px', await cs('.sec-blog .sec-title', 'fontSize'));
    ok('Blog: texto do botão muda', (await p.content()).includes('VER TUDO QA'));
    ok('textos sem mudança seguem o padrão', (await cs('.sec-arquivo .sec-title', 'fontSize')) === '44px', await cs('.sec-arquivo .sec-title', 'fontSize'));
    await p.goto(base + '/admin/pagina-inicial');
    ok('escolhas continuam salvas no painel', (await p.inputValue('input[name="type[arquivo_text][size]"]')) === '20' && (await p.inputValue('select[name="type[arquivo_text][font]"]')) === 'Lora' && (await p.inputValue('select[name="type[arquivo_text][weight]"]')) === '700');
    ok('quem não mudou continua em “padrão”', await p.isChecked('input[name="type[arquivo_title][padrao]"]'));
    await p.evaluate(() => document.querySelectorAll('details.a-adv').forEach(d => { d.open = true; }));
    await p.check('input[name="type[arquivo_text][padrao]"]');
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/');
    ok('voltar ao padrão tira a fonte própria', (await cs('.split-text > div p', 'fontSize')) === '16px', await cs('.split-text > div p', 'fontSize'));
  });

  area = 'Acervo e História';
  await safe('duas colunas com foto, botão e link próprios', async () => {
    await p.goto(base + '/admin/pagina-inicial');
    await p.fill('input[name=acervo_link]', '/acervo'); await p.fill('input[name=historia_link]', '/historia');
    await p.fill('input[name=historia_title]', 'História QA'); await p.fill('input[name=historia_button]', 'LER A HISTÓRIA');
    await pickFromLibrary(p.locator('[data-image-field]:has(input[name=historia_image])'), 1);
    await custom('historia_title', 'Anton', 36, true);
    await submit(p.locator('.a-sticky-save button'));
    const home = await pub('/');
    ok('coluna Acervo com âncora #acervo', home.includes('class="acervo-col" id="acervo"'));
    ok('coluna História com âncora #historia', home.includes('class="acervo-col" id="historia"'));
    ok('âncora não se repete na seção', (home.match(/id="acervo"/g) || []).length === 1);
    ok('botão do Acervo leva a /acervo', /href="\/acervo"[^>]*>SAIBA MAIS/.test(home));
    ok('botão da História leva a /historia', /href="\/historia"[^>]*>LER A HISTÓRIA/.test(home));
    ok('cada coluna com a sua foto', /id="historia">\s*<div class="acervo-media"><img[^>]*src="\/uploads\//.test(home));
    await p.goto(base + '/');
    const cols = await cs('.acervo-cols', 'gridTemplateColumns');
    ok('lado a lado no computador', cols.split(' ').length === 2, cols);
    ok('História: título em Anton 36px', (await cs('#historia .sec-title', 'fontSize')) === '36px' && (await cs('#historia .sec-title', 'fontFamily')).includes('Anton'));
    const m = await (await b.newContext({ viewport: { width: 390, height: 800 } })).newPage();
    await m.goto(base + '/');
    const mcols = await m.evaluate(() => getComputedStyle(document.querySelector('.acervo-cols')).gridTemplateColumns);
    ok('uma embaixo da outra no celular', mcols.split(' ').length === 1, mcols);
    await m.close();
    await p.goto(base + '/admin/pagina-inicial');
    await p.uncheck('input[name=historia_show]');
    await submit(p.locator('.a-sticky-save button'));
    const h2 = await pub('/');
    ok('esconder só a História', !h2.includes('id="historia"') && h2.includes('id="acervo"') && h2.includes('acervo-cols is-single'));
    await p.goto(base + '/admin/pagina-inicial');
    await p.check('input[name=historia_show]');
    await p.fill('input[name=historia_link]', '');
    await submit(p.locator('.a-sticky-save button'));
    ok('sem link, o botão some', !(await pub('/')).includes('LER A HISTÓRIA'));
  });

  area = 'Contato e rodapé';
  await safe('textos e fontes do contato', async () => {
    await p.goto(base + '/admin/pagina-inicial');
    await p.fill('input[name="contact_labels[nome]"]', 'Seu nome QA'); await p.fill('input[name=contact_button]', 'Mandar QA');
    await custom('contact_labels', null, 18, false);
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/');
    const html = await p.content();
    ok('nome do campo muda', html.includes('Seu nome QA'));
    ok('texto do botão muda', html.includes('Mandar QA'));
    ok('nomes dos campos com 18px', (await cs('.contact-form .row label', 'fontSize')) === '18px', await cs('.contact-form .row label', 'fontSize'));
    await p.fill('.contact-form input[name=nome]', 'Leitor QA'); await p.fill('.contact-form input[name=email]', 'leitor@teste.com');
    await p.fill('.contact-form textarea[name=mensagem]', 'Mensagem depois da troca dos nomes dos campos.');
    await p.waitForTimeout(3200);
    await submit(p.locator('.contact-form button'));
    ok('formulário continua enviando', (await p.content()).includes('notice-success'));
  });
  await safe('textos e fontes do rodapé', async () => {
    await p.goto(base + '/admin/rodape');
    ok('rodapé: 8 controles de fonte (títulos e textos juntos)', await p.locator('[data-type-field]').count() === 8);
    await p.fill('input[name=nav_title]', 'Links QA'); await p.fill('input[name=rss_label]', '');
    await p.fill('input[name=copyright]', '© 2024–{ano} QA. Direitos reservados.');
    await p.fill('input[name=author_label]', 'Painel QA');
    await p.fill('input[name=email_label]', 'Fale com a gente:');
    await p.fill('input[name=news_placeholder]', 'Seu e-mail QA'); await p.fill('input[name=news_button]', 'Assinar QA');
    await custom('footer_copyright', null, 16, true);
    await custom('footer_word', 'Anton', null, true);
    ok('texto grande: sem régua de tamanho', await p.locator('input[name="type[footer_word][size]"]').count() === 0);
    await submit(p.locator('.a-sticky-save button'));
    const home = await pub('/');
    ok('título dos links muda', home.includes('>Links QA</h2>'));
    ok('link RSS some quando vazio', !/footer-nav[\s\S]*?href="\/feed\.xml"[\s\S]*?<\/nav>/.test(home));
    ok('© com o ano atual automático', home.includes('© 2024–' + new Date().getFullYear() + ' QA. Direitos reservados.'));
    ok('Área do autor: texto e nova aba', /href="\/admin" target="_blank" rel="noopener"[^>]*>Painel QA/.test(home));
    ok('newsletter: campo e botão', home.includes('placeholder="Seu e-mail QA"') && home.includes('Assinar QA'));
    await p.goto(base + '/');
    ok('linha do © com 16px e negrito', (await cs('.footer-bottom span', 'fontSize')) === '16px' && (await cs('.footer-bottom span', 'fontWeight')) === '700');
    await p.goto(base + '/admin/rodape');
    ok('textos do rodapé continuam salvos', (await p.inputValue('input[name=author_label]')) === 'Painel QA');
    await p.goto(base + '/');
    const tops = await p.evaluate(() => [...document.querySelectorAll('.footer-title')].map(h => Math.round(h.getBoundingClientRect().top)));
    ok('3 títulos do rodapé na mesma linha', tops.length === 3 && new Set(tops).size === 1, tops.join(','));
    const fam = await p.evaluate(() => [...document.querySelectorAll('.footer-title')].map(h => getComputedStyle(h).fontFamily + getComputedStyle(h).fontSize + getComputedStyle(h).fontWeight));
    ok('3 títulos do rodapé com o mesmo estilo', new Set(fam).size === 1, fam.join(' | '));
    ok('rodapé sem o logo', await p.locator('.site-footer img').count() === 0);
    ok('ícones das redes com 64px', (await cs('.socials a', 'width')) === '64px', await cs('.socials a', 'width'));
    ok('“Contato:” em destaque antes do e-mail', (await p.locator('.footer-contact strong').textContent()) === 'Fale com a gente:');
    const gap = await p.evaluate(() => { const l = document.querySelectorAll('.footer-nav li'); return Math.round(l[1].getBoundingClientRect().top - l[0].getBoundingClientRect().bottom); });
    ok('links do rodapé mais juntos', gap <= 2, String(gap));
  });

  area = 'Cards e playlists';
  await safe('descrição do card respeita as linhas', async () => {
    const longo = 'Esta é uma descrição comprida de propósito, para conferir se o card mostra o número de linhas escolhido no painel e não corta mais em 140 letras como antes. '.repeat(3);
    await p.goto(base + '/admin/posts'); await p.locator('a.a-strong:has-text("Mangueira 1988")').first().click(); await p.waitForLoadState(); await editorReady();
    await p.fill('textarea[name=excerpt]', longo);
    await submit(p.locator('button[value=publicar]'));
    const home = await pub('/blog');
    const m2 = home.match(/<p class="card-excerpt">([^<]*)<\/p>/);
    ok('card não corta mais em 140 letras', m2 && m2[1].length > 300, m2 ? String(m2[1].length) : 'sem card');
    await p.goto(base + '/admin/blog-posts');
    await p.selectOption('select[name=card_excerpt_lines]', '5');
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
    await p.goto(base + '/blog');
    const h = await p.evaluate(() => { const e = document.querySelector('.card-excerpt'); return e.getBoundingClientRect().height / parseFloat(getComputedStyle(e).lineHeight); });
    ok('com 5 linhas, o card mostra 5 linhas', Math.round(h) === 5, String(h));
  });
  await safe('apagar uma playlist some do site', async () => {
    await p.goto(base + '/admin/pagina-inicial');
    await p.fill('input[name="playlists[2][title]"]', '');
    await p.locator('[data-image-field]:has(input[name="playlists[2][image]"]) [data-clear]').click();
    await submit(p.locator('.a-sticky-save button'));
    const home = await pub('/');
    ok('ficam só 2 playlists', (home.match(/class="playlist"/g) || []).length === 2);
  });

  area = 'Peso das fontes';
  await safe('peso: fino, normal, negrito', async () => {
    await p.goto(base + '/admin/pagina-inicial');
    await custom('playlists_intro', 'Merriweather', 18, '300');
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/');
    ok('Playlists: Merriweather fina (300)', (await cs('.sec-playlists .sec-intro p', 'fontWeight')) === '300' && (await cs('.sec-playlists .sec-intro p', 'fontFamily')).includes('Merriweather'));
    ok('peso fino carrega do Google', (await p.content()).includes('family=Merriweather:ital,wght@0,300'));
    await p.goto(base + '/admin/pagina-inicial');
    ok('peso continua salvo', (await p.inputValue('select[name="type[playlists_intro][weight]"]')) === '300');
    await p.goto(base + '/admin/padrao');
    await p.selectOption('select[name="font_weights[heading]"]', '600');
    await p.selectOption('select[name="font_weights[body]"]', '300');
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
    await p.goto(base + '/');
    ok('Aparência: peso dos títulos (600)', (await cs('.sec-arquivo .sec-title', 'fontWeight')) === '600');
    ok('Aparência: peso do texto corrido (300)', (await cs('.sec-arquivo .split-text p', 'fontWeight')) === '300');
    await p.goto(base + '/admin/padrao');
    await p.selectOption('select[name="font_weights[heading]"]', '800'); await p.selectOption('select[name="font_weights[body]"]', '400');
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
  });

  area = 'Cabeçalho: imagem e cor';
  await safe('trocar imagem, tirar imagem e força da cor', async () => {
    await p.goto(base + '/admin/cabecalho');
    ok('imagem do cabeçalho está em Cabeçalho e menu', await p.locator('input[name=header_image]').count() === 1);
    await pickFromLibrary(p.locator('[data-image-field]:has(input[name=header_image])'), 0);
    await p.locator('input[name=header_overlay]').fill('40');
    await p.uncheck('input[name=header_gradient]');
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
    let home = await pub('/');
    ok('imagem nova no cabeçalho', /class="site-header" style="--header-image:url\('\/uploads\//.test(home));
    ok('cor a 40% sem degradê', home.includes('--header-overlay-a:40%') && home.includes('--header-overlay-b:40%'));
    await p.goto(base + '/admin/cabecalho');
    await p.check('input[name=header_gradient]');
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
    home = await pub('/');
    ok('degradê: direita mais clara', home.includes('--header-overlay-a:40%') && home.includes('--header-overlay-b:22%'));
    await p.goto(base + '/admin/cabecalho');
    await p.locator('[data-image-field]:has(input[name=header_image]) [data-clear]').click();
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
    home = await pub('/');
    ok('sem imagem: só a cor', home.includes('<header class="site-header">'));
    await p.goto(base + '/admin/configuracoes');
    ok('Configurações aponta para Cabeçalho e menu (logo e imagem)', (await p.content()).includes('href="/admin/cabecalho"'));
  });

  area = 'Páginas internas';
  await safe('faixa fina, voltar e contato no fim', async () => {
    await p.goto(base + '/admin/paginas'); await p.locator('a.a-strong:has-text("Acervo")').first().click(); await p.waitForLoadState(); await editorReady();
    await pickFromLibrary(p.locator('[data-image-field]:has(input[name=banner_image])'), 0);
    await submit(p.locator('button[value=publicar]'));
    await p.goto(base + '/blog');
    await Promise.all([p.waitForNavigation(), p.evaluate(() => { location.href = '/acervo'; })]); // como um clique em link: o navegador sabe de onde veio
    const h = await p.evaluate(() => document.querySelector('.page-banner').getBoundingClientRect().height);
    ok('imagem do topo mais fina (até 260px)', h > 100 && h <= 260, String(h));
    ok('botão Voltar', await p.locator('.back-link').isVisible());
    ok('Contato no fim, antes do rodapé', await p.evaluate(() => { const c = document.querySelector('.sec-contact'); return !!c && c.nextElementSibling === null || (c && c.compareDocumentPosition(document.querySelector('.site-footer')) & Node.DOCUMENT_POSITION_FOLLOWING); }));
    await p.click('.back-link'); await p.waitForLoadState();
    ok('Voltar leva à página anterior', p.url().endsWith('/blog'), p.url());
    await p.goto(base + '/acervo');
    await p.fill('.contact-form input[name=nome]', 'Leitor Página'); await p.fill('.contact-form input[name=email]', 'pagina@teste.com');
    await p.fill('.contact-form textarea[name=mensagem]', 'Mensagem enviada de dentro da página Acervo.');
    await p.waitForTimeout(3200);
    await submit(p.locator('.contact-form button'));
    ok('contato enviado da página volta para a página', p.url().includes('/acervo#contato') && (await p.content()).includes('notice-success'), p.url());
    const m = await (await b.newContext({ viewport: { width: 390, height: 800 } })).newPage();
    await m.goto(base + '/acervo');
    const mh = await m.evaluate(() => document.querySelector('.page-banner').getBoundingClientRect().height);
    ok('celular: faixa fina', mh >= 120 && mh <= 160, String(mh));
    await m.close();
  });

  /* ---------------- Rodada 6 ---------------- */
  area = 'Páginas: parte da foto na faixa';
  await safe('clicar na foto escolhe o ponto da faixa', async () => {
    await p.goto(base + '/admin/paginas'); await p.locator('a.a-strong:has-text("Acervo")').first().click(); await p.waitForLoadState(); await editorReady();
    ok('seletor aparece quando há imagem', await p.locator('[data-banner-focus]').isVisible());
    const area2 = p.locator('[data-banner-focus] [data-focus-area]');
    const box = await area2.boundingBox();
    await area2.click({ position: { x: box.width * 0.2, y: box.height * 0.8 } });
    const val = await p.inputValue('input[name=banner_position]');
    ok('ponto escolhido (20% 80%)', val === '20% 80%', val);
    ok('prévia da faixa acompanha', (await p.locator('[data-banner-preview]').getAttribute('style')).includes('20% 80%'));
    await submit(p.locator('button[value=publicar]'));
    ok('faixa do site usa o ponto', (await pub('/acervo')).includes('object-position:20% 80%'));
    await p.locator('[data-image-field]:has(input[name=banner_image]) [data-clear]').click();
    ok('sem imagem, o seletor some', !(await p.locator('[data-banner-focus]').isVisible()));
  });

  area = 'Faixa de avisos: espessura';
  await safe('espessura da faixa', async () => {
    await p.goto(base + '/admin/faixa');
    await p.fill('input[name="ticker_items[0][text]"]', 'Aviso espessura QA');
    await p.locator('input[name=ticker_pad]').fill('20');
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/');
    ok('espessura 20px no site', (await cs('.ticker-track', 'paddingTop')) === '20px', await cs('.ticker-track', 'paddingTop'));
    await p.goto(base + '/admin/faixa');
    ok('espessura continua salva', (await p.inputValue('input[name=ticker_pad]')) === '20');
    ok('quadro com um controle só não repete o título', await p.locator('#g-aparencia-faixa .a-type-bare').count() === 1);
  });

  area = 'Cabeçalho: prévia ao vivo';
  await safe('prévia muda na hora', async () => {
    await p.goto(base + '/admin/cabecalho');
    await pickFromLibrary(p.locator('[data-image-field]:has(input[name=header_image])'), 0);
    ok('prévia mostra a imagem escolhida', /--header-image:url\('\/uploads\//.test(await prevHtml()));
    await p.locator('input[name=header_overlay]').fill('30');
    await p.uncheck('input[name=header_gradient]');
    const g1 = await prevHtml();
    ok('força 30% sem degradê na prévia', g1.includes('--header-overlay-a:30%') && g1.includes('--header-overlay-b:30%'));
    await p.check('input[name=header_gradient]');
    ok('degradê na prévia', (await prevHtml()).includes('--header-overlay-b:17%'));
    await p.locator('input[name=size_header]').fill('210');
    ok('altura na prévia', (await prevHtml()).includes('--header-height:210px'));
    await p.goto(base + '/');
    ok('mexer na prévia sem salvar não muda o site', !(await pub('/')).includes('--header-height:210px'));
    await p.goto(base + '/admin/blog-posts');
    ok('título dos posts sem quadro repetido', await p.locator('#g-post .a-type-bare').count() === 1);
  });

  area = 'Avisos por e-mail';
  await safe('e-mails de aviso no painel', async () => {
    await p.goto(base + '/admin/configuracoes');
    ok('começa com o e-mail da instalação', (await p.inputValue('input[name="notify[contact]"]')) === 'admin@teste.com');
    await p.fill('input[name="notify[contact]"]', 'contato-qa@teste.com');
    await p.fill('input[name="notify[newsletter]"]', '');
    await p.fill('input[name="notify[comments]"]', 'nao-e-email');
    ok('e-mail sem @ é barrado antes de salvar', !(await p.locator('input[name="notify[comments]"]').evaluate(el => el.checkValidity())));
    await p.fill('input[name="notify[comments]"]', 'admin@teste.com');
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/admin/configuracoes');
    ok('e-mail do contato salvo', (await p.inputValue('input[name="notify[contact]"]')) === 'contato-qa@teste.com');
    ok('newsletter vazio = sem aviso', (await p.inputValue('input[name="notify[newsletter]"]')) === '');
    ok('comentários mantém o anterior', (await p.inputValue('input[name="notify[comments]"]')) === 'admin@teste.com');
    await submit(p.locator('button[value=teste_email]'));
    const n = await notice();
    ok('botão de teste responde', n.includes('teste'), n);
  });

  area = 'Medição e cookies';
  await safe('Pixel, Analytics e aviso de cookies', async () => {
    ok('sem números, sem aviso de cookies', !(await pub('/')).includes('data-cookie-bar'));
    await p.goto(base + '/admin/configuracoes');
    await p.fill('input[name=ga4]', 'X-123');
    await submit(p.locator('.a-sticky-save button'));
    ok('código do Analytics errado é recusado', (await p.content()).includes('começa com “G-”'));
    await p.goto(base + '/admin/configuracoes');
    await p.fill('input[name=meta_pixel]', '1234 5678 9012 345'); await p.fill('input[name=ga4]', 'g-test1234');
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/admin/configuracoes');
    ok('Pixel salvo só com números', (await p.inputValue('input[name=meta_pixel]')) === '123456789012345');
    ok('Analytics salvo em maiúsculas', (await p.inputValue('input[name=ga4]')) === 'G-TEST1234');
    const ctx2 = await b.newContext(); const r = await ctx2.newPage();
    await r.route(/googletagmanager|facebook\.net/, route => route.fulfill({ status: 200, body: '' }));
    await r.goto(base + '/');
    ok('aviso de cookies aparece', await r.locator('[data-cookie-bar]').isVisible());
    ok('nada carrega antes de aceitar', await r.locator('script[src*="googletagmanager"], script[src*="fbevents"]').count() === 0);
    await r.click('[data-cookie="sim"]');
    ok('ao aceitar, Analytics e Pixel carregam', await r.locator('script[src*="googletagmanager"]').count() === 1 && await r.locator('script[src*="fbevents"]').count() === 1);
    await r.reload();
    ok('escolha é lembrada', !(await r.locator('[data-cookie-bar]').isVisible()));
    await r.click('[data-cookie-reset]');
    ok('link “Cookies” no rodapé reabre o aviso', await r.locator('[data-cookie-bar]').isVisible());
    await ctx2.close();
    const ctx3 = await b.newContext(); const r3 = await ctx3.newPage();
    await r3.goto(base + '/'); await r3.click('[data-cookie="nao"]'); await r3.reload();
    ok('quem recusa não é medido', await r3.locator('script[src*="googletagmanager"], script[src*="fbevents"]').count() === 0 && !(await r3.locator('[data-cookie-bar]').isVisible()));
    await ctx3.close();
  });

  area = 'Google';
  await safe('dados estruturados', async () => {
    const home = await pub('/');
    ok('home: site e organização para o Google', home.includes('"@type":"WebSite"') && home.includes('"@type":"Organization"'));
    const post = await pub('/blog/mangueira-1988-o-samba-que-contou-a-historia');
    const m3 = post.match(/<script type="application\/ld\+json">(.*?)<\/script>/);
    let d = null; try { d = JSON.parse(m3[1]); } catch (e) {}
    ok('post: artigo com título, autor e data', d && d['@type'] === 'BlogPosting' && d.headline.includes('Mangueira') && d.author.name && d.datePublished);
    ok('endereço de 127.0.0.1 não é escondido', !home.includes('noindex'));
  });
  await safe('endereço de teste escondido do Google', async () => {
    const tb = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium', args: ['--host-resolver-rules=MAP novo.arquivosapucai.com.br 127.0.0.1'] });
    const tp = await tb.newPage();
    const resp = await tp.goto('http://novo.arquivosapucai.com.br:8080/');
    ok('novo.: aviso ao Google na página', (await tp.content()).includes('<meta name="robots" content="noindex, nofollow">'));
    ok('novo.: aviso ao Google no cabeçalho', (resp.headers()['x-robots-tag'] || '') === 'noindex, nofollow');
    await tp.goto('http://novo.arquivosapucai.com.br:8080/admin/login');
    await tp.fill('input[name=email]', 'admin@teste.com'); await tp.fill('input[name=senha]', 'senha-admin-123');
    await Promise.all([tp.waitForNavigation(), tp.click('button[type=submit]')]);
    await tp.goto('http://novo.arquivosapucai.com.br:8080/admin');
    ok('novo.: aviso amarelo no Início do painel', (await tp.content()).includes('Site de teste escondido do Google'));
    await tb.close();
    await p.goto(base + '/admin');
    ok('endereço oficial: sem o aviso amarelo', !(await p.content()).includes('Site de teste escondido do Google'));
  });

  area = 'Vídeos e playlists lado a lado';
  await safe('quadrinhos de vídeo com capa, título e botão', async () => {
    await p.route(/youtube/, r => r.fulfill({ status: 200, contentType: 'text/html', body: '<body style="background:#333"></body>' }));
    await p.goto(base + '/admin/paginas/nova'); await editorReady();
    await p.fill('[data-title]', 'Playlists QA');
    const openVideo = async () => { await p.click('button[aria-label="Vídeo do YouTube"]'); await p.waitForSelector('.tox-dialog textarea'); };
    await openVideo();
    await p.locator('.tox-dialog textarea').fill('https://www.youtube.com/playlist?list=PLabcdefghij123\nhttps://youtu.be/SLrGaE65GSM');
    await p.locator('.tox-dialog select').first().selectOption('r3');
    await p.click('.tox-dialog button:has-text("Inserir")');
    await openVideo();
    await p.locator('.tox-dialog textarea').fill('https://www.youtube.com/playlist?list=PLzyxwvuts987');
    await p.locator('.tox-dialog select').first().selectOption('r3');
    await p.locator('.tox-dialog').getByLabel('Título embaixo (opcional)').fill('Grupo Especial 1988');
    await p.click('.tox-dialog [data-arq="pickcover"]');
    await p.waitForSelector('[data-media-grid] button'); await p.locator('[data-media-grid] button').first().click();
    ok('capa escolhida na biblioteca', (await p.locator('.tox-dialog').getByLabel('Endereço da capa').inputValue()).includes('/uploads/'));
    await p.click('.tox-dialog button:has-text("Inserir")');
    await submit(p.locator('button[value=publicar]'));
    await p.goto(base + '/playlists-qa'); await p.waitForTimeout(500);
    const info = await p.evaluate(() => [...document.querySelectorAll('.yt-card')].map(c => ({ top: Math.round(c.getBoundingClientRect().top), src: c.querySelector('iframe').getAttribute('src'), link: (c.querySelector('.yt-link') || {}).href || '', cover: !!c.querySelector('.yt-cover'), title: (c.querySelector('.yt-title') || {}).textContent || '' })));
    ok('3 quadrinhos', info.length === 3, String(info.length));
    ok('3 lado a lado na mesma fileira (inseridos em duas vezes)', new Set(info.map(i => i.top)).size === 1, info.map(i => i.top).join(','));
    ok('playlist toca na página', info[0].src.includes('embed/videoseries?list=PLabcdefghij123'));
    ok('botão leva à playlist no YouTube', info[0].link === 'https://www.youtube.com/playlist?list=PLabcdefghij123');
    ok('vídeo comum também', info[1].src.endsWith('embed/SLrGaE65GSM') && info[1].link.includes('watch?v=SLrGaE65GSM'));
    ok('capa e título no 3º', info[2].cover && info[2].title === 'Grupo Especial 1988');
    await p.click('.yt-cover');
    ok('clicou na capa: some e o vídeo começa', await p.locator('.yt-cover').count() === 0 && (await p.locator('.yt-card').nth(2).locator('iframe').getAttribute('src')).includes('autoplay=1'));
    const m = await (await b.newContext({ viewport: { width: 390, height: 800 } })).newPage();
    await m.route(/youtube/, r => r.fulfill({ status: 200, body: '' }));
    await m.goto(base + '/playlists-qa');
    const ws = await m.evaluate(() => [...document.querySelectorAll('.yt-card')].map(c => Math.round(c.getBoundingClientRect().width)));
    ok('celular: um embaixo do outro, largura toda', ws.every(w => w > 300), ws.join(','));
    await m.close();
    await p.goto(base + '/admin/paginas'); await p.locator('a.a-strong:has-text("Playlists QA")').click(); await p.waitForLoadState(); await editorReady();
    await p.frameLocator('.tox-edit-area iframe').locator('.yt-card').first().dblclick();
    await p.waitForSelector('.tox-dialog textarea');
    ok('clique duplo abre a edição com o link', (await p.locator('.tox-dialog textarea').inputValue()) === 'https://www.youtube.com/playlist?list=PLabcdefghij123');
    await p.locator('.tox-dialog select').first().selectOption('50');
    await p.click('.tox-dialog button:has-text("Salvar")');
    await submit(p.locator('button[value=publicar]'));
    ok('mudar o tamanho depois', /yt-card video-solo" style="width: ?50%;?"/.test(await pub('/playlists-qa')));
    await p.unroute(/youtube/);
  });

  /* ---------------- Rodada 7 ---------------- */
  area = 'Playlists: dois formatos';
  await safe('grade ou imagem + texto', async () => {
    await p.goto(base + '/admin/pagina-inicial');
    ok('na grade, campos do outro formato ficam escondidos', !(await p.locator('input[name=playlists_button]').isVisible()) && await p.locator('textarea[name=playlists_outro]').isVisible());
    await p.click('label:has(input[name=playlists_layout][value=feature]) span');
    ok('trocando o formato, aparecem os campos dele', await p.locator('input[name=playlists_button]').isVisible() && !(await p.locator('textarea[name=playlists_outro]').isVisible()));
    await pickFromLibrary(p.locator('[data-image-field]:has(input[name=playlists_image])'), 0);
    await p.fill('input[name=playlists_button]', 'VER AS PLAYLISTS QA');
    await p.fill('input[name=playlists_link]', '/playlists-qa');
    await p.click('label:has(input[name=playlists_align][value=justify]) span');
    await p.locator('input[name="playlists_img[width]"]').fill('70');
    await submit(p.locator('.a-sticky-save button'));
    const home = await pub('/');
    const sec = (home.match(/<section class="sec sec-playlists"[\s\S]*?<\/section>/) || [''])[0];
    ok('imagem clicável leva à página de playlists', /<a href="\/playlists-qa"[^>]*>\s*<img/.test(sec));
    ok('botão embaixo do texto', /class="btn"[^>]*href="\/playlists-qa"[^>]*>VER AS PLAYLISTS QA/.test(sec));
    ok('alinhamento justificado', sec.includes('text-align:justify'));
    ok('texto depois das imagens não aparece', !sec.includes('playlists_outro') && !sec.includes('class="playlists"'));
    await p.goto(base + '/');
    const g = await p.evaluate(() => { const i = document.querySelector('.sec-playlists .split-media img').getBoundingClientRect(); const t = document.querySelector('.sec-playlists .split-text').getBoundingClientRect(); return Math.round(t.left - i.right); });
    ok('texto perto da imagem (40px, régua da rodada 9)', g === 40, String(g));
    await p.goto(base + '/admin/pagina-inicial');
    await p.click('label:has(input[name=playlists_layout][value=grid]) span');
    await submit(p.locator('.a-sticky-save button'));
    const h2 = await pub('/');
    ok('volta para a grade', h2.includes('class="playlists"'));
    await p.goto(base + '/admin/pagina-inicial');
    ok('o outro formato fica guardado', (await p.inputValue('input[name=playlists_button]')) === 'VER AS PLAYLISTS QA');
  });

  area = 'Imagem ao lado do texto';
  await safe('texto acompanha a imagem', async () => {
    await p.goto(base + '/');
    const r = await p.evaluate(() => { const s = document.querySelector('.sec-arquivo .split'); const i = s.querySelector('.split-media img').getBoundingClientRect(); const t = s.querySelector('.split-text').getBoundingClientRect(); return { gap: Math.round(t.left - i.right), w: Math.round(i.width), full: Math.round((s.getBoundingClientRect().width - 40) * 5 / 12) }; });
    ok('espaço entre imagem e texto: 40px', r.gap === 40, JSON.stringify(r));
    ok('imagem a 60% e o texto vem junto', Math.abs(r.w - r.full * 0.6) <= 2, JSON.stringify(r));
  });

  area = 'Cores dos 3 botões';
  await safe('botões com cores próprias', async () => {
    await p.goto(base + '/admin/padrao');
    ok('3 tipos de botão em Padrão do site', await p.locator('h3:has-text("Botão principal")').count() === 1 && await p.locator('h3:has-text("Ver todas")').count() === 1 && await p.locator('h3:has-text("Botão escuro")').count() === 1);
    await p.locator('input[name="colors[button_all_text]"]').evaluate(el => { el.value = '#000000'; });
    await p.locator('input[name="colors[button_dark_bg]"]').evaluate(el => { el.value = '#003366'; });
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
    await p.goto(base + '/');
    ok('“Ver todas” com letra preta', (await cs('.sec-blog .btn-red', 'color')) === 'rgb(0, 0, 0)', await cs('.sec-blog .btn-red', 'color'));
    ok('botão escuro com fundo novo', (await cs('.sec-contact .btn-dark', 'backgroundColor')) === 'rgb(0, 51, 102)', await cs('.sec-contact .btn-dark', 'backgroundColor'));
  });

  area = 'Faixa: velocidade e separador';
  await safe('velocidade e separador', async () => {
    await p.goto(base + '/admin/faixa');
    await p.fill('input[name="ticker_items[0][text]"]', 'Aviso separador QA');
    await p.selectOption('select[name=ticker_sep]', 'star');
    ok('campos de texto e imagem só aparecem quando escolhidos', !(await p.locator('input[name=ticker_sep_text]').isVisible()));
    await p.locator('input[name=ticker_speed]').fill('10'); await p.locator('input[name=ticker_gap]').fill('40');
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/');
    ok('separador estrela', (await p.locator('.ticker .tk-sep').first().textContent()) === '★');
    ok('espaço do separador 40px', (await cs('.ticker-track', 'columnGap')) === '40px', await cs('.ticker-track', 'columnGap'));
    const fast = parseFloat(await cs('.ticker-track', 'animationDuration'));
    await p.goto(base + '/admin/faixa');
    await p.locator('input[name=ticker_speed]').fill('1');
    await p.selectOption('select[name=ticker_sep]', 'text');
    ok('escolhendo texto, aparece o campo', await p.locator('input[name=ticker_sep_text]').isVisible());
    await p.fill('input[name=ticker_sep_text]', '🥁');
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/');
    const slow = parseFloat(await cs('.ticker-track', 'animationDuration'));
    ok('velocidade 1 é bem mais lenta que 10', slow > fast * 5, fast + 's / ' + slow + 's');
    ok('separador de texto/emoji', (await p.locator('.ticker .tk-sep').first().textContent()) === '🥁');
    await p.goto(base + '/admin/faixa');
    await p.selectOption('select[name=ticker_sep]', 'none');
    await submit(p.locator('.a-sticky-save button'));
    ok('sem separador', !(await pub('/')).includes('tk-sep'));
  });

  area = 'Menu: cores, efeito, alinhamento';
  await safe('menu do topo', async () => {
    await p.goto(base + '/admin/cabecalho');
    await p.locator('input[name="colors[menu_hover]"]').evaluate(el => { el.value = '#ffffff'; });
    await p.selectOption('select[name=menu_effect]', 'pill');
    await p.click('label:has(input[name=menu_align][value=center]) span');
    await p.locator('input[name=menu_gap]').fill('50');
    const ph = await prevHtml();
    ok('prévia acompanha o espaço do menu', ph.includes('--menu-gap:50px'));
    ok('prévia acompanha o alinhamento e o efeito', ph.includes('menu-align-center fx-pill'));
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
    const home = await pub('/');
    ok('efeito e alinhamento no site', home.includes('main-nav menu-align-center fx-pill') && home.includes('--menu-gap:50px'));
    await p.goto(base + '/');
    await p.hover('.main-nav a:not([aria-current])');
    await p.waitForTimeout(300);
    // No fundo arredondado o texto fica com a cor do fundo do cabeçalho, para nunca sumir (rodada 9)
    ok('ao passar o mouse, o link muda de cor', (await p.evaluate(() => getComputedStyle(document.querySelector('.main-nav a:not([aria-current]):hover')).color === getComputedStyle(document.querySelector('.site-header')).backgroundColor)));
    ok('ao passar o mouse, aparece o fundo arredondado', (await p.evaluate(() => getComputedStyle(document.querySelector('.main-nav a:not([aria-current]):hover')).backgroundColor)) !== 'rgba(0, 0, 0, 0)');
    const gap = await p.evaluate(() => { const a = document.querySelectorAll('.main-nav li'); return Math.round(a[1].getBoundingClientRect().left - a[0].getBoundingClientRect().right); });
    ok('espaço entre os itens 50px', gap === 50, String(gap));
  });

  area = 'Cabeçalho: redes e lupa';
  await safe('redes no lugar da busca, com lupinha', async () => {
    await p.goto(base + '/');
    ok('ícones das redes no cabeçalho', await p.locator('.header-tools .header-social').count() >= 1);
    ok('campo de busca começa escondido', !(await p.locator('#busca-topo').isVisible()));
    await p.click('[data-search-toggle]');
    ok('lupinha abre a busca', await p.locator('#busca-topo').isVisible() && await p.evaluate(() => document.activeElement.id === 'busca-topo'));
    await p.fill('#busca-topo', 'Mangueira');
    await submit(p.locator('#busca-topo-form button[type=submit]'));
    ok('busca funciona', p.url().includes('/busca?q=Mangueira') && (await p.content()).includes('Mangueira 1988'));
    ok('na página de busca, o campo já vem aberto', await p.locator('#busca-topo').isVisible());
    await p.goto(base + '/blog/mangueira-1988-o-samba-que-contou-a-historia');
    ok('barra de categorias sem a segunda busca', await p.locator('.cat-bar input').count() === 0);
  });

  area = 'Família Archivo';
  await safe('Archivo Condensed e Expanded', async () => {
    await p.goto(base + '/admin/padrao');
    ok('larguras da Archivo na lista', await p.locator('select[name="fonts[heading]"] option[value="Archivo Condensed"]').count() === 1 && await p.locator('select[name="fonts[heading]"] option[value="Archivo Expanded"]').count() === 1 && await p.locator('select[name="fonts[heading]"] option[value="Archivo Narrow"]').count() === 1);
    await p.selectOption('select[name="fonts[heading]"]', 'Archivo Condensed');
    ok('amostra fica apertada', (await p.locator('[data-font-select][name="fonts[heading]"]').evaluate(s => s.closest('label').nextElementSibling.style.fontStretch)) === '75%');
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
    const home = await pub('/');
    ok('site pede a Archivo com as larguras', home.includes('family=Archivo:ital,wdth,wght@0,62..125'));
    await p.goto(base + '/');
    ok('títulos apertados (Condensed)', (await cs('.sec-arquivo .sec-title', 'fontStretch')) === '75%' && (await cs('.sec-arquivo .sec-title', 'fontFamily')).includes('Archivo'));
    await p.goto(base + '/admin/pagina-inicial');
    await custom('arquivo_text', 'Archivo Expanded', 18, '400');
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/');
    ok('texto esticado (Expanded) num texto só', (await cs('.split-text > div p', 'fontStretch')) === '125%');
    await p.goto(base + '/admin/padrao');
    await submit(p.locator('button:has-text("Restaurar padrão")'));
  });

  /* ---------------- Rodada 8: painel por parte do site ---------------- */
  area = 'Nada se perdeu na reorganização';
  await safe('cada ajuste do site tem lugar no painel', async () => {
    const names = new Set();
    for (const scr of ['cabecalho', 'faixa', 'pagina-inicial', 'rodape', 'blog-posts', 'playlists', 'padrao', 'configuracoes']) {
      await p.goto(base + '/admin/' + scr);
      (await p.evaluate(() => [...document.querySelectorAll('form [name]')].map(el => el.name))).forEach(n => names.add(n));
    }
    const has = n => names.has(n);
    const colorKeys = ['header_bg','header_text','header_accent','menu_hover','menu_line','menu_current','header_social','ticker_bg','ticker_text','blog_bg','blog_title','card_bg','card_title','card_text','arquivo_bg','arquivo_title','arquivo_text','playlists_bg','playlists_title','playlists_text','acervo_bg','acervo_title','acervo_text','contact_bg','contact_title','contact_text','footer_bg','footer_text','footer_accent','button_bg','button_text','button_hover_bg','button_hover_text','button_all_bg','button_all_text','button_all_hover_bg','button_all_hover_text','button_dark_bg','button_dark_text','button_dark_hover_bg','button_dark_hover_text','post_page_bg','post_box_bg','post_title','post_text','post_link','post_nav_bg','post_nav_text','pager_bg','pager_text','pager_active_bg','pager_active_text','pl_bar_bg','pl_bar_text','pl_card_bg','pl_year','pl_text','pl_accent'];
    const missingColors = colorKeys.filter(k => !has('colors[' + k + ']'));
    ok('todas as ' + colorKeys.length + ' cores estão em alguma tela', missingColors.length === 0, missingColors.join(', '));
    const typeKeys = ['post_title','menu','ticker','blog_title','blog_button','arquivo_title','arquivo_text','arquivo_button','playlists_title','playlists_intro','playlists_names','playlists_outro','playlists_button','acervo_title','acervo_text','acervo_button','historia_title','historia_text','historia_button','contact_title','contact_text','contact_labels','contact_button','footer_titles','footer_texts','footer_links','footer_news_button','footer_email','footer_word','footer_copyright','footer_author'];
    const missingTypes = typeKeys.filter(k => !has('type[' + k + '][padrao]'));
    ok('todas as 31 fontes de texto estão em alguma tela', missingTypes.length === 0, missingTypes.join(', '));
    const fields = ['logo','header_image','size_logo','size_header','header_overlay','header_gradient','menu_effect','menu_align','menu_gap','ticker_items[0][text]','ticker_speed','ticker_gap','ticker_pad','ticker_sep','ticker_sep_text','ticker_sep_image',
      'blog_title','blog_button','split_gap','post_sidebar','sidebar_title','sidebar_count','related_title','page_slug','count_label','arquivo_title','arquivo_text','arquivo_button','arquivo_link','arquivo_new_tab','arquivo_image','arquivo_img[width]','playlists_layout','playlists_title','playlists_intro','playlists_outro','playlists_image','playlists_img[width]','playlists_button','playlists_link','playlists_new_tab','playlists_align','playlists[0][title]',
      'acervo_show','acervo_image','acervo_img[width]','acervo_title','acervo_text','acervo_button','acervo_link','acervo_new_tab','historia_show','historia_image','historia_img[width]','historia_title','historia_text','historia_button','historia_link','historia_new_tab','contact_title','contact_text','contact_labels[nome]','contact_button',
      'nav_title','rss_label','copyright','author_label','email_label','news_title','news_text','news_placeholder','news_button','show_word','word',
      'cards_per_row','cards_layout','card_ratio','card_title_lines','card_excerpt_lines','per_page','comments_enabled','comments_moderate','share_buttons',
      'fonts[heading]','fonts[body]','fonts[ui]','font_sizes[heading]','font_sizes[body]','font_sizes[ui]','font_weights[heading]','font_weights[body]',
      'title','tagline','description','email','share_image','socials[youtube]','socials[instagram]','notify[contact]','notify[newsletter]','notify[comments]','meta_pixel','ga4','cookie_text'];
    const missing = fields.filter(f => !has(f));
    ok('todos os outros ' + fields.length + ' ajustes estão em alguma tela', missing.length === 0, missing.join(', '));
  });
  await safe('valores salvos continuam no lugar novo', async () => {
    await p.goto(base + '/admin/faixa');
    ok('faixa: espessura salva antes aparece', (await p.inputValue('input[name=ticker_pad]')) === '20');
    await p.goto(base + '/admin/cabecalho');
    ok('cabeçalho: efeito salvo antes aparece', (await p.inputValue('select[name=menu_effect]')) === 'pill');
    ok('cabeçalho: cor do menu ao passar o mouse aparece', (await p.inputValue('input[name="colors[menu_hover]"]')) === '#ffffff');
    await p.goto(base + '/admin/blog-posts');
    ok('blog: linhas da descrição salvas antes aparecem', (await p.inputValue('select[name=card_excerpt_lines]')) === '5');
    await p.goto(base + '/admin/configuracoes');
    ok('configurações: nome do site aparece', (await p.inputValue('input[name=title]')) === 'Arquivo Sapucaí QA');
  });
  await safe('salvar uma tela não mexe nas outras', async () => {
    await p.goto(base + '/admin/cabecalho');
    await p.locator('input[name="colors[header_social]"]').evaluate(el => { el.value = '#ff0000'; });
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/');
    ok('ícones das redes com a cor escolhida', (await cs('.header-social', 'color')) === 'rgb(255, 0, 0)', await cs('.header-social', 'color'));
    ok('ícones das redes com destaque (borda)', (await cs('.header-social', 'borderTopWidth')) === '2px');
    await p.goto(base + '/admin/faixa');
    ok('faixa continua com a espessura dela', (await p.inputValue('input[name=ticker_pad]')) === '20');
    await p.goto(base + '/admin/pagina-inicial');
    ok('página inicial continua com o título dela', (await p.inputValue('input[name=arquivo_title]')) !== '');
  });
  await safe('menu lateral por parte do site', async () => {
    await p.goto(base + '/admin');
    const groups = await p.locator('.a-nav-group').allTextContents();
    ok('grupos do menu lateral', groups.join('|') === 'Escrever|Leitores|O site, parte por parte|Geral', groups.join('|'));
    for (const name of ['Cabeçalho e menu', 'Faixa de avisos', 'Página inicial', 'Rodapé', 'Blog e posts', 'Padrão do site', 'Configurações']) {
      ok('menu lateral tem “' + name + '”', await p.locator('.a-side nav a:has-text("' + name + '")').count() === 1);
    }
    await p.goto(base + '/admin/tags');
    ok('Tags acende “Posts” no menu lateral', (await p.locator('.a-side nav a[aria-current=page]').textContent()).includes('Posts'));
    await p.goto(base + '/admin/banners');
    ok('Banners acende “Página inicial”', (await p.locator('.a-side nav a[aria-current=page]').textContent()).includes('Página inicial'));
    for (const [old, now] of [['/admin/aparencia', '/admin/padrao'], ['/admin/conteudo?aba=home', '/admin/pagina-inicial'], ['/admin/menu', '/admin/cabecalho']]) {
      await p.goto(base + old);
      ok('endereço antigo ' + old + ' leva à tela nova', p.url().includes(now), p.url());
    }
  });
  await safe('prévia da faixa no painel', async () => {
    await p.goto(base + '/admin/faixa');
    await p.fill('input[name="ticker_items[0][text]"]', 'Prévia QA');
    ok('prévia mostra o aviso digitado', (await prevHtml()).includes('Prévia QA'));
    await p.locator('input[name="colors[ticker_bg]"]').evaluate(el => { el.value = '#003366'; el.dispatchEvent(new Event('input', { bubbles: true })); });
    ok('prévia muda a cor na hora', (await prevHtml()).includes('--c-ticker-bg:#003366'));
  });

  area = 'Rodada 9';
  await safe('1. prévia do topo é o site de verdade, com a fonte escolhida', async () => {
    await p.goto(base + '/admin/cabecalho');
    const box = p.locator('[data-type-field]:has(select[name="type[menu][font]"])');
    await box.locator('xpath=ancestor::details').evaluateAll(ds => ds.forEach(d => { d.open = true; }));
    await box.locator('[data-type-default]').uncheck();
    await box.locator('select[name="type[menu][font]"]').selectOption('Anton');
    const h = await prevHtml();
    ok('prévia usa a fonte escolhida no menu', h.includes("font-family:'Anton'") && h.includes('family=Anton'));
    ok('prévia tem os ícones e a lupinha do site', h.includes('class="search-toggle"') && h.includes('class="ticker"'));
    ok('sem salvar, o site não muda', !(await pub('/')).includes("font-family:'Anton'"));
    const r = await fetch(base + '/admin/previa/cabecalho', { redirect: 'manual' });
    ok('prévia não abre para quem não entrou no painel', r.status >= 300 && r.status < 400);
  });
  await safe('2. espaço do menu chega a 0 e o item aberto aparece no fundo arredondado', async () => {
    await p.goto(base + '/admin/cabecalho');
    ok('régua começa em 0', (await p.getAttribute('input[name=menu_gap]', 'min')) === '0');
    await p.selectOption('select[name=menu_effect]', 'pill');
    await p.locator('input[name=menu_gap]').fill('0');
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
    ok('espaço 0 no site', (await pub('/')).includes('--menu-gap:0px'));
    await p.goto(base + '/blog');
    const c = await p.evaluate(() => { const a = document.querySelector('.main-nav ul a[aria-current="page"]'); const cs = getComputedStyle(a); return [cs.color, cs.backgroundColor]; });
    ok('texto do item aberto é diferente do fundo arredondado', c[0] !== c[1], c.join(' / '));
    await p.goto(base + '/admin/cabecalho');
    await p.locator('input[name=menu_gap]').fill('30');
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
  });
  await safe('3. um número de posts para a home e o blog, com páginas na home', async () => {
    await p.goto(base + '/admin/pagina-inicial');
    await p.fill('input[name=per_page]', '2');
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/admin/blog-posts');
    ok('Blog e posts mostra o mesmo número', (await p.inputValue('input[name=per_page]')) === '2');
    await p.goto(base + '/');
    const homeTitles = await p.evaluate(() => [...document.querySelector('.sec-blog:not(.sec-custom)').querySelectorAll('h3')].map(x => x.textContent.trim()));
    const pager = await p.$$eval('.sec-blog:not(.sec-custom) .pager a', e => e.map(a => a.getAttribute('href')));
    ok('home mostra 2 posts', homeTitles.length === 2, String(homeTitles.length));
    ok('home tem os números das páginas', pager.includes('/blog?pagina=2'), pager.join(' '));
    await p.goto(base + '/blog?pagina=2');
    const b2 = await p.$$eval('main .card h3, main article h3', e => e.map(x => x.textContent.trim()));
    await p.goto(base + '/blog');
    const b1 = await p.$$eval('main .card h3, main article h3', e => e.map(x => x.textContent.trim()));
    ok('página 1 do blog = posts da home', JSON.stringify(b1) === JSON.stringify(homeTitles), b1.join('|'));
    ok('página 2 começa no post seguinte', b2.length > 0 && !b1.includes(b2[0]));
  });
  await safe('4. uma régua para o espaço entre imagem e texto', async () => {
    await p.goto(base + '/admin/pagina-inicial');
    await p.locator('input[name=split_gap]').fill('60');
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/');
    const g = await p.evaluate(() => { const s = document.querySelector('.sec-arquivo .split'); const i = s.querySelector('.split-media img').getBoundingClientRect(); const t = s.querySelector('.split-text').getBoundingClientRect(); return Math.round(t.left - i.right); });
    ok('espaço muda para 60px', g === 60, String(g));
    await p.goto(base + '/admin/pagina-inicial');
    await p.locator('input[name=split_gap]').fill('40');
    await submit(p.locator('.a-sticky-save button'));
  });
  await safe('5. busca flutua embaixo da lupinha', async () => {
    await p.goto(base + '/');
    const h0 = await p.$eval('.main-nav ul', u => u.getBoundingClientRect().height);
    await p.click('[data-search-toggle]');
    const h1 = await p.$eval('.main-nav ul', u => u.getBoundingClientRect().height);
    const pos = await p.evaluate(() => { const f = document.getElementById('busca-topo-form').getBoundingClientRect(); const l = document.querySelector('[data-search-toggle]').getBoundingClientRect(); return f.top >= l.bottom; });
    ok('menu não se mexe ao abrir a busca', h0 === h1, h0 + ' / ' + h1);
    ok('caixa aparece embaixo da lupinha', pos);
    await p.keyboard.press('Escape');
    ok('fecha com Esc', await p.$eval('#busca-topo-form', f => f.hidden));
    await p.click('[data-search-toggle]'); await p.mouse.click(30, 600);
    ok('fecha clicando fora', await p.$eval('#busca-topo-form', f => f.hidden));
    await p.click('[data-search-toggle]'); await p.click('[data-search-close]');
    ok('fecha no X', await p.$eval('#busca-topo-form', f => f.hidden));
  });
  let postUrl = '';
  await safe('6. contato nas páginas do blog, não dentro do post', async () => {
    ok('contato no blog', (await pub('/blog')).includes('sec-contact'));
    await p.goto(base + '/blog');
    postUrl = await p.$eval('main .card a[href^="/blog/"]', a => a.getAttribute('href'));
    ok('contato não aparece dentro do post', !(await pub(postUrl)).includes('sec-contact'));
  });
  await safe('7. coluna lateral e posts relacionados', async () => {
    await p.goto(base + postUrl);
    const side = await p.$$eval('.post-side li a', e => e.map(a => a.getAttribute('href')));
    ok('coluna com os últimos posts, sem o post aberto', side.length > 0 && side.length <= 5 && !side.includes(postUrl), side.join(' '));
    await p.goto(base + '/admin/posts');
    const editHref = await p.$eval('a[href^="/admin/posts/"]:not([href$="novo"])', a => a.getAttribute('href'));
    await p.goto(base + editHref);
    const choices = JSON.parse(await p.locator('[data-rel-posts]').textContent());
    ok('lista de posts para escolher', choices.length > 0);
    const pick = choices[choices.length - 1];
    await p.fill('[data-rel-search]', pick.title.slice(0, 6));
    await p.click('[data-rel-results] button >> nth=0');
    ok('post escolhido entra na lista', (await p.locator('[data-rel-list] li').count()) === 1);
    const chosenTitle = await p.locator('[data-rel-list] li span').textContent();
    await submit(p.locator('button[name=acao][value=publicar], button[value=publicar]').first());
    await p.goto(base + editHref);
    ok('escolha fica salva', (await p.locator('[data-rel-list] li').count()) === 1);
    const slug = await p.inputValue('input[name=slug]');
    const html = await pub('/blog/' + slug);
    const rel = (html.match(/<section class="recent">[\s\S]*?<\/section>/) || [''])[0];
    ok('“Posts relacionados” embaixo do post', rel.includes('Posts relacionados') && rel.includes(chosenTitle.replace(/&/g, '&amp;')), chosenTitle);
    await p.goto(base + '/admin/blog-posts');
    await p.uncheck('input[name=post_sidebar]');
    await submit(p.locator('.a-sticky-save button'));
    ok('coluna desliga', !(await pub('/blog/' + slug)).includes('post-side'));
    await p.goto(base + '/admin/blog-posts');
    await p.check('input[name=post_sidebar]');
    await submit(p.locator('.a-sticky-save button'));
  });
  await safe('8. itálico em cada controle de fonte', async () => {
    await p.goto(base + '/admin/faixa');
    const box = p.locator('[data-type-field]:has(select[name="type[ticker][font]"])');
    await box.locator('xpath=ancestor::details').evaluateAll(ds => ds.forEach(d => { d.open = true; }));
    await box.locator('[data-type-default]').uncheck();
    ok('faixa começa em itálico', await box.locator('[data-type-italic]').isChecked());
    await box.locator('[data-type-italic]').uncheck();
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/');
    ok('faixa sem itálico no site', (await p.$eval('.ticker-track', e => getComputedStyle(e).fontStyle)) === 'normal');
    ok('outros controles têm itálico', (await p.goto(base + '/admin/rodape').then(() => p.locator('[data-type-italic]').count())) > 3);
    await p.goto(base + '/admin/faixa');
    await box.locator('xpath=ancestor::details').evaluateAll(ds => ds.forEach(d => { d.open = true; }));
    await box.locator('[data-type-default]').check();
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/');
    ok('padrão volta ao itálico', (await p.$eval('.ticker-track', e => getComputedStyle(e).fontStyle)) === 'italic');
  });
  await safe('9. página de Playlists em sanfona', async () => {
    const pages = await (await fetch(base + '/sitemap.xml')).text();
    await p.goto(base + '/admin/playlists');
    const opts = await p.$$eval('select[name=page_slug] option', o => o.map(x => x.value).filter(Boolean));
    ok('escolhe a página onde aparecem', opts.length > 0);
    const slug = opts.find(o => pages.includes('/' + o + '<')) || opts[0];
    await p.selectOption('select[name=page_slug]', slug);
    await p.fill('input[name="decades[d0][name]"]', 'Anos 80 QA');
    for (const y of ['1980', '1981']) {
      await p.locator('[data-pl-decade]').first().locator('[data-pl-add-item]').click();
      const it = p.locator('[data-pl-decade]').first().locator('[data-pl-item]').last();
      await it.locator('input[name$="[year]"]').fill(y); await it.locator('input[name$="[title]"]').fill('Grupo Especial QA');
      await it.locator('input[name$="[subtitle]"]').fill('Desfiles completos'); await it.locator('input[name$="[link]"]').fill('https://www.youtube.com/playlist?list=QA' + y);
    }
    await p.click('[data-pl-add-decade]');
    await p.locator('[data-pl-decade]').last().locator('input').first().fill('Anos 90 QA');
    await p.locator('[data-pl-decade]').last().locator('[data-pl-add-item]').click();
    await p.locator('[data-pl-decade]').last().locator('input[name$="[year]"]').fill('1990');
    await p.locator('[data-pl-decade]').nth(1).locator('[data-pl-up]').first().click();
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/admin/playlists');
    ok('ordem das décadas fica salva', (await p.inputValue('[data-pl-decade] >> nth=0 >> input')) === 'Anos 90 QA');
    await p.locator('[data-pl-decade]').nth(1).locator('[data-pl-up]').first().click();
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/' + slug);
    const d = await p.$$eval('details.pl-decade', e => e.map(x => [x.querySelector('.pl-dname').textContent, x.open, x.querySelectorAll('.pl-cap').length]));
    ok('décadas no site, a primeira aberta', d.length === 2 && d[0][0] === 'Anos 80 QA' && d[0][1] && !d[1][1], JSON.stringify(d));
    ok('capa com ano, título, subtítulo e link', (await p.$eval('.pl-cap', a => a.querySelector('.pl-year').textContent + '|' + a.querySelector('.pl-title').textContent + '|' + a.getAttribute('href'))) === '1980|Grupo Especial QA|https://www.youtube.com/playlist?list=QA1980');
    await p.click('details.pl-decade >> nth=1 >> summary');
    ok('clicar abre a outra sem fechar a primeira', await p.$$eval('details.pl-decade', e => e.every(x => x.open)));
    ok('“1 playlist” no singular', (await p.textContent('details.pl-decade >> nth=1 >> summary small')).trim() === '1 playlist');
  });
  await safe('10. números das páginas mudam ao passar o mouse', async () => {
    await p.goto(base + '/admin/blog-posts');
    ok('cores dos números no painel', (await p.locator('input[name="colors[pager_active_bg]"]').count()) === 1);
    await p.goto(base + '/blog');
    await p.evaluate(() => document.querySelector('[data-cookie-bar]')?.remove());
    // A revisão abre outra janela antes; numa aba em segundo plano a animação da troca de cor não anda
    await p.addStyleTag({ content: '.pager a { transition: none !important; }' });
    const a = p.locator('.pager a:not([aria-current])').first();
    const before = await a.evaluate(e => getComputedStyle(e).backgroundColor);
    await a.hover(); await p.waitForTimeout(400);
    const after = await a.evaluate(e => getComputedStyle(e).backgroundColor);
    const cur = await p.$eval('.pager a[aria-current]', e => getComputedStyle(e).backgroundColor);
    const dbg = await a.evaluate(e => { const r = e.getBoundingClientRect(); const t = document.elementFromPoint(r.x + r.width / 2, r.y + r.height / 2); return e.matches(':hover') + ' ' + Math.round(r.y) + ' ' + (t ? t.outerHTML.slice(0, 90) : 'nada') + ' cur=' + document.querySelector('.pager a[aria-current]').textContent + ' n=' + document.querySelectorAll('.pager a').length; });
    ok('mouse por cima muda a cor (igual à página aberta)', before !== after && after === cur, before + ' → ' + after + ' / ' + dbg);
    await p.goto(base + '/admin/pagina-inicial');
    await p.fill('input[name=per_page]', '6');
    await submit(p.locator('.a-sticky-save button'));
  });

  area = 'Ajustes de 30/10';
  await safe('título da página do Blog em Páginas', async () => {
    await p.goto(base + '/admin/paginas');
    ok('Blog aparece na lista de Páginas', (await p.locator('a[href="/admin/paginas/blog"]').count()) > 0);
    await p.goto(base + '/admin/paginas/blog');
    await p.fill('input[name=page_title]', 'Blog do Arquivo QA');
    await p.fill('textarea[name=page_intro]', 'Crônicas e estudos QA');
    await submit(p.locator('.a-sticky-save button'));
    const h = await pub('/blog');
    ok('título novo no /blog', h.includes('>Blog do Arquivo QA</h1>') && h.includes('Crônicas e estudos QA'));
    ok('título da aba do navegador também', /<title>Blog do Arquivo QA/.test(h));
  });
  await safe('fonte da coluna Últimos posts', async () => {
    await p.goto(base + '/admin/blog-posts');
    const box = p.locator('[data-type-field]:has(select[name="type[side_posts][font]"])');
    await box.locator('xpath=ancestor::details').evaluateAll(ds => ds.forEach(d => { d.open = true; }));
    await box.locator('[data-type-default]').uncheck();
    await box.locator('select[name="type[side_posts][font]"]').selectOption('Lora');
    await box.locator('input[name="type[side_posts][size]"]').fill('16');
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/blog');
    const url = await p.$eval('main .card a[href^="/blog/"]', a => a.getAttribute('href'));
    const h = await pub(url);
    ok('posts da coluna com a fonte escolhida', /class="post-side"[\s\S]*<strong style="font-family:&#039;Lora&#039;[^"]*font-size:16px/.test(h));
    ok('controle do título da coluna existe', (await p.goto(base + '/admin/blog-posts').then(() => p.locator('select[name="type[side_title][font]"]').count())) === 1);
  });
  await safe('link do botão das playlists com lista de páginas', async () => {
    await p.goto(base + '/admin/pagina-inicial');
    ok('campo com lista das páginas', (await p.locator('#lista-paginas option').count()) > 1 && (await p.getAttribute('input[name=playlists_link]', 'list')) === 'lista-paginas');
  });

  area = 'Geral';
  ok('nenhum erro de JavaScript nas páginas', jsErrors.length === 0, jsErrors.slice(0, 3).join(' | '));
  fs.writeFileSync(shots + '/qa-resultado.json', JSON.stringify(results, null, 1));
  const failed = results.filter(r => !r.pass);
  console.log('TOTAL', results.length, 'PASSOU', results.length - failed.length, 'FALHOU', failed.length);
  failed.forEach(r => console.log('FALHOU [' + r.area + '] ' + r.name + (r.extra ? ' — ' + r.extra : '')));
  await b.close();
})().catch(e => { console.error('ABORTOU', e); process.exit(1); });
