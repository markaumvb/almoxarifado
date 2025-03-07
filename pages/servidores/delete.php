<?php
// pages/servidores/delete.php

// Incluir arquivos necessários
require_once '../../config/config.php';
require_once '../../classes/Database.php';
require_once '../../classes/Servidor.php';
require_once '../../includes/auth.php';

// Verificar se o usuário está logado
requireLogin();

// Verificar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('ID do servidor não especificado', 'danger');
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Inicializar a classe
$servidor = new Servidor();

// Excluir o servidor
$result = $servidor->delete($id);

if ($result === true) {
    setMessage('Servidor excluído com sucesso!');
} else {
    setMessage('Não é possível excluir este servidor porque ele está vinculado a saídas de almoxarifado', 'danger');
}

// Redirecionar para a lista
header('Location: index.php');
exit;