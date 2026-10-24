<?php
declare(strict_types=1);

function page_home(): void
{
    $home = setting('home');
    render('home', [
        'title'  => setting('site')['title'],
        'slides' => active_slides(),
        'posts'  => latest_posts((int) $home['blog_count']),
        'home'   => $home,
        'bodyClass' => 'is-home',
    ]);
}

function page_blog(): void
{
    $per = (int) setting('blog')['per_page'];
    $pg = paginate(count_posts(), $per, (int) ($_GET['pagina'] ?? 1));
    render('blog', [
        'title'      => 'Blog',
        'heading'    => 'Blog',
        'posts'      => latest_posts($per, $pg['offset']),
        'pagination' => $pg,
        'baseLink'   => '/blog',
        'activeCategory' => null,
    ]);
}

function page_category(string $slug): void
{
    $cat = db_one('SELECT * FROM categories WHERE slug = ?', [$slug]) ?? abort(404);
    $per = (int) setting('blog')['per_page'];
    $filter = ['category_id' => $cat['id']];
    $pg = paginate(count_posts($filter), $per, (int) ($_GET['pagina'] ?? 1));
    render('blog', [
        'title'      => $cat['name'],
        'heading'    => $cat['name'],
        'intro'      => $cat['description'],
        'posts'      => latest_posts($per, $pg['offset'], $filter),
        'pagination' => $pg,
        'baseLink'   => '/blog/categoria/' . $cat['slug'],
        'activeCategory' => $cat['slug'],
    ]);
}

function page_tag(string $slug): void
{
    $tag = db_one('SELECT * FROM tags WHERE slug = ?', [$slug]) ?? abort(404);
    $per = (int) setting('blog')['per_page'];
    $filter = ['tag_id' => $tag['id']];
    $pg = paginate(count_posts($filter), $per, (int) ($_GET['pagina'] ?? 1));
    render('blog', [
        'title'      => '#' . $tag['name'],
        'heading'    => '#' . $tag['name'],
        'posts'      => latest_posts($per, $pg['offset'], $filter),
        'pagination' => $pg,
        'baseLink'   => '/blog/tag/' . $tag['slug'],
        'activeCategory' => null,
    ]);
}

function page_author(string $slug): void
{
    $author = db_one('SELECT * FROM users WHERE slug = ? AND active = 1', [$slug]) ?? abort(404);
    $per = (int) setting('blog')['per_page'];
    $filter = ['author_id' => $author['id']];
    $pg = paginate(count_posts($filter), $per, (int) ($_GET['pagina'] ?? 1));
    render('blog', [
        'title'      => $author['name'],
        'heading'    => $author['name'],
        'author'     => $author,
        'posts'      => latest_posts($per, $pg['offset'], $filter),
        'pagination' => $pg,
        'baseLink'   => '/blog/autor/' . $author['slug'],
        'activeCategory' => null,
    ]);
}

function page_post(string $slug): void
{
    $post = find_published_post($slug);
    if (!$post) {
        // Pré-visualização de rascunho para quem está logado no painel
        $user = current_user();
        if ($user && isset($_GET['previa'])) {
            $post = db_one(POST_SELECT . ' WHERE p.slug = ?', [$slug]);
        }
        $post ?? abort(404);
    }
    if (empty($_SESSION['viewed'][$post['id']])) {
        db_exec('UPDATE posts SET views = views + 1 WHERE id = ?', [$post['id']]);
        $_SESSION['viewed'][$post['id']] = true;
    }
    render('post', [
        'title'       => $post['meta_title'] ?: $post['title'],
        'description' => $post['meta_description'] ?: post_excerpt($post, 155),
        'shareImage'  => $post['cover_image'],
        'ogType'      => 'article',
        'post'        => $post,
        'tags'        => post_tags((int) $post['id']),
        'comments'    => approved_comments((int) $post['id']),
        'recent'      => latest_posts(3, 0, ['exclude' => $post['id']]),
        'bodyClass'   => 'is-post',
    ]);
}

