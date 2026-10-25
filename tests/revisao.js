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
    await p.locator('.tox-dialog').getByLabel('Cole o link do vídeo').fill('https://www.youtube.com/watch?v=SLrGaE65GSM');
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
    await p.goto(base + '/admin/menu');
    await p.fill('form.a-card:has-text("Adicionar item") input[name=label]', 'Guia 2027');
    await p.fill('form.a-card:has-text("Adicionar item") input[name=url]', '/blog/categoria/guia-2027');
    await p.check('form.a-card:has-text("Adicionar item") input[name=new_tab]');
    await submit(p.locator('button:has-text("Adicionar ao menu")'));
    let home = await pub('/');
    ok('adicionar item aparece no topo e no rodapé', (home.match(/>Guia 2027</g) || []).length >= 2);
    ok('item em nova aba', /href="\/blog\/categoria\/guia-2027" target="_blank"/.test(home));
    const last = p.locator('.a-menu-item').last();
    await last.locator('input[name=label]').fill('Guia dos Enredos');
    await submit(last.locator('button:has-text("Salvar")'));
    ok('renomear item', (await pub('/')).includes('>Guia dos Enredos<'));
    await submit(p.locator('.a-menu-item').last().locator('button[title=Subir]'));
    home = await pub('/');
    ok('mover item', home.indexOf('>Guia dos Enredos<') < home.indexOf('>Contato<'));
    await submit(p.locator('.a-menu-item:has(input[value="Guia dos Enredos"]) button[title=Remover]'));
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
    await p.goto(base + '/admin/conteudo?aba=site');
    await p.fill('input[name=title]', 'Arquivo Sapucaí QA');
    await p.fill('input[name="socials[instagram]"]', 'instagram.com/arquivosapucai');
    await pickFromLibrary(p.locator('[data-image-field]:has(input[name=logo])'), 0);
    await submit(p.locator('.a-sticky-save button'));
    const home = await pub('/');
    ok('nome do site muda', home.includes('<title>Arquivo Sapucaí QA</title>'));
    ok('Instagram aparece no rodapé (com https)', home.includes('href="https://instagram.com/arquivosapucai"'));
    ok('logo novo aparece', /class="logo"[\s\S]*?<img src="\/uploads\//.test(home));
  });
  await safe('textos da home e avisos', async () => {
    await p.goto(base + '/admin/conteudo?aba=home');
    await p.fill('input[name="ticker_items[0][text]"]', 'Aviso um QA'); await p.fill('input[name="ticker_items[1][text]"]', 'Aviso dois QA');
    await p.fill('input[name="ticker_items[1][link]"]', '/blog');
    await p.fill('input[name=arquivo_title]', 'O Arquivo QA');
    await p.fill('input[name="playlists[0][link]"]', 'https://www.youtube.com/playlist?list=QA');
    await p.fill('input[name=blog_count]', '2');
    await submit(p.locator('.a-sticky-save button'));
    const home = await pub('/');
    ok('faixa com 2 avisos', home.includes('Aviso um QA') && /<a href="\/blog"[^>]*>Aviso dois QA/.test(home));
    ok('título O Arquivo muda', home.includes('O Arquivo QA'));
    ok('link da playlist', home.includes('playlist?list=QA'));
    await p.goto(base + '/admin/conteudo?aba=home');
    await p.fill('input[name="ticker_items[0][text]"]', ''); await p.fill('input[name="ticker_items[1][text]"]', '');
    await submit(p.locator('.a-sticky-save button'));
    ok('sem avisos a faixa some', !(await pub('/')).includes('class="ticker"'));
  });
  await safe('rodapé', async () => {
    await p.goto(base + '/admin/conteudo?aba=rodape');
    await p.fill('input[name=word]', 'SAPUCAÍ QA'); await submit(p.locator('.a-sticky-save button'));
    ok('texto grande do rodapé muda', (await pub('/')).includes('SAPUCAÍ QA'));
    await p.uncheck('input[name=show_word]'); await submit(p.locator('.a-sticky-save button'));
    ok('esconder texto grande', !(await pub('/')).includes('footer-word'));
  });
  await safe('blog e comentários', async () => {
    await p.goto(base + '/admin/conteudo?aba=blog');
    await p.uncheck('input[name=comments_moderate]'); await submit(p.locator('.a-sticky-save button'));
    const msg = await comment('Sem moderação QA');
    ok('sem moderação o comentário entra direto', msg.includes('publicado'), msg);
    await p.goto(base + '/admin/conteudo?aba=blog');
    await p.uncheck('input[name=comments_enabled]'); await p.uncheck('input[name=share_buttons]');
    await submit(p.locator('.a-sticky-save button'));
    const post = await pub('/blog/mangueira-1988-o-samba-que-contou-a-historia');
    ok('desligar comentários tira o formulário', !post.includes('comment-form'));
    ok('desligar compartilhar tira os botões', !post.includes('class="share"'));
    await p.goto(base + '/admin/conteudo?aba=blog');
    await p.check('input[name=comments_enabled]'); await p.check('input[name=share_buttons]'); await p.check('input[name=comments_moderate]');
    await submit(p.locator('.a-sticky-save button'));
  });

  /* ---------------- Aparência ---------------- */
  area = 'Aparência';
  await safe('cores, fontes e cards', async () => {
    await p.goto(base + '/admin/aparencia');
    await p.locator('input[name="colors[blog_bg]"]').evaluate(el => { el.value = '#003366'; });
    await p.selectOption('select[name="fonts[heading]"]', 'Anton');
    await p.locator('input[name=size_logo]').fill('140'); await p.locator('input[name=size_header]').fill('200');
    await p.selectOption('select[name=cards_per_row]', '4');
    await p.check('input[name=cards_layout][value=masonry]', { force: true });
    await p.selectOption('select[name=card_ratio]', 'original');
    await p.selectOption('select[name=card_title_lines]', '2');
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
    const home = await pub('/');
    ok('cor muda no site', home.includes('--c-blog-bg:#003366'));
    ok('fonte do Google carrega', home.includes('fonts.googleapis.com/css2?family=Anton'));
    ok('tamanho do logo e do cabeçalho', home.includes('--logo-size:140px') && home.includes('--header-height:200px'));
    ok('4 por linha em mosaico', home.includes('cards cols-4 is-masonry'));
    ok('limite de linhas do título', home.includes('--card-title-lines:2'));
    await submit(p.locator('button:has-text("Restaurar padrão")'));
    ok('restaurar padrão', (await pub('/')).includes('--c-blog-bg:#20063A'));
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
    ok('autor não vê menus de administrador', !(await p.content()).includes('/admin/aparencia'));
    const r = await p.goto(base + '/admin/aparencia');
    ok('autor é bloqueado em Aparência', r.status() === 403);
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
    await box.locator(`input[name="type[${key}][padrao]"]`).uncheck();
    ok(`${key}: desmarcar padrão mostra as escolhas`, await box.locator('[data-type-custom]').isVisible());
    if (font) await box.locator(`select[name="type[${key}][font]"]`).selectOption(font);
    if (size) await box.locator(`input[name="type[${key}][size]"]`).fill(String(size));
    const b = box.locator(`input[name="type[${key}][bold]"]`);
    if (bold) await b.check(); else await b.uncheck();
  };
  const cs = async (sel, prop) => p.evaluate(([s, pr]) => { const el = document.querySelector(s); return el ? getComputedStyle(el)[pr] : 'NÃO ACHOU ' + s; }, [sel, prop]);
  await safe('Aparência: tamanhos base e título dos posts', async () => {
    await p.goto(base + '/admin/aparencia');
    ok('Aparência: menu e faixa saíram daqui', !(await p.content()).includes('type[menu]') && !(await p.content()).includes('type[ticker]'));
    await p.locator('input[name="font_sizes[heading]"]').fill('50');
    await p.locator('input[name="font_sizes[body]"]').fill('18');
    await p.locator('input[name="font_sizes[ui]"]').fill('16');
    await custom('post_title', 'Bitter', 32, true);
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
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
    await p.goto(base + '/admin/aparencia');
    ok('título do post continua salvo no painel', (await p.inputValue('input[name="type[post_title][size]"]')) === '32' && !(await p.isChecked('input[name="type[post_title][padrao]"]')));
    ok('tamanho base continua salvo', (await p.inputValue('input[name="font_sizes[body]"]')) === '18');
    await p.locator('input[name="font_sizes[heading]"]').fill('44'); await p.locator('input[name="font_sizes[body]"]').fill('16'); await p.locator('input[name="font_sizes[ui]"]').fill('15');
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
  });
  await safe('fonte do menu (tela Menu)', async () => {
    await p.goto(base + '/admin/menu');
    await custom('menu', 'Oswald', 20, true);
    await submit(p.locator('button:has-text("Salvar fonte do menu")'));
    ok('menu: aviso de salvo', (await notice()).includes('Fonte do menu salva'), await notice());
    const home = await pub('/');
    ok('menu: fonte nova carrega do Google', home.includes('family=Oswald'));
    await p.goto(base + '/');
    ok('menu aparece com 20px', (await cs('.main-nav a', 'fontSize')) === '20px', await cs('.main-nav a', 'fontSize'));
    ok('menu aparece em Oswald', (await cs('.main-nav a', 'fontFamily')).includes('Oswald'));
    await p.goto(base + '/admin/menu');
    await p.check('input[name="type[menu][padrao]"]');
    ok('marcar padrão esconde as escolhas', !(await p.locator('[data-type-field] [data-type-custom]').isVisible()));
    await submit(p.locator('button:has-text("Salvar fonte do menu")'));
    await p.goto(base + '/');
    ok('menu volta ao padrão (15px)', (await cs('.main-nav a', 'fontSize')) === '15px', await cs('.main-nav a', 'fontSize'));
  });
  await safe('fonte dos avisos (Conteúdo do site)', async () => {
    await p.goto(base + '/admin/conteudo?aba=home');
    await p.fill('input[name="ticker_items[0][text]"]', 'Aviso fonte QA');
    await custom('ticker', 'Bebas Neue', 18, false);
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/');
    ok('avisos com 18px', (await cs('.ticker-track', 'fontSize')) === '18px', await cs('.ticker-track', 'fontSize'));
    ok('avisos em Bebas Neue', (await cs('.ticker-track', 'fontFamily')).includes('Bebas Neue'));
    await p.goto(base + '/admin/conteudo?aba=home');
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
    await p.goto(base + '/admin/conteudo?aba=home');
    await p.fill('input[name=arquivo_title]', 'mexi');
    lastDialog = '';
    await Promise.all([p.waitForNavigation().catch(() => {}), p.click('.a-side a:has-text("Menu")')]);
    ok('aviso também em outras telas (Textos da home)', lastDialog.includes('não foram salvas'));
    lastDialog = '';
    await p.goto(base + '/admin/menu'); await p.click('.a-side a:has-text("Tags")'); await p.waitForLoadState();
    ok('sem mudanças, sai sem aviso', lastDialog === '');
  });
  await safe('“Salvando…” e aviso de salvo', async () => {
    await p.goto(base + '/admin/conteudo?aba=rodape');
    await p.route(/\/admin\/conteudo/, async r => { try { if (r.request().method() === 'POST') await new Promise(x => setTimeout(x, 1200)); await r.continue(); } catch (e) {} });
    await p.fill('input[name=word]', 'ARQUIVO SAPUCAÍ');
    const r = await p.evaluate(async () => { const btn = document.querySelector('.a-sticky-save button'); btn.click(); await new Promise(x => setTimeout(x, 150)); return [btn.textContent, document.body.classList.contains('is-saving')]; });
    ok('botão mostra “Salvando…” enquanto salva', r[0].includes('Salvando'), r[0]);
    ok('barra de carregamento no topo', r[1]);
    await p.waitForURL(/aba=rodape/); await p.waitForLoadState(); await p.unroute(/\/admin\/conteudo/);
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
    await p.goto(base + '/admin/conteudo?aba=home');
    await p.locator('input[name="arquivo_img[width]"]').fill('60');
    await p.selectOption('select[name="arquivo_img[radius]"]', '999');
    await p.selectOption('select[name="arquivo_img[ratio]"]', 'square');
    await p.selectOption('select[name="acervo_img[align]"]', 'center');
    await submit(p.locator('.a-sticky-save button'));
    const home = await pub('/');
    ok('O Arquivo: tamanho, cantos e formato', /split-media"><img style="width:60%;border-radius:999px;aspect-ratio:1 \/ 1/.test(home));
    ok('Acervo: alinhado ao centro', /acervo-media"><img style="[^"]*margin-left:auto;margin-right:auto/.test(home));
    await p.goto(base + '/admin/conteudo?aba=home');
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
    await p.goto(base + '/admin/menu');
    ok('página aparece nas sugestões do menu', (await p.content()).includes('Página: Acervo'));
    await p.goto(base + '/admin/paginas'); await p.locator('a.a-strong:has-text("História")').click(); await p.waitForLoadState();
    await submit(p.locator('button:has-text("Excluir página")'));
    ok('excluir página', (await notice()).includes('excluída'));
  });

  /* ---------------- Rodada 4 ---------------- */
  area = 'Painel organizado';
  await safe('grupos e atalhos', async () => {
    await p.goto(base + '/admin/conteudo?aba=home');
    ok('Textos da home: 6 grupos com título', await p.locator('.a-group .a-group-title').count() === 6);
    ok('Textos da home: atalhos no topo', await p.locator('.a-toc a').count() === 6);
    await p.click('.a-toc a:has-text("Contato")');
    ok('atalho leva ao grupo Contato', p.url().endsWith('#g-contato'));
    await p.goto(base + '/admin/conteudo?aba=rodape');
    ok('Rodapé: grupos e atalhos', await p.locator('.a-group').count() === 2 && await p.locator('.a-toc a').count() === 2);
    await p.goto(base + '/admin/aparencia');
    ok('Aparência: 4 grupos e atalhos', await p.locator('.a-group').count() === 4 && await p.locator('.a-toc a').count() === 4);
  });

  area = 'Textos da home com fonte própria';
  await safe('fonte, tamanho e negrito em cada texto', async () => {
    await p.goto(base + '/admin/conteudo?aba=home');
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
    await p.goto(base + '/admin/conteudo?aba=home');
    ok('escolhas continuam salvas no painel', (await p.inputValue('input[name="type[arquivo_text][size]"]')) === '20' && (await p.inputValue('select[name="type[arquivo_text][font]"]')) === 'Lora' && await p.isChecked('input[name="type[arquivo_text][bold]"]'));
    ok('quem não mudou continua em “padrão”', await p.isChecked('input[name="type[arquivo_title][padrao]"]'));
    await p.check('input[name="type[arquivo_text][padrao]"]');
    await submit(p.locator('.a-sticky-save button'));
    await p.goto(base + '/');
    ok('voltar ao padrão tira a fonte própria', (await cs('.split-text > div p', 'fontSize')) === '16px', await cs('.split-text > div p', 'fontSize'));
  });

  area = 'Acervo e História';
  await safe('duas colunas com foto, botão e link próprios', async () => {
    await p.goto(base + '/admin/conteudo?aba=home');
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
    await p.goto(base + '/admin/conteudo?aba=home');
    await p.uncheck('input[name=historia_show]');
    await submit(p.locator('.a-sticky-save button'));
    const h2 = await pub('/');
    ok('esconder só a História', !h2.includes('id="historia"') && h2.includes('id="acervo"') && h2.includes('acervo-cols is-single'));
    await p.goto(base + '/admin/conteudo?aba=home');
    await p.check('input[name=historia_show]');
    await p.fill('input[name=historia_link]', '');
    await submit(p.locator('.a-sticky-save button'));
    ok('sem link, o botão some', !(await pub('/')).includes('LER A HISTÓRIA'));
  });

  area = 'Contato e rodapé';
  await safe('textos e fontes do contato', async () => {
    await p.goto(base + '/admin/conteudo?aba=home');
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
    await p.goto(base + '/admin/conteudo?aba=rodape');
    ok('rodapé: 11 controles de fonte', await p.locator('[data-type-field]').count() === 11);
    await p.fill('input[name=nav_title]', 'Links QA'); await p.fill('input[name=rss_label]', '');
    await p.fill('input[name=copyright]', '© 2024–{ano} QA. Direitos reservados.');
    await p.fill('input[name=author_label]', 'Painel QA');
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
    await p.goto(base + '/admin/conteudo?aba=rodape');
    ok('textos do rodapé continuam salvos', (await p.inputValue('input[name=author_label]')) === 'Painel QA');
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
    await p.goto(base + '/admin/aparencia');
    await p.selectOption('select[name=card_excerpt_lines]', '5');
    await submit(p.locator('.a-sticky-save button:has-text("Salvar")'));
    await p.goto(base + '/blog');
    const h = await p.evaluate(() => { const e = document.querySelector('.card-excerpt'); return e.getBoundingClientRect().height / parseFloat(getComputedStyle(e).lineHeight); });
    ok('com 5 linhas, o card mostra 5 linhas', Math.round(h) === 5, String(h));
  });
  await safe('apagar uma playlist some do site', async () => {
    await p.goto(base + '/admin/conteudo?aba=home');
    await p.fill('input[name="playlists[2][title]"]', '');
    await p.locator('[data-image-field]:has(input[name="playlists[2][image]"]) [data-clear]').click();
    await submit(p.locator('.a-sticky-save button'));
    const home = await pub('/');
    ok('ficam só 2 playlists', (home.match(/class="playlist"/g) || []).length === 2);
  });

  area = 'Geral';
  ok('nenhum erro de JavaScript nas páginas', jsErrors.length === 0, jsErrors.slice(0, 3).join(' | '));
  fs.writeFileSync(shots + '/qa-resultado.json', JSON.stringify(results, null, 1));
  const failed = results.filter(r => !r.pass);
  console.log('TOTAL', results.length, 'PASSOU', results.length - failed.length, 'FALHOU', failed.length);
  failed.forEach(r => console.log('FALHOU [' + r.area + '] ' + r.name + (r.extra ? ' — ' + r.extra : '')));
  await b.close();
})().catch(e => { console.error('ABORTOU', e); process.exit(1); });
