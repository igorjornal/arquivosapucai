// Arquivo Sapucaí — interações do site público
(function () {
  'use strict';

  // Menu no celular
  var toggle = document.querySelector('.menu-toggle');
  var nav = document.getElementById('menu-principal');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    nav.addEventListener('click', function (e) {
      if (e.target.closest('a')) {
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // Carrossel do topo
  document.querySelectorAll('[data-slider]').forEach(function (slider) {
    var slides = slider.querySelectorAll('[data-slide]');
    if (slides.length < 2) return;
    var dots = slider.querySelector('[data-dots]');
    var index = 0;
    var timer;
    slides.forEach(function (_, i) {
      var b = document.createElement('button');
      b.type = 'button';
      b.setAttribute('aria-label', 'Destaque ' + (i + 1));
      b.addEventListener('click', function () { go(i); restart(); });
      dots.appendChild(b);
    });
    function go(i) {
      index = (i + slides.length) % slides.length;
      slides.forEach(function (s, n) { s.classList.toggle('is-active', n === index); });
      dots.querySelectorAll('button').forEach(function (d, n) { d.setAttribute('aria-current', n === index ? 'true' : 'false'); });
    }
    function restart() {
      clearInterval(timer);
      timer = setInterval(function () { go(index + 1); }, 6000);
    }
    slider.querySelector('[data-prev]').addEventListener('click', function () { go(index - 1); restart(); });
    slider.querySelector('[data-next]').addEventListener('click', function () { go(index + 1); restart(); });
    slider.addEventListener('mouseenter', function () { clearInterval(timer); });
    slider.addEventListener('mouseleave', restart);
    go(0);
    restart();
  });

  // Copiar link do post
  document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var done = function () {
        btn.classList.add('copied');
        setTimeout(function () { btn.classList.remove('copied'); }, 1800);
      };
      if (navigator.clipboard) {
        navigator.clipboard.writeText(btn.dataset.copy).then(done);
      } else {
        window.prompt('Copie o link:', btn.dataset.copy);
      }
    });
  });

  // Responder comentário
  var replyField = document.querySelector('[data-reply-field]');
  var replyLabel = document.querySelector('[data-reply-label]');
  document.querySelectorAll('[data-reply]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      replyField.value = btn.dataset.reply;
      replyLabel.hidden = false;
      replyLabel.textContent = '— respondendo a ' + btn.dataset.replyName;
      var form = replyField.closest('form');
      form.scrollIntoView({ behavior: 'smooth', block: 'center' });
      form.querySelector('textarea').focus({ preventScroll: true });
    });
  });

  // Texto grande do rodapé: ajusta o tamanho para caber numa linha só
  var word = document.querySelector('.footer-word');
  if (word) {
    var fit = function () {
      word.style.fontSize = '100px';
      var size = 100 * (word.clientWidth * 0.9) / word.scrollWidth;
      word.style.fontSize = Math.min(size, 220) + 'px';
    };
    fit();
    window.addEventListener('resize', fit);
    if (document.fonts) document.fonts.ready.then(fit);
  }

  // Voltar: retorna para a página anterior do site; quem chegou de fora vai para a home
  document.querySelectorAll('[data-back]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      if (document.referrer && document.referrer.indexOf(location.origin) === 0 && history.length > 1) {
        e.preventDefault();
        history.back();
      }
    });
  });

  // Aviso de cookies: Pixel da Meta e Google Analytics só carregam depois do “Aceito” (LGPD)
  var track = window.ARQ_TRACK;
  var bar = document.querySelector('[data-cookie-bar]');
  if (track && bar) {
    var getChoice = function () { try { return localStorage.getItem('arq_cookies'); } catch (e) { return null; } };
    var setChoice = function (v) { try { localStorage.setItem('arq_cookies', v); } catch (e) {} };
    var loaded = false;
    var startTracking = function () {
      if (loaded) return;
      loaded = true;
      if (track.ga) {
        var g = document.createElement('script');
        g.async = true;
        g.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(track.ga);
        document.head.appendChild(g);
        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('config', track.ga);
      }
      if (track.pixel) {
        /* Código oficial do Pixel da Meta */
        !function (f, b, e, v, n, t, s) { if (f.fbq) return; n = f.fbq = function () { n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments); };
          if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = []; t = b.createElement(e); t.async = !0;
          t.src = v; s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s); }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
        window.fbq('init', track.pixel);
        window.fbq('track', 'PageView');
      }
    };
    if (getChoice() === 'sim') startTracking();
    else if (getChoice() !== 'nao') bar.hidden = false;
    bar.addEventListener('click', function (e) {
      var b = e.target.closest('[data-cookie]');
      if (!b) return;
      setChoice(b.dataset.cookie);
      bar.hidden = true;
      if (b.dataset.cookie === 'sim') startTracking();
    });
    var reset = document.querySelector('[data-cookie-reset]');
    if (reset) reset.addEventListener('click', function () { bar.hidden = false; });
  }

  // Vídeo com capa: clicou na capa, ela sai e o vídeo começa
  document.querySelectorAll('.video.has-cover').forEach(function (box) {
    var cover = box.querySelector('.yt-cover');
    var frame = box.querySelector('iframe');
    if (!cover || !frame) return;
    cover.addEventListener('click', function () {
      var src = frame.getAttribute('src');
      frame.setAttribute('src', src + (src.indexOf('?') === -1 ? '?' : '&') + 'autoplay=1');
      cover.remove();
      box.classList.remove('has-cover');
    });
  });

  // Tabelas largas rolam de lado no celular
  document.querySelectorAll('.post-content table').forEach(function (t) {
    if (t.parentElement.classList.contains('table-scroll')) return;
    var w = document.createElement('div');
    w.className = 'table-scroll';
    t.parentNode.insertBefore(w, t);
    w.appendChild(t);
  });
})();
