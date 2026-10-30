<?php
declare(strict_types=1);

// Painel organizado pelo lugar onde a coisa aparece no site: uma tela por parte,
// com texto, fonte e cor juntos. Cada tela salva só os campos dela.

function admin_screens(): array
{
    return [
        'cabecalho'      => ['Cabeçalho e menu', 'screen-cabecalho'],
        'faixa'          => ['Faixa de avisos', 'screen-faixa'],
        'pagina-inicial' => ['Página inicial', 'screen-inicio'],
        'rodape'         => ['Rodapé', 'screen-rodape'],
        'blog-posts'     => ['Blog e posts', 'screen-blog'],
        'playlists'      => ['Página de Playlists', 'screen-playlists'],
        'padrao'         => ['Padrão do site', 'screen-padrao'],
        'configuracoes'  => ['Configurações', 'screen-config'],
    ];
}

// Cores de cada tela (nome do campo => nome simples)
function screen_colors(string $screen): array
{
    $all = [
        'cabecalho_fundo' => ['header_bg' => 'Cor do fundo'],
        'cabecalho_menu'  => ['header_accent' => 'Texto do menu', 'menu_hover' => 'Texto ao passar o mouse', 'menu_line' => 'Risco (ou fundo) ao passar o mouse', 'menu_current' => 'Item da página que está aberta'],
        'cabecalho_redes' => ['header_social' => 'Cor dos ícones das redes e da lupinha'],
        'cabecalho_avancado' => ['header_text' => 'Texto digitado na busca'],
        'faixa' => ['ticker_bg' => 'Fundo da faixa', 'ticker_text' => 'Texto dos avisos'],
        'blog'      => ['blog_bg' => 'Fundo da seção', 'blog_title' => 'Título da seção'],
        'arquivo'   => ['arquivo_bg' => 'Fundo', 'arquivo_title' => 'Título', 'arquivo_text' => 'Texto'],
        'playlists' => ['playlists_bg' => 'Fundo', 'playlists_title' => 'Título', 'playlists_text' => 'Texto'],
        'acervo'    => ['acervo_bg' => 'Fundo', 'acervo_title' => 'Títulos', 'acervo_text' => 'Textos'],
        'contato'   => ['contact_bg' => 'Fundo', 'contact_title' => 'Título', 'contact_text' => 'Texto'],
        'rodape'    => ['footer_bg' => 'Fundo', 'footer_text' => 'Textos e links', 'footer_accent' => 'Títulos, ícones e destaques'],
        'paginas'   => ['pager_bg' => 'Fundo dos números', 'pager_text' => 'Número', 'pager_active_bg' => 'Fundo em destaque (página aberta e mouse por cima)', 'pager_active_text' => 'Número em destaque'],
        'pl_pagina' => ['pl_bar_bg' => 'Fundo das barras das décadas', 'pl_bar_text' => 'Texto das barras', 'pl_card_bg' => 'Fundo das capas', 'pl_year' => 'Ano nas capas', 'pl_text' => 'Título e subtítulo nas capas', 'pl_accent' => 'Botão de play e destaque da barra aberta'],
        'cards'     => ['card_bg' => 'Fundo do card', 'card_title' => 'Título do card', 'card_text' => 'Texto do card'],
        'post'      => ['post_page_bg' => 'Fundo da página do post', 'post_box_bg' => 'Fundo da caixa do texto', 'post_title' => 'Títulos', 'post_text' => 'Texto',
            'post_link' => 'Links no texto', 'post_nav_bg' => 'Fundo da barra de categorias', 'post_nav_text' => 'Texto da barra de categorias'],
        'botao_principal' => ['button_bg' => 'Fundo', 'button_text' => 'Texto', 'button_hover_bg' => 'Fundo ao passar o mouse', 'button_hover_text' => 'Texto ao passar o mouse'],
        'botao_todas'     => ['button_all_bg' => 'Fundo', 'button_all_text' => 'Texto', 'button_all_hover_bg' => 'Fundo ao passar o mouse', 'button_all_hover_text' => 'Texto ao passar o mouse'],
        'botao_escuro'    => ['button_dark_bg' => 'Fundo', 'button_dark_text' => 'Texto', 'button_dark_hover_bg' => 'Fundo ao passar o mouse', 'button_dark_hover_text' => 'Texto ao passar o mouse'],
    ];
    return $all[$screen] ?? [];
}

function color_fields(string $group): string
{
    $colors = setting('appearance')['colors'];
    $out = '';
    foreach (screen_colors($group) as $key => $label) {
        $v = strtolower((string) ($colors[$key] ?? '#000000'));
        $out .= '<label class="a-color"><input type="color" name="colors[' . e($key) . ']" value="' . e($v) . '" data-color>'
            . '<span>' . e($label) . '</span><code data-color-code>' . e(strtoupper($v)) . '</code></label>';
    }
    return $out;
}

