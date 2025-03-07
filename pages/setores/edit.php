<?php
// pages/setores/edit.php

// Incluir o cabeçalho
include_once '../../includes/header.php';

// Incluir as classes necessárias
require_once ROOT_PATH . 'classes/Setor.php';

// Verificar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('ID do setor não especificado', 'danger');
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Inicializar as classes
$setor = new Setor();

// Obter o setor
$setorInfo = $setor->getSetorById($id);

if (!$setorInfo) {
    setMessage('Setor não encontrado', 'danger');
    header('Location: index.php');
    exit;
}

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar e limpar dados
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    
    // Verificar dados obrigatórios
    if (empty($nome)) {
        setMessage('Por favor, preencha o nome do setor.', 'danger');
    } else {
        // Preparar dados para salvar
        $data = [
            'id' => $id,
            'nome' => $nome
        ];
        
        // Atualizar setor
        if ($setor->update($data)) {
            setMessage('Setor atualizado com sucesso!');
            header('Location: index.php');
            exit;
        } else {
            setMessage('Erro ao atualizar setor', 'danger');
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Editar Setor</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações do Setor</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $id; ?>" method="post">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nome" name="nome" maxlength="50" value="<?php echo $setorInfo['NOME']; ?>" required>
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