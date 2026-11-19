<?php
require __DIR__ . '/bootstrap.php';
primeiro_acesso();

if (!empty($_SESSION['admin'])) {
    ir('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    post();
    $db = banco();
    $endereco = ip();

    // Reserva uma tentativa em uma única escrita, inclusive sob concorrência.
    $tentativa = $db->prepare(
        'INSERT INTO tentativas_login (ip, tentativas, janela_inicio) VALUES (?, 1, ?)
         ON CONFLICT(ip) DO UPDATE SET
            tentativas = CASE
                WHEN excluded.janela_inicio - tentativas_login.janela_inicio >= 900 THEN 1
                ELSE tentativas_login.tentativas + 1
            END,
            janela_inicio = CASE
                WHEN excluded.janela_inicio - tentativas_login.janela_inicio >= 900 THEN excluded.janela_inicio
                ELSE tentativas_login.janela_inicio
            END
         WHERE tentativas_login.tentativas < 5
            OR excluded.janela_inicio - tentativas_login.janela_inicio >= 900'
    );
    $tentativa->execute([$endereco, time()]);
    if ($tentativa->rowCount() === 0) {
        aviso('Muitas tentativas. Aguarde 15 minutos antes de tentar novamente.');
        ir('login.php');
    }

    $consulta = $db->prepare('SELECT id, usuario, senha_hash FROM administradores WHERE usuario = ?');
    $consulta->execute([campo('usuario')]);
    $admin = $consulta->fetch();
    $consulta->closeCursor();

    $senha = senha_campo('senha');
    if ($admin && strlen($senha) <= 72 && password_verify($senha, $admin['senha_hash'])) {
        session_regenerate_id(true);
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        $_SESSION['admin'] = $admin['id'];
        $_SESSION['usuario'] = $admin['usuario'];
        $_SESSION['atividade'] = time();
        $db->prepare('DELETE FROM tentativas_login WHERE ip = ?')->execute([$endereco]);
        ir('dashboard.php');
    }

    aviso('Usuário ou senha incorretos.');
    ir('login.php');
}

pagina('Acesso restrito', 'login');
