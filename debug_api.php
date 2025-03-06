<?php
// Arquivo para depurar API de itens (coloque na raiz do projeto)
// Acesse este arquivo pelo navegador para testar a API

// Ativar exibição de erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir config
require_once 'config/config.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    echo "<p style='color:red;'>Você precisa estar logado para usar este debug.</p>";
    exit;
}

// Inicializar classe Item
require_once 'classes/Item.php';
$item = new Item();

echo "<h1>Debug da API de Itens</h1>";

// Testar busca por código
echo "<h2>Testando busca por código</h2>";
echo "<form method='get'>";
echo "<input type='text' name='codigo' placeholder='Digite o código' value='" . (isset($_GET['codigo']) ? htmlspecialchars($_GET['codigo']) : '') . "'>";
echo "<button type='submit'>Buscar por Código</button>";
echo "</form>";

if (isset($_GET['codigo']) && !empty($_GET['codigo'])) {
    $codigo = $_GET['codigo'];
    echo "<p>Buscando código: <strong>$codigo</strong></p>";
    
    $result = $item->getItemByCodigo($codigo);
    
    echo "<h3>Resultado:</h3>";
    echo "<pre>";
    var_dump($result);
    echo "</pre>";
    
    if ($result) {
        echo "<p style='color:green;'>✅ Item encontrado pelo código</p>";
        echo "<ul>";
        echo "<li><strong>Código:</strong> " . htmlspecialchars($result['CODIGO']) . "</li>";
        echo "<li><strong>Nome:</strong> " . htmlspecialchars($result['NOME']) . "</li>";
        echo "<li><strong>Tipo:</strong> " . htmlspecialchars($result['TIPO']) . "</li>";
        echo "<li><strong>Saldo:</strong> " . htmlspecialchars($result['SALDO']) . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color:red;'>❌ Nenhum item encontrado com esse código</p>";
    }
}

// Testar busca por termo (nome)
echo "<h2>Testando busca por termo (nome)</h2>";
echo "<form method='get'>";
echo "<input type='text' name='search' placeholder='Digite um termo' value='" . (isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '') . "'>";
echo "<button type='submit'>Buscar por Nome</button>";
echo "</form>";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    echo "<p>Buscando termo: <strong>$search</strong></p>";
    
    // Verificar se o método searchItems existe na classe Item
    if (method_exists($item, 'searchItems')) {
        $results = $item->searchItems($search);
        
        echo "<h3>Resultados:</h3>";
        if ($results && count($results) > 0) {
            echo "<p style='color:green;'>✅ " . count($results) . " itens encontrados</p>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Código</th><th>Nome</th><th>Tipo</th><th>Saldo</th></tr>";
            
            foreach ($results as $result) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($result['CODIGO']) . "</td>";
                echo "<td>" . htmlspecialchars($result['NOME']) . "</td>";
                echo "<td>" . htmlspecialchars($result['TIPO']) . "</td>";
                echo "<td>" . htmlspecialchars($result['SALDO']) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p style='color:red;'>❌ Nenhum item encontrado com esse termo</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ Método searchItems não existe na classe Item</p>";
        
        // Verificar SQL diretamente
        echo "<h3>Tentando query SQL direta:</h3>";
        try {
            $db = new Database();
            $db->query("SELECT * FROM ITENS WHERE NOME LIKE :term OR CODIGO LIKE :term LIMIT 10");
            $db->bind(':term', '%' . $search . '%');
            $directResults = $db->resultSet();
            
            if ($directResults && count($directResults) > 0) {
                echo "<p style='color:green;'>✅ " . count($directResults) . " itens encontrados com SQL direto</p>";
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>Código</th><th>Nome</th><th>Tipo</th><th>Saldo</th></tr>";
                
                foreach ($directResults as $result) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($result['CODIGO']) . "</td>";
                    echo "<td>" . htmlspecialchars($result['NOME']) . "</td>";
                    echo "<td>" . htmlspecialchars($result['TIPO']) . "</td>";
                    echo "<td>" . htmlspecialchars($result['SALDO']) . "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p style='color:red;'>❌ Nenhum item encontrado com SQL direto</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color:red;'>❌ Erro na query SQL: " . $e->getMessage() . "</p>";
        }
    }
}

// Testar diretamente a API
echo "<h2>Testar diretamente a API</h2>";
echo "<p>URLs da API para testar:</p>";
echo "<ul>";
echo "<li><a href='api/itens.php?codigo=teste' target='_blank'>api/itens.php?codigo=teste</a></li>";
echo "<li><a href='api/itens.php?search=caneta' target='_blank'>api/itens.php?search=caneta</a></li>";
echo "</ul>";
?>