<?php
require __DIR__ . '/bootstrap.php';
primeiro_acesso();

$antigo = $_SESSION['antigo'] ?? [];
unset($_SESSION['antigo']);
$doou = ($antigo['doou'] ?? '1') !== '0';

pagina('Faça parte', 'cadastro', [
    'nome' => e($antigo['nome'] ?? ''),
    'idade' => e($antigo['idade'] ?? ''),
    'serie' => e($antigo['serie'] ?? ''),
    'itens' => e($antigo['itens_doados'] ?? ''),
    'sim' => $doou ? 'checked' : '',
    'nao' => $doou ? '' : 'checked',
]);
