<?php
$site = setting('site');
$pageTitle = isset($title) && $title !== $site['title'] ? $title . ' | ' . $site['title'] : $site['title'];
$metaDescription = $description ?? $site['description'];
$ogImage = absolute_url(media_url($shareImage ?? null) ?: media_url($site['share_image']));
$canonical = absolute_url(strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/');
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($metaDescription) ?>">
<?php if (!empty($robots)): ?><meta name="robots" content="<?= e($robots) ?>"><?php endif; ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:site_name" content="<?= e($site['title']) ?>">
<meta property="og:title" content="<?= e($title ?? $site['title']) ?>">
<meta property="og:description" content="<?= e($metaDescription) ?>">
<meta property="og:type" content="<?= e($ogType ?? 'website') ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:locale" content="pt_BR">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="<?= e(asset('img/favicon.png')) ?>">
<link rel="alternate" type="application/rss+xml" title="<?= e($site['title']) ?>" href="/feed.xml">
<link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
<?= google_fonts_link() ?>
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<style><?= appearance_css() ?></style>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
<a class="skip" href="#conteudo">Ir para o conteúdo</a>
<?php partial('header'); ?>
<main id="conteudo">
<?= $content ?>
</main>
<?php partial('footer'); ?>
<script src="<?= e(asset('js/site.js')) ?>" defer></script>
</body>
</html>
