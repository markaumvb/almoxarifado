<?php
/**
 * login.php - Página de login otimizada e mais segura
 */

// Incluir arquivo de configuração
require_once 'config/config.php';
require_once 'includes/auth.php';

// Verificar se o usuário já está logado
if (isLoggedIn()) {
    redirect('pages/dashboard.php');
    exit;
}

// Definir mensagem de erro
$error_message = '';

// Processar o envio do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar token CSRF
    $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    
    if (!validateCSRFToken($csrf_token)) {
        $error_message = 'Erro de validação do formulário. Tente novamente.';
    } else {
        // Obter as credenciais do usuário com sanitização
        $username = isset($_POST['username']) ? cleanInput($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : ''; // Não sanitizar senha
        
        // Limitar tentativas de login
        if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
            $last_attempt = isset($_SESSION['last_login_attempt']) ? $_SESSION['last_login_attempt'] : 0;
            
            if (time() - $last_attempt < 300) { // 5 minutos de bloqueio
                $error_message = 'Muitas tentativas de login. Tente novamente em ' . 
                                ceil((300 - (time() - $last_attempt)) / 60) . ' minutos.';
            } else {
                // Resetar contagem após 5 minutos
                $_SESSION['login_attempts'] = 0;
            }
        }
        
        if (empty($error_message)) {
            // Autenticar usuário
            if (login($username, $password)) {
                // Limpar contagem de tentativas
                unset($_SESSION['login_attempts']);
                unset($_SESSION['last_login_attempt']);
                
                // Redirecionar para dashboard
                redirect('pages/dashboard.php');
                exit;
            } else {
                // Incrementar contagem de tentativas
                $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? 
                                            $_SESSION['login_attempts'] + 1 : 1;
                $_SESSION['last_login_attempt'] = time();
                
                $error_message = 'Nome de usuário ou senha inválidos.';
            }
        }
    }
}

// Gerar novo token CSRF
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <meta name="description" content="Sistema de controle de almoxarifado">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/style.css">
    <style>
        body { 
            background-color: #f5f5f5;
            display: flex;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 15px;
            margin: auto;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            background: linear-gradient(to right, #007bff, #0062cc);
            padding: 1.5rem;
        }
        .btn-login {
            font-size: 1rem;
            letter-spacing: 0.05rem;
            padding: 0.75rem 1rem;
        }
    </style>
</head>
<body class="text-center">
    <div class="login-container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4><?php echo APP_NAME; ?></h4>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger fade show">
                        <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" autocomplete="off">
                    <!-- Campo CSRF oculto -->
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuário</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required autofocus>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-login text-uppercase fw-bold">Entrar</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center bg-white py-3">
                <small class="text-muted">Sistema de Controle de Almoxarifado</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>