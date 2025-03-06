<?php
// includes/sidebar.php
?>
<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <h5 class="text-white">Sistema de Almoxarifado</h5>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo ROOT_URL; ?>pages/dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white <?php echo strpos($_SERVER['PHP_SELF'], '/saidas/') ? 'active' : ''; ?>" href="<?php echo ROOT_URL; ?>pages/saidas/index.php">
                    <i class="fas fa-dolly me-2"></i>
                    Saídas
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white <?php echo strpos($_SERVER['PHP_SELF'], '/itens/') ? 'active' : ''; ?>" href="<?php echo ROOT_URL; ?>pages/itens/index.php">
                    <i class="fas fa-boxes me-2"></i>
                    Itens
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white <?php echo strpos($_SERVER['PHP_SELF'], '/servidores/') ? 'active' : ''; ?>" href="<?php echo ROOT_URL; ?>pages/servidores/index.php">
                    <i class="fas fa-users me-2"></i>
                    Servidores
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white <?php echo strpos($_SERVER['PHP_SELF'], '/setores/') ? 'active' : ''; ?>" href="<?php echo ROOT_URL; ?>pages/setores/index.php">
                    <i class="fas fa-building me-2"></i>
                    Setores
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white <?php echo strpos($_SERVER['PHP_SELF'], '/unidades/') ? 'active' : ''; ?>" href="<?php echo ROOT_URL; ?>pages/unidades/index.php">
                    <i class="fas fa-ruler me-2"></i>
                    Unidades de Medida
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white <?php echo strpos($_SERVER['PHP_SELF'], '/relatorios/') ? 'active' : ''; ?>" href="<?php echo ROOT_URL; ?>pages/relatorios/index.php">
                    <i class="fas fa-chart-bar me-2"></i>
                    Relatórios
                </a>
            </li>
        </ul>
    </div>
</nav>