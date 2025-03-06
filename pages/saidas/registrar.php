<?php
require_once '../../config/config.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    redirect('../../login.php');
}

// Inicializar classes
$item = new Item();
$servidor = new Servidor();
$saida = new Saida();

// Obter todos os servidores para o select
$servidores = $servidor->getServidores();

// Inicializar variáveis da sessão para itens temporários
if (!isset($_SESSION['temp_saida_items'])) {
    $_SESSION['temp_saida_items'] = [];
}

// Adicionar item à lista temporária
if (isset($_POST['add_item'])) {
    $codigo = trim($_POST['codigo']);
    $qtde = intval($_POST['qtde']);
    
    // Validar dados
    if (empty($codigo) || $qtde <= 0) {
        setFlashMessage('error', 'Código e quantidade são obrigatórios');
    } else {
        // Verificar se o item existe
        $itemData = $item->getItemByCodigo($codigo);
        
        if (!$itemData) {
            setFlashMessage('error', 'Item não encontrado');
        } else {
            // Adicionar à lista temporária sem verificar saldo
            $temp_item = [
                'id' => uniqid(), // ID temporário para identificar o item na sessão
                'codigo' => $codigo,
                'nome' => $itemData['NOME'],
                'qtde' => $qtde,
                'unidade' => $itemData['ID_UNIDADE']
            ];
            
            $_SESSION['temp_saida_items'][] = $temp_item;
            setFlashMessage('success', 'Item adicionado à lista');
        }
    }
    
    // Redirecionar para evitar reenvio do formulário
    header('Location: registrar.php');
    exit;
}

// Remover item da lista temporária
if (isset($_GET['remove']) && !empty($_GET['remove'])) {
    $temp_id = $_GET['remove'];
    
    foreach ($_SESSION['temp_saida_items'] as $key => $item_data) {
        if ($item_data['id'] == $temp_id) {
            unset($_SESSION['temp_saida_items'][$key]);
            $_SESSION['temp_saida_items'] = array_values($_SESSION['temp_saida_items']); // Reindexar o array
            setFlashMessage('success', 'Item removido da lista');
            break;
        }
    }
    
    // Redirecionar para evitar reenvio
    header('Location: registrar.php');
    exit;
}

// Editar quantidade de item na lista temporária
if (isset($_POST['edit_item'])) {
    $temp_id = $_POST['temp_id'];
    $nova_qtde = intval($_POST['nova_qtde']);
    
    if ($nova_qtde <= 0) {
        setFlashMessage('error', 'A quantidade deve ser maior que zero');
    } else {
        $edited = false;
        
        foreach ($_SESSION['temp_saida_items'] as $key => $item_data) {
            if ($item_data['id'] == $temp_id) {
                // Atualizar quantidade sem verificar estoque
                $_SESSION['temp_saida_items'][$key]['qtde'] = $nova_qtde;
                setFlashMessage('success', 'Quantidade atualizada');
                $edited = true;
                break;
            }
        }
        
        if (!$edited) {
            setFlashMessage('error', 'Item não encontrado na lista');
        }
    }
    
    // Redirecionar para evitar reenvio
    header('Location: registrar.php');
    exit;
}

