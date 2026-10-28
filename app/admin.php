<?php
declare(strict_types=1);

// Painel administrativo

require_once ROOT . '/app/media.php';

function admin_dispatch(string $path): void
{
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');
    header('X-LiteSpeed-Cache-Control: no-cache');
    run_migrations();

    $routes = [
        ['#^/admin/login$#',                          'admin_login', false],
        ['#^/admin/sair$#',                           'admin_logout', false],
        ['#^/admin$#',                                'admin_home', true],
        ['#^/admin/posts$#',                          'admin_posts', true],
        ['#^/admin/posts/novo$#',                     'admin_post_new', true],
        ['#^/admin/posts/(\d+)$#',                    'admin_post_edit', true],
        ['#^/admin/posts/(\d+)/excluir$#',            'admin_post_delete', true],
        ['#^/admin/midia$#',                          'admin_media', true],
        ['#^/admin/midia/lista$#',                    'admin_media_list', true],
        ['#^/admin/midia/enviar$#',                   'admin_media_upload', true],
        ['#^/admin/midia/(\d+)/excluir$#',            'admin_media_delete', true],
        ['#^/admin/comentarios$#',                    'admin_comments', true],
        ['#^/admin/comentarios/(\d+)/(aprovar|pendente|spam|excluir|responder)$#', 'admin_comment_action', true],
        ['#^/admin/mensagens$#',                      'admin_messages', true],
        ['#^/admin/mensagens/(\d+)$#',                'admin_message_view', true],
        ['#^/admin/mensagens/(\d+)/excluir$#',        'admin_message_delete', true],
        ['#^/admin/newsletter$#',                     'admin_newsletter', true],
        ['#^/admin/newsletter/exportar$#',            'admin_newsletter_export', true],
        ['#^/admin/newsletter/(\d+)/excluir$#',       'admin_subscriber_delete', true],
        ['#^/admin/categorias$#',                     'admin_categories', true],
        ['#^/admin/categorias/(\d+)/excluir$#',       'admin_category_delete', true],
        ['#^/admin/tags$#',                           'admin_tags', true],
        ['#^/admin/tags/(\d+)/excluir$#',             'admin_tag_delete', true],
        ['#^/admin/autores$#',                        'admin_users', true],
        ['#^/admin/autores/novo$#',                   'admin_user_edit', true],
        ['#^/admin/autores/(\d+)$#',                  'admin_user_edit', true],
        ['#^/admin/perfil$#',                         'admin_profile', true],
        ['#^/admin/aparencia$#',                      'admin_appearance', true],
        ['#^/admin/conteudo$#',                       'admin_content', true],
        ['#^/admin/banners$#',                        'admin_banners', true],
        ['#^/admin/secoes$#',                         'admin_sections', true],
        ['#^/admin/paginas$#',                        'admin_pages', true],
        ['#^/admin/paginas/nova$#',                   'admin_page_edit', true],
        ['#^/admin/paginas/(\d+)$#',                 'admin_page_edit', true],
        ['#^/admin/paginas/(\d+)/excluir$#',         'admin_page_delete', true],
        ['#^/admin/secoes/(\d+)$#',                  'admin_section_edit', true],
        ['#^/admin/menu$#',                           'admin_menu', true],
    ];
    foreach ($routes as [$pattern, $handler, $needsLogin]) {
        if (preg_match($pattern, $path, $m)) {
            if ($needsLogin) {
                require_login();
            }
            if (is_post() && $handler !== 'admin_login') {
                csrf_check();
            }
            $handler(...array_slice($m, 1));
            return;
        }
    }
    require_login();
    http_response_code(404);
    admin_render('simple', ['title' => 'Página não encontrada', 'message' => 'Essa página do painel não existe.']);
}

function admin_render(string $template, array $vars = []): void
{
    render('admin/' . $template, $vars, 'admin/layout');
}

