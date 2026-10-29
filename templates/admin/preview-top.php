<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<base target="_blank">
<link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
<?= google_fonts_link() ?>
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<style><?= appearance_css() ?></style>
<style>body { margin: 0; background: transparent; } .skip, .cookie-bar { display: none !important; }</style>
</head>
<body class="is-preview">
<?php partial('header'); ?>
<script src="<?= e(asset('js/site.js')) ?>"></script>
<script>
// Prévia: os links não saem do painel
document.addEventListener('click', function (e) { var a = e.target.closest('a'); if (a) e.preventDefault(); }, true);
document.addEventListener('submit', function (e) { e.preventDefault(); }, true);
</script>
</body>
</html>