// Salva só as cores enviadas pela tela; as outras continuam como estão
function save_colors_from_post(): void
{
    $a = setting('appearance');
    $defaults = settings_defaults()['appearance']['colors'];
    foreach ((array) ($_POST['colors'] ?? []) as $key => $value) {
        if (isset($defaults[$key])) {
            $a['colors'][$key] = valid_color((string) $value, $a['colors'][$key] ?? $defaults[$key]);
        }
    }
    save_setting('appearance', $a);
}

function admin_screen(string $screen): void
{
    require_admin();
    if (!isset(admin_screens()[$screen])) {
        redirect('/admin');
    }
    [$title, $template] = admin_screens()[$screen];
    if (is_post()) {
        $msg = 'Alterações salvas. Abra o site para conferir.';
        if (isset($_POST['colors'])) {
            save_colors_from_post();
        }
        if ($screen === 'cabecalho') {
            save_header_screen();
        } elseif ($screen === 'playlists') {
            save_playlists_screen();
        } elseif ($screen === 'faixa') {
            save_ticker_screen();
        } elseif ($screen === 'pagina-inicial') {
            save_home_screen();
        } elseif ($screen === 'rodape') {
            save_footer_screen();
        } elseif ($screen === 'blog-posts') {
            save_blog_screen();
        } elseif ($screen === 'padrao') {
            if (input('acao') === 'restaurar') {
                restore_default_look();
                flash('success', 'Fontes e cores dos botões voltaram ao padrão.');
                redirect('/admin/padrao');
            }
            save_defaults_screen();
        } elseif ($screen === 'configuracoes') {
            save_settings_screen();
        }
        flash('success', $msg);
        redirect('/admin/' . $screen);
    }
    admin_render($template, [
        'title'  => $title,
        'screen' => $screen,
        'a'      => setting('appearance'),
        'site'   => setting('site'),
        'home'   => setting('home'),
        'footer' => setting('footer'),
        'news'   => setting('newsletter'),
        'blog'   => setting('blog'),
        'pl'     => setting('playlist_page'),
        'items'  => db_all('SELECT * FROM menu_items ORDER BY sort_order, id'),
        'pages'  => db_all('SELECT title, slug FROM pages ORDER BY title'),
        'categories' => db_all('SELECT name, slug FROM categories ORDER BY sort_order, name'),
    ]);
}

/* ---------- Prévia do cabeçalho e da faixa ----------
   Desenha o cabeçalho do site de verdade com o que está na tela do painel, sem salvar:
   grava dentro de uma transação, monta o HTML e desfaz tudo. */
function admin_preview_top(string $screen): void
{
    require_admin();
    if (!is_post()) {
        redirect('/admin/' . $screen);
    }
    db()->beginTransaction();
    try {
        if (isset($_POST['colors'])) {
            save_colors_from_post();
        }
        $screen === 'cabecalho' ? save_header_screen() : save_ticker_screen();
        $_SERVER['REQUEST_URI'] = '/'; // "Home" aparece como a página aberta, para mostrar esse estilo também
        $html = render_to_string('admin/preview-top');
    } finally {
        db()->rollBack();
    }
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    echo $html;
}

/* ---------- Cabeçalho e menu ---------- */
function save_header_screen(): void
{
    $a = setting('appearance');
    $a['sizes'] = [
        'logo'   => max(40, min(220, (int) input('size_logo', 96))),
        'header' => max(60, min(320, (int) input('size_header', 120))),
    ];
    $a['header'] = ['overlay' => max(0, min(100, (int) input('header_overlay', 100))), 'gradient' => (bool) post_bool('header_gradient')];
    $a['menu'] = [
        'align'  => in_array(input('menu_align'), ['logo', 'center', 'right'], true) ? input('menu_align') : 'right',
        'gap'    => max(0, min(80, (int) input('menu_gap', 30))),
        'effect' => in_array(input('menu_effect'), ['line', 'grow', 'pill', 'color'], true) ? input('menu_effect') : 'line',
    ];
    save_setting('appearance', $a);
    $site = setting('site');
    $site['logo'] = mb_substr(input('logo'), 0, 255);
    $site['header_bg'] = mb_substr(input('header_image'), 0, 255);
    save_setting('site', $site);
    save_type_from_post(['menu']);
    // Itens do menu, na ordem em que estão na tela
    $order = 0;
    foreach ((array) ($_POST['menu'] ?? []) as $item) {
        $id = (int) ($item['id'] ?? 0);
        $label = mb_substr(trim((string) ($item['label'] ?? '')), 0, 80);
        $url = mb_substr(trim((string) ($item['url'] ?? '')), 0, 255);
        if (!empty($item['remove']) || $label === '' || $url === '') {
            if ($id) {
                db_exec('DELETE FROM menu_items WHERE id = ?', [$id]);
            }
            continue;
        }
        $data = ['label' => $label, 'url' => $url, 'new_tab' => !empty($item['new_tab']) ? 1 : 0, 'sort_order' => $order++];
        if ($id) {
            db_update('menu_items', $id, $data);
        } else {
            db_insert('menu_items', $data);
        }
    }
}

