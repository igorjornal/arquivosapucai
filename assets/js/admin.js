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
    r.addEventListener('input', function () { out.textContent = r.value; });
  });

  // Amostra de fonte na Aparência
  var loadedFonts = {};
  document.querySelectorAll('[data-font-select]').forEach(function (select) {
    var sample = select.closest('label').nextElementSibling;
    select.addEventListener('change', function () {
      var family = select.value;
      if (['Syne', 'Bitter', 'Instrument Sans'].indexOf(family) === -1 && !loadedFonts[family]) {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://fonts.googleapis.com/css2?family=' + family.replace(/ /g, '+') + ':wght@400;700;800&display=swap';
        document.head.appendChild(link);
        loadedFonts[family] = true;
      }
      sample.style.fontFamily = "'" + family + "'";
    });
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

  /* ---------- Biblioteca de imagens (janela) ---------- */
  var modal = document.getElementById('media-modal');
  var grid = modal && modal.querySelector('[data-media-grid]');
  var moreBtn = modal && modal.querySelector('[data-media-more]');
  var statusEl = modal && modal.querySelector('[data-media-status]');
  var page = 1;
  var onPick = null;

  function loadMedia(reset) {
    if (reset) { page = 1; grid.innerHTML = ''; }
    fetch('/admin/midia/lista?pagina=' + page, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
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

    // Avisa se sair sem salvar
    var dirty = false;
    form.addEventListener('input', function () { dirty = true; });
    form.addEventListener('submit', function () {
      dirty = false;
      if (window.tinymce && tinymce.activeEditor) tinymce.activeEditor.setDirty(false);
    });
    window.addEventListener('beforeunload', function (e) {
      if (dirty || (window.tinymce && tinymce.activeEditor && tinymce.activeEditor.isDirty())) {
        e.preventDefault();
        e.returnValue = '';
      }
    });
  }
})();
