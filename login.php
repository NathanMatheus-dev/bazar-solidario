<?php
require __DIR__ . '/bootstrap.php'; primeiro_acesso();
if (!empty($_SESSION['admin'])) ir('dashboard.php');
if ($_SERVER['REQUEST_METHOD']==='POST') {
 post(); $db=banco(); $db->beginTransaction();
 $q=$db->prepare('INSERT IGNORE INTO tentativas_login (ip,tentativas,janela_inicio) VALUES (?,0,NOW())'); $q->execute([ip()]);
 $q=$db->prepare('SELECT tentativas, TIMESTAMPDIFF(SECOND,janela_inicio,NOW()) AS decorrido FROM tentativas_login WHERE ip=? FOR UPDATE'); $q->execute([ip()]); $limite=$q->fetch();
 if ((int)$limite['decorrido']>=900) { $q=$db->prepare('UPDATE tentativas_login SET tentativas=0,janela_inicio=NOW() WHERE ip=?'); $q->execute([ip()]); $limite['tentativas']=0; }
 if ((int)$limite['tentativas']>=5) { $db->commit(); aviso('Muitas tentativas. Aguarde 15 minutos antes de tentar novamente.'); ir('login.php'); }
 $q=$db->prepare('UPDATE tentativas_login SET tentativas=tentativas+1 WHERE ip=?'); $q->execute([ip()]); $db->commit();
 $q=$db->prepare('SELECT id,usuario,senha_hash FROM administradores WHERE usuario=?'); $q->execute([campo('usuario')]); $admin=$q->fetch();
 $senha=senha_campo('senha');
 if ($admin && strlen($senha)<=72 && password_verify($senha,$admin['senha_hash'])) {
  session_regenerate_id(true); $_SESSION['csrf']=bin2hex(random_bytes(32)); $_SESSION['admin']=$admin['id']; $_SESSION['usuario']=$admin['usuario']; $_SESSION['atividade']=time();
  $q=$db->prepare('DELETE FROM tentativas_login WHERE ip=?'); $q->execute([ip()]); ir('dashboard.php');
 }
 aviso('Usuário ou senha incorretos.'); ir('login.php');
}
topo('Acesso restrito'); ?>
<section class="cartao"><span class="etiqueta">ÁREA DO ADMINISTRADOR</span><h1>Bom ter você aqui.</h1><p>Entre para acompanhar as participações e o impacto do bazar.</p><form method="post"><?php token(); ?><div class="campo"><label for="usuario">Usuário</label><input id="usuario" name="usuario" required maxlength="60" autocomplete="username"></div><div class="campo"><label for="senha">Senha</label><input id="senha" name="senha" type="password" required maxlength="72" autocomplete="current-password"></div><button class="botao">Entrar no painel →</button></form><a class="voltar" href="index.php">← Voltar ao cadastro</a></section><?php rodape(); ?>
