<?php
declare(strict_types=1);

function render(string $template, array $vars = [], string $layout = 'layout'): void
{
    $content = render_to_string($template, $vars);
    if ($layout === '') {
        echo $content;
        return;
    }
    echo render_to_string($layout, $vars + ['content' => $content]);
}

function render_to_string(string $template, array $vars = []): string
{
    $file = ROOT . '/templates/' . $template . '.php';
    if (!is_file($file)) {
        throw new RuntimeException("Template não encontrado: {$template}");
    }
    extract($vars, EXTR_SKIP);
    ob_start();
    require $file;
    return (string) ob_get_clean();
}

function partial(string $template, array $vars = []): void
{
    echo render_to_string('partials/' . $template, $vars);
}

// Fonte e tamanho de partes específicas (menu, título dos posts, faixa)
function type_settings(): array
{
    $limits = ['menu' => [11, 28], 'post_title' => [20, 90], 'ticker' => [10, 28]];
    $out = [];
    foreach ($limits as $key => [$min, $max]) {
        $t = setting('appearance')['type'][$key] ?? [];
        $font = preg_replace('/[^A-Za-z0-9 ]/', '', (string) ($t['font'] ?? '')) ?: 'Instrument Sans';
        $out[$key] = ['font' => $font, 'size' => max($min, min($max, (int) ($t['size'] ?? $min)))];
    }
    return $out;
}

// Variáveis CSS geradas a partir de Aparência
function appearance_css(): string
{
    $a = setting('appearance');
    $lines = [];
    foreach ($a['colors'] as $name => $value) {
        if (preg_match('/^#[0-9a-fA-F]{3,8}$/', (string) $value)) {
            $lines[] = '--c-' . str_replace('_', '-', $name) . ':' . $value;
        }
    }
    foreach (type_settings() as $key => $t) {
        $var = str_replace('_', '-', $key);
        $lines[] = "--f-{$var}:'" . $t['font'] . "', system-ui, sans-serif";
        $lines[] = "--fs-{$var}:" . $t['size'] . 'px';
    }
    $logo = max(40, min(220, (int) ($a['sizes']['logo'] ?? 96)));
    $header = max(60, min(320, (int) ($a['sizes']['header'] ?? 120)));
    $lines[] = '--logo-size:' . $logo . 'px';
    $lines[] = '--header-height:' . $header . 'px';
    $blog = setting('blog');
    foreach (['card_title_lines' => '--card-title-lines', 'card_excerpt_lines' => '--card-excerpt-lines'] as $key => $var) {
        $n = (int) $blog[$key];
        $lines[] = $var . ':' . ($n > 0 ? $n : 'none');
    }
    foreach ($a['fonts'] as $role => $family) {
        $family = preg_replace('/[^A-Za-z0-9 ]/', '', (string) $family);
        $fallback = $role === 'body' ? 'Georgia, serif' : 'system-ui, sans-serif';
        $lines[] = "--f-{$role}:'{$family}', {$fallback}";
    }
    return ':root{' . implode(';', $lines) . '}';
}

// Link do Google Fonts só para as fontes escolhidas que não vêm embutidas
function google_fonts_link(): string
{
    $families = [];
    $all = array_merge(array_values(setting('appearance')['fonts']), array_column(type_settings(), 'font'));
    foreach ($all as $family) {
        $family = preg_replace('/[^A-Za-z0-9 ]/', '', (string) $family);
        if ($family !== '' && !in_array($family, bundled_fonts(), true)) {
            $families[$family] = 'family=' . str_replace(' ', '+', $family) . ':ital,wght@0,400;0,600;0,700;0,800;1,400;1,700';
        }
    }
    if (!$families) {
        return '';
    }
    return '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
        . '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?' . implode('&', $families) . '&display=swap">';
}

