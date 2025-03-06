<?php
// pages/servidores/delete.php

// Incluir arquivos necessários
define('ROOT_PATH', dirname(dirname(dirname(__FILE__))) . '/');
define('ROOT_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/almoxarifado/');

require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/Servidor.php';
require_once ROOT_PATH . 'includes/auth.php';

// Verificar se o usuário está logado
requireLogin();

// Verificar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('ID do servidor não especificado', 'danger');
    header('Location: ' . ROOT_URL . 'pages/servidores/index.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Inicializar a classe
$servidor = new Servidor();

// Excluir o servidor
if ($servidor->delete($id)) {
    setMessage('Servidor excluído com sucesso!');
} else {
    setMessage('Não é possível excluir este servidor porque ele está vinculado a saídas de almoxarifado', 'danger');
}

// Redirecionar para a lista
header('Location: ' . ROOT_URL . 'pages/servidores/index.php');
exit;