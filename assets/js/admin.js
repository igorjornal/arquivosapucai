// Painel do Arquivo Sapucaí: seletor de imagens, confirmações e pequenos ajustes
(function () {
  'use strict';

  var csrf = document.querySelector('meta[name="csrf"]');
  window.ARQUIVO_CSRF = csrf ? csrf.content : '';

  // Menu lateral no celular
  var burger = document.querySelector('.a-burger');
  var side = document.getElementById('a-side');
  if (burger && side) {
    burger.addEventListener('click', function () {
      var open = side.classList.toggle('is-open');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.addEventListener('click', function (e) {
      if (side.classList.contains('is-open') && !side.contains(e.target) && e.target !== burger) {
        side.classList.remove('is-open');
      }
    });
  }

  // Pede confirmação antes de excluir
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-confirm]');
    if (btn && !window.confirm(btn.dataset.confirm)) {
      e.preventDefault();
    }
  });

  // Mostra/esconde um bloco (checkbox ou botão)
  document.querySelectorAll('[data-toggle-target]').forEach(function (el) {
    var target = document.querySelector(el.dataset.toggleTarget);
    if (!target) return;
    el.addEventListener(el.type === 'checkbox' ? 'change' : 'click', function () {
      target.hidden = el.type === 'checkbox' ? !el.checked : !target.hidden;
    });
  });

  // Copiar texto
  document.querySelectorAll('[data-copy-text]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var text = new URL(btn.dataset.copyText, location.origin).href;
      navigator.clipboard.writeText(text).then(function () {
        var old = btn.textContent;
        btn.textContent = 'Copiado!';
        setTimeout(function () { btn.textContent = old; }, 1500);
      });
    });
  });

  // Arrastar arquivos para a área de envio
  document.querySelectorAll('[data-drop]').forEach(function (drop) {
    ['dragenter', 'dragover'].forEach(function (ev) {
      drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
      drop.addEventListener(ev, function () { drop.classList.remove('is-over'); });
    });
    drop.addEventListener('drop', function (e) {
      e.preventDefault();
      var input = drop.querySelector('input[type=file]');
      input.files = e.dataTransfer.files;
      input.form.submit();
    });
  });

  // Código da cor ao lado do seletor
  document.querySelectorAll('[data-color]').forEach(function (input) {
    var code = input.parentElement.querySelector('[data-color-code]');
    input.addEventListener('input', function () { code.textContent = input.value.toUpperCase(); });
  });

  // Número ao lado das réguas
  document.querySelectorAll('[data-range]').forEach(function (r) {
    var out = r.closest('label').querySelector('[data-range-out]');
    r.addEventListener('input', function () {
      out.textContent = r.value;
      var sample = r.closest('fieldset') && r.closest('fieldset').querySelector('[data-size-sample]');
      if (sample) sample.style.fontSize = Math.min(r.value, 40) + 'px';
    });
  });

  // Amostra de fonte na Aparência
  var loadedFonts = {};
  // Larguras da Archivo (Condensed, Expanded…) são a própria Archivo com outra largura
  var widths = { 'Archivo Extra Condensed': '62.5%', 'Archivo Condensed': '75%', 'Archivo Semi Condensed': '87.5%', 'Archivo Semi Expanded': '112.5%', 'Archivo Expanded': '125%' };
  function realFamily(name) { return widths[name] ? 'Archivo' : name; }
  function loadFont(name) {
    var family = realFamily(name);
    if (['Syne', 'Bitter', 'Instrument Sans'].indexOf(family) !== -1 || loadedFonts[family]) return;
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = family === 'Archivo'
      ? 'https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900&display=swap'
      : 'https://fonts.googleapis.com/css2?family=' + family.replace(/ /g, '+') + ':wght@300;400;600;700;800&display=swap';
    document.head.appendChild(link);
    loadedFonts[family] = true;
  }
  document.querySelectorAll('[data-font-select]').forEach(function (select) {
    var sample = select.closest('label').nextElementSibling;
    if (!sample || !sample.classList.contains('a-font-sample')) return;
    loadFont(select.value); // a amostra já abre com a fonte escolhida
    select.addEventListener('change', function () {
      loadFont(select.value);
      sample.style.fontFamily = "'" + realFamily(select.value) + "'";
      sample.style.fontStretch = widths[select.value] || '100%';
    });
  });

  // Fonte de uma parte do site: “usar o padrão” esconde as escolhas; negrito muda a amostra
  document.querySelectorAll('[data-type-field]').forEach(function (box) {
    var def = box.querySelector('[data-type-default]');
    var weight = box.querySelector('[data-type-weight]');
    var sample = box.querySelector('[data-size-sample]');
    def.addEventListener('change', function () {
      box.querySelector('[data-type-custom]').hidden = def.checked;
      box.querySelector('[data-type-summary]').hidden = !def.checked;
    });
    weight.addEventListener('change', function () { sample.style.fontWeight = weight.value; });
    var italic = box.querySelector('[data-type-italic]');
    if (italic) italic.addEventListener('change', function () { sample.style.fontStyle = italic.checked ? 'italic' : 'normal'; });
  });
  document.querySelectorAll('[data-base-weight]').forEach(function (sel) {
    sel.addEventListener('change', function () { sel.closest('fieldset').querySelector('[data-size-sample]').style.fontWeight = sel.value; });
  });

  // Mais um aviso na faixa corrida
  var addTicker = document.querySelector('[data-add-ticker]');
  if (addTicker) {
    addTicker.addEventListener('click', function () {
      var list = document.querySelector('[data-ticker-list]');
      var rows = list.querySelectorAll('.a-ticker-row');
      var clone = rows[rows.length - 1].cloneNode(true);
      var n = rows.length;
      clone.querySelectorAll('input').forEach(function (input) {
        input.name = input.name.replace(/\[\d+\]/, '[' + n + ']');
        if (input.type === 'checkbox') { input.checked = false; } else { input.value = ''; }
      });
      clone.querySelector('label').firstChild.textContent = 'Aviso ' + (n + 1);
      list.appendChild(clone);
      clone.querySelector('input').focus();
    });
  }

  /* ---------- Não perder o que não foi salvo ---------- */
  // Vale para todo formulário com campos de verdade (não para botões soltos como “Excluir”)
  var dirtyForms = [];
  var leaving = false;
  document.querySelectorAll('form[method=post]').forEach(function (f) {
    var fields = f.querySelectorAll('input:not([type=hidden]):not([type=file]), textarea, select');
    if (!fields.length || f.closest('.a-modal') || f.hasAttribute('data-no-protect')) return;
    f.addEventListener('input', function () { if (dirtyForms.indexOf(f) === -1) dirtyForms.push(f); });
    f.addEventListener('change', function () { if (dirtyForms.indexOf(f) === -1) dirtyForms.push(f); });
  });
  function hasUnsaved() {
    var editorDirty = window.tinymce && tinymce.activeEditor && tinymce.activeEditor.isDirty();
    return !leaving && (dirtyForms.length > 0 || editorDirty);
  }
  window.addEventListener('beforeunload', function (e) {
    if (hasUnsaved()) { e.preventDefault(); e.returnValue = ''; }
  });
  // Clique em link do painel: aviso em português antes de sair
  document.addEventListener('click', function (e) {
    var a = e.target.closest('a[href]');
    if (!a || a.target === '_blank' || a.getAttribute('href').charAt(0) === '#' || e.defaultPrevented) return;
    if (hasUnsaved()) {
      if (window.confirm('Você tem alterações que ainda não foram salvas.\n\nQuer realmente sair desta tela e perder essas alterações?')) {
        leaving = true;
      } else {
        e.preventDefault();
      }
    }
  });

  /* ---------- “Salvando…” ao enviar ---------- */
  document.addEventListener('submit', function (e) {
    var f = e.target;
    leaving = true;
    if (window.tinymce && tinymce.activeEditor) tinymce.activeEditor.setDirty(false);
    var btn = e.submitter || f.querySelector('button[type=submit], button:not([type])');
    if (!btn || f.closest('.a-modal')) return;
    var label = btn.textContent.trim();
    var busy = /excluir|apagar|remover|×/i.test(label) ? 'Excluindo…' : (/entrar/i.test(label) ? 'Entrando…' : 'Salvando…');
    // Desativa depois que o envio começou, para o botão clicado ainda contar
    setTimeout(function () {
      btn.dataset.label = btn.innerHTML;
      btn.classList.add('is-busy');
      btn.innerHTML = '<span class="a-spin" aria-hidden="true"></span> ' + busy;
      f.querySelectorAll('button').forEach(function (b) { b.disabled = true; });
      document.body.classList.add('is-saving');
    }, 0);
  });
  // Botão “voltar” do navegador traz a página com os botões travados: destrava
  window.addEventListener('pageshow', function () {
    leaving = false;
    document.body.classList.remove('is-saving');
    document.querySelectorAll('button.is-busy').forEach(function (b) { b.innerHTML = b.dataset.label; b.classList.remove('is-busy'); });
    document.querySelectorAll('form button[disabled]').forEach(function (b) { if (!b.hasAttribute('data-keep-disabled')) b.disabled = false; });
  });

  // Aviso de “salvo” some sozinho depois de alguns segundos
  document.querySelectorAll('.a-toast').forEach(function (t) {
    setTimeout(function () { t.classList.add('is-hiding'); }, 4500);
    t.querySelector('button').addEventListener('click', function () { t.remove(); });
  });

  // Marcar imagens para mover de pasta
  var checks = document.querySelectorAll('[data-media-check]');
  var countEl = document.querySelector('[data-selected-count]');
  var updateCount = function () {
    var n = document.querySelectorAll('[data-media-check]:checked').length;
    if (countEl) countEl.textContent = n + (n === 1 ? ' marcada' : ' marcadas');
  };
  checks.forEach(function (c) { c.addEventListener('change', updateCount); });
  var all = document.querySelector('[data-select-all]');
  if (all) all.addEventListener('change', function () { checks.forEach(function (c) { c.checked = all.checked; }); updateCount(); });

  /* ---------- Biblioteca de imagens (janela) ---------- */
  var modal = document.getElementById('media-modal');
  var grid = modal && modal.querySelector('[data-media-grid]');
  var moreBtn = modal && modal.querySelector('[data-media-more]');
  var statusEl = modal && modal.querySelector('[data-media-status]');
  var page = 1;
  var onPick = null;
  var folderSel = modal && modal.querySelector('[data-media-folder]');
  if (folderSel) folderSel.addEventListener('change', function () { loadMedia(true); });

  function loadMedia(reset) {
    if (reset) { page = 1; grid.innerHTML = ''; }
    fetch('/admin/midia/lista?pagina=' + page + '&pasta=' + (folderSel.value || 0), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (folderSel.options.length <= 2) {
          (data.folders || []).forEach(function (f) {
            var o = document.createElement('option');
            o.value = f.id; o.textContent = '📁 ' + f.name;
            folderSel.appendChild(o);
          });
        }
        data.items.forEach(addThumb);
        moreBtn.hidden = !data.more;
        if (!data.items.length && page === 1) grid.innerHTML = '<p class="a-muted">Nenhuma imagem ainda. Envie a primeira.</p>';
      });
  }

  function addThumb(item, prepend) {
    var b = document.createElement('button');
    b.type = 'button';
    b.title = item.original_name || '';
    b.innerHTML = '<img loading="lazy" alt="">';
    b.querySelector('img').src = item.url;
    b.addEventListener('click', function () { choose(item); });
    if (prepend) grid.prepend(b); else grid.appendChild(b);
  }

  function choose(item) {
    var cb = onPick;
    closeModal();
    if (cb) cb(item);
  }

  window.ArquivoMedia = {
    open: function (callback) {
      onPick = callback;
      modal.hidden = false;
      document.body.style.overflow = 'hidden';
      loadMedia(true);
    },
    upload: uploadFile
  };

  function closeModal() {
    modal.hidden = true;
    document.body.style.overflow = '';
    onPick = null;
  }

  function uploadFile(file) {
    var fd = new FormData();
    fd.append('arquivo', file);
    fd.append('_csrf', window.ARQUIVO_CSRF);
    if (folderSel && folderSel.value > 0) fd.append('folder_id', folderSel.value);
    return fetch('/admin/midia/enviar', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.error) throw new Error(data.error);
        return data;
      });
  }

  if (modal) {
    modal.addEventListener('click', function (e) {
      if (e.target === modal || e.target.closest('[data-close]')) closeModal();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.hidden) closeModal();
    });
    moreBtn.addEventListener('click', function () { page++; loadMedia(false); });
    modal.querySelector('[data-media-upload]').addEventListener('change', function (e) {
      var file = e.target.files[0];
      if (!file) return;
      statusEl.textContent = 'Enviando…';
      uploadFile(file).then(function (item) {
        statusEl.textContent = 'Enviada!';
        choose(item);
      }).catch(function (err) {
        statusEl.textContent = err.message;
      });
      e.target.value = '';
    });
  }

  // Campos de imagem dos formulários
  document.querySelectorAll('[data-image-field]').forEach(function (field) {
    var value = field.querySelector('[data-value]');
    var preview = field.querySelector('[data-preview]');
    var clear = field.querySelector('[data-clear]');
    field.querySelector('[data-pick]').addEventListener('click', function () {
      window.ArquivoMedia.open(function (item) {
        value.value = item.path;
        preview.querySelector('img').src = item.url;
        preview.hidden = false;
        clear.hidden = false;
        value.dispatchEvent(new Event('change', { bubbles: true }));
      });
    });
    clear.addEventListener('click', function () {
      value.value = '';
      preview.hidden = true;
      clear.hidden = true;
      value.dispatchEvent(new Event('change', { bubbles: true }));
    });
  });

  /* ---------- Tela do post: capa, endereço e recorte ---------- */
  var form = document.querySelector('[data-post-form]');
  if (form) {
    var title = form.querySelector('[data-title]');
    var slug = form.querySelector('[data-slug]');
    var slugTouched = slug.value !== '';
    slug.addEventListener('input', function () { slugTouched = true; });
    title.addEventListener('input', function () {
      if (!slugTouched) {
        slug.value = title.value.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase()
          .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
      }
    });

    var cover = form.querySelector('[data-cover]');
    if (cover) setupCover(cover);
  }

  // Seção com dois formatos: mostra só os campos do formato escolhido
  document.querySelectorAll('[data-layout-switch]').forEach(function (box) {
    var show = function () {
      var v = (box.querySelector('input[name$="_layout"]:checked') || {}).value;
      box.querySelectorAll('[data-layout-only]').forEach(function (el) { el.hidden = el.dataset.layoutOnly !== v; });
    };
    box.querySelectorAll('input[name$="_layout"]').forEach(function (r) { r.addEventListener('change', show); });
    show();
  });

  // Itens do menu (tela Cabeçalho e menu): subir, descer, remover e adicionar; vale depois de salvar
  var menuRows = document.querySelector('[data-menu-rows]');
  if (menuRows) {
    var renumber = function () {
      menuRows.querySelectorAll('[data-menu-row]').forEach(function (row, i) {
        row.querySelectorAll('[name^="menu["]').forEach(function (el) { el.name = el.name.replace(/^menu\[\d+\]/, 'menu[' + i + ']'); });
      });
    };
    var changed = function () { menuRows.dispatchEvent(new Event('change', { bubbles: true })); };
    menuRows.addEventListener('click', function (e) {
      var btn = e.target.closest('button');
      if (!btn) return;
      var row = btn.closest('[data-menu-row]');
      if (btn.dataset.move === 'up' && row.previousElementSibling) row.parentNode.insertBefore(row, row.previousElementSibling);
      if (btn.dataset.move === 'down' && row.nextElementSibling) row.parentNode.insertBefore(row.nextElementSibling, row);
      if (btn.hasAttribute('data-remove-row')) {
        var rm = row.querySelector('[data-remove]');
        rm.value = rm.value ? '' : '1';
        row.classList.toggle('is-removed', rm.value === '1');
        btn.title = rm.value ? 'Desfazer' : 'Remover';
      }
      renumber(); changed();
    });
    document.querySelector('[data-add-menu]').addEventListener('click', function () {
      var n = menuRows.querySelectorAll('[data-menu-row]').length;
      var row = document.createElement('div');
      row.className = 'a-menu-row';
      row.setAttribute('data-menu-row', '');
      row.innerHTML = '<input type="hidden" name="menu[' + n + '][id]" value="0"><input type="hidden" name="menu[' + n + '][remove]" value="" data-remove>' +
        '<label><span class="sr">Nome</span><input name="menu[' + n + '][label]" placeholder="Nome"></label>' +
        '<label><span class="sr">Endereço</span><input name="menu[' + n + '][url]" placeholder="Endereço" list="menu-sugestoes"></label>' +
        '<label class="a-check"><input type="checkbox" name="menu[' + n + '][new_tab]" value="1"> Nova aba</label>' +
        '<div class="a-order"><button type="button" class="a-icon-btn" title="Subir" data-move="up">↑</button><button type="button" class="a-icon-btn" title="Descer" data-move="down">↓</button><button type="button" class="a-icon-btn a-danger-text" title="Remover" data-remove-row>×</button></div>';
      menuRows.appendChild(row);
      row.querySelector('input[name$="[label]"]').focus();
      changed();
    });
  }

  // Faixa de avisos: mostra o campo do separador escolhido (texto ou imagem)
  var sepSel = document.querySelector('[data-sep-select]');
  if (sepSel) {
    var showSep = function () {
      document.querySelectorAll('[data-sep-only]').forEach(function (el) { el.hidden = el.dataset.sepOnly !== sepSel.value; });
    };
    sepSel.addEventListener('change', showSep);
    showSep();
  }

  // Tela do post: posts relacionados. "Escolher posts" abre a janela com caixinhas; a lista ainda ordena e tira
  var rel = document.querySelector('[data-related]');
  if (rel) {
    var relList = rel.querySelector('[data-rel-list]');
    var relDialog = rel.querySelector('[data-rel-dialog]');
    var relPick = rel.querySelector('[data-rel-pick]');
    var relSearch = rel.querySelector('[data-rel-search]');
    var relCount = rel.querySelector('[data-rel-count]');
    var relOpen = rel.querySelector('[data-rel-open]');
    var picked = [];   // [{id, title}] na ordem em que foram marcados
    var relTimer = null;
    var MAX = 6;
    var addRow = function (p) {
      var li = document.createElement('li');
      li.dataset.id = p.id;
      li.innerHTML = '<input type="hidden" name="related[]"><span></span><button type="button" class="a-mini" data-rel-up aria-label="Subir">↑</button><button type="button" class="a-mini" data-rel-down aria-label="Descer">↓</button><button type="button" class="a-mini" data-rel-remove aria-label="Tirar">×</button>';
      li.querySelector('input').value = p.id;
      li.querySelector('span').textContent = p.title;
      relList.appendChild(li);
    };
    var countText = function () { relCount.textContent = picked.length + ' de ' + MAX + ' escolhidos'; };
    var isPicked = function (id) { return picked.some(function (p) { return p.id === id; }); };
    var load = function () {
      relPick.innerHTML = '<li class="a-muted">Carregando…</li>';
      fetch('/admin/posts/escolher?sem=' + relOpen.dataset.exclude + '&q=' + encodeURIComponent(relSearch.value.trim()), { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (rows) {
          relPick.innerHTML = '';
          if (!rows.length) { relPick.innerHTML = '<li class="a-muted">Nenhum post publicado encontrado.</li>'; return; }
          rows.forEach(function (p) {
            var li = document.createElement('li');
            li.innerHTML = '<label><input type="checkbox"><span class="a-rel-thumb"></span><span class="a-rel-info"><strong></strong><small></small></span></label>';
            var box = li.querySelector('input');
            box.checked = isPicked(p.id);
            li.querySelector('strong').textContent = p.title;
            li.querySelector('small').textContent = p.date;
            if (p.thumb) { var im = document.createElement('img'); im.src = p.thumb; im.alt = ''; im.loading = 'lazy'; li.querySelector('.a-rel-thumb').appendChild(im); }
            box.addEventListener('change', function () {
              if (box.checked) {
                if (picked.length >= MAX) { box.checked = false; alert('Dá para escolher até ' + MAX + ' posts.'); return; }
                picked.push({ id: p.id, title: p.title });
              } else {
                picked = picked.filter(function (x) { return x.id !== p.id; });
              }
              countText();
            });
            relPick.appendChild(li);
          });
        });
    };
    relOpen.addEventListener('click', function () {
      picked = [].map.call(relList.children, function (li) { return { id: +li.dataset.id, title: li.querySelector('span').textContent }; });
      relSearch.value = '';
      countText();
      relDialog.showModal();
      load();
    });
    relSearch.addEventListener('input', function () { clearTimeout(relTimer); relTimer = setTimeout(load, 300); });
    relSearch.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
    rel.querySelector('[data-rel-close]').addEventListener('click', function () { relDialog.close(); });
    rel.querySelector('[data-rel-ok]').addEventListener('click', function () {
      relList.innerHTML = '';
      picked.forEach(addRow);
      relDialog.close();
      relList.dispatchEvent(new Event('change', { bubbles: true }));
    });
    relList.addEventListener('click', function (e) {
      var li = e.target.closest('li');
      if (!li) return;
      if (e.target.closest('[data-rel-remove]')) li.remove();
      else if (e.target.closest('[data-rel-up]') && li.previousElementSibling) relList.insertBefore(li, li.previousElementSibling);
      else if (e.target.closest('[data-rel-down]') && li.nextElementSibling) relList.insertBefore(li.nextElementSibling, li);
    });
  }

  // Tela do post: tags em botõezinhos (as mais usadas primeiro), filtradas pelo que se digita,
  // e "Sugeridas pelo texto": só tags que já existem e aparecem no título ou no texto. Nenhuma tag é criada sozinha.
  var tagsBox = document.querySelector('[data-tags]');
  if (tagsBox) {
    var tagInput = tagsBox.querySelector('[data-tag-input]');
    var allTags = JSON.parse(tagsBox.querySelector('[data-tag-list]').textContent || '[]');
    var tagAll = tagsBox.querySelector('[data-tag-all]');
    var sugBox = tagsBox.querySelector('[data-tag-suggest]');
    var sugList = tagsBox.querySelector('[data-tag-suggest-list]');
    var fold = function (t) { return t.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim(); };
    var parts = function () { return tagInput.value.split(',').map(function (t) { return t.trim(); }); };
    var current = function () { return parts().filter(Boolean).map(fold); };
    var typing = function () { var p = parts(); return /,\s*$/.test(tagInput.value) ? '' : fold(p[p.length - 1] || ''); };
    var toggleTag = function (name) {
      var list = parts().filter(Boolean);
      var i = list.map(fold).indexOf(fold(name));
      if (i >= 0) list.splice(i, 1);
      else {
        // Se estava digitando o começo dessa tag, troca o pedaço pela tag inteira
        var t = typing();
        if (t && fold(name).indexOf(t) === 0 && list.length && fold(list[list.length - 1]) === t) list.pop();
        list.push(name);
      }
      tagInput.value = list.join(', ');
      tagInput.dispatchEvent(new Event('input', { bubbles: true }));
    };
    var chip = function (name, on) {
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'a-tag-chip' + (on ? ' is-on' : ''); b.textContent = (on ? '✓ ' : '') + name;
      b.addEventListener('click', function () { toggleTag(name); });
      return b;
    };
    var postText = function () {
      var ed = window.tinymce && tinymce.activeEditor;
      var body = ed ? ed.getContent({ format: 'text' }) : '';
      var title = document.querySelector('[data-title]');
      return fold((title ? title.value : '') + ' ' + body);
    };
    var hasWord = function (text, tag) {
      var w = fold(tag).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      return w !== '' && new RegExp('(^|[^a-z0-9])' + w + '([^a-z0-9]|$)').test(text);
    };
    var drawTags = function () {
      var cur = current(), t = typing();
      tagAll.innerHTML = '';
      allTags.filter(function (n) { return !t || cur.indexOf(fold(n)) >= 0 || fold(n).indexOf(t) >= 0; })
        .forEach(function (n) { tagAll.appendChild(chip(n, cur.indexOf(fold(n)) >= 0)); });
      if (!allTags.length) tagAll.innerHTML = '<span class="a-muted">Nenhuma tag criada ainda.</span>';
      var text = postText();
      var sug = allTags.filter(function (n) { return cur.indexOf(fold(n)) < 0 && hasWord(text, n); });
      sugList.innerHTML = '';
      sug.forEach(function (n) { sugList.appendChild(chip(n, false)); });
      sugBox.hidden = !sug.length;
    };
    tagInput.addEventListener('input', drawTags);
    var titleField = document.querySelector('[data-title]');
    if (titleField) titleField.addEventListener('change', drawTags);
    // O texto do editor chega depois: confere de tempos em tempos (só quando muda)
    var lastText = '';
    setInterval(function () { var t = postText(); if (t !== lastText) { lastText = t; drawTags(); } }, 2000);
    drawTags();
  }

  // Página de Playlists: décadas e playlists (adicionar, mover e tirar)
  var plForm = document.querySelector('[data-pl-form]');
  if (plForm) {
    var plDecades = plForm.querySelector('[data-pl-decades]');
    var plSeq = Date.now();
    var fromTpl = function (sel, map) {
      var html = plForm.querySelector(sel).innerHTML;
      Object.keys(map).forEach(function (k) { html = html.split(k).join(map[k]); });
      var box = document.createElement('div'); box.innerHTML = html.trim();
      return box.firstElementChild;
    };
    plForm.addEventListener('click', function (e) {
      var btn = e.target.closest('button');
      if (!btn) return;
      if (btn.hasAttribute('data-pl-add-decade')) {
        var dec = fromTpl('[data-pl-decade-tpl]', { __D__: 'n' + (plSeq++) });
        plDecades.appendChild(dec);
        dec.querySelector('input').focus();
        return;
      }
      if (btn.hasAttribute('data-pl-add-item')) {
        var d = btn.closest('[data-pl-decade]');
        var it = fromTpl('[data-pl-item-tpl]', { __D__: d.dataset.key, __I__: 'n' + (plSeq++) });
        d.querySelector('[data-pl-items]').appendChild(it);
        it.querySelector('input').focus();
        return;
      }
      var row = btn.closest('[data-pl-item]') || btn.closest('[data-pl-decade]');
      if (!row) return;
      if (btn.hasAttribute('data-pl-remove')) {
        if (row.hasAttribute('data-pl-decade') && row.querySelector('[data-pl-item]') && !confirm('Apagar esta década e as playlists dela?')) return;
        row.remove();
      } else if (btn.hasAttribute('data-pl-up') && row.previousElementSibling) {
        row.parentNode.insertBefore(row, row.previousElementSibling);
      } else if (btn.hasAttribute('data-pl-down') && row.nextElementSibling) {
        row.parentNode.insertBefore(row.nextElementSibling, row);
      }
    });
  }

  // Tela do post: capa dentro do post liga/desliga (esconde formato, tamanho e alinhamento)
  var inPost = document.querySelector('[data-cover-inpost-toggle]');
  if (inPost) {
    inPost.addEventListener('change', function () { document.querySelector('[data-cover-inpost]').hidden = !inPost.checked; });
  }

  // Categorias: mostra as cores do rótulo só quando a caixinha está marcada
  var labelCustom = document.querySelector('[data-label-custom]');
  if (labelCustom) {
    labelCustom.addEventListener('change', function () { document.querySelector('[data-label-colors]').hidden = !labelCustom.checked; });
  }

  // Prévia ao vivo do topo: o próprio site desenha o cabeçalho e a faixa com o que está na tela (sem salvar)
  var live = document.querySelector('[data-live-preview]');
  if (live) {
    var frame = live.querySelector('iframe');
    var liveForm = live.closest('form');
    var liveTimer = null, liveReq = 0;
    var fit = function () {
      var doc = frame.contentDocument;
      if (!doc || !doc.body) return;
      var scale = live.clientWidth / 1366;
      var h = doc.body.scrollHeight;
      frame.style.height = h + 'px';
      frame.style.transform = 'scale(' + scale + ')';
      live.style.height = Math.ceil(h * scale) + 'px';
    };
    var refresh = function () {
      var n = ++liveReq;
      fetch('/admin/previa/' + live.dataset.livePreview, { method: 'POST', body: new FormData(liveForm), credentials: 'same-origin' })
        .then(function (r) { return r.ok ? r.text() : null; })
        .then(function (html) { if (html && n === liveReq) frame.srcdoc = html; });
    };
    var later = function () { clearTimeout(liveTimer); liveTimer = setTimeout(refresh, 350); };
    frame.addEventListener('load', function () {
      fit();
      // A busca aberta e as fontes que chegam depois mudam a altura
      var doc = frame.contentDocument;
      if (doc.fonts) doc.fonts.ready.then(fit);
      doc.addEventListener('click', function () { setTimeout(fit, 50); });
    });
    liveForm.addEventListener('input', later);
    liveForm.addEventListener('change', later);
    window.addEventListener('resize', fit);
    refresh();
  }

  // Página: ponto da foto que aparece na faixa do topo
  var bannerFocus = document.querySelector('[data-banner-focus]');
  if (bannerFocus) {
    var bannerValue = document.querySelector('input[name="banner_image"]');
    var bArea = bannerFocus.querySelector('[data-focus-area]');
    var bImg = bannerFocus.querySelector('[data-focus-img]');
    var bPrev = bannerFocus.querySelector('[data-banner-preview]');
    var bDot = bannerFocus.querySelector('[data-focus-dot]');
    var bPos = bannerFocus.querySelector('[data-focus-value]');
    bannerValue.addEventListener('change', function () {
      var v = bannerValue.value;
      bannerFocus.hidden = v === '';
      if (v) { var url = /^https?:/.test(v) ? v : '/' + v.replace(/^\//, ''); bImg.src = url; bPrev.src = url; }
    });
    bArea.addEventListener('click', function (e) {
      var r = bArea.getBoundingClientRect();
      var x = Math.round(((e.clientX - r.left) / r.width) * 100);
      var y = Math.round(((e.clientY - r.top) / r.height) * 100);
      bDot.style.left = x + '%'; bDot.style.top = y + '%';
      bPos.value = x + '% ' + y + '%';
      bPrev.style.objectPosition = bPos.value;
      bPos.dispatchEvent(new Event('change', { bubbles: true }));
    });
  }

  function setupCover(cover) {
    var coverValue = cover.querySelector('input[name="cover_image"]');
    var ratio = cover.querySelector('[data-cover-ratio]');
    var width = cover.querySelector('[data-cover-width]');
    var widthOut = cover.querySelector('[data-cover-width-out]');
    var focusWrap = cover.querySelector('[data-focus-wrap]');
    var focusArea = cover.querySelector('[data-focus-area]');
    var focusImg = cover.querySelector('[data-focus-img]');
    var focusDot = cover.querySelector('[data-focus-dot]');
    var focusValue = cover.querySelector('[data-focus-value]');
    var preview = cover.querySelector('[data-cover-preview]');
    var previewImg = preview.querySelector('img');
    var ratios = { banner: '3 / 1', wide: '16 / 9', landscape: '4 / 3', square: '1 / 1', portrait: '4 / 5' };

    var updateCover = function () {
      var has = coverValue.value !== '';
      var url = has ? (/^https?:/.test(coverValue.value) ? coverValue.value : '/' + coverValue.value.replace(/^\//, '')) : '';
      preview.hidden = !has;
      focusWrap.hidden = !has || !ratios[ratio.value];
      if (has) { focusImg.src = url; previewImg.src = url; }
      var align = (cover.querySelector('[data-cover-align]:checked') || {}).value || 'center';
      widthOut.textContent = width.value + '%';
      previewImg.style.width = width.value + '%';
      previewImg.style.aspectRatio = ratios[ratio.value] || 'auto';
      previewImg.style.objectFit = ratios[ratio.value] ? 'cover' : '';
      previewImg.style.objectPosition = focusValue.value;
      previewImg.style.float = width.value < 100 && align !== 'center' ? align : 'none';
      previewImg.style.margin = width.value < 100 && align === 'center' ? '0 auto' : (align === 'left' ? '0 10px 6px 0' : align === 'right' ? '0 0 6px 10px' : '0');
    };
    [coverValue, ratio, width].forEach(function (el) { el.addEventListener('input', updateCover); el.addEventListener('change', updateCover); });
    cover.querySelectorAll('[data-cover-align]').forEach(function (el) { el.addEventListener('change', updateCover); });
    focusArea.addEventListener('click', function (e) {
      var r = focusArea.getBoundingClientRect();
      var x = Math.round(((e.clientX - r.left) / r.width) * 100);
      var y = Math.round(((e.clientY - r.top) / r.height) * 100);
      focusDot.style.left = x + '%';
      focusDot.style.top = y + '%';
      focusValue.value = x + '% ' + y + '%';
      updateCover();
    });
    updateCover();
  }
})();
