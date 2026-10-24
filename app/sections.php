<?php
declare(strict_types=1);

// Seções da home: as fixas (que já existiam) e as que o painel pode adicionar.

function section_types(): array
{
    return [
        'hero'           => ['label' => 'Banners do topo', 'builtin' => true],
        'blog'           => ['label' => 'Blog (últimos posts)', 'builtin' => true],
        'arquivo'        => ['label' => 'O Arquivo', 'builtin' => true],
        'playlists'      => ['label' => 'Playlists', 'builtin' => true],
        'acervo'         => ['label' => 'Acervo e História', 'builtin' => true],
        'contact'        => ['label' => 'Contato', 'builtin' => true],
        'text_image'     => ['label' => 'Texto + imagem', 'builtin' => false, 'help' => 'Título, texto, botão e uma imagem ao lado.'],
        'category_posts' => ['label' => 'Posts de uma categoria', 'builtin' => false, 'help' => 'Cards só com os posts de uma categoria.'],
        'rich_text'      => ['label' => 'Texto livre (editor)', 'builtin' => false, 'help' => 'Monte com o mesmo editor dos posts.'],
        'video'          => ['label' => 'Vídeo em destaque', 'builtin' => false, 'help' => 'Um vídeo grande do YouTube com título e texto.'],
    ];
}

function default_section_data(string $type): array
{
    $base = ['bg' => '#FDFCF0', 'title_color' => '#1E0D06', 'text_color' => '#2B211D'];
    return match ($type) {
        'text_image'     => $base + ['title' => 'Novo título', 'text' => '', 'button_text' => '', 'button_link' => '', 'image' => '', 'image_side' => 'left'],
        'category_posts' => ['bg' => '#20063A', 'title_color' => '#CDF50D', 'text_color' => '#FFFFFF', 'title' => '', 'category_id' => 0, 'count' => 3, 'button_text' => 'VER TODOS'],
        'rich_text'      => $base + ['title' => '', 'html' => '', 'narrow' => true],
        'video'          => ['bg' => '#000000', 'title_color' => '#FFFFFF', 'text_color' => '#E9E6D3', 'title' => 'Vídeo em destaque', 'text' => '', 'url' => ''],
        default          => [],
    };
}

function builtin_sections(): array
{
    return [
        ['hero', 'Banners do topo', null],
        ['blog', 'Blog', 'blog'],
        ['arquivo', 'O Arquivo', 'arquivo'],
        ['playlists', 'Playlists', 'playlists'],
        ['acervo', 'Acervo e História', 'acervo'],
        ['contact', 'Contato', 'contato'],
    ];
}

function seed_home_sections(): void
{
    if ((int) db_val('SELECT COUNT(*) FROM home_sections')) {
        return;
    }
    foreach (builtin_sections() as $i => [$type, $label, $anchor]) {
        db_insert('home_sections', ['type' => $type, 'label' => $label, 'anchor' => $anchor, 'active' => 1, 'sort_order' => $i, 'data' => null]);
    }
}

function home_sections(bool $onlyActive = true): array
{
    try {
        $rows = db_all('SELECT * FROM home_sections' . ($onlyActive ? ' WHERE active = 1' : '') . ' ORDER BY sort_order, id');
    } catch (PDOException $e) {
        $rows = [];
        foreach (builtin_sections() as $i => [$type, $label, $anchor]) {
            $rows[] = ['id' => 0, 'type' => $type, 'label' => $label, 'anchor' => $anchor, 'active' => 1, 'sort_order' => $i, 'data' => null];
        }
    }
    foreach ($rows as &$row) {
        $row['data'] = array_replace(default_section_data($row['type']), json_decode((string) $row['data'], true) ?: []);
    }
    return $rows;
}

// Estilo inline com as cores próprias de uma seção adicionada
function section_style(array $data): string
{
    $vars = [];
    foreach (['bg' => '--s-bg', 'title_color' => '--s-title', 'text_color' => '--s-text'] as $key => $var) {
        if (!empty($data[$key]) && preg_match('/^#[0-9a-fA-F]{6}$/', $data[$key])) {
            $vars[] = $var . ':' . $data[$key];
        }
    }
    return $vars ? ' style="' . implode(';', $vars) . '"' : '';
}

function ticker_items(): array
{
    $home = setting('home');
    $items = array_values(array_filter((array) ($home['ticker_items'] ?? []), fn ($i) => trim((string) ($i['text'] ?? '')) !== ''));
    if (!$items && trim((string) ($home['ticker'] ?? '')) !== '') {
        $items = [['text' => $home['ticker'], 'link' => '']];
    }
    return $items;
}

// Atualizações do banco que rodam sozinhas quando o painel é aberto
function run_migrations(): void
{
    $version = (int) (json_decode((string) db_val("SELECT value FROM settings WHERE name = 'schema_version'"), true) ?? 1);
    if ($version < 2) {
        db()->exec((string) preg_replace('/^.*(CREATE TABLE IF NOT EXISTS home_sections.*?;).*$/s', '$1', (string) file_get_contents(ROOT . '/database/schema.sql')));
        seed_home_sections();
        save_setting_raw('schema_version', 2);
    }
    if ($version < 3) {
        $has = db_val("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'slides' AND COLUMN_NAME = 'new_tab'");
        if (!(int) $has) {
            db()->exec('ALTER TABLE slides ADD COLUMN new_tab TINYINT(1) NOT NULL DEFAULT 0 AFTER link');
        }
        save_setting_raw('schema_version', 3);
    }
}

function save_setting_raw(string $name, $value): void
{
    db_exec(
        'INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)',
        [$name, json_encode($value)]
    );
}
