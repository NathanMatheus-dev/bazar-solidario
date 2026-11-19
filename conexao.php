<?php
declare(strict_types=1);

function banco(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $arquivo = __DIR__ . '/config.local.php';
    $local = is_file($arquivo) ? require $arquivo : [];
    $host = getenv('DB_HOST') ?: ($local['host'] ?? '127.0.0.1');
    $porta = getenv('DB_PORT') ?: ($local['port'] ?? '3306');
    $banco = getenv('DB_NAME') ?: ($local['name'] ?? 'bazar_solidario');
    $usuario = getenv('DB_USER') ?: ($local['user'] ?? 'bazar');
    $senha = getenv('DB_PASSWORD');
    $senha = $senha === false ? ($local['password'] ?? '') : $senha;

    $pdo = new PDO("mysql:host=$host;port=$porta;dbname=$banco;charset=utf8mb4", $usuario, $senha, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
