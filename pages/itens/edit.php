<?php
// pages/itens/edit.php

// Incluir o cabeçalho
include_once '../../includes/header.php';

// Incluir as classes necessárias
require_once '../../classes/Item.php';
require_once '../../classes/Unidade.php';

// Verificar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = 'ID do item não especificado';
    $_SESSION['message_type'] = 'danger';
    header('Location: ../../pages/itens/index.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Inicializar as classes
$item = new Item();
$unidade = new Unidade();

// Obter o item
$itemInfo = $item->getItemById($id);

if (!$itemInfo) {
    $_SESSION['message'] = 'Item não encontrado';
    $_SESSION['message_type'] = 'danger';
    header('Location: ../../pages/itens/index.php');
    exit;
}

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
        $_SESSION['message'] = 'Por favor, preencha todos os campos obrigatórios.';
        $_SESSION['message_type'] = 'danger';
    } else {
        // Preparar dados para salvar
        $data = [
            'id' => $id,
            'codigo' => $codigo,
            'nome' => $nome,
            'tipo' => $tipo,
            'id_unidade' => $id_unidade,
            'saldo' => $saldo,
            'saldo_minimo' => $saldo_minimo,
            'saldo_maximo' => $saldo_maximo
        ];
        
        // Atualizar item
        if ($item->update($data)) {
            $_SESSION['message'] = 'Item atualizado com sucesso!';
            $_SESSION['message_type'] = 'success';
            header('Location: ../../pages/itens/index.php');
            exit;
        } else {
            $_SESSION['message'] = 'Erro ao atualizar item';
            $_SESSION['message_type'] = 'danger';
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Editar Item</h1>
        <a href="../../pages/itens/index.php" class="btn btn-secondary">
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
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $id; ?>" method="post">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="codigo" name="codigo" maxlength="15" value="<?php echo $itemInfo['CODIGO']; ?>" required>
                            </div>
                            <div class="col-md-8">
                                <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nome" name="nome" maxlength="80" value="<?php echo $itemInfo['NOME']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="">Selecione</option>
                                    <option value="P" <?php echo ($itemInfo['TIPO'] == 'P') ? 'selected' : ''; ?>>Permanente</option>
                                    <option value="C" <?php echo ($itemInfo['TIPO'] == 'C') ? 'selected' : ''; ?>>Consumo</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label for="id_unidade" class="form-label">Unidade de Medida</label>
                                <select class="form-select" id="id_unidade" name="id_unidade">
                                    <option value="">Selecione</option>
                                    <?php foreach ($unidades as $u): ?>
                                    <option value="<?php echo $u['ID']; ?>" <?php echo ($u['ID'] == $itemInfo['ID_UNIDADE']) ? 'selected' : ''; ?>>
                                        <?php echo $u['NOME']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="saldo" class="form-label">Saldo Atual</label>
                                <input type="number" class="form-control" id="saldo" name="saldo" step="0.01" value="<?php echo $itemInfo['SALDO']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="saldo_minimo" class="form-label">Saldo Mínimo</label>
                                <input type="number" class="form-control" id="saldo_minimo" name="saldo_minimo" min="0" step="0.01" value="<?php echo $itemInfo['SALDO_MINIMO']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="saldo_maximo" class="form-label">Saldo Máximo</label>
                                <input type="number" class="form-control" id="saldo_maximo" name="saldo_maximo" min="0" step="0.01" value="<?php echo $itemInfo['SALDO_MAXIMO']; ?>">
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Salvar Alterações
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