// Finalizar saída (salvar todos os itens)
if (isset($_POST['finalizar_saida'])) {
    $id_servidor = intval($_POST['id_servidor']);
    $obs = trim($_POST['obs']);
    $data = date('Y-m-d'); // Data atual
    
    if (empty($id_servidor)) {
        setFlashMessage('error', 'Selecione um servidor');
    } else if (empty($_SESSION['temp_saida_items'])) {
        setFlashMessage('error', 'Adicione pelo menos um item à lista');
    } else {
        $success = true;
        
        // Iniciar transação
        $db = new Database();
        $db->beginTransaction();
        
        try {
            // Registrar cada item
            foreach ($_SESSION['temp_saida_items'] as $item_data) {
                $saida_data = [
                    'codigo' => $item_data['codigo'],
                    'qtde' => $item_data['qtde'],
                    'id_servidor' => $id_servidor,
                    'data' => $data,
                    'obs' => $obs
                ];
                
                if (!$saida->add($saida_data)) {
                    $success = false;
                    break;
                }
            }
            
            if ($success) {
                $db->commit();
                // Limpar itens temporários
                $_SESSION['temp_saida_items'] = [];
                setFlashMessage('success', 'Saída registrada com sucesso');
            } else {
                $db->rollBack();
                setFlashMessage('error', 'Erro ao registrar saída');
            }
        } catch (Exception $e) {
            $db->rollBack();
            setFlashMessage('error', 'Erro ao processar saída: ' . $e->getMessage());
        }
    }
    
    // Redirecionar para evitar reenvio
    header('Location: registrar.php');
    exit;
}

// Flash message
$flash = getFlashMessage();

