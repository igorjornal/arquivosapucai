<?php
declare(strict_types=1);

// Consultas de conteúdo usadas pelo site público

const POST_SELECT = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug,
        u.name AS author_name, u.slug AS author_slug, u.avatar AS author_avatar
    FROM posts p
    LEFT JOIN categories c ON c.id = p.category_id
    JOIN users u ON u.id = COALESCE((SELECT x.id FROM users x WHERE x.id = p.author_id), (SELECT MIN(y.id) FROM users y WHERE y.role = \'admin\'))';
// (sem autor válido, o post aparece com o primeiro administrador, em vez de sumir)

// Publicado e com data já alcançada (posts agendados aparecem sozinhos na hora certa)
function published_clause(): string
{
    return "p.status = 'published' AND p.published_at <= ?";
}

function latest_posts(int $limit, int $offset = 0, array $filter = []): array
{
    [$where, $params] = post_filter($filter);
    $params[] = $limit;
    $params[] = $offset;
    $order = empty($filter['newest']) ? 'p.featured DESC, p.published_at DESC' : 'p.published_at DESC';
    return db_all(POST_SELECT . " WHERE {$where} ORDER BY {$order} LIMIT ? OFFSET ?", $params);
}

// Posts relacionados: os escolhidos na tela do post, na ordem; se faltar, completa com a mesma categoria e depois com os mais novos
function related_posts(array $post, int $limit): array
{
    $id = (int) $post['id'];
    $list = [];
    foreach (related_ids($id) as $rid) {
        $p = db_one(POST_SELECT . ' WHERE p.id = ? AND ' . published_clause(), [$rid, now()]);
        if ($p) {
            $list[$p['id']] = $p;
        }
    }
    if ($list) {
        return array_values($list);
    }
    $fill = function (array $filter) use (&$list, $id, $limit) {
        foreach (latest_posts($limit + 1, 0, $filter + ['exclude' => $id, 'newest' => true]) as $p) {
            if (count($list) < $limit && !isset($list[$p['id']])) {
                $list[$p['id']] = $p;
            }
        }
    };
    if (!empty($post['category_id'])) {
        $fill(['category_id' => (int) $post['category_id']]);
    }
    if (count($list) < $limit) {
        $fill([]);
    }
    return array_values($list);
}

function related_ids(int $postId): array
{
    try {
        return array_map('intval', array_column(db_all('SELECT related_id FROM post_related WHERE post_id = ? ORDER BY sort_order', [$postId]), 'related_id'));
    } catch (PDOException $e) {
        return [];
    }
}

function count_posts(array $filter = []): int
{
    [$where, $params] = post_filter($filter);
    return (int) db_val("SELECT COUNT(*) FROM posts p WHERE {$where}", $params);
}

function post_filter(array $filter): array
{
    $where = [published_clause()];
    $params = [now()];
    if (!empty($filter['category_id'])) {
        $where[] = 'p.category_id = ?';
        $params[] = (int) $filter['category_id'];
    }
    if (!empty($filter['author_id'])) {
        $where[] = 'p.author_id = ?';
        $params[] = (int) $filter['author_id'];
    }
    if (!empty($filter['tag_id'])) {
        $where[] = 'p.id IN (SELECT post_id FROM post_tag WHERE tag_id = ?)';
        $params[] = (int) $filter['tag_id'];
    }
    if (!empty($filter['exclude'])) {
        $where[] = 'p.id <> ?';
        $params[] = (int) $filter['exclude'];
    }
    if (isset($filter['q']) && $filter['q'] !== '') {
        $like = '%' . addcslashes($filter['q'], '%_\\') . '%';
        $where[] = '(p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)';
        array_push($params, $like, $like, $like);
    }
    return [implode(' AND ', $where), $params];
}

function find_published_post(string $slug): ?array
{
    return db_one(POST_SELECT . ' WHERE p.slug = ? AND ' . published_clause(), [$slug, now()]);
}

function post_url(array $post): string
{
    return '/blog/' . $post['slug'];
}

function post_excerpt(array $post, int $length = 160): string
{
    return $post['excerpt'] ? text_excerpt($post['excerpt'], $length) : text_excerpt($post['content'], $length);
}

function post_tags(int $postId): array
{
    return db_all('SELECT t.* FROM tags t JOIN post_tag pt ON pt.tag_id = t.id WHERE pt.post_id = ? ORDER BY t.name', [$postId]);
}

function categories_with_posts(): array
{
    return db_all(
        'SELECT c.*, (SELECT COUNT(*) FROM posts p WHERE p.category_id = c.id AND ' . published_clause() . ') AS total
         FROM categories c ORDER BY c.sort_order, c.name',
        [now()]
    );
}

function approved_comments(int $postId): array
{
    $rows = db_all(
        "SELECT * FROM comments WHERE post_id = ? AND status = 'approved' ORDER BY created_at ASC",
        [$postId]
    );
    // Organiza respostas embaixo do comentário original
    $byParent = [];
    foreach ($rows as $row) {
        $byParent[(int) ($row['parent_id'] ?? 0)][] = $row;
    }
    return $byParent;
}

function menu_items(): array
{
    try {
        return db_all('SELECT * FROM menu_items ORDER BY sort_order, id');
    } catch (PDOException $e) {
        return [];
    }
}

function active_slides(): array
{
    return db_all('SELECT * FROM slides WHERE active = 1 ORDER BY sort_order, id');
}

function paginate(int $total, int $perPage, int $page): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $page = min(max(1, $page), $pages);
    return ['page' => $page, 'pages' => $pages, 'offset' => ($page - 1) * $perPage, 'per_page' => $perPage];
}

// Formatos de imagem oferecidos no painel
function image_ratios(): array
{
    return [
        'original'  => ['label' => 'Original (sem cortar)', 'css' => null],
        'banner'    => ['label' => 'Banner fininho (3x1)', 'css' => '3 / 1'],
        'wide'      => ['label' => 'Retangular (16x9)', 'css' => '16 / 9'],
        'landscape' => ['label' => 'Retangular (4x3)', 'css' => '4 / 3'],
        'square'    => ['label' => 'Quadrada (1x1)', 'css' => '1 / 1'],
        'portrait'  => ['label' => 'Em pé (4x5)', 'css' => '4 / 5'],
    ];
}

function ratio_css(?string $key): ?string
{
    return image_ratios()[$key ?? '']['css'] ?? null;
}

// Opções de uma imagem da home (tamanho, cantos, formato, alinhamento)
function image_opts($opts, array $defaults = []): array
{
    $o = array_replace(['width' => 100, 'radius' => 16, 'ratio' => 'original', 'align' => 'left'], $defaults, is_array($opts) ? $opts : []);
    return [
        'width'  => max(20, min(100, (int) $o['width'])),
        'radius' => max(0, min(999, (int) $o['radius'])),
        'ratio'  => array_key_exists($o['ratio'], image_ratios()) ? $o['ratio'] : 'original',
        'align'  => in_array($o['align'], ['left', 'center', 'right'], true) ? $o['align'] : 'left',
    ];
}

function image_opts_style(array $o): string
{
    $css = ['width:' . $o['width'] . '%', 'border-radius:' . $o['radius'] . 'px'];
    if ($ratio = ratio_css($o['ratio'])) {
        $css[] = 'aspect-ratio:' . $ratio;
        $css[] = 'object-fit:cover';
    }
    $css[] = $o['align'] === 'center' ? 'margin-left:auto;margin-right:auto' : ($o['align'] === 'right' ? 'margin-left:auto' : 'margin-right:auto');
    return implode(';', $css);
}

function image_opts_from_post(string $key): array
{
    return image_opts([
        'width'  => $_POST[$key]['width'] ?? 100,
        'radius' => $_POST[$key]['radius'] ?? 0,
        'ratio'  => $_POST[$key]['ratio'] ?? 'original',
        'align'  => $_POST[$key]['align'] ?? 'left',
    ]);
}

function cards_class(): string
{
    $blog = setting('blog');
    $cols = max(2, min(5, (int) $blog['cards_per_row']));
    return 'cards cols-' . $cols . ($blog['cards_layout'] === 'masonry' ? ' is-masonry' : '');
}

function avatar_url(?string $avatar): string
{
    return $avatar ? media_url($avatar) : asset('img/avatar-padrao.svg');
}

function gravatar_initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $initials = mb_substr($parts[0] ?? '', 0, 1) . (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '');
    return mb_strtoupper($initials ?: '?');
}

