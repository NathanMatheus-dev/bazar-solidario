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
topo('Primeiro acesso'); ?>
<section class="cartao"><span class="etiqueta">BEM-VINDO AO BAZAR</span><h1>Um começo seguro</h1><p>Este primeiro acesso reservou a configuração para o seu IP. Crie o único administrador do sistema.</p><form method="post"><?php token(); ?><div class="campo"><label for="usuario">Nome de usuário</label><input id="usuario" name="usuario" required minlength="3" maxlength="60" autocomplete="username" pattern="[a-zA-Z0-9_.-]{3,60}"></div><div class="campo"><label for="senha">Senha (12 a 72 bytes)</label><input id="senha" name="senha" type="password" required minlength="12" maxlength="72" autocomplete="new-password"></div><div class="campo"><label for="confirmacao">Confirme sua senha</label><input id="confirmacao" name="confirmacao" type="password" required minlength="12" maxlength="72" autocomplete="new-password"></div><button class="botao">Criar administrador →</button></form></section><?php rodape(); ?>
