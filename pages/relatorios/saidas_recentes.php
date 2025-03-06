<?php
// pages/relatorios/saidas_recentes.php
// Incluir cabeçalho
include_once '../../includes/header.php';

// Incluir classes necessárias
require_once '../../classes/Saida.php';
require_once '../../classes/Servidor.php';
require_once '../../classes/Setor.php';

// Inicializar objetos
$saida = new Saida();
$servidor = new Servidor();

// Definir período (últimos 7 dias)
$dataFim = date('Y-m-d');
$dataInicio = date('Y-m-d', strtotime('-7 days'));

// Obter saídas recentes
$saidas = $saida->getSaidasByPeriodo($dataInicio, $dataFim);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Saídas Recentes (Últimos 7 dias)</h6>
                    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-print me-2"></i> Imprimir
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="dataTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Data</th>
                                    <th>Código</th>
                                    <th>Item</th>
                                    <th>Quantidade</th>
                                    <th>Servidor</th>
                                    <th>Setor</th>
                                    <th>Observações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($saidas)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Nenhuma saída registrada nos últimos 7 dias.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($saidas as $s): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($s['DATA'])); ?></td>
                                        <td><?php echo $s['CODIGO']; ?></td>
                                        <td><?php echo isset($s['item_nome']) ? $s['item_nome'] : '-'; ?></td>
                                        <td class="text-end"><?php echo number_format($s['QTDE'], 2, ',', '.'); ?></td>
                                        <td><?php echo isset($s['servidor_nome']) ? $s['servidor_nome'] : '-'; ?></td>
                                        <td><?php echo isset($s['setor_nome']) ? $s['setor_nome'] : '-'; ?></td>
                                        <td><?php echo $s['OBS']; ?></td>
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
    // Inicializar DataTables para paginação e pesquisa (se tiver jQuery e DataTables disponíveis)
    if ($.fn.DataTable) {
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
            },
            "order": [[0, 'desc']], // Ordenar por data (decrescente)
            "pageLength": 25
        });
    }
});
</script>

<?php
// Incluir rodapé
include_once '../../includes/footer.php';
?>