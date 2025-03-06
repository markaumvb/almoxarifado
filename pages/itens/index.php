<?php
// pages/itens/index.php

// Incluir o cabeçalho (caminho relativo)
include_once '../../includes/header.php';

// Incluir as classes necessárias (caminhos relativos)
require_once '../../classes/Item.php';
require_once '../../classes/Unidade.php';

// Inicializar as classes
$item = new Item();
$itens = $item->getItems();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Gerenciamento de Itens</h1>
        <a href="add.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Novo Item
    </a>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Saldo</th>
                            <th>Saldo Mínimo</th>
                            <th>Unidade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens as $i): ?>
                        <tr>
                            <td><?php echo $i['CODIGO']; ?></td>
                            <td><?php echo $i['NOME']; ?></td>
                            <td>
                                <?php 
                                switch($i['TIPO']) {
                                    case 'P':
                                        echo 'Permanente';
                                        break;
                                    case 'C':
                                        echo 'Consumo';
                                        break;
                                    default:
                                        echo $i['TIPO'];
                                }
                                ?>
                            </td>
                            <td class="<?php echo ($i['SALDO'] < $i['SALDO_MINIMO']) ? 'text-danger' : ''; ?>">
                                <?php echo number_format($i['SALDO'], 2, ',', '.'); ?></td>
                            <td><?php echo number_format($i['SALDO_MINIMO'], 2, ',', '.'); ?></td>
                            <td><?php echo isset($i['unidade_nome']) ? $i['unidade_nome'] : ''; ?></td>
                            <td>
                                <a href="<?php echo URL_ROOT; ?>/pages/itens/edit.php?id=<?php echo $i['ID']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $i['ID']; ?>" data-bs-toggle="tooltip" title="Excluir">
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
<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este item?</p>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Usando addEventListener para maior compatibilidade
        var deleteButtons = document.querySelectorAll('.btn-delete');
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                var id = this.getAttribute('data-id');
                var confirmButton = document.getElementById('btn-confirm-delete');
                confirmButton.setAttribute('href', 'delete.php?id=' + id);
                
                // Usando Bootstrap 5 modal
                var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            });
        });
    });
</script>

<?php
// Incluir o rodapé
include_once '../../includes/footer.php';
?>