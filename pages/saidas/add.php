<?php
// pages/saidas/add.php

// Incluir o cabeçalho
include_once '../../includes/header.php';

// Incluir as classes necessárias
require_once ROOT_PATH . 'classes/Item.php';
require_once ROOT_PATH . 'classes/Servidor.php';
require_once ROOT_PATH . 'classes/Saida.php';

// Inicializar as classes
$item = new Item();
$servidor = new Servidor();
$saida = new Saida();

// Obter servidores ativos
$servidores = $servidor->getServidoresAtivos();

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar e limpar dados
    $data_saida = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING);
    $id_servidor = filter_input(INPUT_POST, 'id_servidor', FILTER_SANITIZE_NUMBER_INT);
    $itens = isset($_POST['itens']) ? $_POST['itens'] : [];
    
    // Verificar dados
    if (empty($data_saida) || empty($id_servidor) || empty($itens)) {
        setMessage('Por favor, preencha todos os campos obrigatórios.', 'danger');
    } else {
        // Preparar dados para salvar
        $saidaData = [
            'data' => $data_saida,
            'id_servidor' => $id_servidor,
            'itens' => []
        ];
        
        // Organizar dados dos itens
        foreach ($itens as $id => $itemData) {
            // Obter o código do item
            $itemInfo = $item->getItemById($itemData['id']);
            
            $saidaData['itens'][] = [
                'codigo' => $itemInfo['CODIGO'],
                'quantidade' => $itemData['quantidade'],
                'observacao' => $itemData['observacao']
            ];
        }
        
        // Salvar a saída
        $result = $saida->addMultiple($saidaData);
        
        if ($result['success']) {
            setMessage('Saída registrada com sucesso!');
            header('Location: ' . ROOT_URL . 'pages/saidas/index.php');
            exit;
        } else {
            setMessage('Erro ao registrar saída: ' . $result['message'], 'danger');
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Nova Saída de Almoxarifado</h1>
        <a href="<?php echo ROOT_URL; ?>pages/saidas/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações da Saída</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="form-saida">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="data" class="form-label">Data <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="data" name="data" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="id_servidor" class="form-label">Servidor <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_servidor" name="id_servidor" required>
                                    <option value="">Selecione um servidor</option>
                                    <?php foreach ($servidores as $s): ?>
                                    <option value="<?php echo $s['ID']; ?>"><?php echo $s['NOME']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Adicionar Item</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="search_item" class="form-label">Buscar Item</label>
                            <input type="text" class="form-control" id="search_item" placeholder="Digite o código ou nome do item">
                            
                            <div id="items-results-container" class="mt-2 d-none">
                                <ul class="list-group" id="items-results">
                                    <!-- Resultados da pesquisa serão inseridos aqui via JavaScript -->
                                </ul>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="item_codigo" class="form-label">Código</label>
                                    <input type="text" class="form-control" id="item_codigo" readonly>
                                    <input type="hidden" id="item_id">
                                </div>
                                <div class="col-md-8">
                                    <label for="item_nome" class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="item_nome" readonly>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <label for="quantidade" class="form-label">Quantidade</label>
                                    <input type="number" class="form-control" id="quantidade" min="0.01" step="0.01">
                                </div>
                                <div class="col-md-8">
                                    <label for="observacao" class="form-label">Observação</label>
                                    <input type="text" class="form-control" id="observacao">
                                </div>
                            </div>
                            
                            <div class="mt-3 text-end">
                                <button type="button" class="btn btn-success btn-add-item">
                                    <i class="fas fa-plus me-2"></i>Adicionar Item
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Itens da Saída</h5>
                    <span class="badge bg-primary items-count">0</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="items-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nome</th>
                                    <th>Quantidade</th>
                                    <th>Observação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Itens adicionados serão inseridos aqui via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3 text-center">
                        <button type="button" id="btn-save" class="btn btn-primary btn-lg" style="display: none;">
                            <i class="fas fa-save me-2"></i>Salvar Saída
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar item -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_item_id">
                <div class="mb-3">
                    <label for="edit_quantidade" class="form-label">Quantidade</label>
                    <input type="number" class="form-control" id="edit_quantidade" min="0.01" step="0.01">
                </div>
                <div class="mb-3">
                    <label for="edit_observacao" class="form-label">Observação</label>
                    <input type="text" class="form-control" id="edit_observacao">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-confirm-edit">Salvar</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Exibir resultados da busca de itens
        $('#search_item').keyup(function() {
            var search = $(this).val();
            
            if (search.length < 2) {
                $('#items-results-container').addClass('d-none');
                return;
            }
            
            $.ajax({
                url: '../../api/itens.php',
                type: 'GET',
                data: { search: search },
                success: function(response) {
                    try {
                        var data = JSON.parse(response);
                        var html = '';
                        
                        if (data.length > 0) {
                            data.forEach(function(item) {
                                html += '<li class="list-group-item item-result" data-id="' + item.ID + '" data-codigo="' + item.CODIGO + '" data-nome="' + item.NOME + '">';
                                html += '<strong>' + item.CODIGO + '</strong> - ' + item.NOME;
                                html += '</li>';
                            });
                            
                            $('#items-results').html(html);
                            $('#items-results-container').removeClass('d-none');
                        } else {
                            $('#items-results').html('<li class="list-group-item">Nenhum item encontrado</li>');
                            $('#items-results-container').removeClass('d-none');
                        }
                    } catch (e) {
                        console.error('Erro ao processar resposta:', e);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição:', error);
                }
            });
        });
        
        // Selecionar item da lista de resultados
        $(document).on('click', '.item-result', function() {
            var id = $(this).data('id');
            var codigo = $(this).data('codigo');
            var nome = $(this).data('nome');
            
            $('#item_id').val(id);
            $('#item_codigo').val(codigo);
            $('#item_nome').val(nome);
            
            $('#items-results-container').addClass('d-none');
            $('#quantidade').focus();
        });
        
        // Adicionar item à lista
        $('.btn-add-item').click(function() {
            var itemId = $('#item_id').val();
            var itemCodigo = $('#item_codigo').val();
            var itemNome = $('#item_nome').val();
            var quantidade = $('#quantidade').val();
            var observacao = $('#observacao').val();
            
            if (!itemId || !quantidade) {
                alert('Por favor, selecione um item e informe a quantidade');
                return;
            }
            
            // Verificar se o item já existe na lista
            var exists = false;
            $('.temp-item').each(function() {
                if ($(this).data('id') == itemId) {
                    exists = true;
                    return false;
                }
            });
            
            if (exists) {
                alert('Este item já está na lista');
                return;
            }
            
            // Adicionar o item à lista
            var html = '<tr class="temp-item" data-id="' + itemId + '">';
            html += '<td>' + itemCodigo + '</td>';
            html += '<td>' + itemNome + '</td>';
            html += '<td>' + quantidade + '</td>';
            html += '<td>' + observacao + '</td>';
            html += '<td>';
            html += '<button type="button" class="btn btn-sm btn-warning btn-edit-item me-1" data-id="' + itemId + '"><i class="fas fa-edit"></i></button>';
            html += '<button type="button" class="btn btn-sm btn-danger btn-remove-item" data-id="' + itemId + '"><i class="fas fa-trash"></i></button>';
            html += '</td>';
            html += '<input type="hidden" name="itens[' + itemId + '][id]" value="' + itemId + '">';
            html += '<input type="hidden" name="itens[' + itemId + '][quantidade]" value="' + quantidade + '">';
            html += '<input type="hidden" name="itens[' + itemId + '][observacao]" value="' + observacao + '">';
            html += '</tr>';
            
            $('#items-table tbody').append(html);
            
            // Limpar campos
            $('#item_id').val('');
            $('#item_codigo').val('');
            $('#item_nome').val('');
            $('#quantidade').val('');
            $('#observacao').val('');
            $('#search_item').val('').focus();
            
            // Atualizar contador
            updateItemCount();
        });
        
        // Remover item da lista
        $(document).on('click', '.btn-remove-item', function() {
            $(this).closest('tr').remove();
            updateItemCount();
        });
        
        // Editar item
        $(document).on('click', '.btn-edit-item', function() {
            var id = $(this).data('id');
            var tr = $(this).closest('tr');
            var quantidade = tr.find('input[name="itens[' + id + '][quantidade]"]').val();
            var observacao = tr.find('input[name="itens[' + id + '][observacao]"]').val();
            
            $('#edit_item_id').val(id);
            $('#edit_quantidade').val(quantidade);
            $('#edit_observacao').val(observacao);
            
            $('#editItemModal').modal('show');
        });
        
        // Confirmar edição
        $('#btn-confirm-edit').click(function() {
            var id = $('#edit_item_id').val();
            var quantidade = $('#edit_quantidade').val();
            var observacao = $('#edit_observacao').val();
            
            if (!quantidade) {
                alert('Por favor, informe a quantidade');
                return;
            }
            
            var tr = $('tr.temp-item[data-id="' + id + '"]');
            tr.find('td:nth-child(3)').text(quantidade);
            tr.find('td:nth-child(4)').text(observacao);
            tr.find('input[name="itens[' + id + '][quantidade]"]').val(quantidade);
            tr.find('input[name="itens[' + id + '][observacao]"]').val(observacao);
            
            $('#editItemModal').modal('hide');
        });
        
        // Salvar saída
        $('#btn-save').click(function() {
            if ($('#id_servidor').val() === '') {
                alert('Por favor, selecione um servidor');
                return;
            }
            
            $('#form-saida').submit();
        });
        
        // Função para atualizar contagem de itens
        function updateItemCount() {
            var count = $('.temp-item').length;
            $('.items-count').text(count);
            
            if (count > 0) {
                $('#btn-save').show();
            } else {
                $('#btn-save').hide();
            }
        }
    });
</script>

<?php
// Incluir o rodapé
include_once '../../includes/footer.php';
?>