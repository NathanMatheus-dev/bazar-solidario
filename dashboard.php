<?php
require __DIR__ . '/bootstrap.php';
protegido();

$linhas = banco()->query(
    'SELECT id, nome, idade, serie, doou, itens_doados, data_cadastro
     FROM doadores ORDER BY data_cadastro DESC, id DESC'
)->fetchAll();
$salas = banco()->query(
    'SELECT serie, AVG(idade) AS media FROM doadores GROUP BY serie ORDER BY serie'
)->fetchAll();

$participacoes = '';
foreach ($linhas as $linha) {
    $participacoes .= vista('participacao', [
        'id' => (string) $linha['id'],
        'nome' => e($linha['nome']),
        'idade' => (string) $linha['idade'],
        'serie' => e($linha['serie']),
        'doou' => $linha['doou'] ? 'Sim' : 'Ainda não',
        'itens' => e($linha['itens_doados'] === '' ? '—' : $linha['itens_doados']),
        'data' => date('d/m/Y H:i', strtotime($linha['data_cadastro'] . ' UTC')),
    ]);
}

$resumo = '';
foreach ($salas as $sala) {
    $resumo .= vista('resumo-turma', [
        'serie' => e($sala['serie']),
        'media' => number_format((float) $sala['media'], 1, ',', '.'),
    ]);
}

$total = count($linhas);
$doaram = array_sum(array_column($linhas, 'doou'));
$naoDoaram = $total - $doaram;
$graficos = json_encode([
    'medias' => array_map('floatval', array_column($salas, 'media')),
    'doacoes' => [$doaram, $naoDoaram],
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);

pagina('Painel', 'painel', [
    'usuario' => e($_SESSION['usuario']),
    'total' => (string) $total,
    'doaram' => (string) $doaram,
    'nao_doaram' => (string) $naoDoaram,
    'resumo_turmas' => $resumo,
    'participacoes' => $participacoes ?: vista('participacoes-vazias', []),
    'graficos' => $graficos,
]);
