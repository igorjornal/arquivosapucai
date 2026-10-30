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

// Partes do site com fonte, tamanho e negrito próprios.
// base: fonte de Aparência usada no padrão (heading = Títulos, body = Texto corrido, ui = Menus/botões).
// size: tamanho no padrão (null = o tamanho dessa fonte em Padrão do site; false = ajusta sozinho, sem escolha).
function type_targets(): array
{
    $t = fn (string $label, string $base, $size, bool $bold, bool $italic = false) => ['label' => $label, 'base' => $base, 'size' => $size, 'bold' => $bold, 'italic' => $italic];
    return [
        'post_title'         => $t('Título dos posts e páginas', 'heading', 50, true),
        'menu'               => $t('Menu do topo', 'ui', 15, true),
        'ticker'             => $t('Texto dos avisos', 'ui', 14, false, true),
        'blog_page_title'    => $t('Título da página do Blog', 'heading', null, true),
        'side_title'         => $t('Título da coluna “Últimos posts”', 'heading', 20, true),
        'side_posts'         => $t('Títulos dos posts na coluna', 'ui', 14, true),
        'blog_title'         => $t('Título', 'heading', 34, true),
        'blog_button'        => $t('Botão', 'ui', 13, true),
        'arquivo_title'      => $t('Título', 'heading', null, true),
        'arquivo_text'       => $t('Texto', 'body', null, false),
        'arquivo_button'     => $t('Botão', 'ui', null, true),
        'playlists_title'    => $t('Título', 'heading', null, true),
        'playlists_intro'    => $t('Texto antes das imagens', 'body', null, false),
        'playlists_names'    => $t('Nomes em cima das imagens', 'ui', 17, true),
        'playlists_outro'    => $t('Texto depois das imagens', 'body', null, false),
        'playlists_button'   => $t('Botão', 'ui', null, true),
        'acervo_title'       => $t('Título', 'heading', null, true),
        'acervo_text'        => $t('Texto', 'body', null, false),
        'acervo_button'      => $t('Botão', 'ui', null, true),
        'historia_title'     => $t('Título', 'heading', null, true),
        'historia_text'      => $t('Texto', 'body', null, false),
        'historia_button'    => $t('Botão', 'ui', null, true),
        'contact_title'      => $t('Título', 'heading', null, true),
        'contact_text'       => $t('Texto', 'body', null, false),
        'contact_labels'     => $t('Nomes dos campos', 'ui', 14, true),
        'contact_button'     => $t('Botão', 'ui', null, true),
        'footer_titles'      => $t('Títulos das 3 colunas', 'heading', 18, true),
        'footer_texts'       => $t('Textos das colunas', 'body', 15, false),
        'footer_links'       => $t('Links', 'ui', 15, false),
        'footer_news_button' => $t('Botão da newsletter', 'ui', null, true),
        'footer_email'       => $t('Linha do contato (e-mail)', 'ui', 15, false),
        'footer_word'        => $t('Texto grande ao fundo', 'heading', false, true),
        'footer_copyright'   => $t('Linha do ©', 'ui', 13, false),
        'footer_author'      => $t('“Área do autor”', 'ui', 13, false),
    ];
}

// Pesos oferecidos (nem toda fonte tem todos; o navegador usa o mais próximo)
function weight_choices(): array
{
    return [300 => 'Fino', 400 => 'Normal', 600 => 'Seminegrito', 700 => 'Negrito', 800 => 'Extra negrito'];
}

function valid_weight($w, int $fallback): int
{
    return isset(weight_choices()[(int) $w]) ? (int) $w : $fallback;
}

function base_font_weights(): array
{
    $w = setting('appearance')['font_weights'] ?? [];
    return ['heading' => valid_weight($w['heading'] ?? 800, 800), 'body' => valid_weight($w['body'] ?? 400, 400)];
}

