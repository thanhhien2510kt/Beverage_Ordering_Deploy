/**
 * Checkout Page JavaScript
 * Xử lý các tương tác trên trang checkout
 * Requires: common.js
 */

$(document).ready(function () {
  // Update note counter
  $("#order-note").on("input", function () {
    const length = $(this).val().length;
    $(this)
      .siblings(".note-counter")
      .text(length + "/52 ký tự");
  });

  // Address: show edit block
  $("#change-address-btn").on("click", function (e) {
    e.preventDefault();
    $("#delivery-address-input").val($("#delivery-address").val());
    $("#address-edit-block").slideDown(300);
    $("#delivery-address-input").focus();
  });

  $("#btn-cancel-address").on("click", function () {
    $("#address-edit-block").slideUp(300);
  });

  // Save delivery address for this order only (does not update User.DiaChi)
  $("#btn-save-address").on("click", function () {
    const val = $("#delivery-address-input").val().trim();
    $("#delivery-address").val(val);
    $("#delivery-address-display").text(val || "Chưa có địa chỉ");
    $("#change-address-btn").text(val ? "Đổi địa chỉ" : "Thêm địa chỉ");
    $("#address-edit-block").slideUp(300);
  });

  // VAT invoice checkbox
  $("#vat-invoice").on("change", function () {
    if ($(this).is(":checked")) {
      $("#vat-fields").slideDown(300);
      // Make VAT fields required
      $("#vat-fields input").prop("required", true);
    } else {
      $("#vat-fields").slideUp(300);
      // Remove required
      $("#vat-fields input").prop("required", false);
    }
  });

  // Province selection
  $("#province-select").on("change", function () {
    const selectedProvince = $(this).val();
    const $storeSelect = $("#store-select");

    // Reset store select and store info
    $storeSelect.val("");
    $("#store-info").slideUp(200);

    if (!selectedProvince) {
      $storeSelect.prop("disabled", true);
      // Show all options when disabled (only placeholder visible to user)
      $storeSelect.find("option").show();
      return;
    }

    // Enable store select and filter options by province
    $storeSelect.prop("disabled", false);
    $storeSelect.find("option").each(function () {
      const value = $(this).attr("value");
      if (!value) {
        // Always show placeholder
        $(this).show();
        return;
      }

      const province = $(this).data("province") || "";
      if (province === selectedProvince) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  });

  // Store selection
  $("#store-select").on("change", function () {
    const selectedOption = $(this).find(":selected");
    const phone = selectedOption.data("phone");
    const address = selectedOption.data("address");

    if (phone || address) {
      $("#store-phone").text(phone || "");
      $("#store-address").text(address || "");
      $("#store-info").slideDown(300);
    } else {
      $("#store-info").slideUp(300);
    }
  });

  // Promotion code variables
  let appliedPromotion = null;

  // Toggle clear button visibility based on input value
  function toggleClearButton() {
    const value = $("#promotion-code").val().trim();
    const $clearBtn = $("#promotion-clear-btn");
    
    // Show clear button if input has value (even when disabled after applying)
    if (value) {
      $clearBtn.show();
    } else {
      $clearBtn.hide();
    }
  }

  // Show/hide clear button on input change
  $("#promotion-code").on("input", function () {
    toggleClearButton();
  });

  // Clear promotion code when clicking X button
  $("#promotion-clear-btn").on("click", function () {
    // If promotion is applied, remove it
    if (appliedPromotion) {
      removePromotionCode();
    } else {
      // Just clear the input
      $("#promotion-code").val("").focus();
      toggleClearButton();
      $("#promotion-message").removeClass("promotion-success promotion-error").text("");
    }
  });

  // Apply promotion code
  $("#btn-apply-promotion").on("click", function () {
    applyPromotionCode();
  });

  // Apply on Enter key
  $("#promotion-code").on("keypress", function (e) {
    if (e.which === 13) {
      e.preventDefault();
      applyPromotionCode();
    }
  });

  function applyPromotionCode() {
    const code = $("#promotion-code").val().trim();
    const $message = $("#promotion-message");
    const $btnApply = $("#btn-apply-promotion");

    // Clear previous message
    $message.removeClass("promotion-success promotion-error").text("");

    if (!code) {
      $message.addClass("promotion-error").text("Vui lòng nhập mã khuyến mãi");
      toggleClearButton();
      return;
    }

    // Disable button while validating
    $btnApply.prop("disabled", true).text("Đang kiểm tra...");

    // Get subtotal
    const subtotal =
      parseFloat($("#subtotal").text().replace(/[^\d]/g, "")) || 0;

    // Validate promotion code via API
    $.ajax({
      url: getApiPath("promotion/validate.php"),
      method: "POST",
      data: {
        code: code,
        subtotal: subtotal,
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          // Store applied promotion
          appliedPromotion = response.promotion;

          // Update UI
          $message.addClass("promotion-success").text(response.message);
          $("#promotion-code").prop("disabled", true);
          $btnApply.hide();
          toggleClearButton(); // Show clear button since input has value

          // Update discount and total
          updatePromotionDiscount(response.discount);
          updateTotals();
        } else {
          $message
            .addClass("promotion-error")
            .text(response.message || "Mã khuyến mãi không hợp lệ");
          appliedPromotion = null;
          updatePromotionDiscount(0);
          updateTotals();
          toggleClearButton();
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
        $message
          .addClass("promotion-error")
          .text("Có lỗi xảy ra. Vui lòng thử lại.");
        appliedPromotion = null;
        toggleClearButton();
      },
      complete: function () {
        $btnApply.prop("disabled", false).text("Áp dụng");
      },
    });
  }

  function removePromotionCode() {
    const $message = $("#promotion-message");
    const $btnApply = $("#btn-apply-promotion");

    // Clear promotion
    appliedPromotion = null;
    $("#promotion-code").val("").prop("disabled", false).focus();
    $message.removeClass("promotion-success promotion-error").text("");
    $btnApply.show();
    toggleClearButton();

    // Update discount and total
    updatePromotionDiscount(0);
    updateTotals();
  }

  function updatePromotionDiscount(discount) {
    if (discount > 0) {
      $("#promotion-discount").text("-" + formatCurrency(discount));
      $("#promotion-row").slideDown(300);
    } else {
      $("#promotion-discount").text("-0₫");
      $("#promotion-row").slideUp(300);
    }
  }

  // Calculate totals
  function updateTotals() {
    const subtotal =
      parseFloat($("#subtotal").text().replace(/[^\d]/g, "")) || 0;
    const shippingFee =
      parseFloat($("#shipping-fee").text().replace(/[^\d]/g, "")) || 0;
    const promotionDiscount =
      parseFloat($("#promotion-discount").text().replace(/[^\d]/g, "")) || 0;

    const total = subtotal + shippingFee - promotionDiscount;
    $("#total-amount").text(formatCurrency(total));
  }

  // Pay now button
  $("#pay-now-btn").on("click", function () {
    // Validate form
    if (!$("#agree-terms").is(":checked")) {
      if (typeof showSnackBar === 'function') {
        showSnackBar('warm', "Vui lòng đồng ý với điều khoản mua hàng");
      } else {
        alert("Vui lòng đồng ý với điều khoản mua hàng");
      }
      return;
    }

    const deliveryAddr = $("#delivery-address").val().trim();
    if (!deliveryAddr) {
      if (typeof showSnackBar === 'function') {
        showSnackBar('warm', "Vui lòng nhập địa chỉ giao hàng");
      } else {
        alert("Vui lòng nhập địa chỉ giao hàng");
      }
      $("#change-address-btn").trigger("click");
      return;
    }

    const province = $("#province-select").val();
    if (!province) {
      if (typeof showSnackBar === 'function') {
        showSnackBar('warm', "Vui lòng chọn Tỉnh/Thành phố");
      } else {
        alert("Vui lòng chọn Tỉnh/Thành phố");
      }
      return;
    }

    const storeId = $("#store-select").val();
    if (!storeId) {
      if (typeof showSnackBar === 'function') {
        showSnackBar('warm', "Vui lòng chọn cửa hàng");
      } else {
        alert("Vui lòng chọn cửa hàng");
      }
      return;
    }

    const paymentMethod = $("input[name='payment_method']:checked").val();
    if (!paymentMethod) {
      if (typeof showSnackBar === 'function') {
        showSnackBar('warm', "Vui lòng chọn phương thức thanh toán");
      } else {
        alert("Vui lòng chọn phương thức thanh toán");
      }
      return;
    }

    // Check VAT fields if VAT invoice is checked
    if ($("#vat-invoice").is(":checked")) {
      const vatEmail = $("input[name='vat_email']").val();
      const vatTaxId = $("input[name='vat_tax_id']").val();
      const vatCompany = $("input[name='vat_company']").val();
      const vatAddress = $("input[name='vat_address']").val();

      if (!vatEmail || !vatTaxId || !vatCompany || !vatAddress) {
        if (typeof showSnackBar === 'function') {
          showSnackBar('warm', "Vui lòng điền đầy đủ thông tin hóa đơn VAT");
        } else {
          alert("Vui lòng điền đầy đủ thông tin hóa đơn VAT");
        }
        return;
      }
    }

    // Disable button
    $(this).prop("disabled", true).text("Đang xử lý...");

    // Prepare order data
    const orderData = {
      store_id: storeId,
      payment_method: paymentMethod,
      delivery_address: $("#delivery-address").val().trim(),
      order_note: $("#order-note").val(),
      vat_invoice: $("#vat-invoice").is(":checked") ? 1 : 0,
      vat_email: $("input[name='vat_email']").val() || "",
      vat_tax_id: $("input[name='vat_tax_id']").val() || "",
      vat_company: $("input[name='vat_company']").val() || "",
      vat_address: $("input[name='vat_address']").val() || "",
      promotion_code: appliedPromotion ? appliedPromotion.code : "",
      promotion_id: appliedPromotion ? appliedPromotion.id : null,
      promotion_discount: appliedPromotion
        ? appliedPromotion.discount_amount
        : 0,
    };

    // Submit order
    $.ajax({
      url: getApiPath("order/create.php"),
      method: "POST",
      data: orderData,
      dataType: "json",
      success: function (response) {
        if (response.success) {
          // Redirect to order result page
          window.location.href =
            "order_result.php?order_id=" + response.order_id;
        } else {
          if (typeof showModalBox === 'function') {
            showModalBox({
              type: 'acknowledge',
              title: 'Lỗi',
              message: "Có lỗi xảy ra: " + (response.message || "Vui lòng thử lại"),
              onConfirm: function() {
                $("#pay-now-btn").prop("disabled", false).text("Thanh toán ngay");
              }
            });
          } else {
            alert("Có lỗi xảy ra: " + (response.message || "Vui lòng thử lại"));
            $("#pay-now-btn").prop("disabled", false).text("Thanh toán ngay");
          }
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
        if (typeof showModalBox === 'function') {
          showModalBox({
            type: 'acknowledge',
            title: 'Lỗi',
            message: "Có lỗi xảy ra. Vui lòng thử lại.",
            onConfirm: function() {
              $("#pay-now-btn").prop("disabled", false).text("Thanh toán ngay");
            }
          });
        } else {
          alert("Có lỗi xảy ra. Vui lòng thử lại.");
          $("#pay-now-btn").prop("disabled", false).text("Thanh toán ngay");
        }
      },
    });
  });

  // Initialize
  updateTotals();
  toggleClearButton();
});