/* ---------- Faixa de avisos ---------- */
function save_ticker_screen(): void
{
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
    $home['ticker_pad'] = max(0, min(40, (int) input('ticker_pad', 9)));
    $home['ticker_speed'] = max(1, min(10, (int) input('ticker_speed', 4)));
    $home['ticker_gap'] = max(0, min(80, (int) input('ticker_gap', 22)));
    $home['ticker_sep'] = in_array(input('ticker_sep'), ['flag', 'star', 'dot', 'diamond', 'dash', 'bar', 'none', 'text', 'image'], true) ? input('ticker_sep') : 'flag';
    $home['ticker_sep_text'] = mb_substr(input('ticker_sep_text'), 0, 30);
    $home['ticker_sep_image'] = mb_substr(input('ticker_sep_image'), 0, 255);
    save_setting('home', $home);
    save_type_from_post(['ticker']);
}

/* ---------- Página do Blog (fica na lista de Páginas) ---------- */
function admin_blog_page(): void
{
    require_admin();
    if (is_post()) {
        $blog = setting('blog');
        $blog['page_title'] = mb_substr(input('page_title'), 0, 120) ?: 'Blog';
        $blog['page_intro'] = mb_substr(input('page_intro'), 0, 600);
        save_setting('blog', $blog);
        save_type_from_post(['blog_page_title']);
        flash('success', 'Página do Blog salva.');
        redirect('/admin/paginas/blog');
    }
    admin_render('blog-page', ['title' => 'Página do Blog', 'blog' => setting('blog')]);
}

/* ---------- Página de Playlists (décadas em sanfona) ---------- */
function save_playlists_screen(): void
{
    $pl = setting('playlist_page');
    $slug = input('page_slug');
    $pl['page_slug'] = $slug === '' || db_val('SELECT COUNT(*) FROM pages WHERE slug = ?', [$slug]) ? $slug : $pl['page_slug'];
    $pl['count_label'] = mb_substr(input('count_label'), 0, 30);
    $decades = [];
    foreach ((array) ($_POST['decades'] ?? []) as $d) {
        $name = mb_substr(trim((string) ($d['name'] ?? '')), 0, 60);
        $items = [];
        foreach ((array) ($d['items'] ?? []) as $it) {
            $year = mb_substr(trim((string) ($it['year'] ?? '')), 0, 20);
            $link = mb_substr(trim((string) ($it['link'] ?? '')), 0, 255);
            if ($year === '' && $link === '') {
                continue;
            }
            $items[] = ['year' => $year, 'title' => mb_substr(trim((string) ($it['title'] ?? '')), 0, 60),
                'subtitle' => mb_substr(trim((string) ($it['subtitle'] ?? '')), 0, 60), 'link' => $link];
        }
        if ($name !== '' || $items) {
            $decades[] = ['name' => $name ?: 'Sem nome', 'items' => $items];
        }
    }
    $pl['decades'] = $decades;
    save_setting('playlist_page', $pl);
}

/* ---------- Página inicial (textos, imagens, fontes e cores de cada seção) ---------- */
function save_home_screen(): void
{
    $home = setting('home');
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
    $home['split_gap'] = max(0, min(120, (int) input('split_gap', 40)));
    $blog = setting('blog');
    $blog['per_page'] = max(1, min(48, (int) input('per_page', 6)));
    save_setting('blog', $blog);
    $home['arquivo_new_tab'] = (bool) post_bool('arquivo_new_tab');
    $home['arquivo_img'] = image_opts_from_post('arquivo_img');
    $playlists = [];
    foreach ((array) ($_POST['playlists'] ?? []) as $pl) {
        if (trim((string) ($pl['title'] ?? '')) === '' && trim((string) ($pl['image'] ?? '')) === '') {
            continue;
        }
        $playlists[] = [
            'title'   => mb_substr(trim((string) $pl['title']), 0, 120),
            'image'   => mb_substr(trim((string) $pl['image']), 0, 255),
            'link'    => mb_substr(trim((string) $pl['link']), 0, 255),
            'new_tab' => !empty($pl['new_tab']),
        ];
    }
    $home['playlists'] = $playlists;
    save_setting('home', $home);
    save_type_from_post(['blog_title', 'blog_button', 'arquivo_title', 'arquivo_text', 'arquivo_button', 'playlists_title', 'playlists_intro', 'playlists_names', 'playlists_outro', 'playlists_button',
        'acervo_title', 'acervo_text', 'acervo_button', 'historia_title', 'historia_text', 'historia_button', 'contact_title', 'contact_text', 'contact_labels', 'contact_button']);
}

