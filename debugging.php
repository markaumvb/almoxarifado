<?php
// Script para depuração - Coloque este arquivo na raiz do projeto

// Habilitar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir config
require_once 'config/config.php';

echo "<h2>Diagnóstico do Sistema</h2>";

// Testar conexão com o banco
try {
    $db = new Database();
    echo "<div style='color: green;'>✅ Conexão com o banco de dados estabelecida com sucesso!</div>";

    // Verificar existência das tabelas
    $tabelas = ['ITENS', 'SERVIDOR', 'SETOR', 'UNIDADE_MEDIDA', 'SAIDA', 'ENTRADA', 'USUARIOS'];
    
    echo "<h3>Verificando tabelas no banco de dados:</h3>";
    echo "<ul>";
    
    foreach ($tabelas as $tabela) {
        if ($db->tableExists($tabela)) {
            echo "<li style='color: green;'>✅ Tabela {$tabela} encontrada.</li>";
            
            // Mostrar número de registros
            $db->query("SELECT COUNT(*) as total FROM {$tabela}");
            $result = $db->single();
            echo " ({$result['total']} registros)";
        } else {
            echo "<li style='color: red;'>❌ Tabela {$tabela} NÃO encontrada!</li>";
        }
    }
    
    echo "</ul>";
    
    // Verificar estrutura das tabelas que podem estar causando erro
    if ($db->tableExists('ITENS')) {
        echo "<h3>Estrutura da tabela ITENS:</h3>";
        $db->query("DESCRIBE ITENS");
        $campos = $db->resultSet();
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
        foreach ($campos as $campo) {
            echo "<tr>";
            foreach ($campo as $key => $value) {
                echo "<td>{$value}</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    if ($db->tableExists('UNIDADE_MEDIDA')) {
        echo "<h3>Estrutura da tabela UNIDADE_MEDIDA:</h3>";
        $db->query("DESCRIBE UNIDADE_MEDIDA");
        $campos = $db->resultSet();
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
        foreach ($campos as $campo) {
            echo "<tr>";
            foreach ($campo as $key => $value) {
                echo "<td>{$value}</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }

} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Erro na conexão com o banco de dados: " . $e->getMessage() . "</div>";
}

// Verificar caminhos
echo "<h3>Verificando pastas e arquivos essenciais:</h3>";
echo "<ul>";

$dirs = [
    'config/',
    'classes/',
    'includes/',
    'pages/',
    'pages/saidas/',
    'pages/relatorios/',
    'assets/css/'
];

foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "<li style='color: green;'>✅ Diretório {$dir} encontrado.</li>";
    } else {
        echo "<li style='color: red;'>❌ Diretório {$dir} NÃO encontrado!</li>";
    }
}

$files = [
    'config/config.php',
    'classes/Database.php',
    'classes/Item.php',
    'classes/Servidor.php',
    'classes/Setor.php',
    'classes/Unidade.php',
    'classes/Saida.php',
    'classes/User.php',
    'includes/header.php',
    'includes/footer.php',
    'pages/saidas/registrar.php',
    'pages/relatorios/relatorio.php',
    'login.php',
    'assets/css/style.css'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<li style='color: green;'>✅ Arquivo {$file} encontrado.</li>";
    } else {
        echo "<li style='color: red;'>❌ Arquivo {$file} NÃO encontrado!</li>";
    }
}

echo "</ul>";

// Exibir informações da sessão
echo "<h3>Informações da Sessão:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Exibir constantes definidas no config
echo "<h3>Constantes Definidas:</h3>";
echo "<ul>";
echo "<li>APP_NAME: " . (defined('APP_NAME') ? APP_NAME : 'Não definido') . "</li>";
echo "<li>URL_ROOT: " . (defined('URL_ROOT') ? URL_ROOT : 'Não definido') . "</li>";
echo "<li>ROOT_PATH: " . (defined('ROOT_PATH') ? ROOT_PATH : 'Não definido') . "</li>";
echo "</ul>";

echo "<h3>Versão do PHP: " . phpversion() . "</h3>";
echo "<h3>Extensões PHP Carregadas:</h3>";
echo "<ul>";
$exts = get_loaded_extensions();
$required = ['pdo', 'pdo_mysql', 'session', 'json'];

foreach ($required as $ext) {
    if (in_array($ext, $exts)) {
        echo "<li style='color: green;'>✅ {$ext}</li>";
    } else {
        echo "<li style='color: red;'>❌ {$ext} (NECESSÁRIA)</li>";
    }
}

echo "</ul>";