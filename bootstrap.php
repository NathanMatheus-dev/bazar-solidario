<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
date_default_timezone_set('America/Sao_Paulo');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax', 'path' => '/']);
session_start();
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self'; img-src 'self' data:; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'");
header('Cache-Control: no-store');
set_exception_handler(function (Throwable $e): void {
    error_log('Bazar: ' . $e->getMessage());
    http_response_code(503);
    echo '<!doctype html><html lang="pt-BR"><meta charset="UTF-8"><title>Serviço indisponível</title><p>Não foi possível acessar o serviço. Tente novamente em alguns instantes.</p></html>';
});
function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function ir(string $url): never { header('Location: ' . $url, true, 303); exit; }
function ip(): string {
    // Cabeçalhos encaminhados não são confiáveis. Configure REMOTE_ADDR no proxy web.
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!filter_var($ip, FILTER_VALIDATE_IP)) throw new RuntimeException('IP inválido.');
    return $ip;
}
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function token(): void { echo '<input type="hidden" name="csrf" value="' . e(csrf()) . '">'; }
function post(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: POST'); http_response_code(405); exit; }
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals(csrf(), $_POST['csrf'])) { http_response_code(403); exit('Sua sessão expirou. Volte e atualize a página.'); }
}
function campo(string $nome): string { return is_string($_POST[$nome] ?? null) ? trim($_POST[$nome]) : ''; }
function senha_campo(string $nome): string { return is_string($_POST[$nome] ?? null) ? $_POST[$nome] : ''; }
function aviso(string $texto, string $tipo = 'erro'): void { $_SESSION['aviso'] = [$texto, $tipo]; }
function mensagem(): void {
    if (isset($_SESSION['aviso'])) { [$t,$tipo] = $_SESSION['aviso']; unset($_SESSION['aviso']); echo '<div role="status" class="aviso ' . e($tipo) . '">' . e($t) . '</div>'; }
}
function admin_existe(): bool { return (bool)banco()->query('SELECT id FROM administradores WHERE id=1')->fetchColumn(); }
// INSERT IGNORE + chave única tornam a disputa pelo primeiro acesso atômica.
function primeiro_acesso(): void {
    $q = banco()->prepare('INSERT IGNORE INTO configuracao (id, primeiro_ip) VALUES (1, ?)'); $q->execute([ip()]);
    if (!admin_existe() && dono_setup()) ir('primeiro_acesso.php');
}
function dono_setup(): bool { return banco()->query('SELECT primeiro_ip FROM configuracao WHERE id=1')->fetchColumn() === ip(); }
function protegido(): void {
    if (empty($_SESSION['admin']) || time() - ($_SESSION['atividade'] ?? 0) > 1800) { unset($_SESSION['admin']); aviso('Entre para acessar o painel.'); ir('login.php'); }
    $_SESSION['atividade'] = time();
}
function topo(string $titulo): void { ?>
<!doctype html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($titulo) ?> · Bazar Solidário</title><link rel="stylesheet" href="assets/style.css"></head><body><header class="cabecalho"><a class="marca" href="index.php"><span class="icone">♡</span><span>Bazar <strong>Solidário</strong><small>Juntos, fazemos a diferença</small></span></a><a class="restrito" href="<?= empty($_SESSION['admin']) ? 'login.php' : 'dashboard.php' ?>">Área do Administrador ↗</a></header><main><?php mensagem(); }
function rodape(): void { ?></main><footer>Feito de pequenos gestos. Movido por grandes corações.<br><span>Bazar Solidário · Uma comunidade que cuida</span></footer></body></html><?php }
