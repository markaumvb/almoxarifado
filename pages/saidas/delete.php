<?php
// pages/saidas/delete.php

// Incluir arquivos necessários
define('ROOT_PATH', dirname(dirname(dirname(__FILE__))) . '/');
define('ROOT_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/almoxarifado/');

require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/Saida.php';
require_once ROOT_PATH . 'includes/auth.php';

// Verificar se o usuário está logado
requireLogin();

// Verificar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('ID da saída não especificado', 'danger');
    header('Location: ' . ROOT_URL . 'pages/saidas/index.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Inicializar a classe
$saida = new Saida();

// Excluir a saída
if ($saida->delete($id)) {
    setMessage('Saída excluída com sucesso!');
} else {
    setMessage('Erro ao excluir a saída', 'danger');
}

// Redirecionar para a lista
header('Location: ' . ROOT_URL . 'pages/saidas/index.php');
exit;