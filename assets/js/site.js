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

  // Tabelas largas rolam de lado no celular
  document.querySelectorAll('.post-content table').forEach(function (t) {
    if (t.parentElement.classList.contains('table-scroll')) return;
    var w = document.createElement('div');
    w.className = 'table-scroll';
    t.parentNode.insertBefore(w, t);
    w.appendChild(t);
  });
})();
