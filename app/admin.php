<?php
declare(strict_types=1);

// Painel administrativo. Nesta primeira etapa: login, saída e página inicial.
// As telas de posts, editor, aparência, comentários etc. chegam na etapa 2.

function admin_dispatch(string $path): void
{
    header('X-Robots-Tag: noindex, nofollow');
    $routes = [
        '/admin/login'  => 'admin_login',
        '/admin/sair'   => 'admin_logout',
        '/admin'        => 'admin_home',
    ];
    $handler = $routes[$path] ?? null;
    if (!$handler) {
        require_login();
        abort(404);
    }
    $handler();
}

function admin_render(string $template, array $vars = []): void
{
    render('admin/' . $template, $vars, 'admin/layout');
}

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
            redirect(str_starts_with($back, '/admin') ? $back : '/admin');
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
    $user = require_login();
    admin_render('dashboard', [
        'title' => 'Painel',
        'user'  => $user,
        'stats' => [
            'Posts publicados'       => (int) db_val("SELECT COUNT(*) FROM posts WHERE status = 'published'"),
            'Rascunhos'              => (int) db_val("SELECT COUNT(*) FROM posts WHERE status = 'draft'"),
            'Comentários pendentes'  => (int) db_val("SELECT COUNT(*) FROM comments WHERE status = 'pending'"),
            'Mensagens não lidas'    => (int) db_val('SELECT COUNT(*) FROM messages WHERE read_at IS NULL'),
            'Inscritos na newsletter' => (int) db_val('SELECT COUNT(*) FROM subscribers'),
        ],
    ]);
}