function admin_json(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function pending_counts(): array
{
    static $counts = null;
    if ($counts === null) {
        $counts = [
            'comments' => (int) db_val("SELECT COUNT(*) FROM comments WHERE status = 'pending'"),
            'messages' => (int) db_val('SELECT COUNT(*) FROM messages WHERE read_at IS NULL'),
        ];
    }
    return $counts;
}

function can_edit_post(array $post): bool
{
    $user = current_user();
    return $user && ($user['role'] === 'admin' || (int) $post['author_id'] === (int) $user['id']);
}

function post_bool(string $key): int
{
    return isset($_POST[$key]) && $_POST[$key] !== '0' ? 1 : 0;
}

function valid_color(string $value, string $fallback = ''): string
{
    return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtoupper($value) : $fallback;
}

/* ---------------- Pedaços de tela reaproveitados ---------------- */

function partial_status(array $p): void
{
    if ($p['status'] === 'draft') {
        echo '<span class="a-pill a-pill-draft">Rascunho</span>';
    } elseif (strtotime((string) $p['published_at']) > time()) {
        echo '<span class="a-pill a-pill-sched" title="' . e(date('d/m/Y H:i', strtotime((string) $p['published_at']))) . '">Agendado</span>';
    } else {
        echo '<span class="a-pill a-pill-pub">Publicado</span>';
    }
}

function image_field(string $name, ?string $value, string $label, string $help = ''): string
{
    return render_to_string('admin/image-field', ['name' => $name, 'value' => (string) $value, 'label' => $label, 'help' => $help]);
}

function admin_pager(array $pg, string $base): string
{
    if ($pg['pages'] < 2) {
        return '';
    }
    $sep = str_contains($base, '?') ? '&' : '?';
    $html = '<nav class="a-pager" aria-label="Páginas">';
    for ($i = 1; $i <= $pg['pages']; $i++) {
        $html .= '<a href="' . e($base . $sep . 'pagina=' . $i) . '"' . ($i === $pg['page'] ? ' aria-current="page"' : '') . '>' . $i . '</a>';
    }
    return $html . '</nav>';
}

function select_options(array $options, ?string $selected): string
{
    $html = '';
    foreach ($options as $value => $label) {
        $html .= '<option value="' . e((string) $value) . '"' . ((string) $value === (string) $selected ? ' selected' : '') . '>' . e($label) . '</option>';
    }
    return $html;
}

/* ---------------- Login ---------------- */

function admin_login(): void
{
    if (current_user()) {
        redirect('/admin');
    }
    $error = null;
    if (is_post()) {
        csrf_check();
        if (too_many_login_attempts()) {
            $error = 'Muitas tentativas. Aguarde 15 minutos e tente de novo.';
        } elseif (attempt_login(input('email'), (string) ($_POST['senha'] ?? ''))) {
            $back = (string) ($_GET['volta'] ?? '/admin');
            redirect(preg_match('#^/admin(/|$)#', $back) ? $back : '/admin');
        } else {
            $error = 'E-mail ou senha incorretos.';
        }
    }
    render('admin/login', [
        'title'     => 'Entrar no painel',
        'error'     => $error,
        'installed' => isset($_GET['instalado']),
    ], '');
}

function admin_logout(): void
{
    if (is_post()) {
        csrf_check();
        logout();
    }
    redirect('/admin/login');
}

function admin_home(): void
{
    $user = current_user();
    $mine = $user['role'] === 'admin' ? '' : ' AND author_id = ' . (int) $user['id'];
    admin_render('dashboard', [
        'title' => 'Início',
        'user'  => $user,
        'stats' => [
            ['Posts publicados', (int) db_val("SELECT COUNT(*) FROM posts WHERE status = 'published' AND published_at <= ?{$mine}", [now()]), '/admin/posts?status=publicados'],
            ['Agendados', (int) db_val("SELECT COUNT(*) FROM posts WHERE status = 'published' AND published_at > ?{$mine}", [now()]), '/admin/posts?status=agendados'],
            ['Rascunhos', (int) db_val("SELECT COUNT(*) FROM posts WHERE status = 'draft'{$mine}"), '/admin/posts?status=rascunhos'],
            ['Comentários pendentes', pending_counts()['comments'], '/admin/comentarios'],
            ['Mensagens não lidas', pending_counts()['messages'], '/admin/mensagens'],
            ['Inscritos na newsletter', (int) db_val('SELECT COUNT(*) FROM subscribers'), '/admin/newsletter'],
        ],
        'recent' => db_all("SELECT p.id, p.title, p.status, p.published_at, p.views FROM posts p WHERE 1=1{$mine} ORDER BY p.updated_at DESC LIMIT 6"),
        'popular' => db_all("SELECT p.id, p.title, p.slug, p.views FROM posts p WHERE p.status = 'published'{$mine} ORDER BY p.views DESC LIMIT 6"),
    ]);
}

/* ---------------- Posts ---------------- */

function admin_posts(): void
{
    $user = current_user();
    $where = ['1=1'];
    $params = [];
    if ($user['role'] !== 'admin') {
        $where[] = 'p.author_id = ?';
        $params[] = (int) $user['id'];
    }
    $status = (string) ($_GET['status'] ?? '');
    if ($status === 'publicados') {
        $where[] = "p.status = 'published' AND p.published_at <= ?";
        $params[] = now();
    } elseif ($status === 'agendados') {
        $where[] = "p.status = 'published' AND p.published_at > ?";
        $params[] = now();
    } elseif ($status === 'rascunhos') {
        $where[] = "p.status = 'draft'";
    }
    $q = trim((string) ($_GET['q'] ?? ''));
    if ($q !== '') {
        $where[] = 'p.title LIKE ?';
        $params[] = '%' . addcslashes($q, '%_\\') . '%';
    }
    $cat = (int) ($_GET['categoria'] ?? 0);
    if ($cat) {
        $where[] = 'p.category_id = ?';
        $params[] = $cat;
    }
    $total = (int) db_val('SELECT COUNT(*) FROM posts p WHERE ' . implode(' AND ', $where), $params);
    $pg = paginate($total, 25, (int) ($_GET['pagina'] ?? 1));
    $posts = db_all(
        'SELECT p.*, c.name AS category_name, u.name AS author_name,
            (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id AND cm.status = \'approved\') AS comments_count
         FROM posts p LEFT JOIN categories c ON c.id = p.category_id JOIN users u ON u.id = p.author_id
         WHERE ' . implode(' AND ', $where) . ' ORDER BY COALESCE(p.published_at, p.updated_at) DESC LIMIT ? OFFSET ?',
        [...$params, $pg['per_page'], $pg['offset']]
    );
    admin_render('posts', [
        'title'      => 'Posts',
        'posts'      => $posts,
        'pagination' => $pg,
        'status'     => $status,
        'q'          => $q,
        'category'   => $cat,
        'categories' => db_all('SELECT id, name FROM categories ORDER BY sort_order, name'),
    ]);
}

function empty_post(): array
{
    return [
        'id' => null, 'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '',
        'cover_image' => '', 'cover_alt' => '', 'cover_ratio' => 'original', 'cover_width' => 100,
        'cover_align' => 'center', 'cover_position' => '50% 50%', 'category_id' => null,
        'author_id' => current_user()['id'], 'status' => 'draft', 'published_at' => null,
        'featured' => 0, 'allow_comments' => 1, 'card_bg' => null, 'card_ratio' => null,
        'meta_title' => '', 'meta_description' => '',
    ];
}

function admin_post_new(): void
{
    admin_post_form(empty_post());
}

function admin_post_edit(string $id): void
{
    $post = db_one('SELECT * FROM posts WHERE id = ?', [(int) $id]);
    if (!$post || !can_edit_post($post)) {
        flash('error', 'Post não encontrado.');
        redirect('/admin/posts');
    }
    admin_post_form($post);
}

function admin_post_form(array $post): void
{
    $errors = [];
    if (is_post()) {
        [$post, $errors] = admin_post_save($post);
        if (!$errors) {
            flash('success', $post['status'] === 'draft' ? 'Rascunho salvo.' : (strtotime((string) $post['published_at']) > time() ? 'Post agendado.' : 'Post publicado.'));
            redirect('/admin/posts/' . $post['id']);
        }
    }
    $tags = $post['id']
        ? implode(', ', array_column(post_tags((int) $post['id']), 'name'))
        : (string) ($_POST['tags'] ?? '');
    admin_render('post-edit', [
        'title'      => $post['id'] ? 'Editar post' : 'Novo post',
        'post'       => $post,
        'errors'     => $errors,
        'tags'       => $tags,
        'categories' => db_all('SELECT id, name FROM categories ORDER BY sort_order, name'),
        'authors'    => db_all('SELECT id, name FROM users WHERE active = 1 ORDER BY name'),
        'editorAssets' => true,
    ]);
}

function admin_post_save(array $post): array
{
    $user = current_user();
    $errors = [];
    $data = [
        'title'            => mb_substr(input('title'), 0, 255),
        'excerpt'          => mb_substr(input('excerpt'), 0, 600) ?: null,
        'content'          => sanitize_html((string) ($_POST['content'] ?? '')),
        'cover_image'      => mb_substr(input('cover_image'), 0, 255) ?: null,
        'cover_alt'        => mb_substr(input('cover_alt'), 0, 255) ?: null,
        'cover_ratio'      => array_key_exists(input('cover_ratio'), image_ratios()) ? input('cover_ratio') : 'original',
        'cover_width'      => max(20, min(100, (int) input('cover_width', 100))),
        'cover_align'      => in_array(input('cover_align'), ['left', 'center', 'right'], true) ? input('cover_align') : 'center',
        'cover_position'   => preg_match('/^\d{1,3}% \d{1,3}%$/', input('cover_position')) ? input('cover_position') : '50% 50%',
        'category_id'      => (int) input('category_id') ?: null,
        'featured'         => post_bool('featured'),
        'allow_comments'   => post_bool('allow_comments'),
        'card_bg'          => isset($_POST['card_bg_custom']) ? (valid_color(input('card_bg')) ?: null) : null,
        'card_ratio'       => array_key_exists(input('card_ratio'), image_ratios()) ? input('card_ratio') : null,
        'meta_title'       => mb_substr(input('meta_title'), 0, 255) ?: null,
        'meta_description' => mb_substr(input('meta_description'), 0, 300) ?: null,
        'updated_at'       => now(),
    ];
    $action = input('acao', 'rascunho');
    $data['status'] = $action === 'rascunho' ? 'draft' : 'published';
    $when = input('published_at');
    if ($data['status'] === 'published') {
        $data['published_at'] = $when !== '' && strtotime($when) ? date('Y-m-d H:i:s', strtotime($when)) : ($post['published_at'] ?: now());
        if ($action === 'publicar_agora') {
            $data['published_at'] = now();
        }
    } else {
        $data['published_at'] = $when !== '' && strtotime($when) ? date('Y-m-d H:i:s', strtotime($when)) : null;
    }
    if ($user['role'] === 'admin' && (int) input('author_id')) {
        $data['author_id'] = (int) input('author_id');
    } elseif (!$post['id']) {
        $data['author_id'] = (int) $user['id'];
    }
    if ($data['title'] === '') {
        $errors[] = 'O título é obrigatório.';
    }
    $slugInput = input('slug');
    $data['slug'] = unique_slug('posts', $slugInput !== '' ? $slugInput : $data['title'], $post['id'] ? (int) $post['id'] : null);

    if ($errors) {
        return [array_merge($post, $data), $errors];
    }
    if ($post['id']) {
        db_update('posts', (int) $post['id'], $data);
        $id = (int) $post['id'];
    } else {
        $data['created_at'] = now();
        $id = db_insert('posts', $data);
    }
    save_post_tags($id, (string) ($_POST['tags'] ?? ''));
    return [db_one('SELECT * FROM posts WHERE id = ?', [$id]), []];
}

function save_post_tags(int $postId, string $raw): void
{
    db_exec('DELETE FROM post_tag WHERE post_id = ?', [$postId]);
    $names = array_unique(array_filter(array_map(fn ($t) => trim(ltrim(trim($t), '#')), explode(',', $raw))));
    foreach (array_slice($names, 0, 20) as $name) {
        $slug = slugify($name);
        $tagId = (int) db_val('SELECT id FROM tags WHERE slug = ?', [$slug]);
        if (!$tagId) {
            $tagId = db_insert('tags', ['name' => mb_substr($name, 0, 120), 'slug' => $slug]);
        }
        db_exec('INSERT IGNORE INTO post_tag (post_id, tag_id) VALUES (?, ?)', [$postId, $tagId]);
    }
}

function admin_post_delete(string $id): void
{
    $post = db_one('SELECT * FROM posts WHERE id = ?', [(int) $id]);
    if (is_post() && $post && can_edit_post($post)) {
        db_exec('DELETE FROM post_tag WHERE post_id = ?', [$post['id']]);
        db_exec('DELETE FROM comments WHERE post_id = ?', [$post['id']]);
        db_exec('DELETE FROM posts WHERE id = ?', [$post['id']]);
        flash('success', 'Post “' . $post['title'] . '” excluído.');
    }
    redirect('/admin/posts');
}

/* ---------------- Imagens ---------------- */

// Pasta escolhida na tela: 0 = todas, -1 = sem pasta, >0 = pasta
function media_folder_filter(): array
{
    $f = (int) ($_GET['pasta'] ?? 0);
    if ($f > 0) {
        return [$f, 'WHERE folder_id = ?', [$f]];
    }
    if ($f === -1) {
        return [-1, 'WHERE folder_id IS NULL', []];
    }
    return [0, '', []];
}

function admin_media(): void
{
    $back = '/admin/midia' . (isset($_GET['pasta']) ? '?pasta=' . (int) $_GET['pasta'] : '');
    if (is_post()) {
        $action = input('acao');
        $folderId = (int) input('folder_id') ?: null;
        if ($action === 'criar_pasta') {
            $name = mb_substr(input('nome'), 0, 120);
            if ($name !== '') {
                $id = db_insert('media_folders', ['name' => $name, 'created_at' => now()]);
                flash('success', 'Pasta “' . $name . '” criada.');
                redirect('/admin/midia?pasta=' . $id);
            }
        } elseif ($action === 'renomear_pasta' && $folderId) {
            $name = mb_substr(input('nome'), 0, 120);
            if ($name !== '') {
                db_exec('UPDATE media_folders SET name = ? WHERE id = ?', [$name, $folderId]);
                flash('success', 'Pasta renomeada.');
            }
        } elseif ($action === 'excluir_pasta' && $folderId) {
            db_exec('UPDATE media SET folder_id = NULL WHERE folder_id = ?', [$folderId]);
            db_exec('DELETE FROM media_folders WHERE id = ?', [$folderId]);
            flash('success', 'Pasta excluída. As imagens dela foram para “Sem pasta”.');
            redirect('/admin/midia');
        } elseif ($action === 'mover') {
            $ids = array_filter(array_map('intval', (array) ($_POST['ids'] ?? [])));
            $dest = (int) input('destino');
            if ($ids) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                db_exec("UPDATE media SET folder_id = ? WHERE id IN ({$in})", [$dest > 0 ? $dest : null, ...$ids]);
                flash('success', count($ids) === 1 ? 'Imagem movida.' : count($ids) . ' imagens movidas.');
            } else {
                flash('error', 'Marque as imagens que quer mover.');
            }
        } elseif (!empty($_FILES['arquivos'])) {
            $files = $_FILES['arquivos'];
            $ok = 0;
            foreach ((array) $files['name'] as $i => $name) {
                try {
                    $saved = store_uploaded_image([
                        'name' => $name, 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i],
                    ], (int) current_user()['id']);
                    if ($folderId) {
                        db_exec('UPDATE media SET folder_id = ? WHERE id = ?', [$folderId, $saved['id']]);
                    }
                    $ok++;
                } catch (RuntimeException $e) {
                    flash('error', $name . ': ' . $e->getMessage());
                }
            }
            if ($ok) {
                flash('success', $ok === 1 ? 'Imagem enviada.' : $ok . ' imagens enviadas.');
            }
        }
        redirect($back);
    }
    [$current, $where, $params] = media_folder_filter();
    $pg = paginate((int) db_val("SELECT COUNT(*) FROM media {$where}", $params), 48, (int) ($_GET['pagina'] ?? 1));
    admin_render('media', [
        'title'      => 'Imagens',
        'items'      => db_all("SELECT * FROM media {$where} ORDER BY id DESC LIMIT ? OFFSET ?", [...$params, $pg['per_page'], $pg['offset']]),
        'pagination' => $pg,
        'folders'    => db_all('SELECT f.*, (SELECT COUNT(*) FROM media m WHERE m.folder_id = f.id) AS total FROM media_folders f ORDER BY f.name'),
        'current'    => $current,
        'counts'     => ['all' => (int) db_val('SELECT COUNT(*) FROM media'), 'none' => (int) db_val('SELECT COUNT(*) FROM media WHERE folder_id IS NULL')],
    ]);
}

