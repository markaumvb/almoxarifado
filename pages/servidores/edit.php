<?php
// pages/servidores/edit.php

// Incluir o cabeçalho
include_once '../../includes/header.php';

// Incluir as classes necessárias
require_once ROOT_PATH . 'classes/Servidor.php';
require_once ROOT_PATH . 'classes/Setor.php';

// Verificar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('ID do servidor não especificado', 'danger');
    header('Location: ' . ROOT_URL . 'pages/servidores/index.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Inicializar as classes
$servidor = new Servidor();
$setor = new Setor();

// Obter o servidor
$servidorInfo = $servidor->getServidorById($id);

if (!$servidorInfo) {
    setMessage('Servidor não encontrado', 'danger');
    header('Location: ' . ROOT_URL . 'pages/servidores/index.php');
    exit;
}

// Obter setores
$setores = $setor->getSetores();

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar e limpar dados
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $matricula = filter_input(INPUT_POST, 'matricula', FILTER_SANITIZE_NUMBER_INT);
    $id_setor = filter_input(INPUT_POST, 'id_setor', FILTER_SANITIZE_NUMBER_INT);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
    // Verificar dados obrigatórios
    if (empty($nome) || empty($matricula)) {
        setMessage('Por favor, preencha todos os campos obrigatórios.', 'danger');
    } else {
        // Preparar dados para salvar
        $data = [
            'id' => $id,
            'nome' => $nome,
            'matricula' => $matricula,
            'id_setor' => $id_setor,
            'email' => $email,
            'status' => $status
        ];
        
        // Atualizar servidor
        if ($servidor->update($data)) {
            setMessage('Servidor atualizado com sucesso!');
            header('Location: ' . ROOT_URL . 'pages/servidores/index.php');
            exit;
        } else {
            setMessage('Erro ao atualizar servidor', 'danger');
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Editar Servidor</h1>
        <a href="<?php echo ROOT_URL; ?>pages/servidores/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações do Servidor</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $id; ?>" method="post">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nome" name="nome" maxlength="50" value="<?php echo $servidorInfo['NOME']; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="matricula" class="form-label">Matrícula <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="matricula" name="matricula" maxlength="20" value="<?php echo $servidorInfo['MATRICULA']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="id_setor" class="form-label">Setor</label>
                                <select class="form-select" id="id_setor" name="id_setor">
                                    <option value="">Selecione</option>
                                    <?php foreach ($setores as $s): ?>
                                    <option value="<?php echo $s['ID']; ?>" <?php echo ($s['ID'] == $servidorInfo['ID_SETOR']) ? 'selected' : ''; ?>>
                                        <?php echo $s['NOME']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" maxlength="50" value="<?php echo $servidorInfo['EMAIL']; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="A" <?php echo ($servidorInfo['STATUS'] == 'A') ? 'selected' : ''; ?>>Ativo</option>
                                <option value="I" <?php echo ($servidorInfo['STATUS'] == 'I') ? 'selected' : ''; ?>>Inativo</option>
                            </select>
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