/* ---------- Rodapé ---------- */
function save_footer_screen(): void
{
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
}

/* ---------- Blog e posts ---------- */
function save_blog_screen(): void
{
    $blog = setting('blog');
    $blog['per_page'] = max(1, min(48, (int) input('per_page', 6)));
    $blog['comments_enabled'] = (bool) post_bool('comments_enabled');
    $blog['comments_moderate'] = (bool) post_bool('comments_moderate');
    $blog['share_buttons'] = (bool) post_bool('share_buttons');
    $blog['cards_per_row'] = max(2, min(5, (int) input('cards_per_row', 3)));
    $blog['cards_layout'] = input('cards_layout') === 'masonry' ? 'masonry' : 'grid';
    $blog['card_ratio'] = array_key_exists(input('card_ratio'), image_ratios()) ? input('card_ratio') : 'wide';
    $blog['card_title_lines'] = max(0, min(6, (int) input('card_title_lines')));
    $blog['card_excerpt_lines'] = max(0, min(8, (int) input('card_excerpt_lines')));
    $blog['post_sidebar'] = (bool) post_bool('post_sidebar');
    $blog['sidebar_title'] = mb_substr(input('sidebar_title'), 0, 60) ?: 'Últimos posts';
    $blog['sidebar_count'] = max(3, min(8, (int) input('sidebar_count', 5)));
    $blog['related_title'] = mb_substr(input('related_title'), 0, 60) ?: 'Posts relacionados';
    save_setting('blog', $blog);
    save_type_from_post(['post_title', 'side_title', 'side_posts']);
}

/* ---------- Padrão do site: fontes base e botões ---------- */
function save_defaults_screen(): void
{
    $a = setting('appearance');
    foreach (['heading', 'body', 'ui'] as $role) {
        $font = (string) ($_POST['fonts'][$role] ?? '');
        $a['fonts'][$role] = in_array($font, font_choices(), true) ? $font : $a['fonts'][$role];
    }
    foreach (['heading' => [24, 80], 'body' => [13, 24], 'ui' => [11, 22]] as $role => [$min, $max]) {
        $a['font_sizes'][$role] = max($min, min($max, (int) ($_POST['font_sizes'][$role] ?? base_font_sizes()[$role])));
    }
    $a['font_weights'] = [
        'heading' => valid_weight($_POST['font_weights']['heading'] ?? 0, 800),
        'body'    => valid_weight($_POST['font_weights']['body'] ?? 0, 400),
    ];
    save_setting('appearance', $a);
}

// "Restaurar padrão": só as fontes base e as cores dos botões (o resto fica como está)
function restore_default_look(): void
{
    $a = setting('appearance');
    $d = settings_defaults()['appearance'];
    $a['fonts'] = $d['fonts'];
    $a['font_sizes'] = $d['font_sizes'];
    $a['font_weights'] = $d['font_weights'];
    foreach (array_merge(screen_colors('botao_principal'), screen_colors('botao_todas'), screen_colors('botao_escuro')) as $key => $label) {
        $a['colors'][$key] = $d['colors'][$key];
    }
    save_setting('appearance', $a);
}

/* ---------- Configurações ---------- */
function save_settings_screen(): void
{
    $site = setting('site');
    foreach (['title' => 120, 'tagline' => 300, 'description' => 300, 'email' => 190] as $k => $len) {
        $site[$k] = mb_substr(input($k), 0, $len);
    }
    $site['share_image'] = mb_substr(input('share_image'), 0, 255);
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
        redirect('/admin/configuracoes#g-avisos');
    }
}

// Endereços antigos do painel levam para a tela nova
function admin_old_redirect(): void
{
    $path = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
    $map = ['/admin/aparencia' => '/admin/padrao', '/admin/menu' => '/admin/cabecalho#g-itens'];
    if ($path === '/admin/conteudo') {
        $tabs = ['site' => '/admin/configuracoes', 'home' => '/admin/pagina-inicial', 'rodape' => '/admin/rodape', 'blog' => '/admin/blog-posts'];
        redirect($tabs[$_GET['aba'] ?? 'site'] ?? '/admin/configuracoes');
    }
    redirect($map[$path] ?? '/admin');
}