// Usado pelo seletor de imagens (JSON)
function admin_media_list(): void
{
    $page = max(1, (int) ($_GET['pagina'] ?? 1));
    [, $where, $params] = media_folder_filter();
    $rows = db_all("SELECT id, path, width, height, alt, original_name FROM media {$where} ORDER BY id DESC LIMIT 61 OFFSET ?", [...$params, ($page - 1) * 60]);
    admin_json([
        'items'   => array_map(fn ($r) => $r + ['url' => media_url($r['path'])], array_slice($rows, 0, 60)),
        'more'    => count($rows) > 60,
        'folders' => db_all('SELECT id, name FROM media_folders ORDER BY name'),
    ]);
}

function admin_media_upload(): void
{
    if (!is_post() || empty($_FILES['arquivo'])) {
        admin_json(['error' => 'Nenhum arquivo recebido.'], 400);
    }
    try {
        $saved = store_uploaded_image($_FILES['arquivo'], (int) current_user()['id'], input('alt'));
        if ((int) input('folder_id') > 0) {
            db_exec('UPDATE media SET folder_id = ? WHERE id = ?', [(int) input('folder_id'), $saved['id']]);
        }
        admin_json($saved + ['url' => media_url($saved['path'])]);
    } catch (RuntimeException $e) {
        admin_json(['error' => $e->getMessage()], 422);
    }
}

function admin_media_delete(string $id): void
{
    if (is_post()) {
        delete_media((int) $id);
        flash('success', 'Imagem excluída.');
    }
    redirect('/admin/midia');
}

/* ---------------- Comentários ---------------- */

function admin_comments(): void
{
    $status = in_array($_GET['status'] ?? '', ['pending', 'approved', 'spam'], true) ? $_GET['status'] : 'pending';
    $pg = paginate((int) db_val('SELECT COUNT(*) FROM comments WHERE status = ?', [$status]), 30, (int) ($_GET['pagina'] ?? 1));
    admin_render('comments', [
        'title'    => 'Comentários',
        'status'   => $status,
        'counts'   => array_column(db_all('SELECT status, COUNT(*) AS n FROM comments GROUP BY status'), 'n', 'status'),
        'comments' => db_all(
            'SELECT c.*, p.title AS post_title, p.slug AS post_slug, parent.author_name AS parent_name
             FROM comments c JOIN posts p ON p.id = c.post_id LEFT JOIN comments parent ON parent.id = c.parent_id
             WHERE c.status = ? ORDER BY c.created_at DESC LIMIT ? OFFSET ?',
            [$status, $pg['per_page'], $pg['offset']]
        ),
        'pagination' => $pg,
    ]);
}