// Dados estruturados: dizem ao Google, por baixo dos panos, o que é cada página
function site_json_ld(): array
{
    $site = setting('site');
    return [
        '@context' => 'https://schema.org',
        '@graph'   => [
            ['@type' => 'WebSite', 'name' => $site['title'], 'url' => absolute_url('/'), 'inLanguage' => 'pt-BR',
                'potentialAction' => ['@type' => 'SearchAction', 'target' => absolute_url('/busca') . '?q={busca}', 'query-input' => 'required name=busca']],
            ['@type' => 'Organization', 'name' => $site['title'], 'url' => absolute_url('/'),
                'logo' => absolute_url(media_url($site['logo'])), 'sameAs' => array_values(array_filter($site['socials']))],
        ],
    ];
}

function post_json_ld(array $post): array
{
    $site = setting('site');
    $data = [
        '@context'      => 'https://schema.org',
        '@type'         => 'BlogPosting',
        'headline'      => mb_substr($post['title'], 0, 110),
        'description'   => $post['meta_description'] ?: post_excerpt($post, 155),
        'url'           => absolute_url(post_url($post)),
        'datePublished' => date('c', strtotime((string) ($post['published_at'] ?: $post['created_at']))),
        'dateModified'  => date('c', strtotime((string) $post['updated_at'])),
        'inLanguage'    => 'pt-BR',
        'author'        => ['@type' => 'Person', 'name' => $post['author_name'], 'url' => absolute_url('/blog/autor/' . $post['author_slug'])],
        'publisher'     => ['@type' => 'Organization', 'name' => $site['title'], 'logo' => ['@type' => 'ImageObject', 'url' => absolute_url(media_url($site['logo']))]],
        'mainEntityOfPage' => absolute_url(post_url($post)),
    ];
    if ($post['cover_image']) {
        $data['image'] = absolute_url(media_url($post['cover_image']));
    }
    if (!empty($post['category_name'])) {
        $data['articleSection'] = $post['category_name'];
    }
    return $data;
}

