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
            <div class="table-responsive">
                <table class="table table-hover datatable">
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
                                <a href="<?php echo ROOT_URL; ?>pages/unidades/edit.php?id=<?php echo $u['ID']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
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

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir esta unidade de medida?</p>
                <p class="text-danger">Esta ação não poderá ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="btn-confirm-delete" class="btn btn-danger">Excluir</a>
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
            $('#btn-confirm-delete').attr('href', '<?php echo ROOT_URL; ?>pages/unidades/delete.php?id=' + id);
            $('#deleteModal').modal('show');
        });
    });
</script>

<?php
// Incluir o rodapé
include_once '../../includes/footer.php';
?>