<?php
// api/itens.php

// Definir o caminho manualmente em vez de usar constantes
$rootPath = dirname(dirname(__FILE__)) . '/';

// Incluir os arquivos de configuração usando caminhos relativos
require_once $rootPath . 'config/config.php';
require_once $rootPath . 'classes/Item.php';
require_once $rootPath . 'includes/auth.php';

// Verificar se o usuário está logado
if(!isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

// Processar a requisição
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Inicializar a classe
    $item = new Item();
    
    // Verificar se é uma busca por código específico
    if (isset($_GET['codigo']) && !empty($_GET['codigo'])) {
        $codigo = filter_input(INPUT_GET, 'codigo', FILTER_SANITIZE_STRING);
        
        // Buscar pelo código exato
        $itemData = $item->getItemByCodigo($codigo);
        
        // Retornar resultado como JSON
        header('Content-Type: application/json');
        if ($itemData) {
            echo json_encode([$itemData]);
        } else {
            echo json_encode([]);
        }
    } 
    // Verificar se é uma busca por nome/termo
    else if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
        
        // Buscar itens que contenham o termo no nome ou código
        $items = $item->searchItems($search);
        
        // Retornar os resultados em JSON
        header('Content-Type: application/json');
        echo json_encode($items);
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