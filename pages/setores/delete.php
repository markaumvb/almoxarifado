<?php
// pages/setores/delete.php

// Incluir arquivos necessários
require_once '../../config/config.php';
require_once '../../classes/Database.php';
require_once '../../classes/Setor.php';
require_once '../../includes/auth.php';

// Verificar se o usuário está logado
requireLogin();

// Verificar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('ID do setor não especificado', 'danger');
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Inicializar a classe
$setor = new Setor();

// Excluir o setor
$result = $setor->delete($id);

if ($result === true) {
    setMessage('Setor excluído com sucesso!');
} else {
    setMessage('Não é possível excluir este setor porque ele está sendo usado por servidores', 'danger');
}

// Redirecionar para a lista
header('Location: index.php');
exit;