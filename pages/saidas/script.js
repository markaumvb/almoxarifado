/**
 * script.js - Funções para a página de registro de saídas
 */

document.addEventListener("DOMContentLoaded", function () {
  // Elementos do formulário
  const codigoInput = document.getElementById("codigo");
  const nomeItemDisplay = document.getElementById("nome_item_display");
  const qtdeInput = document.getElementById("qtde");
  const btnBuscarCodigo = document.getElementById("btn-buscar-codigo");
  const searchTermInput = document.getElementById("search_term");
  const searchResults = document.getElementById("search_results");
  const btnSelecionarItem = document.getElementById("btn-selecionar-item");
  const formAdicao = document.getElementById("form-adicao");

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
      fetch(`../../api/itens.php?codigo=${encodeURIComponent(codigo)}`)
        .then((response) => {
          if (!response.ok) {
            throw new Error("Erro na requisição");
          }
          return response.json();
        })
        .then((data) => {
          if (data && data.length > 0) {
            // Item encontrado, exibir o nome
            nomeItemDisplay.textContent = data[0].NOME;
            nomeItemDisplay.style.color = "black";

            // Focar no campo de quantidade
            qtdeInput.focus();
            qtdeInput.select();
          } else {
            // Item não encontrado
            nomeItemDisplay.textContent = "Item não encontrado";
            nomeItemDisplay.style.color = "red";
          }
        })
        .catch((error) => {
          console.error("Erro:", error);
          nomeItemDisplay.textContent = "Erro ao buscar item";
          nomeItemDisplay.style.color = "red";
        });
    } else {
      nomeItemDisplay.textContent = "Nenhum item selecionado";
      nomeItemDisplay.style.color = "#666";
    }
  }

  // Função para pesquisar itens por nome
  function pesquisarItensPorNome() {
    const searchTerm = searchTermInput.value.trim();

    if (searchTerm.length < 2) {
      searchResults.innerHTML =
        '<tr><td colspan="5" class="text-center">Digite no mínimo 2 caracteres para pesquisar</td></tr>';
      btnSelecionarItem.disabled = true;
      return;
    }

    // Fazer requisição AJAX para buscar itens pelo nome
    fetch(`../../api/itens.php?search=${encodeURIComponent(searchTerm)}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error("Erro na requisição");
        }
        return response.json();
      })
      .then((data) => {
        if (data && data.length > 0) {
          // Limitar a 15 resultados
          const itens = data.slice(0, 15);
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
                <td class="text-end">${parseFloat(item.SALDO).toFixed(2)}</td>
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
              btnSelecionarItem.disabled = false;
            });
          });
        } else {
          searchResults.innerHTML =
            '<tr><td colspan="5" class="text-center">Nenhum item encontrado</td></tr>';
          btnSelecionarItem.disabled = true;
        }
      })
      .catch((error) => {
        console.error("Erro:", error);
        searchResults.innerHTML =
          '<tr><td colspan="5" class="text-center">Erro ao buscar itens</td></tr>';
        btnSelecionarItem.disabled = true;
      });
  }

  // Verificar campo servidor antes de adicionar item
  function validarFormularioAdicao() {
    const idServidor = document.getElementById("id_servidor");
    const hiddenServidor = document.getElementById("hidden_id_servidor");

    if (idServidor && idServidor.value) {
      // Atualizar o campo hidden com o valor atual
      if (hiddenServidor) {
        hiddenServidor.value = idServidor.value;
      }
      return true;
    } else {
      alert(
        "Por favor, selecione um Servidor Responsável antes de adicionar itens."
      );
      if (idServidor) {
        idServidor.focus();
      }
      return false;
    }
  }

  // Sincronizar campos de observação
  function sincronizarObservacoes() {
    const obs = document.getElementById("obs");
    const hiddenObs = document.getElementById("hidden_obs");
    const hiddenObsFinal = document.getElementById("hidden_obs_final");

    if (obs && obs.value) {
      if (hiddenObs) hiddenObs.value = obs.value;
      if (hiddenObsFinal) hiddenObsFinal.value = obs.value;
    }
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

    // Também buscar quando o campo perder o foco
    codigoInput.addEventListener("blur", buscarItemPorCodigo);
  }

  // Adicionar evento de validação ao formulário de adição
  if (formAdicao) {
    formAdicao.addEventListener("submit", function (e) {
      if (!validarFormularioAdicao()) {
        e.preventDefault();
      } else {
        sincronizarObservacoes();
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

    // Pesquisar quando pressionar Enter
    searchTermInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        pesquisarItensPorNome();
      }
    });
  }

  // Adicionar evento ao botão de selecionar item
  if (btnSelecionarItem) {
    btnSelecionarItem.addEventListener("click", function () {
      if (selectedItemFromSearch) {
        // Preencher o campo de código
        if (codigoInput) codigoInput.value = selectedItemFromSearch.codigo;

        // Exibir o nome do item
        if (nomeItemDisplay) {
          nomeItemDisplay.textContent = selectedItemFromSearch.nome;
          nomeItemDisplay.style.color = "black";
        }

        // Fechar o modal
        const modalBuscaItem = document.getElementById("modalBuscaItem");
        if (modalBuscaItem) {
          const modal = bootstrap.Modal.getInstance(modalBuscaItem);
          if (modal) modal.hide();
        }

        // Focar no campo de quantidade
        if (qtdeInput) {
          qtdeInput.focus();
          qtdeInput.select();
        }
      }
    });
  }

  // Script para preencher o modal de edição
  const editButtons = document.querySelectorAll(".edit-item");
  if (editButtons.length > 0) {
    editButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const id = this.getAttribute("data-id");
        const qtde = this.getAttribute("data-qtde");

        const editTempId = document.getElementById("edit_temp_id");
        const novaQtde = document.getElementById("nova_qtde");

        if (editTempId) editTempId.value = id;
        if (novaQtde) {
          novaQtde.value = qtde;
          setTimeout(() => novaQtde.focus(), 500);
        }
      });
    });
  }

  // Verificar código ao carregar a página
  if (codigoInput && codigoInput.value.trim()) {
    buscarItemPorCodigo();
  }

  // Atualizar o botão finalizar com base na presença de itens
  const atualizarBotaoFinalizar = function () {
    const btnFinalizar = document.getElementById("btn-finalizar");
    const temItens = document.querySelectorAll(".temp-item").length > 0;

    if (btnFinalizar) {
      btnFinalizar.disabled = !temItens;
    }
  };

  // Chamar ao carregar a página
  atualizarBotaoFinalizar();

  // Sincronizar campos ao carregar
  window.addEventListener("load", function () {
    // Sincronizar servidor
    const idServidor = document.getElementById("id_servidor");
    const hiddenServidor = document.getElementById("hidden_id_servidor");

    if (idServidor && hiddenServidor) {
      hiddenServidor.value = idServidor.value;
    }

    // Sincronizar observações
    sincronizarObservacoes();
  });
});