function admin_comment_action(string $id, string $action): void
{
    $comment = db_one('SELECT * FROM comments WHERE id = ?', [(int) $id]);
    if (!is_post() || !$comment) {
        redirect('/admin/comentarios');
    }
    $back = '/admin/comentarios?status=' . urlencode((string) input('voltar', 'pending'));
    if ($action === 'excluir') {
        db_exec('DELETE FROM comments WHERE id = ? OR parent_id = ?', [$comment['id'], $comment['id']]);
        flash('success', 'Comentário excluído.');
    } elseif ($action === 'responder') {
        $user = current_user();
        $text = mb_substr(input('resposta'), 0, 5000);
        if ($text !== '') {
            db_exec("UPDATE comments SET status = 'approved' WHERE id = ?", [$comment['id']]);
            db_insert('comments', [
                'post_id' => $comment['post_id'], 'parent_id' => $comment['parent_id'] ?: $comment['id'],
                'user_id' => $user['id'], 'author_name' => $user['name'], 'author_email' => $user['email'],
                'content' => $text, 'status' => 'approved', 'ip' => client_ip(), 'user_agent' => null, 'created_at' => now(),
            ]);
            flash('success', 'Resposta publicada.');
        }
    } else {
        $map = ['aprovar' => 'approved', 'pendente' => 'pending', 'spam' => 'spam'];
        db_exec('UPDATE comments SET status = ? WHERE id = ?', [$map[$action], $comment['id']]);
        flash('success', ['aprovar' => 'Comentário aprovado.', 'pendente' => 'Comentário voltou para pendentes.', 'spam' => 'Marcado como spam.'][$action]);
    }
    redirect($back);
}

/* ---------------- Mensagens e newsletter ---------------- */

function admin_messages(): void
{
    $pg = paginate((int) db_val('SELECT COUNT(*) FROM messages'), 30, (int) ($_GET['pagina'] ?? 1));
    admin_render('messages', [
        'title'      => 'Mensagens',
        'messages'   => db_all('SELECT * FROM messages ORDER BY created_at DESC LIMIT ? OFFSET ?', [$pg['per_page'], $pg['offset']]),
        'pagination' => $pg,
    ]);
}

function admin_message_view(string $id): void
{
    $msg = db_one('SELECT * FROM messages WHERE id = ?', [(int) $id]);
    if (!$msg) {
        redirect('/admin/mensagens');
    }
    if (!$msg['read_at']) {
        db_exec('UPDATE messages SET read_at = ? WHERE id = ?', [now(), $msg['id']]);
    }
    admin_render('message', ['title' => 'Mensagem de ' . $msg['first_name'], 'msg' => $msg]);
}

function admin_message_delete(string $id): void
{
    if (is_post()) {
        db_exec('DELETE FROM messages WHERE id = ?', [(int) $id]);
        flash('success', 'Mensagem excluída.');
    }
    redirect('/admin/mensagens');
}

function admin_newsletter(): void
{
    if (is_post() && input('acao') === 'textos') {
        require_admin();
        save_setting('newsletter', ['title' => mb_substr(input('news_title'), 0, 120), 'text' => mb_substr(input('news_text'), 0, 400)]);
        flash('success', 'Textos da newsletter salvos.');
        redirect('/admin/newsletter');
    }
    $pg = paginate((int) db_val('SELECT COUNT(*) FROM subscribers'), 50, (int) ($_GET['pagina'] ?? 1));
    admin_render('newsletter', [
        'title'       => 'Newsletter',
        'subscribers' => db_all('SELECT * FROM subscribers ORDER BY created_at DESC LIMIT ? OFFSET ?', [$pg['per_page'], $pg['offset']]),
        'total'       => (int) db_val('SELECT COUNT(*) FROM subscribers'),
        'pagination'  => $pg,
        'news'        => setting('newsletter'),
    ]);
}

function admin_newsletter_export(): void
{
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="inscritos-newsletter-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // acentos certos no Excel
    fputcsv($out, ['E-mail', 'Nome', 'Data de inscrição'], ';');
    foreach (db_all('SELECT email, name, created_at FROM subscribers ORDER BY created_at') as $row) {
        fputcsv($out, [$row['email'], $row['name'], date('d/m/Y H:i', strtotime($row['created_at']))], ';');
    }
    fclose($out);
    exit;
}

function admin_subscriber_delete(string $id): void
{
    if (is_post()) {
        db_exec('DELETE FROM subscribers WHERE id = ?', [(int) $id]);
        flash('success', 'Inscrito removido.');
    }
    redirect('/admin/newsletter');
}

/* ---------------- Categorias e tags ---------------- */

function admin_categories(): void
{
    if (is_post()) {
        $id = (int) input('id');
        $name = mb_substr(input('name'), 0, 120);
        if ($name === '') {
            flash('error', 'Dê um nome para a categoria.');
            redirect('/admin/categorias');
        }
        $data = [
            'name'        => $name,
            'slug'        => unique_slug('categories', input('slug') ?: $name, $id ?: null),
            'description' => mb_substr(input('description'), 0, 1000) ?: null,
            'sort_order'  => (int) input('sort_order'),
        ];
        $id ? db_update('categories', $id, $data) : db_insert('categories', $data);
        flash('success', $id ? 'Categoria atualizada.' : 'Categoria criada.');
        redirect('/admin/categorias');
    }
    admin_render('categories', [
        'title'      => 'Categorias',
        'categories' => db_all('SELECT c.*, (SELECT COUNT(*) FROM posts p WHERE p.category_id = c.id) AS total FROM categories c ORDER BY c.sort_order, c.name'),
        'editing'    => isset($_GET['editar']) ? db_one('SELECT * FROM categories WHERE id = ?', [(int) $_GET['editar']]) : null,
    ]);
}

function admin_category_delete(string $id): void
{
    if (is_post()) {
        db_exec('UPDATE posts SET category_id = NULL WHERE category_id = ?', [(int) $id]);
        db_exec('DELETE FROM categories WHERE id = ?', [(int) $id]);
        flash('success', 'Categoria excluída. Os posts dela ficaram sem categoria.');
    }
    redirect('/admin/categorias');
}

function admin_tags(): void
{
    if (is_post()) {
        $id = (int) input('id');
        $name = mb_substr(trim(ltrim(input('name'), '#')), 0, 120);
        if ($name !== '' && $id) {
            db_update('tags', $id, ['name' => $name, 'slug' => unique_slug('tags', $name, $id)]);
            flash('success', 'Tag atualizada.');
        }
        redirect('/admin/tags');
    }
    admin_render('tags', [
        'title' => 'Tags',
        'tags'  => db_all('SELECT t.*, (SELECT COUNT(*) FROM post_tag pt WHERE pt.tag_id = t.id) AS total FROM tags t ORDER BY t.name'),
    ]);
}

function admin_tag_delete(string $id): void
{
    if (is_post()) {
        db_exec('DELETE FROM post_tag WHERE tag_id = ?', [(int) $id]);
        db_exec('DELETE FROM tags WHERE id = ?', [(int) $id]);
        flash('success', 'Tag excluída.');
    }
    redirect('/admin/tags');
}

/* ---------------- Autores ---------------- */

function admin_users(): void
{
    require_admin();
    admin_render('users', [
        'title' => 'Autores',
        'users' => db_all('SELECT u.*, (SELECT COUNT(*) FROM posts p WHERE p.author_id = u.id) AS total FROM users u ORDER BY u.active DESC, u.name'),
    ]);
}

