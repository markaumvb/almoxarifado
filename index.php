<?php
// index.php
// Incluir arquivo de configuração
require_once 'config/config.php';

// Redirecionar para a página de login ou dashboard
if (isLoggedIn()) {
    redirect('pages/dashboard.php');
} else {
    redirect('login.php');
}