function action_comment(string $slug): void
{
    csrf_check();
    $post = find_published_post($slug) ?? abort(404);
    $back = post_url($post) . '#comentarios';
    $blog = setting('blog');
    if (!$blog['comments_enabled'] || !$post['allow_comments']) {
        redirect($back);
    }
    $name = mb_substr(input('nome'), 0, 120);
    $email = mb_strtolower(mb_substr(input('email'), 0, 190));
    $text = mb_substr(input('comentario'), 0, 5000);
    $parent = (int) input('resposta_a', 0) ?: null;
    remember_old(['nome' => $name, 'email' => $email, 'comentario' => $text]);

    if (looks_like_spam()) {
        flash('error', 'Não foi possível enviar. Tente de novo em alguns segundos.');
        redirect($back);
    }
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($text) < 2) {
        flash('error', 'Preencha nome, e-mail válido e o comentário.');
        redirect($back);
    }
    if (rate_limited('comment', 5, 10)) {
        flash('error', 'Muitos comentários em pouco tempo. Aguarde alguns minutos.');
        redirect($back);
    }
    if ($parent && !db_val('SELECT id FROM comments WHERE id = ? AND post_id = ?', [$parent, $post['id']])) {
        $parent = null;
    }
    $linkCount = preg_match_all('#https?://#i', $text);
    $user = current_user();
    $status = ($user || !$blog['comments_moderate']) && $linkCount < 3 ? 'approved' : 'pending';
    if ($linkCount >= 3) {
        $status = 'spam';
    }
    db_insert('comments', [
        'post_id'      => $post['id'],
        'parent_id'    => $parent,
        'user_id'      => $user['id'] ?? null,
        'author_name'  => $name,
        'author_email' => $email,
        'content'      => $text,
        'status'       => $status,
        'ip'           => client_ip(),
        'user_agent'   => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        'created_at'   => now(),
    ]);
    forget_old();
    if ($status === 'pending') {
        send_mail(
            (string) config('notify_email'),
            'Novo comentário aguardando aprovação',
            "Post: {$post['title']}\nDe: {$name} <{$email}>\n\n{$text}\n\nAprovar: " . absolute_url('/admin/comentarios'),
            $email
        );
        flash('success', 'Comentário recebido! Ele aparece aqui assim que for aprovado.');
    } else {
        flash('success', 'Comentário publicado.');
    }
    redirect($back);
}

function page_search(): void
{
    $q = mb_substr(input('q'), 0, 100);
    $per = (int) setting('blog')['per_page'];
    $filter = ['q' => $q];
    $pg = paginate($q === '' ? 0 : count_posts($filter), $per, (int) ($_GET['pagina'] ?? 1));
    render('blog', [
        'title'      => 'Busca',
        'heading'    => $q === '' ? 'Buscar no Arquivo' : 'Resultados para “' . $q . '”',
        'search'     => $q,
        'posts'      => $q === '' ? [] : latest_posts($per, $pg['offset'], $filter),
        'pagination' => $pg,
        'baseLink'   => '/busca?q=' . urlencode($q),
        'activeCategory' => null,
        'robots'     => 'noindex',
    ]);
}

