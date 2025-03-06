<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Função para verificar se o usuário está logado
function isLoggedIn() {
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        return true;
    } else {
        return false;
    }
}

// Redirecionar se não estiver logado
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['message'] = 'Por favor, faça login para acessar o sistema';
        $_SESSION['message_type'] = 'danger';
        header('Location: ' . URL_ROOT . '/login.php');
        exit;
    }
}

// Função para fazer login (usando apenas o arquivo restrito)
function login($username, $password) {
    // Verificar credenciais com o arquivo restrito
    if (file_exists(ROOT_PATH . '/restrito')) {
        $arquivo_restrito = file_get_contents(ROOT_PATH . '/restrito');
        if ($arquivo_restrito !== false) {
            // Formato esperado: "username:password"
            list($user_correto, $senha_correta) = explode(':', $arquivo_restrito);
            
            // Verificar se as credenciais coincidem
            if (trim($username) === trim($user_correto) && trim($password) === trim($senha_correta)) {
                // Login bem-sucedido
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = $username;
                $_SESSION['user_name'] = 'Administrador';
                $_SESSION['user_level'] = 3; // Nível de administrador
                return true;
            }
        }
    }
    return false;
}

// Função para logout
function logout() {
    // Unset todas as variáveis de sessão
    $_SESSION = array();
    
    // Destruir a sessão
    session_destroy();
    
    // Redirecionar para página de login
    header('Location: ' . URL_ROOT . '/login.php');
    exit;
}

// Definir mensagens flash
function setMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

// Exibir mensagens flash
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
        
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo $message;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        
        // Limpar mensagem após exibição
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

// Função para verificar permissão (simplificada)
function hasPermission($level) {
    if(isset($_SESSION['user_level']) && $_SESSION['user_level'] >= $level) {
        return true;
    }
    return false;
}
?>