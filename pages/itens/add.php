<?php
// pages/itens/add.php

// Incluir o cabeçalho
include_once '../../includes/header.php';

// Incluir as classes necessárias
require_once ROOT_PATH . 'classes/Item.php';
require_once ROOT_PATH . 'classes/Unidade.php';

// Inicializar as classes
$item = new Item();
$unidade = new Unidade();

// Obter unidades
$unidades = $unidade->getUnidades();

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar e limpar dados
    $codigo = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_STRING);
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $tipo = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_STRING);
    $id_unidade = filter_input(INPUT_POST, 'id_unidade', FILTER_SANITIZE_NUMBER_INT);
    $saldo = filter_input(INPUT_POST, 'saldo', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $saldo_minimo = filter_input(INPUT_POST, 'saldo_minimo', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $saldo_maximo = filter_input(INPUT_POST, 'saldo_maximo', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    
    // Verificar dados obrigatórios
    if (empty($codigo) || empty($nome) || empty($tipo)) {
        setMessage('Por favor, preencha todos os campos obrigatórios.', 'danger');
    } else {
        // Preparar dados para salvar
        $data = [
            'codigo' => $codigo,
            'nome' => $nome,
            'tipo' => $tipo,
            'id_unidade' => $id_unidade,
            'saldo' => $saldo,
            'saldo_minimo' => $saldo_minimo,
            'saldo_maximo' => $saldo_maximo
        ];
        
        // Adicionar item
        if ($item->add($data)) {
            setMessage('Item adicionado com sucesso!');
            header('Location: ' . ROOT_URL . 'pages/itens/index.php');
            exit;
        } else {
            setMessage('Erro ao adicionar item', 'danger');
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Novo Item</h1>
        <a href="<?php echo ROOT_URL; ?>pages/itens/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações do Item</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="codigo" name="codigo" maxlength="15" required>
                            </div>
                            <div class="col-md-8">
                                <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nome" name="nome" maxlength="80" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="">Selecione</option>
                                    <option value="P">Permanente</option>
                                    <option value="C">Consumo</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label for="id_unidade" class="form-label">Unidade de Medida</label>
                                <select class="form-select" id="id_unidade" name="id_unidade">
                                    <option value="">Selecione</option>
                                    <?php foreach ($unidades as $u): ?>
                                    <option value="<?php echo $u['ID']; ?>"><?php echo $u['NOME']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="saldo" class="form-label">Saldo Atual</label>
                                <input type="number" class="form-control" id="saldo" name="saldo" min="0" step="0.01" value="0">
                            </div>
                            <div class="col-md-4">
                                <label for="saldo_minimo" class="form-label">Saldo Mínimo</label>
                                <input type="number" class="form-control" id="saldo_minimo" name="saldo_minimo" min="0" step="0.01" value="0">
                            </div>
                            <div class="col-md-4">
                                <label for="saldo_maximo" class="form-label">Saldo Máximo</label>
                                <input type="number" class="form-control" id="saldo_maximo" name="saldo_maximo" min="0" step="0.01" value="0">
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Salvar Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir o rodapé
include_once '../../includes/footer.php';
?>