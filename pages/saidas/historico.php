<?php
require_once '../../config/config.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    redirect('../../login.php');
}

// Inicializar classes
$saida = new Saida();
$servidor = new Servidor();

// Inicializar variáveis
$dataInicio = date('Y-m-d', strtotime('-30 days'));
$dataFim = date('Y-m-d');
$id_servidor = '';

// Processar filtros
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dataInicio = $_POST['data_inicio'] ?? $dataInicio;
    $dataFim = $_POST['data_fim'] ?? $dataFim;
    $id_servidor = $_POST['id_servidor'] ?? '';
}

// Buscar servidores para o filtro
$servidores = $servidor->getServidores();

// Buscar dados das saídas
$dados = $saida->getSaidasByPeriodo($dataInicio, $dataFim);

// Filtrar por servidor se necessário
if (!empty($id_servidor)) {
    $dados = array_filter($dados, function($item) use ($id_servidor) {
        return $item['ID_SERVIDOR'] == $id_servidor;
    });
}

// Incluir cabeçalho
include_once '../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Histórico de Saídas</h6>
                </div>
                <div class="card-body">
                    <!-- Formulário de filtros -->
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="mb-4">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="data_inicio" class="form-label">Data Início</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo $dataInicio; ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="data_fim" class="form-label">Data Fim</label>
                                <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo $dataFim; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="id_servidor" class="form-label">Servidor</label>
                                <select class="form-select" id="id_servidor" name="id_servidor">
                                    <option value="">Todos os servidores</option>
                                    <?php foreach($servidores as $srv): ?>
                                        <option value="<?php echo $srv['ID']; ?>" <?php echo $id_servidor == $srv['ID'] ? 'selected' : ''; ?>>
                                            <?php echo $srv['NOME']; ?> (<?php echo $srv['MATRICULA']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-2"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Resultados do histórico -->
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
                                <?php if (empty($dados)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Nenhum registro encontrado para o período selecionado.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($dados as $saida): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($saida['DATA'])); ?></td>
                                        <td><?php echo $saida['CODIGO']; ?></td>
                                        <td><?php echo $saida['item_nome']; ?></td>
                                        <td class="text-end"><?php echo number_format($saida['QTDE'], 2, ',', '.'); ?></td>
                                        <td><?php echo $saida['servidor_nome']; ?></td>
                                        <td><?php echo $saida['setor_nome']; ?></td>
                                        <td><?php echo $saida['OBS']; ?></td>
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
        "order": [[1, 'desc'], [0, 'desc']], // Ordenar por data (decrescente) e depois por ID
        "pageLength": 25
    });
});
</script>

<?php
// Incluir rodapé
include_once '../../includes/footer.php';
?>