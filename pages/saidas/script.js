/**
 * script.js - Funções para a página de registro de saídas
 */

document.addEventListener("DOMContentLoaded", function () {
  // Elementos do formulário
  const codigoInput = document.getElementById("codigo");
  const nomeItemDisplay = document.getElementById("nome_item_display");
  const btnBuscarCodigo = document.getElementById("btn-buscar-codigo");
  const searchTermInput = document.getElementById("search_term");
  const searchResults = document.getElementById("search_results");
  const btnSelecionarItem = document.getElementById("btn-selecionar-item");

  // Variável para armazenar o item selecionado na pesquisa
  let selectedItemFromSearch = null;

  // Função para buscar item por código
  function buscarItemPorCodigo() {
    const codigo = codigoInput.value.trim();

    if (codigo) {
      // Mostrar "Buscando..." enquanto procura
      nomeItemDisplay.textContent = "Buscando...";
      nomeItemDisplay.style.color = "#666";

      // Fazer requisição AJAX para buscar o item pelo código
      const xhr = new XMLHttpRequest();
      xhr.open(
        "GET",
        "../../api/itens.php?codigo=" + encodeURIComponent(codigo),
        true
      );

      xhr.onload = function () {
        if (xhr.status === 200) {
          try {
            const response = JSON.parse(xhr.responseText);

            if (response && response.length > 0) {
              // Item encontrado, exibir o nome
              nomeItemDisplay.textContent = response[0].NOME;
              nomeItemDisplay.style.color = "black";
            } else {
              // Item não encontrado
              nomeItemDisplay.textContent = "Item não encontrado";
              nomeItemDisplay.style.color = "red";
            }
          } catch (error) {
            console.error("Erro ao processar resposta:", error);
            nomeItemDisplay.textContent = "Erro ao processar dados";
            nomeItemDisplay.style.color = "red";
          }
        } else {
          nomeItemDisplay.textContent = "Erro na requisição";
          nomeItemDisplay.style.color = "red";
        }
      };

      xhr.onerror = function () {
        nomeItemDisplay.textContent = "Erro de conexão";
        nomeItemDisplay.style.color = "red";
      };

      xhr.send();
    } else {
      nomeItemDisplay.textContent = "Nenhum item selecionado";
      nomeItemDisplay.style.color = "grey";
    }
  }

  // Função para pesquisar itens por nome
  function pesquisarItensPorNome() {
    const searchTerm = searchTermInput.value.trim();

    if (searchTerm.length < 2) {
      searchResults.innerHTML =
        '<tr><td colspan="5" class="text-center">Digite no mínimo 2 caracteres para pesquisar</td></tr>';
      return;
    }

    // Fazer requisição AJAX para buscar itens pelo nome
    const xhr = new XMLHttpRequest();
    xhr.open(
      "GET",
      "../../api/itens.php?search=" + encodeURIComponent(searchTerm),
      true
    );

    xhr.onload = function () {
      if (xhr.status === 200) {
        try {
          const response = JSON.parse(xhr.responseText);

          if (response && response.length > 0) {
            // Limitar a 15 resultados
            const itens = response.slice(0, 15);
            let html = "";

            itens.forEach(function (item) {
              const tipoText =
                item.TIPO === "P"
                  ? "Permanente"
                  : item.TIPO === "C"
                  ? "Consumo"
                  : item.TIPO;
              html += `
                              <tr class="search-result-row" data-codigo="${
                                item.CODIGO
                              }" data-nome="${item.NOME}">
                                  <td>${item.CODIGO}</td>
                                  <td>${item.NOME}</td>
                                  <td>${tipoText}</td>
                                  <td>${parseFloat(item.SALDO).toFixed(2)}</td>
                                  <td>${item.unidade_nome || ""}</td>
                              </tr>
                          `;
            });

            searchResults.innerHTML = html;

            // Adicionar evento de clique nas linhas da tabela
            const rows = document.querySelectorAll(".search-result-row");
            rows.forEach(function (row) {
              row.addEventListener("click", function () {
                // Remover seleção de todas as linhas
                rows.forEach((r) => r.classList.remove("table-primary"));

                // Adicionar seleção à linha clicada
                this.classList.add("table-primary");

                // Armazenar o item selecionado
                selectedItemFromSearch = {
                  codigo: this.getAttribute("data-codigo"),
                  nome: this.getAttribute("data-nome"),
                };

                // Habilitar o botão de seleção
                btnSelecionarItem.removeAttribute("disabled");
              });
            });
          } else {
            searchResults.innerHTML =
              '<tr><td colspan="5" class="text-center">Nenhum item encontrado</td></tr>';
            btnSelecionarItem.setAttribute("disabled", "disabled");
          }
        } catch (error) {
          console.error("Erro ao processar resposta:", error);
          searchResults.innerHTML =
            '<tr><td colspan="5" class="text-center">Erro ao processar dados</td></tr>';
        }
      } else {
        searchResults.innerHTML =
          '<tr><td colspan="5" class="text-center">Erro na requisição</td></tr>';
      }
    };

    xhr.onerror = function () {
      searchResults.innerHTML =
        '<tr><td colspan="5" class="text-center">Erro de conexão</td></tr>';
    };

    xhr.send();
  }

  // Verificar campo servidor antes de adicionar item
  function validarFormularioAdicao() {
    const idServidor = document.getElementById("id_servidor");
    if (idServidor && idServidor.value === "") {
      alert(
        "Por favor, selecione um Servidor Responsável antes de adicionar itens."
      );
      idServidor.focus();
      return false;
    }
    return true;
  }

  // Inicialização de eventos

  // Adicionar evento ao botão de busca por código
  if (btnBuscarCodigo) {
    btnBuscarCodigo.addEventListener("click", buscarItemPorCodigo);
  }

  // Adicionar evento ao pressionar Enter no campo de código
  if (codigoInput) {
    codigoInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        e.preventDefault(); // Evitar submissão do formulário
        buscarItemPorCodigo();
      }
    });
  }

  // Adicionar evento de validação ao formulário de adição
  const formAdicao = document.getElementById("form-adicao");
  if (formAdicao) {
    formAdicao.addEventListener("submit", function (e) {
      if (!validarFormularioAdicao()) {
        e.preventDefault();
      }
    });
  }

  // Adicionar evento ao campo de pesquisa
  if (searchTermInput) {
    // Debounce para evitar muitas requisições enquanto o usuário digita
    let debounceTimer;
    searchTermInput.addEventListener("input", function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(pesquisarItensPorNome, 300);
    });
  }

  // Adicionar evento ao botão de selecionar item
  if (btnSelecionarItem) {
    btnSelecionarItem.addEventListener("click", function () {
      if (selectedItemFromSearch) {
        // Preencher o campo de código
        codigoInput.value = selectedItemFromSearch.codigo;

        // Exibir o nome do item
        nomeItemDisplay.textContent = selectedItemFromSearch.nome;
        nomeItemDisplay.style.color = "black";

        // Fechar o modal
        const modalBuscaItem = bootstrap.Modal.getInstance(
          document.getElementById("modalBuscaItem")
        );
        modalBuscaItem.hide();

        // Focar no campo de quantidade
        document.getElementById("qtde").focus();
      }
    });
  }

  // Script para preencher o modal de edição
  const editButtons = document.querySelectorAll(".edit-item");

  editButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const id = this.getAttribute("data-id");
      const qtde = this.getAttribute("data-qtde");

      document.getElementById("edit_temp_id").value = id;
      document.getElementById("nova_qtde").value = qtde;
    });
  });

  // Verificar código ao carregar a página
  if (codigoInput && codigoInput.value.trim()) {
    buscarItemPorCodigo();
  }

  // Desabilitar botão finalizar se não houver itens
  const btnFinalizar = document.getElementById("btn-finalizar");
  if (btnFinalizar) {
    // Verificar se há itens na lista
    const temItens = document.querySelectorAll(".temp-item").length > 0;
    btnFinalizar.disabled = !temItens;
  }
});
