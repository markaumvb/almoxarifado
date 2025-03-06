<?php
// pages/unidades/delete.php

// Incluir arquivos necessários
define('ROOT_PATH', dirname(dirname(dirname(__FILE__))) . '/');
define('ROOT_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/almoxarifado/');

require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'classes/Database.php';
require_once ROOT_PATH . 'classes/Unidade.php';
require_once ROOT_PATH . 'includes/auth.php';

// Verificar se o usuário está logado
requireLogin();

// Verificar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('ID da unidade não especificado', 'danger');
    header('Location: ' . ROOT_URL . 'pages/unidades/index.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Inicializar a classe
$unidade = new Unidade();

// Excluir a unidade
if ($unidade->delete($id)) {
    setMessage('Unidade de medida excluída com sucesso!');
} else {
    setMessage('Não é possível excluir esta unidade porque ela está sendo usada por itens', 'danger');
}

// Redirecionar para a lista
header('Location: ' . ROOT_URL . 'pages/unidades/index.php');
exit;