function admin_user_edit(?string $id = null): void
{
    require_admin();
    $user = $id ? db_one('SELECT * FROM users WHERE id = ?', [(int) $id]) : null;
    if ($id && !$user) {
        redirect('/admin/autores');
    }
    $user ??= ['id' => null, 'name' => '', 'email' => '', 'role' => 'author', 'bio' => '', 'avatar' => '', 'active' => 1];
    $errors = [];
    if (is_post()) {
        [$user, $errors] = save_user_form($user, true);
        if (!$errors) {
            flash('success', 'Autor salvo.');
            redirect('/admin/autores');
        }
    }
    admin_render('user-edit', ['title' => $user['id'] ? 'Editar autor' : 'Novo autor', 'u' => $user, 'errors' => $errors, 'adminMode' => true]);
}

function admin_profile(): void
{
    $user = current_user();
    $errors = [];
    if (is_post()) {
        [$user, $errors] = save_user_form($user, false);
        if (!$errors) {
            flash('success', 'Perfil atualizado.');
            redirect('/admin/perfil');
        }
    }
    admin_render('user-edit', ['title' => 'Meu perfil', 'u' => $user, 'errors' => $errors, 'adminMode' => false]);
}

function save_user_form(array $user, bool $adminMode): array
{
    $errors = [];
    $me = current_user();
    $data = [
        'name'   => mb_substr(input('name'), 0, 120),
        'email'  => mb_strtolower(mb_substr(input('email'), 0, 190)),
        'bio'    => mb_substr(input('bio'), 0, 1500) ?: null,
        'avatar' => mb_substr(input('avatar'), 0, 255) ?: null,
    ];
    if ($adminMode) {
        $data['role'] = input('role') === 'admin' ? 'admin' : 'author';
        $data['active'] = post_bool('active');
        if ($user['id'] && (int) $user['id'] === (int) $me['id']) {
            // Ninguém tira o próprio acesso de administrador sem querer
            $data['role'] = 'admin';
            $data['active'] = 1;
        }
    }
    if ($data['name'] === '') {
        $errors[] = 'Informe o nome.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Informe um e-mail válido.';
    } elseif (db_val('SELECT id FROM users WHERE email = ? AND id <> ?', [$data['email'], (int) ($user['id'] ?? 0)])) {
        $errors[] = 'Já existe um autor com esse e-mail.';
    }
    $password = (string) ($_POST['password'] ?? '');
    if ($password !== '' && strlen($password) < 10) {
        $errors[] = 'A senha precisa ter pelo menos 10 caracteres.';
    }
    if (!$user['id'] && $password === '') {
        $errors[] = 'Defina uma senha inicial para o novo autor.';
    }
    if (!$adminMode && $password !== '' && !password_verify((string) ($_POST['current_password'] ?? ''), $me['password_hash'])) {
        $errors[] = 'A senha atual não confere.';
    }
    if ($errors) {
        return [array_merge($user, $data), $errors];
    }
    if ($password !== '') {
        $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    }
    $data['slug'] = unique_slug('users', $data['name'], $user['id'] ? (int) $user['id'] : null);
    if ($user['id']) {
        db_update('users', (int) $user['id'], $data);
        $id = (int) $user['id'];
    } else {
        $data['created_at'] = now();
        $data['role'] ??= 'author';
        $data['active'] ??= 1;
        $id = db_insert('users', $data);
    }
    return [db_one('SELECT * FROM users WHERE id = ?', [$id]), []];
}

/* ---------------- Aparência ---------------- */

function appearance_groups(): array
{
    return [
        'Cabeçalho e faixa' => ['header_bg' => 'Fundo do cabeçalho', 'header_text' => 'Texto do cabeçalho', 'header_accent' => 'Menu e destaques',
            'menu_hover' => 'Menu ao passar o mouse', 'menu_line' => 'Risco ou fundo do menu', 'menu_current' => 'Menu na página atual', 'ticker_bg' => 'Fundo da faixa corrida', 'ticker_text' => 'Texto da faixa corrida'],
        'Seção Blog e cards' => ['blog_bg' => 'Fundo da seção Blog', 'blog_title' => 'Título da seção Blog', 'card_bg' => 'Fundo dos cards', 'card_title' => 'Título dos cards', 'card_text' => 'Texto dos cards'],
        'Seção O Arquivo' => ['arquivo_bg' => 'Fundo', 'arquivo_title' => 'Título', 'arquivo_text' => 'Texto'],
        'Seção Playlists' => ['playlists_bg' => 'Fundo', 'playlists_title' => 'Título', 'playlists_text' => 'Texto'],
        'Seção Acervo e História' => ['acervo_bg' => 'Fundo', 'acervo_title' => 'Títulos', 'acervo_text' => 'Texto'],
        'Seção Contato' => ['contact_bg' => 'Fundo', 'contact_title' => 'Título', 'contact_text' => 'Texto'],
        'Rodapé' => ['footer_bg' => 'Fundo', 'footer_text' => 'Texto', 'footer_accent' => 'Títulos e destaques'],
        'Botão principal (O Arquivo, Playlists, newsletter, Texto + imagem, busca)' => ['button_bg' => 'Fundo', 'button_text' => 'Texto', 'button_hover_bg' => 'Fundo ao passar o mouse', 'button_hover_text' => 'Texto ao passar o mouse'],
        'Botão “Ver todas” (blog e Posts de uma categoria)' => ['button_all_bg' => 'Fundo', 'button_all_text' => 'Texto', 'button_all_hover_bg' => 'Fundo ao passar o mouse', 'button_all_hover_text' => 'Texto ao passar o mouse'],
        'Botão escuro (Acervo, História, Enviar do contato, comentário)' => ['button_dark_bg' => 'Fundo', 'button_dark_text' => 'Texto', 'button_dark_hover_bg' => 'Fundo ao passar o mouse', 'button_dark_hover_text' => 'Texto ao passar o mouse'],
        'Página do post' => ['post_page_bg' => 'Fundo da página', 'post_box_bg' => 'Fundo do box do post', 'post_title' => 'Títulos', 'post_text' => 'Texto', 'post_link' => 'Links', 'post_nav_bg' => 'Fundo da barra de categorias', 'post_nav_text' => 'Texto da barra de categorias'],
    ];
}

