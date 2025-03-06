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
                    <h6 class="m-0 font-weight-bold text-primary">Inventário Atual</h6>
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
                                    <th class="text-end">Saldo Mínimo</th>
                                    <th class="text-end">Saldo Máximo</th>
                                    <th>Unidade</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Nenhum item cadastrado.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?php echo $item['CODIGO']; ?></td>
                                        <td><?php echo $item['NOME']; ?></td>
                                        <td>
                                            <?php 
                                            switch($item['TIPO']) {
                                                case 'P':
                                                    echo 'Permanente';
                                                    break;
                                                case 'C':
                                                    echo 'Consumo';
                                                    break;
                                                default:
                                                    echo $item['TIPO'];
                                            }
                                            ?>
                                        </td>
                                        <td class="text-end"><?php echo number_format($item['SALDO'], 2, ',', '.'); ?></td>
                                        <td class="text-end"><?php echo number_format($item['SALDO_MINIMO'], 2, ',', '.'); ?></td>
                                        <td class="text-end"><?php echo number_format($item['SALDO_MAXIMO'], 2, ',', '.'); ?></td>
                                        <td><?php echo $item['unidade_nome']; ?></td>
                                        <td>
                                            <?php if($item['SALDO'] <= 0): ?>
                                            <span class="badge bg-danger">Sem Estoque</span>
                                            <?php elseif($item['SALDO'] <= $item['SALDO_MINIMO']): ?>
                                            <span class="badge bg-warning">Baixo</span>
                                            <?php elseif($item['SALDO'] >= $item['SALDO_MAXIMO']): ?>
                                            <span class="badge bg-info">Excesso</span>
                                            <?php else: ?>
                                            <span class="badge bg-success">Normal</span>
                                            <?php endif; ?>
                                        </td>
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