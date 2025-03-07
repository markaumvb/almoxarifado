<?php
require_once '../config/config.php';

// Verificar se o usuário está logado
if(!isLoggedIn()) {
    redirect('../login.php');
}

// Instanciar Item para obter contagem
$item = new Item();
$items = $item->getItems();
$itemCount = count($items);

// Instanciar Saida para obter movimentações recentes
$saida = new Saida();
$today = date('Y-m-d');
$sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
$recentSaidas = $saida->getSaidasByPeriodo($sevenDaysAgo, $today);
$recentSaidasCount = count($recentSaidas);

// Incluir cabeçalho
include_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Dashboard</h2>
            Bem-vindo, <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Usuário'; ?>!
        </div>
    </div>

    <div class="row mt-4">
        <!-- Card de itens -->
        <div class="col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total de Itens Cadastrados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $itemCount; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="../pages/itens/index.php" class="btn btn-sm btn-primary">Ver Itens</a>
                </div>
            </div>
        </div>

        <!-- Card de saídas recentes -->
        <div class="col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Saídas (Últimos 7 dias)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $recentSaidasCount; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="../pages/relatorios/saidas_recentes.php" class="btn btn-sm btn-success">Ver Detalhes</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimas saídas -->
    <?php if(count($recentSaidas) > 0): ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Saídas Recentes</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Item</th>
                                    <th>Quantidade</th>
                                    <th>Servidor</th>
                                    <th>Setor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recentSaidas as $saida): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($saida['DATA'])); ?></td>
                                    <td><?php echo $saida['item_nome']; ?></td>
                                    <td><?php echo $saida['QTDE']; ?></td>
                                    <td><?php echo $saida['servidor_nome']; ?></td>
                                    <td><?php echo $saida['setor_nome']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Incluir rodapé
include_once '../includes/footer.php';
?>