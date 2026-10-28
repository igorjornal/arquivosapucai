// Editor de posts (TinyMCE) com os recursos do Arquivo Sapucaí:
// imagem com formato/tamanho/alinhamento, vídeo do YouTube, botão com cor ao passar o mouse,
// divisórias, espaçador e espaço antes/depois dos blocos.
(function () {
  'use strict';
  if (!window.tinymce || !document.querySelector('[data-editor]')) return;

  var A = window.ARQUIVO_ASSETS;
  var palette = [
    '#20063A', 'Roxo Arquivo', '#CDF50D', 'Verde-limão', '#E31E24', 'Vermelho', '#FFD700', 'Dourado',
    '#1E0D06', 'Marrom', '#E9E6D3', 'Bege', '#000624', 'Azul-marinho', '#FF0000', 'Vermelho vivo',
    '#FFFC00', 'Amarelo', '#007A33', 'Verde', '#7B2FF7', 'Lilás', '#FFDACD', 'Salmão',
    '#000000', 'Preto', '#4A3F3A', 'Cinza escuro', '#9E9E9E', 'Cinza', '#FFFFFF', 'Branco'
  ];
  var ratios = { original: null, banner: '3 / 1', wide: '16 / 9', landscape: '4 / 3', square: '1 / 1', portrait: '4 / 5' };

  function youtubeId(url) {
    var m = String(url).match(/(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/))([A-Za-z0-9_-]{11})/);
    return m ? m[1] : null;
  }

  // size: '100' | '75' | '50' (sozinho, no centro) ou 'r2' | 'r3' | 'r4' (vários por linha)
  function videoHtml(src, size) {
    size = size || '100';
    if (src.indexOf('/') === -1) src = 'https://www.youtube-nocookie.com/embed/' + src;
    var row = size.charAt(0) === 'r';
    return '<div class="video ' + (row ? 'video-row-' + size.charAt(1) : 'video-solo') + '"' +
      (!row && size !== '100' ? ' style="width:' + size + '%"' : '') +
      ' contenteditable="false"><iframe src="' + src + '" title="Vídeo do YouTube" allowfullscreen="allowfullscreen"></iframe></div>';
  }

  // Link de vídeo ou de playlist do YouTube vira o endereço do player
  function youtubeEmbed(url) {
    var list = (String(url).match(/[?&]list=([A-Za-z0-9_-]{10,})/) || [])[1];
    var id = youtubeId(url);
    if (id) return 'https://www.youtube-nocookie.com/embed/' + id + (list ? '?list=' + list : '');
    if (list) return 'https://www.youtube-nocookie.com/embed/videoseries?list=' + list;
    return null;
  }

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; });
  }

  // Se o cursor estiver dentro de uma imagem (ou da legenda), leva para o parágrafo logo depois dela,
  // para o próximo conteúdo não entrar na legenda.
  function moveOutOfFigure(editor) {
    var fig = editor.dom.getParent(editor.selection.getNode(), 'figure');
    if (!fig) return;
    var next = fig.nextElementSibling;
    if (!next || next.nodeName !== 'P') {
      next = editor.dom.create('p', {}, '<br data-mce-bogus="1">');
      editor.dom.insertAfter(next, fig);
    }
    editor.selection.setCursorLocation(next, 0);
  }

  // Insere conteúdo e deixa o cursor num parágrafo novo logo depois
  var insertingBlock = false;
  function insertBlock(editor, html) {
    moveOutOfFigure(editor);
    insertingBlock = true;
    editor.insertContent(html + '<p data-arq-after="1"><br data-mce-bogus="1"></p>');
    var after = editor.getBody().querySelector('p[data-arq-after]');
    if (after) {
      after.removeAttribute('data-arq-after');
      editor.selection.setCursorLocation(after, 0);
    }
    insertingBlock = false;
  }

  /* ---------- Imagem ---------- */
  function imageDialog(editor) {
    var fig = editor.dom.getParent(editor.selection.getNode(), 'figure.image');
    var img = fig ? fig.querySelector('img') : null;
    var cap = fig ? fig.querySelector('figcaption') : null;
    var align = fig ? (['left', 'center', 'right', 'full'].filter(function (a) { return fig.classList.contains('align-' + a); })[0] || 'center') : 'center';
    var widthMatch = fig && fig.style.width ? parseInt(fig.style.width, 10) : 100;
    var ratioKey = 'original';
    if (img && img.style.aspectRatio) {
      Object.keys(ratios).forEach(function (k) { if (ratios[k] && ratios[k].replace(/\s/g, '') === img.style.aspectRatio.replace(/\s/g, '')) ratioKey = k; });
    }
    var data = {
      src: img ? img.getAttribute('src') : '',
      alt: img ? img.getAttribute('alt') || '' : '',
      caption: cap ? cap.textContent : '',
      align: align,
      width: String(widthMatch || 100),
      ratio: ratioKey,
      radius: img && img.style.borderRadius ? String(parseInt(img.style.borderRadius, 10)) : '0',
      position: img && img.style.objectPosition ? img.style.objectPosition : '50% 50%',
      link: fig && fig.querySelector('a') ? fig.querySelector('a').getAttribute('href') : ''
    };

    var dialog = editor.windowManager.open({
      title: fig ? 'Editar imagem' : 'Inserir imagem',
      size: 'medium',
      body: {
        type: 'panel',
        items: [
          { type: 'htmlpanel', html: '<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">' +
            '<button type="button" class="tox-button" data-arq="pick">Escolher da biblioteca / enviar</button>' +
            '<span data-arq="status" style="font-size:13px;color:#555"></span></div>' +
            '<div data-arq="thumb" style="margin-top:10px">' + thumbHtml(data.src) + '</div>' },
          { type: 'input', name: 'src', label: 'Endereço da imagem' },
          { type: 'input', name: 'alt', label: 'Descrição (acessibilidade)' },
          { type: 'input', name: 'caption', label: 'Legenda (opcional)' },
          { type: 'grid', columns: 2, items: [
            { type: 'selectbox', name: 'align', label: 'Alinhamento', items: [
              { value: 'center', text: 'Centro' }, { value: 'left', text: 'Esquerda (texto contorna)' },
              { value: 'right', text: 'Direita (texto contorna)' }, { value: 'full', text: 'Largura toda' }
            ] },
            { type: 'selectbox', name: 'width', label: 'Tamanho', items: [
              '100', '90', '80', '75', '70', '60', '50', '45', '40', '35', '30', '25', '20'
            ].map(function (v) { return { value: v, text: v + '%' }; }) },
            { type: 'selectbox', name: 'ratio', label: 'Formato (recorte)', items: [
              { value: 'original', text: 'Original (sem cortar)' }, { value: 'banner', text: 'Banner fininho (3x1)' },
              { value: 'wide', text: 'Retangular (16x9)' }, { value: 'landscape', text: 'Retangular (4x3)' },
              { value: 'square', text: 'Quadrada' }, { value: 'portrait', text: 'Em pé (4x5)' }
            ] },
            { type: 'selectbox', name: 'position', label: 'Parte que aparece no recorte', items: [
              { value: '50% 50%', text: 'Centro' }, { value: '50% 0%', text: 'Topo' }, { value: '50% 100%', text: 'Base' },
              { value: '0% 50%', text: 'Esquerda' }, { value: '100% 50%', text: 'Direita' },
              { value: '50% 25%', text: 'Meio-alto' }, { value: '50% 75%', text: 'Meio-baixo' }
            ] },
            { type: 'selectbox', name: 'radius', label: 'Cantos', items: [
              { value: '0', text: 'Retos' }, { value: '8', text: 'Levemente arredondados' }, { value: '18', text: 'Arredondados' },
              { value: '32', text: 'Bem arredondados' }, { value: '999', text: 'Redondo (use com quadrada)' }
            ] },
            { type: 'input', name: 'link', label: 'Link ao clicar (opcional)' }
          ] }
        ]
      },
      initialData: data,
      buttons: [
        { type: 'cancel', text: 'Cancelar' },
        { type: 'submit', text: fig ? 'Atualizar' : 'Inserir', primary: true }
      ],
      onSubmit: function (api) {
        var d = api.getData();
        if (!d.src) { editor.notificationManager.open({ text: 'Escolha uma imagem.', type: 'warning', timeout: 3000 }); return; }
        var ratio = ratios[d.ratio];
        var imgStyle = [];
        if (d.radius !== '0') imgStyle.push('border-radius: ' + d.radius + 'px');
        if (ratio) imgStyle.push('aspect-ratio: ' + ratio, 'object-fit: cover', 'object-position: ' + d.position);
        var w = d.align === 'full' ? 100 : parseInt(d.width, 10);
        var imgTag = '<img src="' + esc(d.src) + '" alt="' + esc(d.alt) + '"' + (imgStyle.length ? ' style="' + imgStyle.join('; ') + '"' : '') + '>';
        if (d.link) imgTag = '<a href="' + esc(d.link) + '"' + (/^https?:/.test(d.link) ? ' target="_blank"' : '') + '>' + imgTag + '</a>';
        var html = '<figure class="image align-' + d.align + '" style="width: ' + w + '%">' + imgTag +
          (d.caption ? '<figcaption>' + esc(d.caption) + '</figcaption>' : '') + '</figure>';
        if (fig) {
          editor.undoManager.transact(function () { editor.dom.setOuterHTML(fig, html); });
        } else {
          insertBlock(editor, html);
        }
        api.close();
      }
    });

    // O botão da biblioteca é atendido por um ouvinte único (abaixo), que precisa saber qual janela está aberta
    openImageDialog = dialog;
  }

  function thumbHtml(src) {
    return src ? '<img src="' + esc(src) + '" style="max-height:120px;border-radius:8px">' : '';
  }

  // Funciona mesmo se a janela demorar a aparecer: não depende de achar o botão logo depois de abrir
  var openImageDialog = null;
  document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('.tox-dialog [data-arq="pick"]');
    if (!btn || !openImageDialog) return;
    var dialog = openImageDialog;
    var thumb = btn.closest('.tox-dialog').querySelector('[data-arq="thumb"]');
    window.ArquivoMedia.open(function (item) {
      dialog.setData({ src: item.url, alt: dialog.getData().alt || item.alt || '' });
      if (thumb) thumb.innerHTML = thumbHtml(item.url);
    });
  }, true);

  /* ---------- Vídeo ---------- */
  // Tamanhos: os de "por linha" deixam vários vídeos lado a lado
  var VIDEO_SIZES = [
    { value: '100', text: 'Largura toda' }, { value: '75', text: '75%' }, { value: '50', text: '50% (sozinho)' },
    { value: 'r2', text: '2 por linha (lado a lado)' }, { value: 'r3', text: '3 por linha (lado a lado)' }, { value: 'r4', text: '4 por linha (lado a lado)' }
  ];

  // Vídeo novo (um ou vários links) ou, com um vídeo selecionado, muda o tamanho dele
  function videoDialog(editor) {
    var current = editor.dom.getParent(editor.selection.getNode(), 'div.video');
    var frame = current ? current.querySelector('iframe') : null;
    var size = '100';
    if (current) {
      var row = (current.className.match(/video-row-(\d)/) || [])[1];
      size = row ? 'r' + row : (current.style.width ? String(parseInt(current.style.width, 10)) : '100');
    }
    editor.windowManager.open({
      title: current ? 'Tamanho do vídeo' : 'Vídeo do YouTube',
      body: { type: 'panel', items: (current ? [] : [
        { type: 'textarea', name: 'urls', label: 'Cole o link do vídeo ou da playlist (para vários, um por linha)', placeholder: 'https://www.youtube.com/watch?v=...\nhttps://www.youtube.com/playlist?list=...' },
        { type: 'htmlpanel', html: '<p style="font-size:13px;color:#555">Vale vídeo, playlist, Shorts, transmissão ao vivo ou youtu.be.</p>' }
      ]).concat([
        { type: 'selectbox', name: 'size', label: 'Tamanho', items: VIDEO_SIZES }
      ]) },
      initialData: { urls: '', size: size },
      buttons: [{ type: 'cancel', text: 'Cancelar' }, { type: 'submit', text: current ? 'Salvar' : 'Inserir', primary: true }],
      onSubmit: function (api) {
        var d = api.getData();
        if (current) {
          editor.undoManager.transact(function () {
            current.className = current.className.replace(/\s*video-row-\d|\s*video-solo/g, '') + (d.size.charAt(0) === 'r' ? ' video-row-' + d.size.charAt(1) : ' video-solo');
            current.style.width = d.size.charAt(0) === 'r' || d.size === '100' ? '' : d.size + '%';
            if (!current.getAttribute('style')) current.removeAttribute('style');
          });
          editor.nodeChanged();
          api.close();
          return;
        }
        var srcs = d.urls.split(/\s+/).map(youtubeEmbed).filter(Boolean);
        if (!srcs.length) { editor.notificationManager.open({ text: 'Não reconheci nenhum link do YouTube.', type: 'warning', timeout: 3000 }); return; }
        insertBlock(editor, srcs.map(function (src) { return videoHtml(src, d.size); }).join(''));
        api.close();
      }
    });
  }

  /* ---------- Botão ---------- */
  function buttonDialog(editor) {
    var a = editor.dom.getParent(editor.selection.getNode(), 'a.btn-post');
    var p = a ? editor.dom.getParent(a, 'p') : null;
    var get = function (prop, fallback) {
      if (!a) return fallback;
      var v = a.style.getPropertyValue(prop).trim();
      return v || fallback;
    };
    var data = {
      text: a ? a.textContent : editor.selection.getContent({ format: 'text' }) || '',
      href: a ? a.getAttribute('href') : '',
      newtab: a ? a.getAttribute('target') === '_blank' : false,
      bg: get('--btn-bg', '#E31E24'), color: get('--btn-color', '#FFFFFF'),
      border: get('--btn-border', ''), hbg: get('--btn-hover-bg', '#FFFC00'), hcolor: get('--btn-hover-color', '#000000'),
      radius: get('--btn-radius', '4px').replace('px', ''),
      align: p && p.style.textAlign ? p.style.textAlign : 'center'
    };
    editor.windowManager.open({
      title: a ? 'Editar botão' : 'Inserir botão',
      body: { type: 'panel', items: [
        { type: 'input', name: 'text', label: 'Texto do botão' },
        { type: 'input', name: 'href', label: 'Link', placeholder: 'https://...' },
        { type: 'checkbox', name: 'newtab', label: 'Abrir em nova aba' },
        { type: 'grid', columns: 2, items: [
          { type: 'colorinput', name: 'bg', label: 'Cor de fundo' },
          { type: 'colorinput', name: 'color', label: 'Cor do texto' },
          { type: 'colorinput', name: 'hbg', label: 'Fundo ao passar o mouse' },
          { type: 'colorinput', name: 'hcolor', label: 'Texto ao passar o mouse' },
          { type: 'colorinput', name: 'border', label: 'Cor da borda (opcional)' },
          { type: 'selectbox', name: 'radius', label: 'Cantos', items: [
            { value: '0', text: 'Retos' }, { value: '4', text: 'Levemente arredondados' }, { value: '12', text: 'Arredondados' }, { value: '999', text: 'Pílula' }
          ] },
          { type: 'selectbox', name: 'align', label: 'Posição', items: [
            { value: 'left', text: 'Esquerda' }, { value: 'center', text: 'Centro' }, { value: 'right', text: 'Direita' }
          ] }
        ] }
      ] },
      initialData: data,
      buttons: [{ type: 'cancel', text: 'Cancelar' }, { type: 'submit', text: a ? 'Atualizar' : 'Inserir', primary: true }],
      onSubmit: function (api) {
        var d = api.getData();
        if (!d.text || !d.href) { editor.notificationManager.open({ text: 'Preencha o texto e o link.', type: 'warning', timeout: 3000 }); return; }
        var style = ['--btn-bg: ' + d.bg, '--btn-color: ' + d.color, '--btn-hover-bg: ' + d.hbg, '--btn-hover-color: ' + d.hcolor, '--btn-radius: ' + d.radius + 'px'];
        if (d.border) style.push('--btn-border: ' + d.border);
        var html = '<p style="text-align: ' + d.align + '"><a class="btn-post" href="' + esc(d.href) + '"' + (d.newtab ? ' target="_blank"' : '') +
          ' style="' + style.join('; ') + '">' + esc(d.text) + '</a></p>';
        if (a && p) {
          editor.undoManager.transact(function () { editor.dom.setOuterHTML(p, html); });
        } else {
          insertBlock(editor, html);
        }
        api.close();
      }
    });
  }

  /* ---------- Espaços ---------- */
  function spacerDialog(editor) {
    editor.windowManager.open({
      title: 'Inserir espaço',
      body: { type: 'panel', items: [
        { type: 'selectbox', name: 'h', label: 'Altura do espaço', items: ['10', '20', '30', '40', '60', '80', '100', '140'].map(function (v) { return { value: v, text: v + ' pixels' }; }) }
      ] },
      initialData: { h: '40' },
      buttons: [{ type: 'cancel', text: 'Cancelar' }, { type: 'submit', text: 'Inserir', primary: true }],
      onSubmit: function (api) {
        insertBlock(editor, '<div class="spacer" style="height: ' + api.getData().h + 'px"></div>');
        api.close();
      }
    });
  }

  function setBlockSpacing(editor, side, px) {
    var blocks = editor.selection.getSelectedBlocks();
    if (!blocks.length) blocks = [editor.dom.getParent(editor.selection.getNode(), 'p,h1,h2,h3,h4,h5,h6,figure,table,ul,ol,div')];
    editor.undoManager.transact(function () {
      blocks.forEach(function (b) {
        if (!b) return;
        var target = editor.dom.getParent(b, 'figure,table') || b;
        if (side === 'both' || side === 'top') editor.dom.setStyle(target, 'margin-top', px === null ? '' : px + 'px');
        if (side === 'both' || side === 'bottom') editor.dom.setStyle(target, 'margin-bottom', px === null ? '' : px + 'px');
      });
    });
    editor.nodeChanged();
  }

  tinymce.init({
    selector: '[data-editor]',
    license_key: 'gpl',
    language: 'pt_BR',
    language_url: '/assets/vendor/tinymce/langs/pt_BR.js',
    promotion: false,
    branding: false,
    menubar: 'edit insert format table view',
    plugins: 'lists advlist link image table code autolink wordcount fullscreen charmap searchreplace visualblocks quickbars autoresize',
    toolbar_mode: 'wrap',
    toolbar: [
      'undo redo | blocks fontsizeinput lineheight | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify',
      'bullist numlist | link arqimagem arqvideo arqbotao | arqdivisor arqespaco arqmargem | table | removeformat code fullscreen'
    ],
    quickbars_selection_toolbar: 'bold italic underline | forecolor backcolor | fontsizeinput | link',
    quickbars_insert_toolbar: false,
    min_height: 520,
    max_height: 1400,
    autoresize_bottom_margin: 40,
    font_size_input_default_unit: 'px',
    line_height_formats: '1 1.2 1.4 1.6 1.8 2 2.4 3',
    block_formats: 'Parágrafo=p; Título grande=h2; Título médio=h3; Título pequeno=h4; Subtítulo=h5; Citação=blockquote',
    color_map: palette,
    color_cols: 5,
    custom_colors: true,
    link_default_target: '_blank',
    link_assume_external_targets: 'https',
    link_title: false,
    table_advtab: true,
    table_cell_advtab: true,
    table_row_advtab: true,
    table_appearance_options: true,
    table_default_styles: { 'border-collapse': 'collapse', width: '100%' },
    table_default_attributes: {},
    table_border_widths: [{ title: 'Nenhuma', value: '0px' }, { title: 'Fina', value: '1px' }, { title: 'Média', value: '2px' }, { title: 'Grossa', value: '4px' }],
    table_border_styles: [{ title: 'Sólida', value: 'solid' }, { title: 'Tracejada', value: 'dashed' }, { title: 'Pontilhada', value: 'dotted' }, { title: 'Dupla', value: 'double' }, { title: 'Nenhuma', value: 'none' }],
    image_caption: true,
    object_resizing: false,
    sandbox_iframes: false,
    convert_unsafe_embeds: false,
    extended_valid_elements: 'iframe[src|title|width|height|allow|allowfullscreen|frameborder|loading],div[class|style|contenteditable],figure[class|style],figcaption,a[href|target|rel|class|style|title]',
    valid_children: '+a[img],+figure[a]',
    entity_encoding: 'raw',
    relative_urls: false,
    convert_urls: false,
    body_class: 'post-content',
    content_css: [A.fonts, A.site],
    content_style: A.vars + ' body{background:var(--c-post-box-bg);color:var(--c-post-text);margin:24px 28px;max-width:none}' +
      ' .spacer{background:repeating-linear-gradient(45deg,#f2efe4,#f2efe4 6px,#fff 6px,#fff 12px);outline:1px dashed #c9c4ad}' +
      ' .video{outline:3px dashed transparent} .video[data-mce-selected]{outline-color:#CDF50D}' +
      ' figure.image[data-mce-selected]{outline:3px solid #CDF50D}',
    paste_preprocess: function (editor, args) {
      // Link do YouTube colado sozinho vira vídeo
      var text = args.content.replace(/<[^>]+>/g, '').trim();
      var id = /^https?:\/\/\S+$/.test(text) ? youtubeId(text) : null;
      if (id) args.content = videoHtml(id);
    },
    images_upload_handler: function (blobInfo) {
      var file = new File([blobInfo.blob()], blobInfo.filename(), { type: blobInfo.blob().type });
      return window.ArquivoMedia.upload(file).then(function (item) { return item.url; });
    },
    setup: function (editor) {
      editor.ui.registry.addIcon('arq-video', '<svg width="24" height="24" viewBox="0 0 24 24"><path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4a2.5 2.5 0 0 0-1.8 1.8C2 8.8 2 12 2 12s0 3.2.4 4.8a2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8c.4-1.6.4-4.8.4-4.8s0-3.2-.4-4.8zM10 15V9l5.2 3z"/></svg>');
      editor.ui.registry.addIcon('arq-button', '<svg width="24" height="24" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="10" rx="3" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8 12h8" stroke="currentColor" stroke-width="2"/></svg>');
      editor.ui.registry.addIcon('arq-spacer', '<svg width="24" height="24" viewBox="0 0 24 24"><path d="M12 3v6M9 6l3-3 3 3M12 21v-6M9 18l3 3 3-3M4 12h16" fill="none" stroke="currentColor" stroke-width="2"/></svg>');
      editor.ui.registry.addIcon('arq-margin', '<svg width="24" height="24" viewBox="0 0 24 24"><path d="M4 4h16M4 20h16" stroke="currentColor" stroke-width="2"/><rect x="6" y="8" width="12" height="8" rx="1" fill="none" stroke="currentColor" stroke-width="2"/></svg>');

      editor.ui.registry.addButton('arqimagem', { icon: 'image', tooltip: 'Imagem (formato, tamanho, alinhamento)', onAction: function () { imageDialog(editor); } });
      editor.ui.registry.addButton('arqvideo', { icon: 'arq-video', tooltip: 'Vídeo do YouTube', onAction: function () { videoDialog(editor); } });
      editor.ui.registry.addButton('arqbotao', { icon: 'arq-button', tooltip: 'Botão colorido', onAction: function () { buttonDialog(editor); } });
      editor.ui.registry.addButton('arqespaco', { icon: 'arq-spacer', tooltip: 'Inserir espaço', onAction: function () { spacerDialog(editor); } });

      editor.ui.registry.addMenuButton('arqdivisor', {
        icon: 'horizontal-rule',
        tooltip: 'Linha divisória',
        fetch: function (cb) {
          var styles = [['', 'Simples'], ['divider-dashed', 'Tracejada'], ['divider-dotted', 'Pontilhada'], ['divider-double', 'Dupla'],
            ['divider-short', 'Simples curta'], ['divider-dashed divider-short', 'Tracejada curta']];
          cb(styles.map(function (s) {
            return { type: 'menuitem', text: s[1], onAction: function () { insertBlock(editor, '<hr' + (s[0] ? ' class="' + s[0] + '"' : '') + '>'); } };
          }));
        }
      });

      editor.ui.registry.addMenuButton('arqmargem', {
        icon: 'arq-margin',
        tooltip: 'Espaço antes e depois do bloco',
        fetch: function (cb) {
          var sizes = [0, 8, 16, 24, 40, 64];
          var sub = function (side) {
            return sizes.map(function (px) {
              return { type: 'menuitem', text: px + ' px', onAction: function () { setBlockSpacing(editor, side, px); } };
            });
          };
          cb([
            { type: 'nestedmenuitem', text: 'Espaço antes', getSubmenuItems: function () { return sub('top'); } },
            { type: 'nestedmenuitem', text: 'Espaço depois', getSubmenuItems: function () { return sub('bottom'); } },
            { type: 'nestedmenuitem', text: 'Antes e depois', getSubmenuItems: function () { return sub('both'); } },
            { type: 'menuitem', text: 'Voltar ao padrão', onAction: function () { setBlockSpacing(editor, 'both', null); } }
          ]);
        }
      });

      // Tabela ou qualquer comando de inserir não pode cair dentro da legenda da imagem
      editor.on('BeforeExecCommand', function (e) {
        if (/^(mceInsertTable|mceInsertContent|InsertHorizontalRule|InsertUnorderedList|InsertOrderedList)$/.test(e.command) && !insertingBlock) {
          var fig = editor.dom.getParent(editor.selection.getNode(), 'figcaption');
          if (fig) moveOutOfFigure(editor);
        }
      });

      // Duplo clique numa imagem ou botão abre a janela de edição
      editor.on('dblclick', function (e) {
        if (editor.dom.getParent(e.target, 'figure.image') || e.target.nodeName === 'IMG') imageDialog(editor);
        else if (editor.dom.getParent(e.target, 'a.btn-post')) buttonDialog(editor);
      });
      editor.ui.registry.addContextToolbar('arq-figure', {
        predicate: function (node) { return !!editor.dom.getParent(node, 'figure.image'); },
        items: 'arqimagem',
        position: 'node',
        scope: 'node'
      });
    }
  });
})();