function admin_appearance(): void
{
    require_admin();
    $current = setting('appearance');
    $defaults = settings_defaults()['appearance'];
    if (is_post()) {
        if (input('acao') === 'restaurar') {
            save_setting('appearance', $defaults);
            $type = setting('type') ?? [];
            unset($type['post_title']);
            save_setting('type', $type);
            flash('success', 'Cores e fontes voltaram ao padrão.');
            redirect('/admin/aparencia');
        }
        $colors = [];
        foreach ($defaults['colors'] as $key => $default) {
            $colors[$key] = valid_color((string) ($_POST['colors'][$key] ?? ''), $current['colors'][$key] ?? $default);
        }
        $fonts = [];
        foreach (['heading', 'body', 'ui'] as $role) {
            $font = (string) ($_POST['fonts'][$role] ?? '');
            $fonts[$role] = in_array($font, font_choices(), true) ? $font : $current['fonts'][$role];
        }
        $sizes = [
            'logo'   => max(40, min(220, (int) input('size_logo', 96))),
            'header' => max(60, min(320, (int) input('size_header', 120))),
        ];
        $fontSizes = [];
        foreach (['heading' => [24, 80], 'body' => [13, 24], 'ui' => [11, 22]] as $role => [$min, $max]) {
            $fontSizes[$role] = max($min, min($max, (int) ($_POST['font_sizes'][$role] ?? base_font_sizes()[$role])));
        }
        $fontWeights = [
            'heading' => valid_weight($_POST['font_weights']['heading'] ?? 0, 800),
            'body'    => valid_weight($_POST['font_weights']['body'] ?? 0, 400),
        ];
        $header = ['overlay' => max(0, min(100, (int) input('header_overlay', 100))), 'gradient' => (bool) post_bool('header_gradient')];
        $menu = [
            'align'  => in_array(input('menu_align'), ['logo', 'center', 'right'], true) ? input('menu_align') : 'right',
            'gap'    => max(8, min(80, (int) input('menu_gap', 30))),
            'effect' => in_array(input('menu_effect'), ['line', 'grow', 'pill', 'color'], true) ? input('menu_effect') : 'line',
        ];
        save_setting('appearance', ['colors' => $colors, 'fonts' => $fonts, 'font_sizes' => $fontSizes, 'font_weights' => $fontWeights, 'header' => $header, 'menu' => $menu, 'sizes' => $sizes]);
        $site = setting('site');
        $site['header_bg'] = mb_substr(input('header_image'), 0, 255);
        save_setting('site', $site);
        save_type_from_post(['post_title']);
        $blog = setting('blog');
        $blog['cards_per_row'] = max(2, min(5, (int) input('cards_per_row', 3)));
        $blog['cards_layout'] = input('cards_layout') === 'masonry' ? 'masonry' : 'grid';
        $blog['card_ratio'] = array_key_exists(input('card_ratio'), image_ratios()) ? input('card_ratio') : 'wide';
        $blog['card_title_lines'] = max(0, min(6, (int) input('card_title_lines')));
        $blog['card_excerpt_lines'] = max(0, min(8, (int) input('card_excerpt_lines')));
        save_setting('blog', $blog);
        flash('success', 'Aparência salva. Abra o site para conferir.');
        redirect('/admin/aparencia');
    }
    admin_render('appearance', [
        'title'    => 'Aparência',
        'a'        => $current,
        'blog'     => setting('blog'),
        'site'     => setting('site'),
        'groups'   => appearance_groups(),
    ]);
}

/* ---------------- Conteúdo do site ---------------- */

function admin_content(): void
{
    require_admin();
    $tab = in_array($_GET['aba'] ?? '', ['site', 'home', 'rodape', 'blog'], true) ? $_GET['aba'] : 'site';
    if (is_post()) {
        $tab = input('aba');
        if ($tab === 'site') {
            $site = setting('site');
            foreach (['title' => 120, 'tagline' => 300, 'description' => 300, 'email' => 190] as $k => $len) {
                $site[$k] = mb_substr(input($k), 0, $len);
            }
            foreach (['logo', 'share_image'] as $k) {
                $site[$k] = mb_substr(input($k), 0, 255);
            }
            foreach (array_keys($site['socials']) as $net) {
                $link = trim((string) ($_POST['socials'][$net] ?? ''));
                $site['socials'][$net] = $link === '' || preg_match('#^https?://#', $link) ? $link : 'https://' . $link;
            }
            save_setting('site', $site);
            $notify = [];
            foreach (['contact', 'newsletter', 'comments'] as $k) {
                $addr = mb_strtolower(trim((string) ($_POST['notify'][$k] ?? '')));
                if ($addr !== '' && !filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                    flash('error', 'O e-mail “' . $addr . '” não parece válido. Confira e salve de novo.');
                    $addr = notify_to($k);
                }
                $notify[$k] = mb_substr($addr, 0, 190);
            }
            save_setting('notify', $notify);
            $pixel = preg_replace('/\D/', '', input('meta_pixel'));
            $ga = strtoupper(trim(input('ga4')));
            if ($ga !== '' && !preg_match('/^G-[A-Z0-9]{4,20}$/', $ga)) {
                flash('error', 'O código do Google Analytics começa com “G-”, por exemplo G-AB12CD34EF.');
                $ga = (string) (setting('tracking')['ga4'] ?? '');
            }
            save_setting('tracking', [
                'meta_pixel'  => mb_substr($pixel, 0, 20),
                'ga4'         => $ga,
                'cookie_text' => mb_substr(input('cookie_text'), 0, 300) ?: settings_defaults()['tracking']['cookie_text'],
            ]);
            if (input('acao') === 'teste_email') {
                $sent = [];
                foreach (array_unique(array_filter([notify_to('contact'), notify_to('newsletter'), notify_to('comments')])) as $addr) {
                    if (send_mail($addr, 'Teste de aviso do site', "Se você recebeu este e-mail, os avisos do site estão chegando.\n\n" . absolute_url('/admin'))) {
                        $sent[] = $addr;
                    }
                }
                if ($sent) {
                    flash('success', 'E-mail de teste enviado para ' . implode(', ', $sent) . '. Confira a caixa de entrada e o spam nos próximos minutos.');
                } else {
                    flash('error', 'O servidor não conseguiu enviar o e-mail de teste (ou nenhum e-mail está preenchido).');
                }
                redirect('/admin/conteudo?aba=site#g-avisos');
            }
        } elseif ($tab === 'home') {
            $home = setting('home');
            $ticker = [];
            foreach ((array) ($_POST['ticker_items'] ?? []) as $item) {
                $t = mb_substr(trim((string) ($item['text'] ?? '')), 0, 200);
                if ($t !== '') {
                    $ticker[] = ['text' => $t, 'link' => mb_substr(trim((string) ($item['link'] ?? '')), 0, 255), 'new_tab' => !empty($item['new_tab'])];
                }
            }
            $home['ticker_items'] = $ticker;
            $home['ticker'] = '';
            $text = ['blog_title', 'blog_button', 'arquivo_title', 'arquivo_text', 'arquivo_button', 'arquivo_link', 'playlists_title', 'playlists_intro', 'playlists_outro', 'contact_title', 'contact_text', 'arquivo_image',
                'playlists_image', 'playlists_button', 'playlists_link'];
            $home['playlists_layout'] = input('playlists_layout') === 'feature' ? 'feature' : 'grid';
            $home['playlists_align'] = in_array(input('playlists_align'), ['left', 'center', 'right', 'justify'], true) ? input('playlists_align') : 'left';
            $home['playlists_new_tab'] = (bool) post_bool('playlists_new_tab');
            $home['playlists_img'] = image_opts_from_post('playlists_img');
            foreach (['acervo', 'historia'] as $col) {
                array_push($text, $col . '_title', $col . '_text', $col . '_button', $col . '_link', $col . '_image');
                $home[$col . '_show'] = (bool) post_bool($col . '_show');
                $home[$col . '_new_tab'] = (bool) post_bool($col . '_new_tab');
                $home[$col . '_img'] = image_opts_from_post($col . '_img');
            }
            foreach ($text as $k) {
                $home[$k] = trim(mb_substr((string) ($_POST[$k] ?? ''), 0, 3000));
            }
            foreach (array_keys(settings_defaults()['home']['contact_labels']) as $f) {
                $home['contact_labels'][$f] = mb_substr(trim((string) ($_POST['contact_labels'][$f] ?? '')), 0, 60) ?: settings_defaults()['home']['contact_labels'][$f];
            }
            $home['contact_button'] = mb_substr(input('contact_button'), 0, 40) ?: 'Enviar';
            $home['blog_count'] = max(1, min(24, (int) input('blog_count', 6)));
            $home['ticker_pad'] = max(0, min(40, (int) input('ticker_pad', 9)));
            $home['ticker_speed'] = max(1, min(10, (int) input('ticker_speed', 4)));
            $home['ticker_gap'] = max(0, min(80, (int) input('ticker_gap', 22)));
            $home['ticker_sep'] = in_array(input('ticker_sep'), ['flag', 'star', 'dot', 'diamond', 'dash', 'bar', 'none', 'text', 'image'], true) ? input('ticker_sep') : 'flag';
            $home['ticker_sep_text'] = mb_substr(input('ticker_sep_text'), 0, 30);
            $home['ticker_sep_image'] = mb_substr(input('ticker_sep_image'), 0, 255);
            $home['arquivo_new_tab'] = (bool) post_bool('arquivo_new_tab');
            $home['arquivo_img'] = image_opts_from_post('arquivo_img');
            $playlists = [];
            foreach ((array) ($_POST['playlists'] ?? []) as $pl) {
                if (trim((string) ($pl['title'] ?? '')) === '' && trim((string) ($pl['image'] ?? '')) === '') {
                    continue;
                }
                $playlists[] = [
                    'title' => mb_substr(trim((string) $pl['title']), 0, 120),
                    'image' => mb_substr(trim((string) $pl['image']), 0, 255),
                    'link'  => mb_substr(trim((string) $pl['link']), 0, 255),
                    'new_tab' => !empty($pl['new_tab']),
                ];
            }
            $home['playlists'] = $playlists;
            save_setting('home', $home);
            save_type_from_post(['ticker', 'blog_title', 'blog_button', 'arquivo_title', 'arquivo_text', 'arquivo_button', 'playlists_title', 'playlists_intro', 'playlists_names', 'playlists_outro', 'playlists_button',
                'acervo_title', 'acervo_text', 'acervo_button', 'historia_title', 'historia_text', 'historia_button', 'contact_title', 'contact_text', 'contact_labels', 'contact_button']);
        } elseif ($tab === 'rodape') {
            save_setting('footer', [
                'word'         => mb_substr(input('word'), 0, 40),
                'show_word'    => (bool) post_bool('show_word'),
                'nav_title'    => mb_substr(input('nav_title'), 0, 60),
                'rss_label'    => mb_substr(input('rss_label'), 0, 40),
                'copyright'    => mb_substr(input('copyright'), 0, 200),
                'author_label' => mb_substr(input('author_label'), 0, 40),
                'email_label'  => mb_substr(input('email_label'), 0, 40),
            ]);
            save_setting('newsletter', [
                'title'       => mb_substr(input('news_title'), 0, 120),
                'text'        => mb_substr(input('news_text'), 0, 400),
                'placeholder' => mb_substr(input('news_placeholder'), 0, 60),
                'button'      => mb_substr(input('news_button'), 0, 40) ?: 'Quero receber',
            ]);
            save_type_from_post(['footer_titles', 'footer_texts', 'footer_links', 'footer_news_button', 'footer_email', 'footer_word', 'footer_copyright', 'footer_author']);
        } elseif ($tab === 'blog') {
            $blog = setting('blog');
            $blog['per_page'] = max(3, min(48, (int) input('per_page', 9)));
            $blog['comments_enabled'] = (bool) post_bool('comments_enabled');
            $blog['comments_moderate'] = (bool) post_bool('comments_moderate');
            $blog['share_buttons'] = (bool) post_bool('share_buttons');
            save_setting('blog', $blog);
        }
        flash('success', 'Alterações salvas.');
        redirect('/admin/conteudo?aba=' . urlencode($tab));
    }
    admin_render('content', [
        'title'  => 'Conteúdo do site',
        'tab'    => $tab,
        'site'   => setting('site'),
        'home'   => setting('home'),
        'footer' => setting('footer'),
        'news'   => setting('newsletter'),
        'blog'   => setting('blog'),
    ]);
}

