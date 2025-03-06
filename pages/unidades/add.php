<?php
// pages/unidades/add.php

// Incluir o cabeçalho
include_once '../../includes/header.php';

// Incluir as classes necessárias
require_once ROOT_PATH . 'classes/Unidade.php';

// Inicializar as classes
$unidade = new Unidade();

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar e limpar dados
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $sigla = filter_input(INPUT_POST, 'sigla', FILTER_SANITIZE_STRING);
    
    // Verificar dados obrigatórios
    if (empty($nome) || empty($sigla)) {
        setMessage('Por favor, preencha todos os campos obrigatórios.', 'danger');
    } else {
        // Preparar dados para salvar
        $data = [
            'nome' => $nome,
            'sigla' => $sigla
        ];
        
        // Adicionar unidade
        if ($unidade->add($data)) {
            setMessage('Unidade de medida adicionada com sucesso!');
            header('Location: ' . ROOT_URL . 'pages/unidades/index.php');
            exit;
        } else {
            setMessage('Erro ao adicionar unidade de medida', 'danger');
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Nova Unidade de Medida</h1>
        <a href="<?php echo ROOT_URL; ?>pages/unidades/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações da Unidade</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nome" name="nome" maxlength="50" required>
                            <div class="form-text">Exemplo: Unidade, Quilograma, Litro, etc.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="sigla" class="form-label">Sigla <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sigla" name="sigla" maxlength="10" required>
                            <div class="form-text">Exemplo: UN, KG, L, etc.</div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Salvar Unidade
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