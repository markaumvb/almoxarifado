<?php
ob_start();
// Configurações do aplicativo
define('APP_NAME', 'Sistema de Almoxarifado');
define('APP_VERSION', '1.0.0');

// Configurações de caminho
define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');

// IMPORTANTE: Correção da URL_ROOT e ROOT_URL
define('URL_ROOT', 'https://200.238.174.7/almoxarifado');
define('ROOT_URL', 'https://200.238.174.7/almoxarifado');

// Configurações do banco de dados
define('DB_HOST', '200.238.174.7');
define('DB_USER', 'almoxarifado');
define('DB_PASS', 'Almoxarifado1*');
define('DB_NAME', 'almoxarifado');

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Função para redirecionar
function redirect($page) {
    header('Location: ' . URL_ROOT . '/' . $page);
    exit;
}

// Função auxiliar para limpar dados de entrada
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Função para mensagens flash (retrocompatibilidade)
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if(isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Carrega classes automaticamente
spl_autoload_register(function($className) {
    $file = ROOT_PATH . '/classes/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    } else {
        trigger_error("Não foi possível carregar a classe $className. Arquivo $file não encontrado.", E_USER_WARNING);
    }
});

require_once ROOT_PATH . '/includes/auth.php';