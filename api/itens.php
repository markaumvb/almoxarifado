<?php
// api/itens.php

// Definir o path absoluto para a raiz do projeto
define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');

// Incluir os arquivos de configuração
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'classes/Item.php';
require_once ROOT_PATH . 'includes/auth.php';

// Verificar se o usuário está logado
if(!isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

// Processar a requisição
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Inicializar a classe
    $item = new Item();
    
    // Verificar se é uma busca por nome/termo
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
        $items = $item->searchItems($search);
        
        // Retornar os resultados em JSON
        header('Content-Type: application/json');
        echo json_encode($items);
    } 
    // Verificar se é uma busca por código específico
    else if (isset($_GET['codigo']) && !empty($_GET['codigo'])) {
        $codigo = filter_input(INPUT_GET, 'codigo', FILTER_SANITIZE_STRING);
        
        // Buscar pelo código exato usando o método apropriado
        $itemData = $item->getItemByCodigo($codigo);
        
        if ($itemData) {
            // Retornar como array para manter consistência
            header('Content-Type: application/json');
            echo json_encode([$itemData]);
        } else {
            // Se não encontrar, retorna array vazio
            header('Content-Type: application/json');
            echo json_encode([]);
        }
    } 
    // Caso contrário, retornar todos os itens
    else {
        $items = $item->getItems();
        
        // Retornar os resultados em JSON
        header('Content-Type: application/json');
        echo json_encode($items);
    }
} else {
    // Método não permitido
    header("HTTP/1.1 405 Method Not Allowed");
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
}