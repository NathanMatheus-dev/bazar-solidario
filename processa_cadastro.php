<?php
require __DIR__ . '/bootstrap.php'; post(); primeiro_acesso();
$dados = ['nome'=>campo('nome'), 'idade'=>campo('idade'), 'serie'=>campo('serie'), 'doou'=>campo('doou'), 'itens_doados'=>campo('itens_doados')];
$idade = filter_var($dados['idade'], FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>120]]);
$valido = preg_match("~^[\\p{L}\\p{M} .’'-]{2,120}$~u", $dados['nome']) && $idade !== false && preg_match('~^[\\p{L}\\p{N} °ºª./-]{1,20}$~u', $dados['serie']) && in_array($dados['doou'], ['0','1'], true) && preg_match('//u', $dados['itens_doados']) && preg_match_all('/./us', $dados['itens_doados']) <= 3000 && ($dados['doou'] === '0' || preg_match_all('/./us', $dados['itens_doados']) >= 2);
if (!$valido) { $_SESSION['antigo']=$dados; aviso('Confira os campos. Use nome válido, idade de 1 a 120, série e descreva os itens se marcou sim.'); ir('index.php'); }
$q = banco()->prepare('INSERT INTO doadores (nome,idade,serie,doou,itens_doados) VALUES (?,?,?,?,?)');
$q->execute([$dados['nome'],$idade,$dados['serie'],(int)$dados['doou'],$dados['doou']==='1' ? $dados['itens_doados'] : '']);
aviso('Participação registrada! Obrigado por fazer parte dessa corrente do bem.', 'sucesso'); ir('index.php');
