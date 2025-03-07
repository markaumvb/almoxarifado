<?php
require_once '../../config/config.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    redirect('../../login.php');
}

// Inicializar classes
$saida = new Saida();
$setor = new Setor();

// Inicializar variáveis
$dataInicio = date('Y-m-d', strtotime('-30 days'));
$dataFim = date('Y-m-d');
$agrupamento = 'item'; // Padrão: agrupar por item

// Processar filtros
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dataInicio = $_POST['data_inicio'] ?? $dataInicio;
    $dataFim = $_POST['data_fim'] ?? $dataFim;
    $agrupamento = $_POST['agrupamento'] ?? $agrupamento;
}

// Buscar dados conforme agrupamento
switch ($agrupamento) {
    case 'item':
        $dados = $saida->getSaidasAgrupadasPorItem($dataInicio, $dataFim);
        break;
    case 'setor':
        $dados = $saida->getSaidasAgrupadasPorSetor($dataInicio, $dataFim);
        break;
    case 'data':
        $dados = $saida->getSaidasAgrupadasPorData($dataInicio, $dataFim);
        break;
    default:
        $dados = $saida->getSaidasByPeriodo($dataInicio, $dataFim);
        break;
}

// Incluir cabeçalho
include_once '../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Relatório Geral de Saídas</h6>
                    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-print me-2"></i> Imprimir
                    </button>
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
                                <label for="agrupamento" class="form-label">Agrupar Por</label>
                                <select class="form-select" id="agrupamento" name="agrupamento">
                                    <option value="item" <?php echo $agrupamento == 'item' ? 'selected' : ''; ?>>Item</option>
                                    <option value="setor" <?php echo $agrupamento == 'setor' ? 'selected' : ''; ?>>Setor</option>
                                    <option value="data" <?php echo $agrupamento == 'data' ? 'selected' : ''; ?>>Data</option>
                                    <option value="detalhado" <?php echo $agrupamento == 'detalhado' ? 'selected' : ''; ?>>Detalhado</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-2"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Resultados do relatório -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <?php if ($agrupamento == 'item'): ?>
                                <tr>
                                    <th>Código</th>
                                    <th>Item</th>
                                    <th class="text-end">Quantidade Total</th>
                                </tr>
                                <?php elseif ($agrupamento == 'setor'): ?>
                                <tr>
                                    <th>ID</th>
                                    <th>Setor</th>
                                    <th class="text-end">Quantidade Total</th>
                                </tr>
                                <?php elseif ($agrupamento == 'data'): ?>
                                <tr>
                                    <th>Data</th>
                                    <th class="text-end">Total de Saídas</th>
                                    <th class="text-end">Quantidade Total</th>
                                </tr>
                                <?php else: // detalhado ?>
                                <tr>
                                    <th>Data</th>
                                    <th>Código</th>
                                    <th>Item</th>
                                    <th>Servidor</th>
                                    <th>Setor</th>
                                    <th class="text-end">Quantidade</th>
                                </tr>
                                <?php endif; ?>
                            </thead>
                            <tbody>
                                <?php if (empty($dados)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Nenhum registro encontrado para o período selecionado.</td>
                                </tr>
                                <?php else: ?>
                                    <?php if ($agrupamento == 'item'): ?>
                                        <?php foreach ($dados as $item): ?>
                                        <tr>
                                            <td><?php echo $item['CODIGO']; ?></td>
                                            <td><?php echo $item['item_nome']; ?></td>
                                            <td class="text-end"><?php echo number_format($item['total_qtde'], 2, ',', '.'); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php elseif ($agrupamento == 'setor'): ?>
                                        <?php foreach ($dados as $setor): ?>
                                        <tr>
                                            <td><?php echo $setor['ID']; ?></td>
                                            <td><?php echo $setor['setor_nome']; ?></td>
                                            <td class="text-end"><?php echo number_format($setor['total_qtde'], 2, ',', '.'); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php elseif ($agrupamento == 'data'): ?>
                                        <?php foreach ($dados as $data): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($data['data'])); ?></td>
                                            <td class="text-end"><?php echo $data['total_saidas']; ?></td>
                                            <td class="text-end"><?php echo number_format($data['total_qtde'], 2, ',', '.'); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: // detalhado ?>
                                        <?php foreach ($dados as $saida): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($saida['DATA'])); ?></td>
                                            <td><?php echo $saida['CODIGO']; ?></td>
                                            <td><?php echo $saida['item_nome']; ?></td>
                                            <td><?php echo $saida['servidor_nome']; ?></td>
                                            <td><?php echo $saida['setor_nome']; ?></td>
                                            <td class="text-end"><?php echo number_format($saida['QTDE'], 2, ',', '.'); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir rodapé
include_once '../../includes/footer.php';
?>