// Peso que a parte usa no padrão: títulos e texto corrido seguem o peso escolhido em Padrão do site
function default_weight(array $target): int
{
    if ($target['base'] === 'heading' && $target['bold']) {
        return base_font_weights()['heading'];
    }
    if ($target['base'] === 'body' && !$target['bold']) {
        return base_font_weights()['body'];
    }
    return $target['bold'] ? 700 : 400;
}

// Menor e maior tamanho oferecidos na régua de cada fonte base
function type_limits(string $base): array
{
    return ['heading' => [16, 90], 'body' => [11, 30], 'ui' => [10, 30]][$base] ?? [10, 90];
}

function base_font_sizes(): array
{
    $s = setting('appearance')['font_sizes'] ?? [];
    $out = [];
    foreach (['heading' => 44, 'body' => 16, 'ui' => 15] as $role => $default) {
        [$min, $max] = ['heading' => [24, 80], 'body' => [13, 24], 'ui' => [11, 22]][$role];
        $out[$role] = max($min, min($max, (int) ($s[$role] ?? $default)));
    }
    return $out;
}

// Como a parte fica quando segue o padrão do site
function type_default(string $key): array
{
    $t = type_targets()[$key];
    return [
        'font' => setting('appearance')['fonts'][$t['base']],
        'size' => $t['size'] === false ? 0 : ($t['size'] ?? base_font_sizes()[$t['base']]),
        'weight' => default_weight($t),
        'italic' => !empty($t['italic']),
    ];
}

// A escolha feita no painel para a parte, ou null se ela segue o padrão
function type_choice(string $key): ?array
{
    $c = (setting('type') ?? [])[$key] ?? null;
    if (!is_array($c) || !isset(type_targets()[$key])) {
        return null;
    }
    $t = type_targets()[$key];
    [$min, $max] = type_limits($t['base']);
    $font = preg_replace('/[^A-Za-z0-9 ]/', '', (string) ($c['font'] ?? '')) ?: type_default($key)['font'];
    // Escolhas antigas guardavam só "negrito sim/não"
    $weight = isset($c['weight']) ? valid_weight($c['weight'], 400) : (!empty($c['bold']) ? default_weight(['bold' => true, 'base' => $t['base']]) : 400);
    $italic = isset($c['italic']) ? (bool) $c['italic'] : !empty($t['italic']);
    return ['font' => $font, 'size' => $t['size'] === false ? 0 : max($min, min($max, (int) ($c['size'] ?? 0))), 'weight' => $weight, 'italic' => $italic];
}

// CSS da escolha (vazio quando segue o padrão). Tamanhos grandes encolhem no celular.
function type_css(string $key): string
{
    $c = type_choice($key);
    if (!$c) {
        return '';
    }
    $base = type_targets()[$key]['base'];
    $fallback = $base === 'body' ? 'Georgia, serif' : 'system-ui, sans-serif';
    $fp = font_parts($c['font']);
    $css = "font-family:'{$fp['family']}', {$fallback};font-stretch:{$fp['stretch']}";
    if ($c['size']) {
        $css .= ';font-size:' . ($c['size'] >= 26 ? 'clamp(' . round($c['size'] * .6) . 'px, 7vw, ' . $c['size'] . 'px)' : $c['size'] . 'px');
    }
    return $css . ';font-weight:' . $c['weight'] . ';font-style:' . ($c['italic'] ? 'italic' : 'normal');
}