/* ---------------- Páginas ---------------- */

const RESERVED_SLUGS = ['admin', 'blog', 'busca', 'contato', 'newsletter', 'sitemap', 'feed', 'robots', 'install', 'assets', 'uploads', 'app', 'templates', 'database', 'storage', 'tests', 'index'];

function admin_pages(): void
{
    require_admin();
    admin_render('pages', ['title' => 'Páginas', 'pages' => db_all('SELECT * FROM pages ORDER BY title')]);
}

function admin_page_edit(?string $id = null): void
{
    require_admin();
    $page = $id ? db_one('SELECT * FROM pages WHERE id = ?', [(int) $id]) : null;
    if ($id && !$page) {
        redirect('/admin/paginas');
    }
    $page ??= ['id' => null, 'title' => '', 'slug' => '', 'subtitle' => '', 'content' => '', 'banner_image' => '', 'banner_position' => '50% 50%', 'title_over_banner' => 1,
        'width' => 'narrow', 'bg_color' => null, 'text_color' => null, 'title_color' => null, 'status' => 'draft', 'meta_title' => '', 'meta_description' => ''];
    $errors = [];
    if (is_post()) {
        $data = [
            'title'             => mb_substr(input('title'), 0, 255),
            'subtitle'          => mb_substr(input('subtitle'), 0, 500) ?: null,
            'content'           => sanitize_html((string) ($_POST['content'] ?? '')),
            'banner_image'      => mb_substr(input('banner_image'), 0, 255) ?: null,
            'banner_position'   => preg_match('/^\d{1,3}% \d{1,3}%$/', input('banner_position')) ? input('banner_position') : '50% 50%',
            'title_over_banner' => post_bool('title_over_banner'),
            'width'             => input('width') === 'wide' ? 'wide' : 'narrow',
            'status'            => input('acao') === 'publicar' ? 'published' : 'draft',
            'meta_title'        => mb_substr(input('meta_title'), 0, 255) ?: null,
            'meta_description'  => mb_substr(input('meta_description'), 0, 300) ?: null,
            'updated_at'        => now(),
        ];
        foreach (['bg_color', 'text_color', 'title_color'] as $c) {
            $data[$c] = isset($_POST[$c . '_on']) ? (valid_color(input($c)) ?: null) : null;
        }
        if ($data['title'] === '') {
            $errors[] = 'O título é obrigatório.';
        }
        $slug = slugify(input('slug') !== '' ? input('slug') : $data['title']);
        if (in_array($slug, RESERVED_SLUGS, true)) {
            $errors[] = 'O endereço “/' . $slug . '” é usado pelo próprio site. Escolha outro.';
        }
        if (!$errors) {
            $data['slug'] = unique_slug('pages', $slug, $page['id'] ? (int) $page['id'] : null);
            if ($page['id']) {
                db_update('pages', (int) $page['id'], $data);
                $newId = (int) $page['id'];
            } else {
                $data['created_at'] = now();
                $newId = db_insert('pages', $data);
            }
            flash('success', $data['status'] === 'published' ? 'Página publicada.' : 'Rascunho da página salvo.');
            redirect('/admin/paginas/' . $newId);
        }
        $page = array_merge($page, $data, ['slug' => $slug]);
    }
    admin_render('page-edit', ['title' => $page['id'] ? 'Editar página' : 'Nova página', 'page' => $page, 'errors' => $errors, 'editorAssets' => true]);
}

function admin_page_delete(string $id): void
{
    require_admin();
    if (is_post()) {
        db_exec('DELETE FROM pages WHERE id = ?', [(int) $id]);
        flash('success', 'Página excluída.');
    }
    redirect('/admin/paginas');
}

/* ---------------- Seções da home ---------------- */