// Imagem ao lado do texto: quando a imagem é diminuída, a coluna dela encolhe junto e o texto se aproxima
function split_image(array $o): array
{
    return ['vars' => '--img-w:' . round($o['width'] / 100, 2), 'style' => image_opts_style(['width' => 100] + $o)];
}

// Separador entre os avisos da faixa
function ticker_separator(): string
{
    $home = setting('home');
    $symbols = ['star' => '★', 'dot' => '•', 'diamond' => '◆', 'dash' => '—', 'bar' => '|'];
    $sep = (string) ($home['ticker_sep'] ?? 'flag');
    if (isset($symbols[$sep])) {
        return '<span class="tk-sep" aria-hidden="true">' . $symbols[$sep] . '</span>';
    }
    if ($sep === 'text' && trim((string) $home['ticker_sep_text']) !== '') {
        return '<span class="tk-sep" aria-hidden="true">' . e($home['ticker_sep_text']) . '</span>';
    }
    if ($sep === 'image' && $home['ticker_sep_image'] !== '') {
        return '<img class="tk-sep-img" src="' . e(media_url($home['ticker_sep_image'])) . '" alt="">';
    }
    if ($sep === 'none' || $sep === 'text' || $sep === 'image') {
        return '';
    }
    return '<img class="tk-sep-img" src="' . e(asset('img/ticker-icone.svg')) . '" alt="" width="18" height="18">';
}
