<?php
// pages/unidades/index.php

// Incluir o cabeçalho
include_once '../../includes/header.php';

// Incluir as classes necessárias
require_once ROOT_PATH . 'classes/Unidade.php';

// Inicializar as classes
$unidade = new Unidade();
$unidades = $unidade->getUnidades();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Gerenciamento de Unidades de Medida</h1>
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nova Unidade
        </a>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <?php displayMessage(); ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Sigla</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unidades as $u): ?>
                        <tr>
                            <td><?php echo $u['NOME']; ?></td>
                            <td><?php echo $u['SIGLA']; ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $u['ID']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <!-- Botão de exclusão simplificado -->
                                <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $u['ID']; ?>" data-bs-toggle="tooltip" title="Excluir">
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

<script>
$(document).ready(function() {
    // Botão de exclusão
    $('.btn-delete').click(function(e) {
        e.preventDefault();
        
        var id = $(this).data('id');
        var confirmDelete = confirm("Tem certeza que deseja excluir esta unidade?");
        
        if(confirmDelete) {
            // Redirecionar para a página de exclusão
            window.location.href = 'delete.php?id=' + id;
        }
    });
    
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php
// Incluir o rodapé
include_once '../../includes/footer.php';
?>