<?php
// pages/saidas/edit.php

// Incluir o cabeçalho
include_once '../../includes/header.php';

// Incluir as classes necessárias
require_once ROOT_PATH . 'classes/Item.php';
require_once ROOT_PATH . 'classes/Servidor.php';
require_once ROOT_PATH . 'classes/Saida.php';

// Verificar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('ID da saída não especificado', 'danger');
    header('Location: ' . ROOT_URL . 'pages/saidas/index.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Inicializar as classes
$item = new Item();
$servidor = new Servidor();
$saida = new Saida();

// Obter a saída
$saidaInfo = $saida->getSaidaById($id);

if (!$saidaInfo) {
    setMessage('Saída não encontrada', 'danger');
    header('Location: ' . ROOT_URL . 'pages/saidas/index.php');
    exit;
}

// Obter servidores ativos
$servidores = $servidor->getServidoresAtivos();

// Obter informações do item
$itemInfo = $item->getItemByCodigo($saidaInfo['CODIGO']);

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar e limpar dados
    $data_saida = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING);
    $id_servidor = filter_input(INPUT_POST, 'id_servidor', FILTER_SANITIZE_NUMBER_INT);
    $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $observacao = filter_input(INPUT_POST, 'observacao', FILTER_SANITIZE_STRING);
    
    // Verificar dados
    if (empty($data_saida) || empty($id_servidor) || empty($quantidade)) {
        setMessage('Por favor, preencha todos os campos obrigatórios.', 'danger');
    } else {
        // Preparar dados para salvar
        $data = [
            'id' => $id,
            'data' => $data_saida,
            'id_servidor' => $id_servidor,
            'qtde' => $quantidade,
            'codigo' => $saidaInfo['CODIGO'],
            'obs' => $observacao
        ];
        
        // Atualizar a saída
        if ($saida->update($data)) {
            setMessage('Saída atualizada com sucesso!');
            header('Location: ' . ROOT_URL . 'pages/saidas/index.php');
            exit;
        } else {
            setMessage('Erro ao atualizar saída', 'danger');
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Editar Saída</h1>
        <a href="<?php echo ROOT_URL; ?>pages/saidas/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações da Saída</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $id; ?>" method="post">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="data" class="form-label">Data <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="data" name="data" value="<?php echo $saidaInfo['DATA']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="id_servidor" class="form-label">Servidor <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_servidor" name="id_servidor" required>
                                    <option value="">Selecione um servidor</option>
                                    <?php foreach ($servidores as $s): ?>
                                    <option value="<?php echo $s['ID']; ?>" <?php echo ($s['ID'] == $saidaInfo['ID_SERVIDOR']) ? 'selected' : ''; ?>>
                                        <?php echo $s['NOME']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Código do Item</label>
                                <input type="text" class="form-control" value="<?php echo $saidaInfo['CODIGO']; ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nome do Item</label>
                                <input type="text" class="form-control" value="<?php echo $itemInfo['NOME']; ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="quantidade" class="form-label">Quantidade <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantidade" name="quantidade" min="0.01" step="0.01" value="<?php echo $saidaInfo['QTDE']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="observacao" class="form-label">Observação</label>
                                <input type="text" class="form-control" id="observacao" name="observacao" value="<?php echo $saidaInfo['OBS']; ?>">
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