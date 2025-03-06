<?php
// Iniciar buffer de saída
ob_start();

// Incluir arquivos necessários
require_once '../../classes/Item.php';

// Verificar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = 'ID do item não especificado';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Inicializar a classe
$item = new Item();

// Excluir o item
if ($item->delete($id)) {
    $_SESSION['message'] = 'Item excluído com sucesso!';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Erro ao excluir o item';
    $_SESSION['message_type'] = 'danger';
}

// Redirecionar para a lista
header('Location: index.php');
exit;
?>