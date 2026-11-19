<?php
declare(strict_types=1);

function banco(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $arquivo = getenv('BAZAR_DB_PATH') ?: __DIR__ . '/.runtime/bazar.sqlite';
    $diretorio = dirname($arquivo);
    if (!is_dir($diretorio) && !@mkdir($diretorio, 0700, true) && !is_dir($diretorio)) {
        throw new RuntimeException('Não foi possível criar o diretório do banco local.');
    }

    $mascara = umask(0077);
    try {
        $conexao = new PDO('sqlite:' . $arquivo, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5,
        ]);
    } finally {
        umask($mascara);
    }

    if ((int) $conexao->query('PRAGMA user_version')->fetchColumn() === 0) {
        $schema = file_get_contents(__DIR__ . '/banco.sql');
        if ($schema === false) {
            throw new RuntimeException('Schema do banco local indisponível.');
        }
        $conexao->exec($schema);
    }

    return $pdo = $conexao;
}