// Incluir cabeçalho
include_once '../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Registrar Saída de Itens</h6>
                </div>
                <div class="card-body">
                    <?php if(isset($flash)): ?>
                        <div class="alert alert-<?php echo ($flash['type'] == 'error') ? 'danger' : $flash['type']; ?> alert-dismissible fade show">
                            <?php echo $flash['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Formulário para adicionar item -->
                    <form action="registrar.php" method="post">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="codigo" class="form-label">Código do Item</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="codigo" name="codigo" required>
                                    <button type="button" class="btn btn-outline-secondary" id="btn-buscar-codigo">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="btn-abrir-pesquisa" data-bs-toggle="modal" data-bs-target="#modalBuscaItem">
                                        <i class="fas fa-list"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Nome do Item</label>
                                <div class="form-control bg-light" id="nome_item_display">Nenhum item selecionado</div>
                            </div>
                            <div class="col-md-2">
                                <label for="qtde" class="form-label">Quantidade</label>
                                <input type="number" class="form-control" id="qtde" name="qtde" min="1" step="1" value="1" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" name="add_item" class="btn btn-primary w-100">
                                    <i class="fas fa-plus-circle me-2"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Formulário para finalizar saída -->
                    <form action="registrar.php" method="post" class="mt-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="id_servidor" class="form-label">Servidor Responsável</label>
                                <select class="form-select" id="id_servidor" name="id_servidor" required>
                                    <option value="">Selecione um servidor</option>
                                    <?php foreach($servidores as $srv): ?>
                                        <option value="<?php echo $srv['ID']; ?>"><?php echo $srv['NOME']; ?> (<?php echo $srv['MATRICULA']; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="obs" class="form-label">Observações</label>
                                <input type="text" class="form-control" id="obs" name="obs">
                            </div>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3 mb-4">
                            <a href="../dashboard.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" name="finalizar_saida" class="btn btn-success" <?php echo empty($_SESSION['temp_saida_items']) ? 'disabled' : ''; ?>>
                                <i class="fas fa-save me-2"></i> Finalizar Saída
                            </button>
                        </div>
                    </form>
                    
                    <!-- Lista de itens temporários - Movida para o final -->
                    <div class="table-responsive mt-4">
                        <h6 class="mb-3 font-weight-bold">Itens a Serem Retirados</h6>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Item</th>
                                    <th>Quantidade</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($_SESSION['temp_saida_items'])): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Nenhum item adicionado</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($_SESSION['temp_saida_items'] as $item_data): ?>
                                        <tr>
                                            <td><?php echo $item_data['codigo']; ?></td>
                                            <td><?php echo $item_data['nome']; ?></td>
                                            <td class="text-center"><?php echo $item_data['qtde']; ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-warning edit-item" 
                                                        data-id="<?php echo $item_data['id']; ?>" 
                                                        data-qtde="<?php echo $item_data['qtde']; ?>"
                                                        data-bs-toggle="modal" data-bs-target="#editItemModal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="?remove=<?php echo $item_data['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja remover este item?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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

<!-- Modal para editar quantidade -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editItemModalLabel">Editar Quantidade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="registrar.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="temp_id" id="edit_temp_id">
                    <div class="mb-3">
                        <label for="nova_qtde" class="form-label">Nova Quantidade</label>
                        <input type="number" class="form-control" id="nova_qtde" name="nova_qtde" step="1" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="edit_item" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para pesquisa de itens -->
<div class="modal fade" id="modalBuscaItem" tabindex="-1" aria-labelledby="modalBuscaItemLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalBuscaItemLabel">Pesquisa de Itens</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="search_term" class="form-label">Digite o nome do item</label>
                    <input type="text" class="form-control" id="search_term" placeholder="Digite para pesquisar...">
                </div>
                
                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-hover" id="search_results_table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Saldo</th>
                                <th>Unidade</th>
                            </tr>
                        </thead>
                        <tbody id="search_results">
                            <tr>
                                <td colspan="5" class="text-center">Digite algo para pesquisar</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-selecionar-item" disabled>Selecionar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos do formulário
    const codigoInput = document.getElementById('codigo');
    const nomeItemDisplay = document.getElementById('nome_item_display');
    const btnBuscarCodigo = document.getElementById('btn-buscar-codigo');
    const searchTermInput = document.getElementById('search_term');
    const searchResults = document.getElementById('search_results');
    const btnSelecionarItem = document.getElementById('btn-selecionar-item');
    
    // Variável para armazenar o item selecionado na pesquisa
    let selectedItemFromSearch = null;
    
    // Função para buscar item por código
    function buscarItemPorCodigo() {
        const codigo = codigoInput.value.trim();
        
        if (codigo) {
            // Mostrar "Buscando..." enquanto procura
            nomeItemDisplay.textContent = "Buscando...";
            nomeItemDisplay.style.color = '#666';
            
            // Fazer requisição AJAX para buscar o item pelo código
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '../../api/itens.php?codigo=' + encodeURIComponent(codigo), true);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response && response.length > 0) {
                            // Item encontrado, exibir o nome
                            nomeItemDisplay.textContent = response[0].NOME;
                            nomeItemDisplay.style.color = 'black';
                        } else {
                            // Item não encontrado
                            nomeItemDisplay.textContent = 'Item não encontrado';
                            nomeItemDisplay.style.color = 'red';
                        }
                    } catch (error) {
                        console.error('Erro ao processar resposta:', error);
                        nomeItemDisplay.textContent = 'Erro ao processar dados';
                        nomeItemDisplay.style.color = 'red';
                    }
                } else {
                    nomeItemDisplay.textContent = 'Erro na requisição';
                    nomeItemDisplay.style.color = 'red';
                }
            };
            
            xhr.onerror = function() {
                nomeItemDisplay.textContent = 'Erro de conexão';
                nomeItemDisplay.style.color = 'red';
            };
            
            xhr.send();
        } else {
            nomeItemDisplay.textContent = 'Nenhum item selecionado';
            nomeItemDisplay.style.color = 'grey';
        }
    }
    
    // Função para pesquisar itens por nome
    function pesquisarItensPorNome() {
        const searchTerm = searchTermInput.value.trim();
        
        if (searchTerm.length < 2) {
            searchResults.innerHTML = '<tr><td colspan="5" class="text-center">Digite no mínimo 2 caracteres para pesquisar</td></tr>';
            return;
        }
        
        // Fazer requisição AJAX para buscar itens pelo nome
        const xhr = new XMLHttpRequest();
        xhr.open('GET', '../../api/itens.php?search=' + encodeURIComponent(searchTerm), true);
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response && response.length > 0) {
                        // Limitar a 15 resultados
                        const itens = response.slice(0, 15);
                        let html = '';
                        
                        itens.forEach(function(item) {
                            const tipoText = item.TIPO === 'P' ? 'Permanente' : (item.TIPO === 'C' ? 'Consumo' : item.TIPO);
                            html += `
                                <tr class="search-result-row" data-codigo="${item.CODIGO}" data-nome="${item.NOME}">
                                    <td>${item.CODIGO}</td>
                                    <td>${item.NOME}</td>
                                    <td>${tipoText}</td>
                                    <td>${parseFloat(item.SALDO).toFixed(2)}</td>
                                    <td>${item.unidade_nome || ''}</td>
                                </tr>
                            `;
                        });
                        
                        searchResults.innerHTML = html;
                        
                        // Adicionar evento de clique nas linhas da tabela
                        const rows = document.querySelectorAll('.search-result-row');
                        rows.forEach(function(row) {
                            row.addEventListener('click', function() {
                                // Remover seleção de todas as linhas
                                rows.forEach(r => r.classList.remove('table-primary'));
                                
                                // Adicionar seleção à linha clicada
                                this.classList.add('table-primary');
                                
                                // Armazenar o item selecionado
                                selectedItemFromSearch = {
                                    codigo: this.getAttribute('data-codigo'),
                                    nome: this.getAttribute('data-nome')
                                };
                                
                                // Habilitar o botão de seleção
                                btnSelecionarItem.removeAttribute('disabled');
                            });
                        });
                    } else {
                        searchResults.innerHTML = '<tr><td colspan="5" class="text-center">Nenhum item encontrado</td></tr>';
                        btnSelecionarItem.setAttribute('disabled', 'disabled');
                    }
                } catch (error) {
                    console.error('Erro ao processar resposta:', error);
                    searchResults.innerHTML = '<tr><td colspan="5" class="text-center">Erro ao processar dados</td></tr>';
                }
            } else {
                searchResults.innerHTML = '<tr><td colspan="5" class="text-center">Erro na requisição</td></tr>';
            }
        };
        
        xhr.onerror = function() {
            searchResults.innerHTML = '<tr><td colspan="5" class="text-center">Erro de conexão</td></tr>';
        };
        
        xhr.send();
    }
    
    // Adicionar evento ao botão de busca por código
    if (btnBuscarCodigo) {
        btnBuscarCodigo.addEventListener('click', buscarItemPorCodigo);
    }
    
    // Adicionar evento ao pressionar Enter no campo de código
    if (codigoInput) {
        codigoInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Evitar submissão do formulário
                buscarItemPorCodigo();
            }
        });
    }
    
    // Adicionar evento ao campo de pesquisa
    if (searchTermInput) {
        // Debounce para evitar muitas requisições enquanto o usuário digita
        let debounceTimer;
        searchTermInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(pesquisarItensPorNome, 300);
        });
    }
    
    // Adicionar evento ao botão de selecionar item
    if (btnSelecionarItem) {
        btnSelecionarItem.addEventListener('click', function() {
            if (selectedItemFromSearch) {
                // Preencher o campo de código
                codigoInput.value = selectedItemFromSearch.codigo;
                
                // Exibir o nome do item
                nomeItemDisplay.textContent = selectedItemFromSearch.nome;
                nomeItemDisplay.style.color = 'black';
                
                // Fechar o modal
                const modalBuscaItem = bootstrap.Modal.getInstance(document.getElementById('modalBuscaItem'));
                modalBuscaItem.hide();
                
                // Focar no campo de quantidade
                document.getElementById('qtde').focus();
            }
        });
    }
    
    // Script para preencher o modal de edição
    const editButtons = document.querySelectorAll('.edit-item');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const qtde = this.getAttribute('data-qtde');
            
            document.getElementById('edit_temp_id').value = id;
            document.getElementById('nova_qtde').value = qtde;
        });
    });
    
    // Verificar código ao carregar a página
    if (codigoInput.value.trim()) {
        buscarItemPorCodigo();
    }
});
</script>

<?php
// Incluir rodapé
include_once '../../includes/footer.php';
?>