function social_icon(string $network): string
{
    $icons = [
        'youtube'   => '<path d="M23 7.2a3 3 0 0 0-2.1-2.1C19 4.6 12 4.6 12 4.6s-7 0-8.9.5A3 3 0 0 0 1 7.2 31 31 0 0 0 .5 12a31 31 0 0 0 .5 4.8 3 3 0 0 0 2.1 2.1c1.9.5 8.9.5 8.9.5s7 0 8.9-.5a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .5-4.8 31 31 0 0 0-.5-4.8zM9.6 15.1V8.9l5.8 3.1z"/>',
        'instagram' => '<path d="M12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 8.2a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0 6.4zM17.3 5.5a1.2 1.2 0 1 0 0 2.4 1.2 1.2 0 0 0 0-2.4z"/><path d="M12 2.2c-2.7 0-3 0-4 .1-3.6.2-5.6 2.2-5.8 5.8-.1 1-.1 1.3-.1 4s0 3 .1 4c.2 3.6 2.2 5.6 5.8 5.8 1 .1 1.3.1 4 .1s3 0 4-.1c3.6-.2 5.6-2.2 5.8-5.8.1-1 .1-1.3.1-4s0-3-.1-4c-.2-3.6-2.2-5.6-5.8-5.8-1-.1-1.3-.1-4-.1zm0 1.8c2.6 0 2.9 0 4 .1 2.6.1 3.8 1.4 4 4 .1 1 .1 1.3.1 3.9s0 2.9-.1 3.9c-.1 2.6-1.4 3.8-4 4-1 .1-1.3.1-4 .1s-2.9 0-3.9-.1c-2.6-.1-3.8-1.4-4-4-.1-1-.1-1.3-.1-3.9s0-2.9.1-3.9c.1-2.6 1.4-3.8 4-4 1-.1 1.3-.1 3.9-.1z"/>',
        'facebook'  => '<path d="M14 8.5V6.6c0-.9.6-1.1 1-1.1h2.6V1.6h-3.6c-4 0-4.9 3-4.9 4.9v2H6.8v4h2.3V22h4.9v-9.5h3.3l.4-4z"/>',
        'x'         => '<path d="M17.8 2.5h3.3l-7.2 8.2 8.5 11.2h-6.6l-5.2-6.8-6 6.8H1.3l7.7-8.8L.9 2.5h6.8l4.7 6.2zm-1.2 17.4h1.8L6.6 4.4h-2z"/>',
        'tiktok'    => '<path d="M19.6 6.7a4.8 4.8 0 0 1-3.8-4.2V2h-3.4v13.7a2.9 2.9 0 1 1-2-2.8V9.4a6.3 6.3 0 1 0 5.4 6.3V8.8a8.2 8.2 0 0 0 4.8 1.5V6.9a4.8 4.8 0 0 1-1-.2z"/>',
        'linkedin'  => '<path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9.5h4V21H3zM9.5 9.5h3.8v1.6h.1c.5-1 1.8-2 3.8-2 4 0 4.8 2.6 4.8 6V21h-4v-5.1c0-1.2 0-2.8-1.7-2.8s-2 1.3-2 2.7V21h-4z"/>',
        'link'      => '<path d="M10.6 13.4a1 1 0 0 1 0-1.4l3.5-3.5a1 1 0 1 1 1.4 1.4L12 13.4a1 1 0 0 1-1.4 0z"/><path d="M8.5 20.5a5 5 0 0 1-3.5-8.5l2.1-2.1a1 1 0 1 1 1.4 1.4l-2.1 2.1a3 3 0 0 0 4.2 4.2l2.1-2.1a1 1 0 1 1 1.4 1.4L12 19a5 5 0 0 1-3.5 1.5zm8.4-6.6a1 1 0 0 1-.7-1.7l2.1-2.1a3 3 0 0 0-4.2-4.2L12 8a1 1 0 1 1-1.4-1.4L12.7 4.5a5 5 0 0 1 7 7l-2.1 2.1a1 1 0 0 1-.7.3z"/>',
    ];
    return '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">' . ($icons[$network] ?? '') . '</svg>';
}
