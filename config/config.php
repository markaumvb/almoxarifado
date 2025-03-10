<?php
/**
 * Arquivo de configuração principal do sistema
 * Versão otimizada com constantes e funções auxiliares
 */

// Iniciar buffer de saída para controle de redirecionamentos
ob_start();

// Configurar relatório de erros (somente em desenvolvimento)
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
    // Ambiente de desenvolvimento
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    define('DEBUG_MODE', true);
} else {
    // Ambiente de produção
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
    ini_set('display_errors', 0);
    define('DEBUG_MODE', false);
}

// Configuração do fuso horário
date_default_timezone_set('America/Recife');

// Configurações do aplicativo
define('APP_NAME', 'Sistema de Almoxarifado');
define('APP_VERSION', '1.1.0');

// Definir caminho da raiz do projeto
define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');

// Definir URLs do sistema
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$domain = $_SERVER['HTTP_HOST'];
$base_path = dirname(dirname($_SERVER['SCRIPT_NAME']));
$base_path = str_replace('\\', '/', $base_path);
$base_path = $base_path !== '/' ? rtrim($base_path, '/') : '';

// Ajustes para configurações de produção ou teste local
if ($domain === '200.238.174.7') {
    define('URL_ROOT', 'https://200.238.174.7/almoxarifado');
    define('ROOT_URL', 'https://200.238.174.7/almoxarifado');
} else {
    define('URL_ROOT', $protocol . $domain . $base_path);
    define('ROOT_URL', $protocol . $domain . $base_path);
}

// Configurações do banco de dados
define('DB_HOST', '200.238.174.7');
define('DB_USER', 'almoxarifado');
define('DB_PASS', 'Almoxarifado1*');
define('DB_NAME', 'almoxarifado');

// Iniciar sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    // Configurações seguras para cookies de sessão
    ini_set('session.cookie_httponly', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// Função para redirecionar
function redirect($page) {
    // Remover qualquer saída prévia
    ob_clean();
    
    // Verificar se a URL começa com http
    if (strpos($page, 'http') === 0) {
        header('Location: ' . $page);
    } else {
        // Se começar com /, não adicionar barra extra
        $page = (substr($page, 0, 1) === '/') ? $page : '/' . $page;
        header('Location: ' . URL_ROOT . $page);
    }
    exit;
}

// Função auxiliar para limpar dados de entrada
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Sanitizar arrays recursivamente
function sanitizeArray($array) {
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = sanitizeArray($value);
        } else {
            $array[$key] = cleanInput($value);
        }
    }
    return $array;
}

// Carregar classes automaticamente
spl_autoload_register(function($className) {
    $classFile = ROOT_PATH . '/classes/' . $className . '.php';
    
    if (file_exists($classFile)) {
        require_once $classFile;
    } else {
        // Log do erro sem expor caminhos completos em produção
        if (DEBUG_MODE) {
            trigger_error("Não foi possível carregar a classe $className. Arquivo $classFile não encontrado.", E_USER_WARNING);
        } else {
            error_log("Erro ao carregar a classe $className.");
        }
    }
});

// Incluir funções de autenticação
require_once ROOT_PATH . '/includes/auth.php';

// Variáveis globais para JavaScript
$js_globals = [
    'url_root' => URL_ROOT,
    'csrf_token' => isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '',
    'debug_mode' => DEBUG_MODE
];

// Função para formatar moeda brasileira
function formatMoney($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

// Função para formatar data no padrão brasileiro
function formatDateBR($date) {
    if (empty($date)) return '';
    return date('d/m/Y', strtotime($date));
}

// Função segura para debug (somente em desenvolvimento)
function debug($var, $die = false) {
    if (DEBUG_MODE) {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
        if ($die) die();
    }
}
?>