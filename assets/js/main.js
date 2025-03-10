// Usar IIFE para evitar poluição do escopo global
(function () {
  // DOM Ready - Executar quando o DOM estiver carregado
  document.addEventListener("DOMContentLoaded", function () {
    // Inicializar componentes da UI
    initializeUI();

    // Inicializar funcionalidades específicas para cada página
    initializeFunctions();

    // Funcionalidades gerais
    handleResponsiveLayout();
  });

  /**
   * Inicializa componentes da interface
   */
  function initializeUI() {
    // Inicializar DataTables
    initDataTables();

    // Inicializar Bootstrap Tooltips
    initTooltips();

    // Inicializar Bootstrap Popovers
    initPopovers();

    // Inicializar alertas automáticos
    initAutoCloseAlerts();

    // Inicializar sidebar
    initSidebar();
  }

  /**
   * Inicializar DataTables com configurações padrão
   */
  function initDataTables() {
    if ($.fn.DataTable) {
      $(".datatable").DataTable({
        language: {
          url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json",
        },
        responsive: true,
        pageLength: 25,
        stateSave: true,
        columnDefs: [
          { orderable: false, targets: -1 }, // Última coluna (ações) não ordenável
        ],
      });
    }
  }

  /**
   * Inicializar tooltips do Bootstrap
   */
  function initTooltips() {
    var tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );

    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl, {
        trigger: "hover",
        boundary: "window",
      });
    });
  }

  /**
   * Inicializar popovers do Bootstrap
   */
  function initPopovers() {
    var popoverTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="popover"]')
    );

    popoverTriggerList.map(function (popoverTriggerEl) {
      return new bootstrap.Popover(popoverTriggerEl);
    });
  }

  /**
   * Inicializar fechamento automático de alertas
   */
  function initAutoCloseAlerts() {
    // Fechar alertas automaticamente após 5 segundos
    window.setTimeout(function () {
      const alerts = document.querySelectorAll(".alert:not(.alert-permanent)");
      alerts.forEach((alert) => {
        if (alert && bootstrap.Alert) {
          const bsAlert = new bootstrap.Alert(alert);
          bsAlert.close();
        }
      });
    }, 5000);
  }

  /**
   * Inicializa a sidebar e o botão de colapso
   */
  function initSidebar() {
    const sidebarCollapseBtn = document.getElementById("sidebarCollapse");

    if (sidebarCollapseBtn) {
      sidebarCollapseBtn.addEventListener("click", function () {
        const sidebar = document.getElementById("sidebar");
        const content = document.getElementById("content");

        if (sidebar) {
          sidebar.classList.toggle("active");
        }

        if (content) {
          content.classList.toggle("active");
        }
      });
    }

    // Expandir menus baseado na URL atual
    expandActiveMenus();
  }

  /**
   * Expande os menus ativos baseados na URL atual
   */
  function expandActiveMenus() {
    const currentUrl = window.location.href;

    // Expandir menu de cadastros quando estiver em alguma página de cadastro
    if (
      currentUrl.includes("/itens/") ||
      currentUrl.includes("/setores/") ||
      currentUrl.includes("/servidores/") ||
      currentUrl.includes("/unidades/")
    ) {
      const cadastrosSubmenu = document.getElementById("cadastrosSubmenu");
      if (cadastrosSubmenu) {
        cadastrosSubmenu.classList.add("show");
      }
    }

    // Expandir menu de relatórios quando estiver em alguma página de relatório
    if (currentUrl.includes("/relatorios/")) {
      const relatoriosSubmenu = document.getElementById("relatoriosSubmenu");
      if (relatoriosSubmenu) {
        relatoriosSubmenu.classList.add("show");
      }
    }

    // Adiciona a classe 'active' ao link atual na sidebar
    const navLinks = document.querySelectorAll(".nav-link");
    navLinks.forEach((link) => {
      if (link.href === currentUrl) {
        link.classList.add("active");
        // Adiciona a classe 'active' ao pai, se existir
        const parentLi = link.closest("li");
        if (parentLi) {
          parentLi.classList.add("active");
        }
      }
    });
  }

  /**
   * Inicializa funcionalidades específicas para cada página
   */
  function initializeFunctions() {
    // Inicializar funções para página de saídas
    initSaidasFunctions();

    // Inicializar funções para página de itens
    initItensFunctions();

    // Inicializar funções para página de relatórios
    initRelatoriosFunctions();
  }

  /**
   * Funções específicas para as páginas de saídas
   */
  function initSaidasFunctions() {
    // Adicionar item à lista
    const btnAddItem = document.querySelector(".btn-add-item");
    if (btnAddItem) {
      btnAddItem.addEventListener("click", function () {
        addItemToList();
      });
    }

    // Remover item da lista
    document.addEventListener("click", function (e) {
      if (e.target.closest(".btn-remove-item")) {
        e.preventDefault();
        if (confirm("Tem certeza que deseja remover este item?")) {
          const btn = e.target.closest(".btn-remove-item");
          const tr = btn.closest("tr");
          if (tr) {
            tr.remove();
            updateItemCount();
          }
        }
      }
    });

    // Editar item
    document.addEventListener("click", function (e) {
      if (e.target.closest(".btn-edit-item")) {
        const btn = e.target.closest(".btn-edit-item");
        editItem(btn);
      }
    });

    // Pesquisa de itens
    const searchItemInput = document.getElementById("search_item");
    if (searchItemInput) {
      searchItemInput.addEventListener("keyup", debounce(searchItems, 300));
    }
  }

  /**
   * Adicionar item à lista de saída
   */
  function addItemToList() {
    const itemId = document.getElementById("item_id")?.value;
    const itemCodigo = document.getElementById("item_codigo")?.value;
    const itemNome = document.getElementById("item_nome")?.value;
    const quantidade = document.getElementById("quantidade")?.value;
    const observacao = document.getElementById("observacao")?.value || "";

    if (!itemId || !quantidade) {
      alert("Por favor, selecione um item e informe a quantidade");
      return;
    }

    // Verificar se o item já existe na lista
    const exists = Array.from(document.querySelectorAll(".temp-item")).some(
      (item) => {
        return item.dataset.id === itemId;
      }
    );

    if (exists) {
      alert("Este item já está na lista");
      return;
    }

    // Criar a linha da tabela
    const tr = document.createElement("tr");
    tr.className = "temp-item";
    tr.dataset.id = itemId;

    tr.innerHTML = `
          <td>${itemCodigo}</td>
          <td>${itemNome}</td>
          <td>${quantidade}</td>
          <td>${observacao}</td>
          <td>
              <button type="button" class="btn btn-sm btn-warning btn-edit-item me-1" data-id="${itemId}">
                  <i class="fas fa-edit"></i>
              </button>
              <button type="button" class="btn btn-sm btn-danger btn-remove-item" data-id="${itemId}">
                  <i class="fas fa-trash"></i>
              </button>
          </td>
          <input type="hidden" name="itens[${itemId}][id]" value="${itemId}">
          <input type="hidden" name="itens[${itemId}][quantidade]" value="${quantidade}">
          <input type="hidden" name="itens[${itemId}][observacao]" value="${observacao}">
      `;

    // Adicionar à tabela
    const tbody = document.querySelector("#items-table tbody");
    if (tbody) {
      tbody.appendChild(tr);

      // Limpar campos
      if (document.getElementById("item_id"))
        document.getElementById("item_id").value = "";
      if (document.getElementById("item_codigo"))
        document.getElementById("item_codigo").value = "";
      if (document.getElementById("item_nome"))
        document.getElementById("item_nome").value = "";
      if (document.getElementById("quantidade"))
        document.getElementById("quantidade").value = "";
      if (document.getElementById("observacao"))
        document.getElementById("observacao").value = "";
      if (document.getElementById("search_item")) {
        document.getElementById("search_item").value = "";
        document.getElementById("search_item").focus();
      }

      // Atualizar contador
      updateItemCount();
    }
  }

  /**
   * Editar item na lista
   */
  function editItem(btn) {
    const itemId = btn.dataset.id;
    const tr = btn.closest("tr");

    if (tr && itemId) {
      const quantidade = tr.querySelector(
        `input[name="itens[${itemId}][quantidade]"]`
      )?.value;
      const observacao = tr.querySelector(
        `input[name="itens[${itemId}][observacao]"]`
      )?.value;

      // Preencher modal
      const editItemIdEl = document.getElementById("edit_item_id");
      const editQuantidadeEl = document.getElementById("edit_quantidade");
      const editObservacaoEl = document.getElementById("edit_observacao");

      if (editItemIdEl) editItemIdEl.value = itemId;
      if (editQuantidadeEl) editQuantidadeEl.value = quantidade;
      if (editObservacaoEl) editObservacaoEl.value = observacao;

      // Abrir modal
      const editModal = document.getElementById("editItemModal");
      if (editModal) {
        const modal = new bootstrap.Modal(editModal);
        modal.show();

        // Confirmar edição
        const btnConfirmEdit = document.getElementById("btn-confirm-edit");
        if (btnConfirmEdit) {
          btnConfirmEdit.onclick = function () {
            const newQuantidade = editQuantidadeEl.value;
            const newObservacao = editObservacaoEl.value;

            if (!newQuantidade) {
              alert("Por favor, informe a quantidade");
              return;
            }

            // Atualizar valores na tabela
            tr.querySelector("td:nth-child(3)").textContent = newQuantidade;
            tr.querySelector("td:nth-child(4)").textContent = newObservacao;
            tr.querySelector(
              `input[name="itens[${itemId}][quantidade]"]`
            ).value = newQuantidade;
            tr.querySelector(
              `input[name="itens[${itemId}][observacao]"]`
            ).value = newObservacao;

            modal.hide();
          };
        }
      }
    }
  }

  /**
   * Atualizar contador de itens
   */
  function updateItemCount() {
    const count = document.querySelectorAll(".temp-item").length;
    const counters = document.querySelectorAll(".items-count");

    counters.forEach((counter) => {
      counter.textContent = count;
    });

    // Exibir ou ocultar botão de salvar
    const btnSave = document.getElementById("btn-save");
    if (btnSave) {
      btnSave.style.display = count > 0 ? "inline-block" : "none";
    }
  }

  /**
   * Pesquisar itens com debounce
   */
  function searchItems() {
    const search = document.getElementById("search_item")?.value;
    const resultsContainer = document.getElementById("items-results-container");
    const resultsList = document.getElementById("items-results");

    if (!search || search.length < 2 || !resultsList) {
      if (resultsContainer) resultsContainer.classList.add("d-none");
      return;
    }

    // Realizar a busca via AJAX
    fetch(`../../api/itens.php?search=${encodeURIComponent(search)}`)
      .then((response) => response.json())
      .then((data) => {
        if (data && data.length > 0) {
          let html = "";

          data.forEach((item) => {
            html += `<li class="list-group-item item-result" data-id="${item.ID}" data-codigo="${item.CODIGO}" data-nome="${item.NOME}">
                          <strong>${item.CODIGO}</strong> - ${item.NOME}
                      </li>`;
          });

          resultsList.innerHTML = html;
          resultsContainer.classList.remove("d-none");

          // Adicionar evento aos resultados
          document.querySelectorAll(".item-result").forEach((item) => {
            item.addEventListener("click", selectSearchItem);
          });
        } else {
          resultsList.innerHTML =
            '<li class="list-group-item">Nenhum item encontrado</li>';
          resultsContainer.classList.remove("d-none");
        }
      })
      .catch((error) => {
        console.error("Erro na requisição:", error);
        resultsList.innerHTML =
          '<li class="list-group-item">Erro ao buscar itens</li>';
        resultsContainer.classList.remove("d-none");
      });
  }

  /**
   * Selecionar item da lista de resultados
   */
  function selectSearchItem() {
    const id = this.dataset.id;
    const codigo = this.dataset.codigo;
    const nome = this.dataset.nome;

    document.getElementById("item_id").value = id;
    document.getElementById("item_codigo").value = codigo;
    document.getElementById("item_nome").value = nome;

    document.getElementById("items-results-container").classList.add("d-none");
    document.getElementById("quantidade").focus();
  }

  /**
   * Funções específicas para páginas de itens
   */
  function initItensFunctions() {
    // Confirmar exclusão
    document.addEventListener("click", function (e) {
      if (e.target.closest(".btn-delete")) {
        e.preventDefault();
        const btn = e.target.closest(".btn-delete");
        const id = btn.dataset.id;

        const confirmButton = document.getElementById("btn-confirm-delete");
        if (confirmButton) {
          confirmButton.href = `delete.php?id=${id}`;

          const deleteModal = new bootstrap.Modal(
            document.getElementById("deleteModal")
          );
          deleteModal.show();
        }
      }
    });
  }

  /**
   * Funções específicas para página de relatórios
   */
  function initRelatoriosFunctions() {
    // Alternar opções de agrupamento
    const agruparSelect = document.getElementById("agrupamento");
    if (agruparSelect) {
      agruparSelect.addEventListener("change", function () {
        const value = this.value;

        document
          .getElementById("item-group-options")
          ?.classList.toggle("d-none", value !== "item");
        document
          .getElementById("setor-group-options")
          ?.classList.toggle("d-none", value !== "setor");
        document
          .getElementById("data-group-options")
          ?.classList.toggle("d-none", value !== "data");
      });
    }
  }

  /**
   * Ajustes para layout responsivo
   */
  function handleResponsiveLayout() {
    // Verificar largura da tela e ajustar sidebar
    function checkWidth() {
      if (window.innerWidth < 768) {
        document.getElementById("sidebar")?.classList.add("active");
        document.getElementById("content")?.classList.add("active");
      } else {
        document.getElementById("sidebar")?.classList.remove("active");
        document.getElementById("content")?.classList.remove("active");
      }
    }

    // Executar na inicialização
    checkWidth();

    // Executar ao redimensionar
    window.addEventListener("resize", debounce(checkWidth, 250));
  }

  /**
   * Função de debounce para limitar execução repetida de funções
   */
  function debounce(func, wait) {
    let timeout;
    return function () {
      const context = this;
      const args = arguments;
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        func.apply(context, args);
      }, wait);
    };
  }
})();