function action_contact(): void
{
    csrf_check();
    $back = '/#contato';
    $data = [
        'first_name' => mb_substr(input('nome'), 0, 120),
        'last_name'  => mb_substr(input('sobrenome'), 0, 120),
        'email'      => mb_strtolower(mb_substr(input('email'), 0, 190)),
        'phone'      => mb_substr(input('telefone'), 0, 40),
        'message'    => mb_substr(input('mensagem'), 0, 5000),
    ];
    remember_old(['nome' => $data['first_name'], 'sobrenome' => $data['last_name'], 'email' => $data['email'], 'telefone' => $data['phone'], 'mensagem' => $data['message']]);
    if (looks_like_spam()) {
        flash('contact_error', 'Não foi possível enviar. Tente de novo em alguns segundos.');
        redirect($back);
    }
    if ($data['first_name'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['message']) < 3) {
        flash('contact_error', 'Preencha nome, e-mail válido e a mensagem.');
        redirect($back);
    }
    if (rate_limited('contact', 3, 10)) {
        flash('contact_error', 'Muitas mensagens em pouco tempo. Aguarde alguns minutos.');
        redirect($back);
    }
    db_insert('messages', $data + ['ip' => client_ip(), 'created_at' => now()]);
    forget_old();
    send_mail(
        (string) config('notify_email'),
        'Nova mensagem pelo site: ' . $data['first_name'],
        "De: {$data['first_name']} {$data['last_name']} <{$data['email']}>\nTelefone: {$data['phone']}\n\n{$data['message']}",
        $data['email']
    );
    flash('contact_success', 'Mensagem enviada! Obrigado pelo contato.');
    redirect($back);
}

function action_newsletter(): void
{
    csrf_check();
    $back = ($_SERVER['HTTP_REFERER'] ?? '') !== '' && str_starts_with((string) $_SERVER['HTTP_REFERER'], base_url())
        ? $_SERVER['HTTP_REFERER'] . '#newsletter'
        : '/#newsletter';
    $email = mb_strtolower(mb_substr(input('email'), 0, 190));
    if (looks_like_spam(2) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('newsletter_error', 'Confira o e-mail digitado.');
        redirect($back);
    }
    if (rate_limited('newsletter', 5, 10)) {
        flash('newsletter_error', 'Aguarde alguns minutos e tente de novo.');
        redirect($back);
    }
    if (!db_val('SELECT id FROM subscribers WHERE email = ?', [$email])) {
        db_insert('subscribers', [
            'email'      => $email,
            'name'       => mb_substr(input('nome'), 0, 120) ?: null,
            'ip'         => client_ip(),
            'created_at' => now(),
        ]);
    }
    flash('newsletter_success', 'Pronto! Você vai receber as novidades do Arquivo.');
    redirect($back);
}

function page_sitemap(): void
{
    header('Content-Type: application/xml; charset=UTF-8');
    $urls = [['loc' => absolute_url('/'), 'lastmod' => null], ['loc' => absolute_url('/blog'), 'lastmod' => null]];
    foreach (db_all('SELECT slug, updated_at FROM posts p WHERE ' . published_clause() . ' ORDER BY published_at DESC', [now()]) as $p) {
        $urls[] = ['loc' => absolute_url('/blog/' . $p['slug']), 'lastmod' => date('c', strtotime($p['updated_at']))];
    }
    foreach (db_all('SELECT slug FROM categories') as $c) {
        $urls[] = ['loc' => absolute_url('/blog/categoria/' . $c['slug']), 'lastmod' => null];
    }
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $u) {
        echo '  <url><loc>' . e($u['loc']) . '</loc>' . ($u['lastmod'] ? '<lastmod>' . $u['lastmod'] . '</lastmod>' : '') . "</url>\n";
    }
    echo '</urlset>';
}

function page_feed(): void
{
    header('Content-Type: application/rss+xml; charset=UTF-8');
    $site = setting('site');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<rss version="2.0"><channel>';
    echo '<title>' . e($site['title']) . '</title><link>' . e(absolute_url('/')) . '</link><description>' . e($site['description']) . '</description><language>pt-BR</language>';
    foreach (latest_posts(20) as $p) {
        echo '<item><title>' . e($p['title']) . '</title><link>' . e(absolute_url(post_url($p))) . '</link>'
            . '<guid>' . e(absolute_url(post_url($p))) . '</guid><pubDate>' . date(DATE_RSS, strtotime($p['published_at'])) . '</pubDate>'
            . '<description>' . e(post_excerpt($p, 300)) . '</description></item>';
    }
    echo '</channel></rss>';
}

function page_robots(): void
{
    header('Content-Type: text/plain; charset=UTF-8');
    echo "User-agent: *\nDisallow: /admin\nSitemap: " . absolute_url('/sitemap.xml') . "\n";
}