function type_style(string $key): string
{
    $css = type_css($key);
    return $css === '' ? '' : ' style="' . e($css) . '"';
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
    foreach (base_font_sizes() as $role => $size) {
        $lines[] = "--fs-{$role}:{$size}px";
    }
    foreach (base_font_weights() as $role => $weight) {
        $lines[] = "--fw-{$role}:{$weight}";
    }
    $logo = max(40, min(220, (int) ($a['sizes']['logo'] ?? 96)));
    $header = max(60, min(320, (int) ($a['sizes']['header'] ?? 120)));
    $overlay = max(0, min(100, (int) ($a['header']['overlay'] ?? 100)));
    $lines[] = '--header-overlay-a:' . $overlay . '%';
    $lines[] = '--header-overlay-b:' . (!empty($a['header']['gradient']) ? (int) round($overlay * .55) : $overlay) . '%';
    $lines[] = '--menu-gap:' . max(0, min(80, (int) ($a['menu']['gap'] ?? 30))) . 'px';
    $lines[] = '--split-gap:' . max(0, min(120, (int) (setting('home')['split_gap'] ?? 40))) . 'px';
    $lines[] = '--ticker-pad:' . max(0, min(40, (int) (setting('home')['ticker_pad'] ?? 9))) . 'px';
    $lines[] = '--logo-size:' . $logo . 'px';
    $lines[] = '--header-height:' . $header . 'px';
    $blog = setting('blog');
    foreach (['card_title_lines' => '--card-title-lines', 'card_excerpt_lines' => '--card-excerpt-lines'] as $key => $var) {
        $n = (int) $blog[$key];
        $lines[] = $var . ':' . ($n > 0 ? $n : 'none');
    }
    foreach ($a['fonts'] as $role => $family) {
        $fp = font_parts((string) preg_replace('/[^A-Za-z0-9 ]/', '', (string) $family));
        $fallback = $role === 'body' ? 'Georgia, serif' : 'system-ui, sans-serif';
        $lines[] = "--f-{$role}:'{$fp['family']}', {$fallback}";
        $lines[] = "--fst-{$role}:{$fp['stretch']}";
    }
    return ':root{' . implode(';', $lines) . '}';
}

// Link do Google Fonts só para as fontes escolhidas que não vêm embutidas
function google_fonts_link(): string
{
    $families = [];
    $all = array_merge(array_values(setting('appearance')['fonts']), array_column(array_filter(array_map('type_choice', array_keys(type_targets()))), 'font'));
    foreach ($all as $family) {
        $family = font_parts((string) preg_replace('/[^A-Za-z0-9 ]/', '', (string) $family))['family'];
        if ($family === 'Archivo') {
            // A Archivo vem com todas as larguras (Condensed, Expanded…) num arquivo só
            $families[$family] = 'family=Archivo:ital,wdth,wght@0,62..125,100..900;1,62..125,100..900';
        } elseif ($family !== '' && !in_array($family, bundled_fonts(), true)) {
            $families[$family] = 'family=' . str_replace(' ', '+', $family) . ':ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800';
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

// Guarda as escolhas de fonte enviadas por uma tela do painel (só as partes daquela tela)
function save_type_from_post(array $keys): void
{
    $all = setting('type') ?? [];
    foreach ($keys as $key) {
        $p = $_POST['type'][$key] ?? null;
        if (!is_array($p) || !isset(type_targets()[$key])) {
            continue;
        }
        if (!empty($p['padrao'])) {
            unset($all[$key]);
            continue;
        }
        [$min, $max] = type_limits(type_targets()[$key]['base']);
        $font = (string) ($p['font'] ?? '');
        $all[$key] = [
            'font' => in_array($font, font_choices(), true) ? $font : type_default($key)['font'],
            'size' => max($min, min($max, (int) ($p['size'] ?? type_default($key)['size']))),
            'weight' => valid_weight($p['weight'] ?? 0, type_default($key)['weight']),
            'italic' => !empty($p['italic']),
        ];
    }
    save_setting('type', $all);
}

// Quando o quadro tem um controle só, ele sai sem borda nem título próprio (o quadro de fora já tem título)
function type_fields(array $keys): string
{
    $bare = count($keys) === 1;
    $out = '';
    foreach ($keys as $key => $label) {
        $out .= is_int($key) ? render_to_string('admin/type-field', ['key' => $label, 'bare' => $bare]) : render_to_string('admin/type-field', ['key' => $key, 'label' => $label, 'bare' => $bare]);
    }
    return '<div class="a-type-grid' . ($bare ? ' is-single' : '') . '">' . $out . '</div>';
}
