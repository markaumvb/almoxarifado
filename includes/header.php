<?php
// Incluir explicitamente o arquivo auth.php no início do header
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';

// Agora podemos usar a função isLoggedIn()
if (!isLoggedIn()) {
    // Se o URL_ROOT não estiver definido, use um caminho relativo
    if (defined('URL_ROOT')) {
        header('Location: ' . URL_ROOT . '/login.php');
    } else {
        // Determinar o caminho relativo para login.php
        $path_to_root = '../../';
        if (strpos($_SERVER['PHP_SELF'], '/pages/itens/') !== false ||
            strpos($_SERVER['PHP_SELF'], '/pages/setores/') !== false ||
            strpos($_SERVER['PHP_SELF'], '/pages/unidades/') !== false ||
            strpos($_SERVER['PHP_SELF'], '/pages/servidores/') !== false ||
            strpos($_SERVER['PHP_SELF'], '/pages/saidas/') !== false) {
            $path_to_root = '../../';
        } elseif (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) {
            $path_to_root = '../';
        }
        header('Location: ' . $path_to_root . 'login.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="bg-dark text-white">
            <div class="sidebar-header p-3 border-bottom">
                <h3><?php echo APP_NAME; ?></h3>
            </div>
            <ul class="list-unstyled components p-3">
                <li>
                    <a href="<?php echo URL_ROOT; ?>/pages/dashboard.php" class="nav-link text-white">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="<?php echo URL_ROOT; ?>/pages/saidas/registrar.php" class="nav-link text-white">
                        <i class="fas fa-sign-out-alt me-2"></i> Saída de Itens
                    </a>
                </li>
                <li>
                    <a href="<?php echo URL_ROOT; ?>/pages/saidas/historico.php" class="nav-link text-white">
                        <i class="fas fa-history me-2"></i> Histórico de Saídas
                    </a>
                </li>
                <li>
                    <a href="#cadastrosSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link text-white">
                        <i class="fas fa-database me-2"></i> Cadastros
                    </a>
                    <ul class="collapse list-unstyled ms-3" id="cadastrosSubmenu">
                        <li>
                            <a href="<?php echo URL_ROOT; ?>/pages/itens/index.php" class="nav-link text-white">
                                <i class="fas fa-box me-2"></i> Itens
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo URL_ROOT; ?>/pages/servidores/index.php" class="nav-link text-white">
                                <i class="fas fa-user me-2"></i> Servidores
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo URL_ROOT; ?>/pages/setores/index.php" class="nav-link text-white">
                                <i class="fas fa-building me-2"></i> Setores
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo URL_ROOT; ?>/pages/unidades/index.php" class="nav-link text-white">
                                <i class="fas fa-ruler me-2"></i> Unidades
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#relatoriosSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle nav-link text-white">
                        <i class="fas fa-chart-bar me-2"></i> Relatórios
                    </a>
                    <ul class="collapse list-unstyled ms-3" id="relatoriosSubmenu">
                        <li>
                            <a href="<?php echo URL_ROOT; ?>/pages/relatorios/index.php" class="nav-link text-white">
                                <i class="fas fa-file-alt me-2"></i> Relatório Geral
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo URL_ROOT; ?>/pages/relatorios/inventario.php" class="nav-link text-white">
                                <i class="fas fa-clipboard-list me-2"></i> Inventário
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        <!-- Conteúdo da página -->
        <div id="content" class="w-100">
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-primary">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="navbar-brand ms-3"><?php echo APP_NAME; ?></span>
                </div>
            </nav>