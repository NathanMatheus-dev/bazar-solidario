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
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'");
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
function post(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: POST'); http_response_code(405); exit; }
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals(csrf(), $_POST['csrf'])) { http_response_code(403); exit('Sua sessão expirou. Volte e atualize a página.'); }
}
function campo(string $nome): string { return is_string($_POST[$nome] ?? null) ? trim($_POST[$nome]) : ''; }
function senha_campo(string $nome): string { return is_string($_POST[$nome] ?? null) ? $_POST[$nome] : ''; }
function aviso(string $texto, string $tipo = 'erro'): void { $_SESSION['aviso'] = [$texto, $tipo]; }
function admin_existe(): bool { return (bool)banco()->query('SELECT id FROM administradores WHERE id=1')->fetchColumn(); }
// INSERT com tratamento de conflito e chave única tornam a disputa pelo primeiro acesso atômica.
function primeiro_acesso(): void {
    $q = banco()->prepare('INSERT INTO configuracao (id, primeiro_ip) VALUES (1, ?) ON CONFLICT(id) DO NOTHING'); $q->execute([ip()]);
    if (!admin_existe() && dono_setup()) ir('primeiro_acesso.php');
}
function dono_setup(): bool { return banco()->query('SELECT primeiro_ip FROM configuracao WHERE id=1')->fetchColumn() === ip(); }
function protegido(): void {
    if (empty($_SESSION['admin']) || time() - ($_SESSION['atividade'] ?? 0) > 1800) { unset($_SESSION['admin']); aviso('Entre para acessar o painel.'); ir('login.php'); }
    $_SESSION['atividade'] = time();
}
// Os valores são escapados no ponto de origem. Fragmentos vêm de templates locais.
function vista(string $nome, array $valores): string {
    $template = file_get_contents(__DIR__ . '/views/' . $nome . '.html');
    if ($template === false) {
        throw new RuntimeException('Template indisponível.');
    }

    $substituicoes = [];
    foreach ($valores as $chave => $valor) {
        $substituicoes['{{' . $chave . '}}'] = $valor;
    }
    return strtr($template, $substituicoes);
}

function pagina(string $titulo, string $nome, array $valores = []): void {
    $aviso = '';
    if (isset($_SESSION['aviso'])) {
        [$texto, $tipo] = $_SESSION['aviso'];
        unset($_SESSION['aviso']);
        $aviso = vista('aviso', [
            'tipo' => $tipo === 'sucesso' ? 'success' : 'danger',
            'texto' => e($texto),
        ]);
    }

    echo vista('layout', [
        'titulo' => e($titulo),
        'area_admin' => empty($_SESSION['admin']) ? 'login.php' : 'dashboard.php',
        'aviso' => $aviso,
        'conteudo' => vista($nome, $valores + ['csrf' => e(csrf())]),
    ]);
}
