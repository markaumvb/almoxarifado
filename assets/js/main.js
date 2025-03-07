// assets/js/main.js atualizado

// Inicializar DataTables
$(document).ready(function () {
  if ($(".datatable").length > 0) {
    $(".datatable").DataTable({
      language: {
        url: "https://cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json",
      },
      responsive: true,
    });
  }

  // Inicializar tooltips
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Inicializar popovers
  var popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]')
  );
  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });

  // Verificar URL atual para expandir menus automáticamente
  var currentUrl = window.location.href;

  // Expandir menu de cadastros quando estiver em alguma página de cadastro
  if (
    currentUrl.includes("/itens/") ||
    currentUrl.includes("/setores/") ||
    currentUrl.includes("/servidores/") ||
    currentUrl.includes("/unidades/")
  ) {
    $("#cadastrosSubmenu").addClass("show");
  }

  // Expandir menu de relatórios quando estiver em alguma página de relatório
  if (currentUrl.includes("/relatorios/")) {
    $("#relatoriosSubmenu").addClass("show");
  }

  // Adicionar item à lista de saída temporária
  $(document).on("click", ".btn-add-item", function (e) {
    e.preventDefault();
    var itemId = $("#item_id").val();
    var itemCodigo = $("#item_codigo").val();
    var itemNome = $("#item_nome").val();
    var quantidade = $("#quantidade").val();
    var observacao = $("#observacao").val();

    if (!itemId || !quantidade) {
      alert("Por favor, selecione um item e informe a quantidade");
      return;
    }

    // Verificar se o item já existe na lista
    var exists = false;
    $(".temp-item").each(function () {
      if ($(this).data("id") == itemId) {
        exists = true;
        return false;
      }
    });

    if (exists) {
      alert("Este item já está na lista");
      return;
    }

    // Adicionar o item à lista temporária
    var html = `
          <tr class="temp-item" data-id="${itemId}">
              <td>${itemCodigo}</td>
              <td>${itemNome}</td>
              <td>${quantidade}</td>
              <td>${observacao}</td>
              <td>
                  <button type="button" class="btn btn-sm btn-warning btn-edit-item" data-id="${itemId}">
                      <i class="fas fa-edit"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-danger btn-remove-item" data-id="${itemId}">
                      <i class="fas fa-trash"></i>
                  </button>
              </td>
              <input type="hidden" name="itens[${itemId}][id]" value="${itemId}">
              <input type="hidden" name="itens[${itemId}][quantidade]" value="${quantidade}">
              <input type="hidden" name="itens[${itemId}][observacao]" value="${observacao}">
          </tr>
      `;

    $("#items-table tbody").append(html);

    // Limpar o formulário
    $("#item_id").val("");
    $("#item_codigo").val("");
    $("#item_nome").val("");
    $("#quantidade").val("");
    $("#observacao").val("");

    // Atualizar o contador de itens
    updateItemsCount();
  });

  // Remover item da lista temporária
  $(document).on("click", ".btn-remove-item", function () {
    var itemId = $(this).data("id");

    // Remover o item da lista
    $(this).closest("tr").remove();

    // Atualizar o contador de itens
    updateItemsCount();
  });

  // Editar item na lista temporária
  $(document).on("click", ".btn-edit-item", function () {
    var tr = $(this).closest("tr");
    var itemId = $(this).data("id");
    var quantidade = tr
      .find('input[name="itens[' + itemId + '][quantidade]"]')
      .val();
    var observacao = tr
      .find('input[name="itens[' + itemId + '][observacao]"]')
      .val();

    // Preencher o modal de edição
    $("#edit_item_id").val(itemId);
    $("#edit_quantidade").val(quantidade);
    $("#edit_observacao").val(observacao);

    // Abrir o modal de edição
    $("#editItemModal").modal("show");
  });

  // Confirmar edição de item
  $(document).on("click", "#btn-confirm-edit", function () {
    var itemId = $("#edit_item_id").val();
    var quantidade = $("#edit_quantidade").val();
    var observacao = $("#edit_observacao").val();

    // Atualizar os valores na lista
    var tr = $('.temp-item[data-id="' + itemId + '"]');
    tr.find("td:eq(2)").text(quantidade);
    tr.find("td:eq(3)").text(observacao);
    tr.find('input[name="itens[' + itemId + '][quantidade]"]').val(quantidade);
    tr.find('input[name="itens[' + itemId + '][observacao]"]').val(observacao);

    // Fechar o modal
    $("#editItemModal").modal("hide");
  });

  // Função para atualizar o contador de itens
  function updateItemsCount() {
    var count = $(".temp-item").length;
    $(".items-count").text(count);

    // Exibir ou ocultar o botão de salvar
    if (count > 0) {
      $("#btn-save").show();
    } else {
      $("#btn-save").hide();
    }
  }

  // Habilitar pesquisa de itens
  $(document).on("keyup", "#search_item", function () {
    var search = $(this).val().toLowerCase();

    $.ajax({
      url: "../../api/itens.php",
      type: "GET",
      data: { search: search },
      success: function (data) {
        var items = JSON.parse(data);
        var html = "";

        items.forEach(function (item) {
          let tipoText = "Desconhecido";
          if (item.TIPO == 1) tipoText = "Consumo";
          else if (item.TIPO == 2) tipoText = "Equipamento";
          else if (item.TIPO == 3) tipoText = "Empenho";

          html += `
              <li class="list-group-item item-result" data-id="${item.ID}" data-codigo="${item.CODIGO}" data-nome="${item.NOME}">
                  <strong>${item.CODIGO}</strong> - ${item.NOME} (${tipoText})
              </li>
          `;
        });

        $("#items-results").html(html);

        if (html) {
          $("#items-results-container").show();
        } else {
          $("#items-results-container").hide();
        }
      },
    });
  });

  // Selecionar item da pesquisa
  $(document).on("click", ".item-result", function () {
    var id = $(this).data("id");
    var codigo = $(this).data("codigo");
    var nome = $(this).data("nome");

    $("#item_id").val(id);
    $("#item_codigo").val(codigo);
    $("#item_nome").val(nome);

    $("#items-results-container").hide();
    $("#quantidade").focus();
  });

  // Relatórios - Alternar opções de agrupamento
  $(document).on("change", "#agrupar", function () {
    var value = $(this).val();

    if (value == "item") {
      $("#item-group-options").show();
      $("#setor-group-options").hide();
      $("#data-group-options").hide();
    } else if (value == "setor") {
      $("#item-group-options").hide();
      $("#setor-group-options").show();
      $("#data-group-options").hide();
    } else if (value == "data") {
      $("#item-group-options").hide();
      $("#setor-group-options").hide();
      $("#data-group-options").show();
    } else {
      $("#item-group-options").hide();
      $("#setor-group-options").hide();
      $("#data-group-options").hide();
    }
  });
});
