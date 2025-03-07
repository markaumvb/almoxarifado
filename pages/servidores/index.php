<?php
// pages/servidores/index.php

// Incluir o cabeçalho
include_once '../../includes/header.php';

// Incluir as classes necessárias
require_once ROOT_PATH . 'classes/Servidor.php';
require_once ROOT_PATH . 'classes/Setor.php';

// Inicializar as classes
$servidor = new Servidor();
$servidores = $servidor->getServidores();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Gerenciamento de Servidores</h1>
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Novo Servidor
        </a>
    </div>
        
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Matrícula</th>
                            <th>Setor</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servidores as $s): ?>
                        <tr>
                            <td><?php echo $s['NOME']; ?></td>
                            <td><?php echo $s['MATRICULA']; ?></td>
                            <td><?php echo $s['SETOR_NOME']; ?></td>
                            <td><?php echo $s['EMAIL']; ?></td>
                            <td><?php echo isset($s['TELEFONE']) ? $s['TELEFONE'] : ''; ?></td>
                            <td>
                                <?php if ($s['STATUS'] == 'A'): ?>
                                <span class="badge bg-success">Ativo</span>
                                <?php else: ?>
                                <span class="badge bg-danger">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $s['ID']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $s['ID']; ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este servidor?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir o rodapé
include_once '../../includes/footer.php';
?>