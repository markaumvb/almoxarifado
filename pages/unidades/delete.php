<?php
// pages/unidades/delete.php

// Incluir arquivos necessários para ter acesso a todas as constantes e funções
require_once '../../config/config.php';
require_once '../../classes/Database.php';
require_once '../../classes/Unidade.php';
require_once '../../includes/auth.php';

// Verificar se o usuário está logado
if(!isLoggedIn()) {
    setMessage('Você precisa estar logado para acessar esta página', 'danger');
    redirect('../../login.php');
    exit;
}

// Verificar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('ID da unidade não especificado', 'danger');
    header('Location: index.php');
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
header('Location: index.php');
exit;