<?php
require __DIR__ . '/bootstrap.php';
if (admin_existe() || !dono_setup()) ir('index.php');
if ($_SERVER['REQUEST_METHOD']==='POST') {
 post(); $usuario=campo('usuario'); $senha=senha_campo('senha');
 if (!preg_match('/^[a-zA-Z0-9_.-]{3,60}$/', $usuario) || strlen($senha)<12 || strlen($senha)>72 || $senha!==senha_campo('confirmacao')) { aviso('Use usuário de 3 a 60 letras, números, pontos, hífens ou sublinhados e senha de 12 a 72 bytes. As senhas devem coincidir.'); ir('primeiro_acesso.php'); }
 // A chave primária fixa impede a criação de um segundo administrador, inclusive em concorrência.
 $q=banco()->prepare('INSERT IGNORE INTO administradores (id,usuario,senha_hash) VALUES (1,?,?)'); $q->execute([$usuario,password_hash($senha,PASSWORD_DEFAULT)]);
 aviso($q->rowCount() ? 'Administrador criado. Entre com suas credenciais.' : 'O administrador já foi configurado.', 'sucesso'); ir('login.php');
}
pagina('Primeiro acesso', 'primeiro-acesso');
