<?php
declare(strict_types=1);

// Limpa o HTML que vem do editor do painel: mantém toda a formatação permitida
// (cores, tamanhos, imagens, tabelas, botões, vídeos) e remove qualquer script.

const ALLOWED_TAGS = [
    'p', 'br', 'span', 'strong', 'b', 'em', 'i', 'u', 's', 'del', 'sub', 'sup', 'mark', 'small',
    'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'code',
    'ul', 'ol', 'li', 'a', 'img', 'figure', 'figcaption', 'hr', 'div',
    'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'colgroup', 'col', 'iframe',
];

const ALLOWED_ATTRS = [
    '*'      => ['style', 'class', 'title', 'id'],
    'a'      => ['href', 'target', 'rel'],
    'img'    => ['src', 'alt', 'width', 'height', 'loading'],
    'td'     => ['colspan', 'rowspan'],
    'th'     => ['colspan', 'rowspan', 'scope'],
    'col'    => ['span'],
    'ol'     => ['start', 'type'],
    'iframe' => ['src', 'width', 'height', 'allow', 'allowfullscreen', 'frameborder', 'title'],
];

const ALLOWED_CSS = [
    'color', 'background-color', 'background', 'font-size', 'font-weight', 'font-style', 'font-family',
    'text-decoration', 'text-decoration-line', 'text-align', 'line-height', 'letter-spacing', 'text-transform',
    'vertical-align', 'width', 'max-width', 'min-width', 'height', 'max-height', 'min-height',
    'margin', 'margin-top', 'margin-bottom', 'margin-left', 'margin-right',
    'padding', 'padding-top', 'padding-bottom', 'padding-left', 'padding-right',
    'border', 'border-top', 'border-bottom', 'border-left', 'border-right', 'border-color', 'border-width',
    'border-style', 'border-radius', 'border-collapse', 'aspect-ratio', 'object-fit', 'object-position',
    'float', 'display', 'list-style-type',
];

function sanitize_html(string $html): string
{
    if (trim($html) === '') {
        return '';
    }
    $doc = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $root = $doc->getElementById('__root');
    if (!$root) {
        return '';
    }
    sanitize_node($root);
    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= $doc->saveHTML($child);
    }
    return $out;
}

function sanitize_node(DOMNode $node): void
{
    // Percorre de trás pra frente porque filhos podem ser removidos
    for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
        $child = $node->childNodes->item($i);
        if ($child instanceof DOMComment) {
            $node->removeChild($child);
            continue;
        }
        if (!$child instanceof DOMElement) {
            continue;
        }
        $tag = strtolower($child->tagName);
        if (in_array($tag, ['script', 'style', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'link', 'meta', 'base', 'svg', 'math'], true)) {
            $node->removeChild($child);
            continue;
        }
        if (!in_array($tag, ALLOWED_TAGS, true)) {
            // Tag desconhecida: mantém o conteúdo, tira a casca
            sanitize_node($child);
            while ($child->firstChild) {
                $node->insertBefore($child->firstChild, $child);
            }
            $node->removeChild($child);
            continue;
        }
        if ($tag === 'iframe' && !youtube_embed_src($child->getAttribute('src'))) {
            $node->removeChild($child);
            continue;
        }
        sanitize_attributes($child, $tag);
        sanitize_node($child);
    }
}

function sanitize_attributes(DOMElement $el, string $tag): void
{
    $allowed = array_merge(ALLOWED_ATTRS['*'], ALLOWED_ATTRS[$tag] ?? []);
    for ($i = $el->attributes->length - 1; $i >= 0; $i--) {
        $attr = $el->attributes->item($i);
        $name = strtolower($attr->nodeName);
        if (!in_array($name, $allowed, true)) {
            $el->removeAttribute($attr->nodeName);
            continue;
        }
        $value = $attr->nodeValue ?? '';
        if ($name === 'style') {
            $clean = sanitize_style($value);
            $clean === '' ? $el->removeAttribute('style') : $el->setAttribute('style', $clean);
        } elseif ($name === 'href') {
            if (!safe_url($value, true)) {
                $el->removeAttribute('href');
            }
        } elseif ($name === 'src') {
            $src = $tag === 'iframe' ? youtube_embed_src($value) : (safe_url($value, false) ? $value : null);
            $src ? $el->setAttribute('src', $src) : $el->removeAttribute('src');
        } elseif ($name === 'class') {
            $el->setAttribute('class', preg_replace('/[^A-Za-z0-9_ -]/', '', $value));
        } elseif ($name === 'id') {
            $el->setAttribute('id', preg_replace('/[^A-Za-z0-9_-]/', '', $value));
        } elseif ($name === 'target') {
            $value === '_blank' ? $el->setAttribute('target', '_blank') : $el->removeAttribute('target');
        }
    }
    if ($tag === 'a' && $el->getAttribute('target') === '_blank') {
        $el->setAttribute('rel', 'noopener noreferrer');
    }
    if ($tag === 'img' && !$el->hasAttribute('loading')) {
        $el->setAttribute('loading', 'lazy');
    }
    if ($tag === 'iframe') {
        $el->setAttribute('allowfullscreen', 'allowfullscreen');
        $el->setAttribute('allow', 'accelerometer; encrypted-media; gyroscope; picture-in-picture; web-share');
        $el->setAttribute('loading', 'lazy');
    }
}

function sanitize_style(string $style): string
{
    $out = [];
    foreach (explode(';', $style) as $decl) {
        if (!str_contains($decl, ':')) {
            continue;
        }
        [$prop, $value] = array_map('trim', explode(':', $decl, 2));
        $prop = strtolower($prop);
        $isVar = (bool) preg_match('/^--btn-[a-z-]+$/', $prop);
        if (!$isVar && !in_array($prop, ALLOWED_CSS, true)) {
            continue;
        }
        if ($value === '' || preg_match('/(url\s*\(|expression|javascript:|@import|[<>{}\\\\])/i', $value)) {
            continue;
        }
        if (!preg_match('/^[#%.,()\/\'" a-zA-Z0-9-]+$/', $value) || mb_strlen($value) > 200) {
            continue;
        }
        if ($prop === 'display' && !in_array($value, ['block', 'inline', 'inline-block', 'none', 'table', 'flex'], true)) {
            continue;
        }
        $out[] = $prop . ': ' . $value;
    }
    return implode('; ', $out);
}

function safe_url(string $url, bool $allowMailto): bool
{
    $url = trim($url);
    if ($url === '' || str_starts_with($url, '#') || str_starts_with($url, '/')) {
        return !str_starts_with($url, '//');
    }
    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    $schemes = $allowMailto ? ['http', 'https', 'mailto', 'tel'] : ['http', 'https'];
    return in_array($scheme, $schemes, true);
}

// Só aceita player do YouTube: vídeo (com ou sem playlist junto) ou playlist inteira
function youtube_embed_src(string $src): ?string
{
    $list = preg_match('/[?&]list=([A-Za-z0-9_-]{10,64})/', $src, $m) ? $m[1] : null;
    $id = youtube_id($src);
    if ($id === 'videoseries') {
        $id = null;
    }
    if ($id) {
        return 'https://www.youtube-nocookie.com/embed/' . $id . ($list ? '?list=' . $list : '');
    }
    return $list ? 'https://www.youtube-nocookie.com/embed/videoseries?list=' . $list : null;
}
