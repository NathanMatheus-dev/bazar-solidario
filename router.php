<?php
// Roteador exclusivo do servidor de desenvolvimento: não exponha SQL, segredos ou testes.
declare(strict_types=1);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$publicos = ['/', '/index.php', '/processa_cadastro.php', '/primeiro_acesso.php', '/login.php', '/dashboard.php', '/logout.php'];
if (in_array($path, $publicos, true)) {
    require __DIR__ . ($path === '/' ? '/index.php' : $path);
    return true;
}
if (in_array($path, ['/assets/style.css', '/assets/dashboard.js'], true)) return false;
http_response_code(404);
header('Content-Type: text/plain; charset=UTF-8');
echo 'Página não encontrada.';