function admin_sections(): void
{
    require_admin();
    if (is_post()) {
        $action = input('acao');
        $id = (int) input('id');
        $row = $id ? db_one('SELECT * FROM home_sections WHERE id = ?', [$id]) : null;
        if ($action === 'mover' && $row) {
            move_row('home_sections', $id, input('direcao'));
        } elseif ($action === 'alternar' && $row) {
            db_exec('UPDATE home_sections SET active = 1 - active WHERE id = ?', [$id]);
            flash('success', $row['active'] ? '“' . $row['label'] . '” escondida da home.' : '“' . $row['label'] . '” voltou para a home.');
        } elseif ($action === 'excluir' && $row && !section_types()[$row['type']]['builtin']) {
            db_exec('DELETE FROM home_sections WHERE id = ?', [$id]);
            flash('success', 'Seção removida.');
        } elseif ($action === 'duplicar' && $row && !section_types()[$row['type']]['builtin']) {
            $newId = db_insert('home_sections', [
                'type' => $row['type'], 'label' => mb_substr($row['label'] . ' (cópia)', 0, 120), 'anchor' => null,
                'active' => 0, 'sort_order' => (int) $row['sort_order'] + 1, 'data' => $row['data'],
            ]);
            flash('success', 'Seção duplicada. A cópia começa escondida.');
            redirect('/admin/secoes/' . $newId);
        } elseif ($action === 'adicionar') {
            $type = input('tipo');
            $types = section_types();
            if (isset($types[$type]) && !$types[$type]['builtin']) {
                $newId = db_insert('home_sections', [
                    'type' => $type, 'label' => $types[$type]['label'], 'anchor' => null, 'active' => 0,
                    'sort_order' => (int) db_val('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM home_sections'),
                    'data' => json_encode(default_section_data($type), JSON_UNESCAPED_UNICODE),
                ]);
                flash('success', 'Seção criada. Preencha e marque “Mostrar na home”.');
                redirect('/admin/secoes/' . $newId);
            }
        }
        redirect('/admin/secoes');
    }
    admin_render('sections', ['title' => 'Seções da home', 'sections' => home_sections(false), 'types' => section_types()]);
}

function admin_section_edit(string $id): void
{
    require_admin();
    $section = null;
    foreach (home_sections(false) as $s) {
        if ((int) $s['id'] === (int) $id) {
            $section = $s;
        }
    }
    if (!$section) {
        redirect('/admin/secoes');
    }
    $type = $section['type'];
    $builtin = section_types()[$type]['builtin'];
    if (is_post()) {
        $anchor = slugify(input('anchor'));
        $update = [
            'label'  => mb_substr(input('label'), 0, 120) ?: $section['label'],
            'anchor' => input('anchor') !== '' ? $anchor : null,
            'active' => post_bool('active'),
        ];
        if (!$builtin) {
            $d = $section['data'];
            foreach (['bg', 'title_color', 'text_color'] as $c) {
                $d[$c] = valid_color(input($c), $d[$c]);
            }
            $d['title'] = mb_substr(input('title'), 0, 200);
            if ($type === 'text_image') {
                $d['text'] = mb_substr((string) ($_POST['text'] ?? ''), 0, 5000);
                $d['button_text'] = mb_substr(input('button_text'), 0, 80);
                $d['button_link'] = mb_substr(input('button_link'), 0, 255);
                $d['button_new_tab'] = (bool) post_bool('button_new_tab');
                $d['image'] = mb_substr(input('image'), 0, 255);
                $d['image_side'] = input('image_side') === 'right' ? 'right' : 'left';
                $d['img'] = image_opts_from_post('img');
            } elseif ($type === 'category_posts') {
                $d['category_id'] = (int) input('category_id');
                $d['count'] = max(1, min(24, (int) input('count', 3)));
                $d['button_text'] = mb_substr(input('button_text'), 0, 80);
            } elseif ($type === 'rich_text') {
                $d['html'] = sanitize_html((string) ($_POST['html'] ?? ''));
                $d['narrow'] = (bool) post_bool('narrow');
            } elseif ($type === 'video') {
                $d['url'] = mb_substr(input('url'), 0, 255);
                $d['text'] = mb_substr((string) ($_POST['text'] ?? ''), 0, 3000);
                if ($d['url'] !== '' && !youtube_id($d['url'])) {
                    flash('error', 'Não reconheci o link do YouTube.');
                }
            }
            $update['data'] = json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        db_update('home_sections', (int) $section['id'], $update);
        flash('success', 'Seção salva.');
        redirect('/admin/secoes/' . (int) $section['id']);
    }
    admin_render('section-edit', [
        'title'        => 'Seção: ' . $section['label'],
        's'            => $section,
        'builtin'      => $builtin,
        'categories'   => db_all('SELECT id, name FROM categories ORDER BY sort_order, name'),
        'editorAssets' => $type === 'rich_text',
    ]);
}

/* ---------------- Banners e menu ---------------- */

function admin_banners(): void
{
    require_admin();
    if (is_post()) {
        $action = input('acao');
        $id = (int) input('id');
        if ($action === 'excluir' && $id) {
            db_exec('DELETE FROM slides WHERE id = ?', [$id]);
            flash('success', 'Banner removido.');
        } elseif ($action === 'mover' && $id) {
            move_row('slides', $id, input('direcao'));
        } elseif ($action === 'salvar') {
            $data = [
                'image'  => mb_substr(input('image'), 0, 255),
                'alt'    => mb_substr(input('alt'), 0, 255) ?: null,
                'link'    => mb_substr(input('link'), 0, 255) ?: null,
                'new_tab' => post_bool('new_tab'),
                'active'  => post_bool('active'),
            ];
            if ($data['image'] === '') {
                flash('error', 'Escolha a imagem do banner.');
            } elseif ($id) {
                db_update('slides', $id, $data);
                flash('success', 'Banner atualizado.');
            } else {
                $data['sort_order'] = (int) db_val('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM slides');
                db_insert('slides', $data);
                flash('success', 'Banner adicionado.');
            }
        }
        redirect('/admin/banners');
    }
    admin_render('banners', ['title' => 'Banners do topo', 'slides' => db_all('SELECT * FROM slides ORDER BY sort_order, id')]);
}

function admin_menu(): void
{
    require_admin();
    if (is_post()) {
        $action = input('acao');
        $id = (int) input('id');
        if ($action === 'excluir' && $id) {
            db_exec('DELETE FROM menu_items WHERE id = ?', [$id]);
            flash('success', 'Item removido do menu.');
        } elseif ($action === 'mover' && $id) {
            move_row('menu_items', $id, input('direcao'));
        } elseif ($action === 'fonte') {
            save_type_from_post(['menu']);
            flash('success', 'Fonte do menu salva.');
        } elseif ($action === 'salvar') {
            $data = [
                'label'   => mb_substr(input('label'), 0, 80),
                'url'     => mb_substr(input('url'), 0, 255),
                'new_tab' => post_bool('new_tab'),
            ];
            if ($data['label'] === '' || $data['url'] === '') {
                flash('error', 'Preencha o nome e o endereço do item.');
            } elseif ($id) {
                db_update('menu_items', $id, $data);
                flash('success', 'Item atualizado.');
            } else {
                $data['sort_order'] = (int) db_val('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM menu_items');
                db_insert('menu_items', $data);
                flash('success', 'Item adicionado ao menu.');
            }
        }
        redirect('/admin/menu');
    }
    admin_render('menu', [
        'title' => 'Menu',
        'items' => db_all('SELECT * FROM menu_items ORDER BY sort_order, id'),
        'categories' => db_all('SELECT name, slug FROM categories ORDER BY sort_order, name'),
        'pages'      => db_all('SELECT title, slug FROM pages ORDER BY title'),
    ]);
}

// Sobe ou desce um item numa lista ordenada
function move_row(string $table, int $id, string $direction): void
{
    $rows = db_all("SELECT id FROM {$table} ORDER BY sort_order, id");
    $ids = array_map('intval', array_column($rows, 'id'));
    $pos = array_search($id, $ids, true);
    if ($pos === false) {
        return;
    }
    $swap = $direction === 'cima' ? $pos - 1 : $pos + 1;
    if (!isset($ids[$swap])) {
        return;
    }
    [$ids[$pos], $ids[$swap]] = [$ids[$swap], $ids[$pos]];
    foreach ($ids as $order => $rowId) {
        db_exec("UPDATE {$table} SET sort_order = ? WHERE id = ?", [$order, $rowId]);
    }
}
