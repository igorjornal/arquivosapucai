<?php
declare(strict_types=1);

// Servidor de teste do PHP (php -S): entrega arquivos reais direto
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file) && !preg_match('#/(app|templates|database|storage)/|config\.php$#', $file)) {
        return false;
    }
}

require __DIR__ . '/app/bootstrap.php';
require ROOT . '/app/public.php';

// Páginas sempre atualizadas: navegador e cache da hospedagem não guardam versão antiga
header('Cache-Control: no-cache, must-revalidate');
header('X-LiteSpeed-Cache-Control: no-cache');

$path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$path = '/' . trim($path, '/');

if ($path === '/admin' || str_starts_with($path, '/admin/')) {
    require ROOT . '/app/admin.php';
    admin_dispatch($path);
    exit;
}

$routes = [
    ['GET',  '#^/$#',                              'page_home'],
    ['GET',  '#^/blog$#',                          'page_blog'],
    ['GET',  '#^/blog/categoria/([a-z0-9-]+)$#',   'page_category'],
    ['GET',  '#^/blog/tag/([a-z0-9-]+)$#',         'page_tag'],
    ['GET',  '#^/blog/autor/([a-z0-9-]+)$#',       'page_author'],
    ['GET',  '#^/blog/([a-z0-9-]+)$#',             'page_post'],
    ['POST', '#^/blog/([a-z0-9-]+)/comentar$#',    'action_comment'],
    ['GET',  '#^/busca$#',                         'page_search'],
    ['POST', '#^/contato$#',                       'action_contact'],
    ['POST', '#^/newsletter$#',                    'action_newsletter'],
    ['GET',  '#^/sitemap\.xml$#',                  'page_sitemap'],
    ['GET',  '#^/feed\.xml$#',                     'page_feed'],
    ['GET',  '#^/robots\.txt$#',                   'page_robots'],
];

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
foreach ($routes as [$verb, $pattern, $handler]) {
    if (preg_match($pattern, $path, $m)) {
        if ($verb !== $method && !($verb === 'GET' && $method === 'HEAD')) {
            http_response_code(405);
            exit;
        }
        $handler(...array_slice($m, 1));
        exit;
    }
}

abort(404);
