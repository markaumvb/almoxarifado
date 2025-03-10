<?php
// includes/auth.php - Versão otimizada e mais segura
if (session_status() === PHP_SESSION_NONE) {
    // Configurações de segurança para sessões
    ini_set('session.cookie_httponly', 1);
    
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    session_start();
}

// Função para verificar se o usuário está logado
function isLoggedIn() {
    return (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && 
            isset($_SESSION['user_id']) && isset($_SESSION['last_activity']));
}

// Verificar inatividade e forçar logout se necessário (30 minutos)
function checkSessionActivity() {
    $max_inactivity = 30 * 60; // 30 minutos em segundos
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $max_inactivity)) {
        logout(); // Logout automático após inatividade
        return false;
    }
    
    // Atualizar timestamp de última atividade
    $_SESSION['last_activity'] = time();
    return true;
}

// Redirecionar se não estiver logado
function requireLogin() {
    if (!isLoggedIn() || !checkSessionActivity()) {
        $_SESSION['message'] = 'Por favor, faça login para acessar o sistema';
        $_SESSION['message_type'] = 'danger';
        if (defined('URL_ROOT')) {
            header('Location: ' . URL_ROOT . '/login.php');
        } else {
            header('Location: /login.php');
        }
        exit;
    }
}

// Função para login seguro
function login($username, $password) {
    // Verificar credenciais com o arquivo restrito
    if (file_exists(ROOT_PATH . '/restrito')) {
        $arquivo_restrito = file_get_contents(ROOT_PATH . '/restrito');
        if ($arquivo_restrito !== false) {
            // Formato esperado: "username:password"
            list($user_correto, $senha_correta) = explode(':', $arquivo_restrito);
            
            // Verificar usando comparação de string segura contra timing attacks
            if (hash_equals(trim($user_correto), trim($username)) && 
                hash_equals(trim($senha_correta), trim($password))) {
                
                // Gerar novo ID de sessão para prevenir session fixation
                session_regenerate_id(true);
                
                // Login bem-sucedido
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = $username;
                $_SESSION['user_name'] = 'Administrador';
                $_SESSION['user_level'] = 3; // Nível de administrador
                $_SESSION['last_activity'] = time();
                
                // Registrar IP e user agent para detecção de sessão roubada
                $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                
                return true;
            }
        }
    }
    
    // Adicionar pequeno delay para dificultar ataques de força bruta
    usleep(random_int(100000, 300000)); // 0.1 a 0.3 segundos
    
    return false;
}

// Verificar se a sessão foi comprometida
function validateSession() {
    if (isset($_SESSION['ip']) && isset($_SESSION['user_agent'])) {
        if ($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR'] || 
            $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            // Possível sessão roubada - forçar logout
            logout();
            return false;
        }
    }
    return true;
}

// Função para logout seguro
function logout() {
    // Limpar todas as variáveis de sessão
    $_SESSION = array();
    
    // Destruir o cookie da sessão
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruir a sessão
    session_destroy();
    
    // Redirecionar para página de login
    if (defined('URL_ROOT')) {
        header('Location: ' . URL_ROOT . '/login.php');
    } else {
        header('Location: /login.php');
    }
    exit;
}

// Definir mensagens flash
function setMessage($message, $type = 'success') {
    $_SESSION['message'] = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $_SESSION['message_type'] = $type;
}

// Exibir mensagens flash com segurança contra XSS
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
        
        // Sanitizar a saída
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $type = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
        
        // Garantir que o tipo seja válido
        $validTypes = ['success', 'info', 'warning', 'danger'];
        if (!in_array($type, $validTypes)) {
            $type = 'info';
        }
        
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo $message;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        
        // Limpar mensagem após exibição
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

// Função para verificar permissão
function hasPermission($level) {
    if(isset($_SESSION['user_level']) && $_SESSION['user_level'] >= $level) {
        return true;
    }
    return false;
}

// Criar token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verificar token CSRF
function validateCSRFToken($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

// Adicionar campo CSRF a formulários
function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// Verifica se sessão é válida
if (isLoggedIn()) {
    validateSession();
    // Atualiza tempo de atividade
    $_SESSION['last_activity'] = time();
}
?>