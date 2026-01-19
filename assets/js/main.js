/**
 * Main JavaScript for MeowTea Fresh
 * jQuery và AJAX functions
 * Requires: common.js
 */

$(document).ready(function () {
  // Back to top button
  $(".back-to-top-link").on("click", function (e) {
    e.preventDefault();
    $("html, body").animate(
      {
        scrollTop: 0,
      },
      600
    );
  });

  // Show/hide back to top button
  $(window).scroll(function () {
    if ($(this).scrollTop() > 300) {
      $(".back-to-top").fadeIn();
    } else {
      $(".back-to-top").fadeOut();
    }
  });

  // Add to cart functionality - Open modal
  $(document).on("click", ".add-to-cart-btn", function (e) {
    e.preventDefault();
    e.stopPropagation();

    const productId = $(this).data("product-id");
    openProductCustomizeModal(productId);
  });

  // Load cart count on page load
  updateCartCount();

  // User dropdown menu toggle
  $(".user-dropdown-toggle").on("click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    const $dropdown = $(this).closest(".user-dropdown");
    $dropdown.toggleClass("active");
  });

  // Close dropdown when clicking outside
  $(document).on("click", function (e) {
    if (!$(e.target).closest(".user-dropdown").length) {
      $(".user-dropdown").removeClass("active");
    }
  });

  // Close dropdown when clicking on dropdown item
  $(".dropdown-item").on("click", function () {
    $(".user-dropdown").removeClass("active");
  });

  // ===== PRODUCT CUSTOMIZE MODAL =====

  // Open product customize modal
  function openProductCustomizeModal(productId) {
    const $modal = $("#product-customize-modal");
    const $loading = $("#modal-loading");
    const $content = $("#modal-product-content");

    // Show modal
    $modal.addClass("active");
    $("body").css("overflow", "hidden");

    // Show loading, hide content
    $loading.show();
    $content.hide();

    // Load product data
    $.ajax({
      url: getApiPath(`product/get.php?id=${productId}`),
      method: "GET",
      dataType: "json",
      success: function (response) {
        $loading.hide();
        if (response.success && response.data) {
          renderProductModal(response.data);
          $content.show();
        } else {
          alert(
            "Không thể tải thông tin sản phẩm: " +
              (response.message || "Lỗi không xác định")
          );
          closeProductModal();
        }
      },
      error: function (xhr, status, error) {
        $loading.hide();
        console.error("Error loading product:", error);
        alert("Có lỗi xảy ra khi tải sản phẩm. Vui lòng thử lại.");
        closeProductModal();
      },
    });
  }

  // Render product data in modal
  function renderProductModal(data) {
    const product = data.product;
    const optionGroups = data.optionGroups;

    // Set product info: GiaNiemYet = price for calc, GiaCoBan = reference (strikethrough)
    const giaNiemYet = product.GiaNiemYet ?? product.GiaCoBan ?? 0;
    const giaCoBan = product.GiaCoBan ?? product.GiaNiemYet ?? giaNiemYet;
    $("#modal-product-id").val(product.MaSP);
    $("#modal-base-price").val(giaNiemYet);
    $("#modal-product-name").text(product.TenSP);
    $("#modal-current-price").text(formatCurrency(giaNiemYet));
    if (giaCoBan > giaNiemYet) {
      $("#modal-old-price").text(formatCurrency(giaCoBan)).show();
      $("#modal-reference-price").val(giaCoBan);
    } else {
      $("#modal-old-price").hide();
      $("#modal-reference-price").val("");
    }

    // Set product image
    const imagePath = product.HinhAnh || "assets/img/products/product_one.png";
    const currentPath = window.location.pathname;
    let imageUrl = imagePath;
    if (currentPath.includes("/pages/menu/")) {
      imageUrl = "../../" + imagePath;
    } else if (currentPath.includes("/pages/")) {
      imageUrl = "../../../" + imagePath;
    }
    $("#modal-product-image").attr("src", imageUrl).attr("alt", product.TenSP);

    // Reset quantity (format with leading zero)
    $("#modal-quantity").val(1);
    $("#modal-quantity-display").text("01");
    $("#modal-product-note").val("");
    $("#modal-char-count").text("0");

    // Render option groups
    const $optionGroupsContainer = $("#modal-option-groups");
    $optionGroupsContainer.empty();

    optionGroups.forEach(function (group) {
      const $groupDiv = $('<div class="option-group"></div>');
      const titleText = group.IsMultiple
        ? `Thêm ${group.TenNhom}`
        : `Chọn ${group.TenNhom}`;
      const instructionText = group.IsMultiple ? "" : "Tối đa 1 loại";
      $groupDiv.append(
        `<h3 class="option-group-title">
          ${titleText}
          ${
            instructionText
              ? `<span class="option-group-instruction">${instructionText}</span>`
              : ""
          }
        </h3>`
      );
      const listClass = group.IsMultiple
        ? "option-list option-list-checkbox"
        : "option-list option-list-radio";
      const $optionList = $('<div class="' + listClass + '"></div>');

      group.options.forEach(function (option, index) {
        const isFirst = index === 0;
        const inputType = group.IsMultiple ? "checkbox" : "radio";
        const inputName = group.IsMultiple
          ? "options[]"
          : `option_group_${group.MaOptionGroup}`;
        const checked = !group.IsMultiple && isFirst ? "checked" : "";

        const $optionItem = $(`
          <div class="option-item">
            <input 
              type="${inputType}" 
              name="${inputName}" 
              id="modal_option_${option.MaOptionValue}"
              value="${option.MaOptionValue}"
              data-price="${option.GiaThem}"
              class="${group.IsMultiple ? "option-checkbox" : "option-radio"}"
              ${checked}
            >
            <label for="modal_option_${option.MaOptionValue}">
              ${option.TenGiaTri}
            </label>
            ${
              option.GiaThem > 0
                ? `<span class="option-price">+${formatCurrency(
                    option.GiaThem
                  )}</span>`
                : ""
            }
          </div>
        `);

        $optionList.append($optionItem);
      });

      $groupDiv.append($optionList);
      $optionGroupsContainer.append($groupDiv);
    });

    // Initialize modal handlers
    initModalHandlers();
  }

  // Initialize modal event handlers
  function initModalHandlers() {
    const basePrice = parseFloat($("#modal-base-price").val());
    let quantity = parseInt($("#modal-quantity").val()) || 1;

    // Update total price function
    function updateModalTotalPrice() {
      let total = basePrice;

      // Add selected options prices
      $(".option-checkbox:checked, .option-radio:checked").each(function () {
        total += parseFloat($(this).data("price") || 0);
      });

      // Multiply by quantity
      total *= quantity;

      // Update display
      $("#modal-total-price").text(formatCurrency(total));
    }

    // Helper to format quantity with leading zero
    function formatQuantity(num) {
      return num < 10 ? "0" + num : num.toString();
    }

    // Quantity controls
    $("#modal-increase-qty")
      .off("click")
      .on("click", function () {
        if (quantity < 10) {
          quantity++;
          $("#modal-quantity").val(quantity);
          $("#modal-quantity-display").text(formatQuantity(quantity));
          updateModalTotalPrice();
        }
      });

    $("#modal-decrease-qty")
      .off("click")
      .on("click", function () {
        if (quantity > 1) {
          quantity--;
          $("#modal-quantity").val(quantity);
          $("#modal-quantity-display").text(formatQuantity(quantity));
          updateModalTotalPrice();
        }
      });

    $("#modal-quantity")
      .off("change")
      .on("change", function () {
        quantity = Math.max(1, Math.min(10, parseInt($(this).val()) || 1));
        $(this).val(quantity);
        $("#modal-quantity-display").text(formatQuantity(quantity));
        updateModalTotalPrice();
      });

    // Option change handlers
    $(document)
      .off(
        "change",
        "#modal-product-content .option-checkbox, #modal-product-content .option-radio"
      )
      .on(
        "change",
        "#modal-product-content .option-checkbox, #modal-product-content .option-radio",
        function () {
          $(this)
            .closest(".option-item")
            .toggleClass("selected", $(this).is(":checked"));
          updateModalTotalPrice();
        }
      );

    // Initialize selected state
    $(
      "#modal-product-content .option-radio:checked, #modal-product-content .option-checkbox:checked"
    ).each(function () {
      $(this).closest(".option-item").addClass("selected");
    });

    // Note character counter
    $("#modal-product-note")
      .off("input")
      .on("input", function () {
        const length = $(this).val().length;
        $("#modal-char-count").text(length);
      });

    // Add to cart button
    $("#modal-add-to-cart-btn")
      .off("click")
      .on("click", function () {
        const options = [];

        // Collect selected options
        $(
          "#modal-product-content .option-checkbox:checked, #modal-product-content .option-radio:checked"
        ).each(function () {
          options.push({
            option_value_id: $(this).val(),
            price: parseFloat($(this).data("price") || 0),
          });
        });

        // Calculate total
        let total = basePrice;
        options.forEach(function (opt) {
          total += opt.price;
        });
        total *= quantity;

        const formData = {
          product_id: $("#modal-product-id").val(),
          quantity: quantity,
          options: JSON.stringify(options),
          note: $("#modal-product-note").val(),
          base_price: basePrice,
          total_price: total,
        };
        var refPrice = parseFloat($("#modal-reference-price").val() || 0);
        if (refPrice > basePrice) formData.reference_price = refPrice;

        // Send to cart API
        $.ajax({
          url: getApiPath("cart/add.php"),
          method: "POST",
          data: formData,
          dataType: "json",
          success: function (response) {
            if (response.success) {
              updateCartCount();
              if (typeof showSnackBar === 'function') {
                showSnackBar('success', response.message || 'Đã thêm vào giỏ hàng thành công!');
              }
            } else {
              if (typeof showSnackBar === 'function') {
                showSnackBar('failed', response.message || 'Có lỗi xảy ra. Vui lòng thử lại.');
              } else {
                alert("Có lỗi xảy ra: " + (response.message || "Vui lòng thử lại"));
              }
            }
          },
          error: function (xhr, status, error) {
            console.error("Error:", error);
            if (typeof showSnackBar === 'function') {
              showSnackBar('failed', 'Có lỗi xảy ra. Vui lòng thử lại.');
            } else {
              alert("Có lỗi xảy ra. Vui lòng thử lại.");
            }
          },
        });
      });

    // Initial price update
    updateModalTotalPrice();
  }

  // Close product modal
  function closeProductModal() {
    $("#product-customize-modal").removeClass("active");
    $("body").css("overflow", "");
  }

  // Close modal handlers
  $("#close-modal-btn").on("click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    closeProductModal();
  });

  $(".modal-overlay").on("click", function (e) {
    if (e.target === this) {
      closeProductModal();
    }
  });

  // Close modal on ESC key
  $(document).on("keydown", function (e) {
    if (
      e.key === "Escape" &&
      $("#product-customize-modal").hasClass("active")
    ) {
      closeProductModal();
    }
  });
});
