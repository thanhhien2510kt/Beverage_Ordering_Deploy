/**
 * Cart Page JavaScript
 * Xử lý các tương tác trên trang giỏ hàng
 * Requires: common.js
 */

$(document).ready(function () {
  // Flag to prevent multiple simultaneous delete operations
  let isDeleting = false;

  // Calculate and update total amount
  function updateTotalAmount() {
    let total = 0;
    $(".cart-item").each(function () {
      const $item = $(this);
      const $checkbox = $item.find(".item-checkbox");
      if ($checkbox.is(":checked")) {
        const totalText = $item.find(".total-value").text();
        const totalValue = parseFloat(totalText.replace(/[^\d]/g, ""));
        if (!isNaN(totalValue)) {
          total += totalValue;
        }
      }
    });

    $("#cart-total-amount").text(formatCurrency(total));
  }

  // Update quantity
  function updateQuantity(itemIndex, newQuantity) {
    // Prevent update if deletion is in progress
    if (isDeleting) {
      return;
    }

    // Validate item index exists in DOM
    const $item = $(".cart-item[data-item-index='" + itemIndex + "']");
    if ($item.length === 0) {
      return;
    }

    $.ajax({
      url: getApiPath("cart/update.php"),
      method: "POST",
      data: {
        item_index: itemIndex,
        quantity: newQuantity,
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          // Reload page to reflect changes
          location.reload();
        } else {
          if (typeof showSnackBar === 'function') {
            showSnackBar('failed', "Có lỗi xảy ra: " + (response.message || "Vui lòng thử lại"));
          } else {
            alert("Có lỗi xảy ra: " + (response.message || "Vui lòng thử lại"));
          }
        }
      },
      error: function () {
        if (typeof showSnackBar === 'function') {
          showSnackBar('failed', "Có lỗi xảy ra. Vui lòng thử lại.");
        } else {
          alert("Có lỗi xảy ra. Vui lòng thử lại.");
        }
      },
    });
  }

  // Delete item
  function deleteItem(itemIndex) {
    // Prevent multiple simultaneous delete operations
    if (isDeleting) {
      return;
    }

    // Validate item index exists in DOM
    const $item = $(".cart-item[data-item-index='" + itemIndex + "']");
    if ($item.length === 0) {
      showModalBox({
        title: 'Lỗi',
        message: 'Sản phẩm không tồn tại trong giỏ hàng',
        type: 'acknowledge'
      });
      return;
    }

    // Show confirmation modal
    showModalBox({
      title: 'Xóa sản phẩm',
      message: 'Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?',
      type: 'yesno',
      onConfirm: function() {
        // Continue with deletion
        proceedWithDeletion(itemIndex);
      },
      onCancel: function() {
        // User cancelled, do nothing
      }
    });
  }

  // Proceed with deletion after confirmation
  function proceedWithDeletion(itemIndex) {
    // Set deleting flag and disable all delete buttons
    isDeleting = true;
    $(".delete-btn").prop("disabled", true);

    $.ajax({
      url: getApiPath("cart/delete.php"),
      method: "POST",
      data: {
        item_index: itemIndex,
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          // Reload page to reflect changes and update item indices
          location.reload();
        } else {
          showModalBox({
            title: 'Lỗi',
            message: 'Có lỗi xảy ra: ' + (response.message || "Vui lòng thử lại"),
            type: 'acknowledge'
          });
          // Re-enable buttons on error
          isDeleting = false;
          $(".delete-btn").prop("disabled", false);
        }
      },
      error: function () {
        showModalBox({
          title: 'Lỗi',
          message: 'Có lỗi xảy ra. Vui lòng thử lại.',
          type: 'acknowledge'
        });
        // Re-enable buttons on error
        isDeleting = false;
        $(".delete-btn").prop("disabled", false);
      },
    });
  }

  // Update note
  function updateNote(itemIndex, note) {
    // Prevent update if deletion is in progress
    if (isDeleting) {
      return;
    }

    // Validate item index exists in DOM
    const $item = $(".cart-item[data-item-index='" + itemIndex + "']");
    if ($item.length === 0) {
      return;
    }

    $.ajax({
      url: getApiPath("cart/update.php"),
      method: "POST",
      data: {
        item_index: itemIndex,
        note: note,
      },
      dataType: "json",
      success: function (response) {
        if (!response.success) {
          console.error("Error updating note:", response.message);
        }
      },
      error: function () {
        console.error("Error updating note");
      },
    });
  }

  // Select all checkbox
  $("#select-all-items").on("change", function () {
    const isChecked = $(this).is(":checked");
    $(".item-checkbox").prop("checked", isChecked);
    updateTotalAmount();
  });

  // Individual item checkbox
  $(document).on("change", ".item-checkbox", function () {
    updateTotalAmount();

    // Update select all checkbox state
    const totalItems = $(".item-checkbox").length;
    const checkedItems = $(".item-checkbox:checked").length;
    $("#select-all-items").prop("checked", totalItems === checkedItems);
  });

  // Quantity buttons
  $(document).on("click", ".minus-btn", function () {
    const itemIndex = $(this).data("item-index");
    const $input = $(this).siblings(".quantity-input");
    let quantity = parseInt($input.val()) || 1;

    if (quantity > 1) {
      quantity--;
      updateQuantity(itemIndex, quantity);
    }
  });

  $(document).on("click", ".plus-btn", function () {
    const itemIndex = $(this).data("item-index");
    const $input = $(this).siblings(".quantity-input");
    let quantity = parseInt($input.val()) || 1;
    quantity++;
    updateQuantity(itemIndex, quantity);
  });

  // Quantity input change
  $(document).on("change", ".quantity-input", function () {
    const itemIndex = $(this).data("item-index");
    let quantity = parseInt($(this).val()) || 1;

    if (quantity < 1) {
      quantity = 1;
      $(this).val(1);
    }

    updateQuantity(itemIndex, quantity);
  });

  // Delete button
  $(document).on("click", ".delete-btn", function () {
    const itemIndex = $(this).data("item-index");
    deleteItem(itemIndex);
  });

  // Note input - debounced update
  let noteUpdateTimeout;
  $(document).on("input", ".note-input", function () {
    const $input = $(this);
    const itemIndex = $input.data("item-index");
    const note = $input.val();
    const noteLength = note.length;

    // Update counter
    $input.siblings(".note-counter").text(noteLength + "/52 ký tự");

    // Debounce API call
    clearTimeout(noteUpdateTimeout);
    noteUpdateTimeout = setTimeout(function () {
      updateNote(itemIndex, note);
    }, 500);
  });

  // Checkout button
  $("#checkout-btn").on("click", function () {
    const checkedItems = $(".item-checkbox:checked");

    if (checkedItems.length === 0) {
      
        showSnackBar('warm', "Vui lòng chọn ít nhất một sản phẩm để đặt hàng");
      
    }

    // Get selected item indices
    const selectedIndices = [];
    checkedItems.each(function () {
      selectedIndices.push($(this).data("item-index"));
    });

    // Store selected items in session or pass to checkout
    // For now, redirect to checkout page
    // In a real implementation, you might want to filter cart items first
    const currentPath = window.location.pathname;
    let checkoutUrl = "checkout.php";
    if (currentPath.includes("/pages/cart/")) {
      checkoutUrl = "checkout.php";
    } else {
      checkoutUrl = "pages/cart/checkout.php";
    }
    window.location.href = checkoutUrl;
  });

  // Initialize total amount on page load
  updateTotalAmount();
});
