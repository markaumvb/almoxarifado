<?php
// Temporário para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir arquivo de configuração
require_once 'config/config.php';
require_once 'includes/auth.php';

// Verificar se o usuário já está logado
if (isLoggedIn()) {
    redirect('pages/dashboard.php');
}

// Definir mensagem de erro
$error_message = '';

// Processar o envio do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obter as credenciais do usuário
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // Verificar credenciais com o arquivo restrito
    if (file_exists('restrito')) {
        $arquivo_restrito = file_get_contents('restrito');
        if ($arquivo_restrito !== false) {
            // Formato esperado: "username:password"
            list($user_correto, $senha_correta) = explode(':', $arquivo_restrito);
            
            // Verificar se as credenciais coincidem
            if ($username === $user_correto && $password === $senha_correta) {
                // Autenticação bem-sucedida
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = $username;
                $_SESSION['user_name'] = 'Administrador';
                $_SESSION['user_level'] = 3;
                
                redirect('pages/dashboard.php');
                exit;
            } else {
                // Autenticação falhou
                $error_message = 'Nome de usuário ou senha inválidos.';
            }
        }
    } else {
        // Arquivo restrito não encontrado
        $error_message = 'Erro no sistema de autenticação. Contate o administrador.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4>Sistema de Almoxarifado</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuário</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Entrar</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center bg-white py-3">
                        <small class="text-muted">Sistema de Controle de Almoxarifado</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>