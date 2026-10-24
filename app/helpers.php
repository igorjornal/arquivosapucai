<?php
declare(strict_types=1);

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function base_url(): string
{
    $configured = rtrim((string) config('base_url', ''), '/');
    if ($configured !== '') {
        return $configured;
    }
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return (is_https() ? 'https' : 'http') . '://' . $host;
}

function url(string $path = '/'): string
{
    return '/' . ltrim($path, '/');
}

function absolute_url(string $path = '/'): string
{
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    return base_url() . url($path);
}

function asset(string $path): string
{
    $file = ROOT . '/assets/' . ltrim($path, '/');
    $version = is_file($file) ? (string) filemtime($file) : APP_VERSION;
    return '/assets/' . ltrim($path, '/') . '?v=' . $version;
}

// Caminho de imagem salvo no banco ("uploads/...", "assets/..." ou URL completa)
function media_url(?string $path): string
{
    if (!$path) {
        return '';
    }
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    return '/' . ltrim($path, '/');
}

function redirect(string $to, int $status = 302): never
{
    header('Location: ' . $to, true, $status);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function input(string $key, $default = '')
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $value;
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

function abort(int $status = 404): never
{
    http_response_code($status);
    render('404', ['title' => 'Página não encontrada']);
    exit;
}

/* ---------- Segurança de formulários ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = (string) ($_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if ($sent === '' || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Sessão expirada. Volte, recarregue a página e tente de novo.');
    }
}

// Campo invisível que robôs costumam preencher, mais o tempo mínimo de preenchimento
function spam_fields(): string
{
    $ts = time();
    $sig = hash_hmac('sha256', (string) $ts, csrf_token());
    return '<div class="hp" aria-hidden="true"><label>Não preencha<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>'
        . '<input type="hidden" name="_ts" value="' . $ts . '"><input type="hidden" name="_tss" value="' . e($sig) . '">';
}

function looks_like_spam(int $minSeconds = 3): bool
{
    if (trim((string) ($_POST['website'] ?? '')) !== '') {
        return true;
    }
    $ts = (int) ($_POST['_ts'] ?? 0);
    $sig = (string) ($_POST['_tss'] ?? '');
    if (!hash_equals(hash_hmac('sha256', (string) $ts, csrf_token()), $sig)) {
        return true;
    }
    return (time() - $ts) < $minSeconds;
}

function rate_limited(string $action, int $max, int $minutes): bool
{
    $since = date('Y-m-d H:i:s', time() - $minutes * 60);
    $count = (int) db_val(
        'SELECT COUNT(*) FROM rate_limits WHERE action = ? AND ip = ? AND created_at > ?',
        [$action, client_ip(), $since]
    );
    if ($count >= $max) {
        return true;
    }
    db_exec('INSERT INTO rate_limits (action, ip, created_at) VALUES (?, ?, ?)', [$action, client_ip(), now()]);
    return false;
}

/* ---------- Mensagens entre páginas ---------- */

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    // Só as mensagens gerais; as de grupo ("contact_…") ficam para take_flash_group()
    $items = [];
    foreach ($_SESSION['flash'] ?? [] as $i => $item) {
        if (!str_contains($item['type'], '_')) {
            $items[] = $item;
            unset($_SESSION['flash'][$i]);
        }
    }
    return $items;
}

// Mensagens de um bloco específico da página (ex.: "newsletter_success" → grupo "newsletter")
function take_flash_group(string $group): array
{
    $mine = [];
    foreach ($_SESSION['flash'] ?? [] as $i => $item) {
        if (str_starts_with($item['type'], $group . '_')) {
            $mine[] = ['type' => substr($item['type'], strlen($group) + 1), 'message' => $item['message']];
            unset($_SESSION['flash'][$i]);
        }
    }
    return $mine;
}

function remember_old(array $data): void
{
    $_SESSION['old'] = $data;
}

function old(string $key, string $default = ''): string
{
    return (string) ($_SESSION['old'][$key] ?? $default);
}

function forget_old(): void
{
    unset($_SESSION['old']);
}

/* ---------- Texto ---------- */

function slugify(string $text): string
{
    $text = trim($text);
    $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text) ?: strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim((string) $text, '-') ?: 'item';
}

function unique_slug(string $table, string $base, ?int $ignoreId = null): string
{
    $slug = slugify($base);
    $candidate = $slug;
    $n = 2;
    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = ?" . ($ignoreId ? ' AND id <> ?' : '');
        $params = $ignoreId ? [$candidate, $ignoreId] : [$candidate];
        if (!db_val($sql, $params)) {
            return $candidate;
        }
        $candidate = $slug . '-' . $n++;
    }
}

function text_excerpt(?string $html, int $length = 160): string
{
    $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8')));
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    $cut = mb_substr($text, 0, $length);
    $space = mb_strrpos($cut, ' ');
    return rtrim(mb_substr($cut, 0, $space ?: $length), " ,.;:—-") . '…';
}

function reading_minutes(?string $html): int
{
    $words = str_word_count(strip_tags((string) $html), 0, 'ÀÁÂÃÇÉÊÍÓÔÕÚàáâãçéêíóôõú');
    return max(1, (int) ceil($words / 220));
}

const MESES_CURTOS = ['jan.', 'fev.', 'mar.', 'abr.', 'mai.', 'jun.', 'jul.', 'ago.', 'set.', 'out.', 'nov.', 'dez.'];
const MESES = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];

function date_short(?string $datetime): string
{
    if (!$datetime) {
        return '';
    }
    $t = strtotime($datetime);
    return date('j', $t) . ' de ' . MESES_CURTOS[(int) date('n', $t) - 1];
}

function date_long(?string $datetime): string
{
    if (!$datetime) {
        return '';
    }
    $t = strtotime($datetime);
    return date('j', $t) . ' de ' . MESES[(int) date('n', $t) - 1] . ' de ' . date('Y', $t);
}

function date_relative(?string $datetime): string
{
    if (!$datetime) {
        return '';
    }
    $diff = time() - strtotime($datetime);
    if ($diff < 60) {
        return 'agora mesmo';
    }
    if ($diff < 3600) {
        $m = intdiv($diff, 60);
        return 'há ' . $m . ($m === 1 ? ' minuto' : ' minutos');
    }
    if ($diff < 86400) {
        $h = intdiv($diff, 3600);
        return 'há ' . $h . ($h === 1 ? ' hora' : ' horas');
    }
    if ($diff < 86400 * 7) {
        $d = intdiv($diff, 86400);
        return 'há ' . $d . ($d === 1 ? ' dia' : ' dias');
    }
    return date_short($datetime);
}

function youtube_id(string $url): ?string
{
    if (preg_match('~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{11})~', $url, $m)) {
        return $m[1];
    }
    return null;
}

function send_mail(string $to, string $subject, string $body, ?string $replyTo = null): bool
{
    if ($to === '') {
        return false;
    }
    $from = 'nao-responda@' . preg_replace('/^www\./', '', parse_url(base_url(), PHP_URL_HOST) ?: 'localhost');
    $headers = [
        'From: ' . setting('site')['title'] . ' <' . $from . '>',
        'Content-Type: text/plain; charset=UTF-8',
    ];
    if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }
    return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
}
