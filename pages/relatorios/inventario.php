<?php
require_once '../../config/config.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    redirect('../../login.php');
}

// Inicializar classe
$item = new Item();

// Obter todos os itens
$items = $item->getItems();

// Incluir cabeçalho
include_once '../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Relatório de Inventário Atual</h6>
                    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-print me-2"></i> Imprimir
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="dataTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Item</th>
                                    <th>Tipo</th>
                                    <th class="text-end">Saldo Atual</th>
                                    <th>Unidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Nenhum item cadastrado.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?php echo $item['CODIGO']; ?></td>
                                        <td><?php echo $item['NOME']; ?></td>
                                        <td>
                                            <?php 
                                            switch($item['TIPO']) {
                                                case 1:
                                                    echo 'Consumo';
                                                    break;
                                                case 2:
                                                    echo 'Equipamento';
                                                    break;
                                                case 3:
                                                    echo 'Empenho';
                                                    break;
                                                default:
                                                    echo 'Desconhecido';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-end"><?php echo number_format($item['SALDO'], 2, ',', '.'); ?></td>
                                        <td><?php echo $item['unidade_nome']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar DataTables para paginação e pesquisa
    $('#dataTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
        },
        "pageLength": 25
    });
});
</script>

<?php
// Incluir rodapé
include_once '../../includes/footer.php';
?>