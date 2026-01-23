/**
 * Common Utility Functions
 * Shared JavaScript utilities for MeowTea Fresh
 */


/**
 * Get API path based on current page location
 * @param {string} endpoint - API endpoint (e.g., "cart/add.php")
 * @param {string} basePath - Base API path (default: "api/")
 * @returns {string} Full API path
 */
function getApiPath(endpoint, basePath = "api/") {
  const currentPath = window.location.pathname;
  let apiPath = basePath + endpoint;

  if (currentPath.includes("/pages/")) {
    const pathParts = currentPath.split("/").filter((p) => p);
    const pagesIndex = pathParts.indexOf("pages");
    if (pagesIndex >= 0) {
      const levels = pathParts.length - pagesIndex - 1;
      apiPath = "../".repeat(levels) + apiPath;
    }
  }

  return apiPath;
}

/**
 * Get API base path for management endpoints
 * @returns {string} API base path
 */
function getApiBasePath() {
  return getApiPath("", "api/management/");
}


/**
 * Format currency to Vietnamese format
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
  return new Intl.NumberFormat("vi-VN").format(amount) + "₫";
}

/**
 * Format currency with currency style (for management pages)
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrencyWithStyle(amount) {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
    minimumFractionDigits: 0,
  }).format(amount);
}

/**
 * Escape HTML to prevent XSS attacks
 * @param {string} text - Text to escape
 * @returns {string} Escaped HTML string
 */
function escapeHtml(text) {
  if (!text) return "";
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  };
  return String(text).replace(/[&<>"']/g, function (m) {
    return map[m];
  });
}


/**
 * Update cart count in header
 */
function updateCartCount() {
  $.ajax({
    url: getApiPath("cart/count.php"),
    method: "GET",
    dataType: "json",
    success: function (response) {
      if (response.success) {
        $(".cart-count").text(response.count || 0);
      }
    },
    error: function () {

    },
  });
}


/**
 * Show alert message
 * @param {string} message - Alert message
 * @param {string} type - Alert type: "success" or "error"
 * @param {string} containerSelector - Container selector (default: ".management-content")
 * @param {number} autoHideDelay - Auto hide delay in ms (default: 5000)
 */
function showAlert(
  message,
  type,
  containerSelector = ".management-content",
  autoHideDelay = 5000
) {

  $(".alert").remove();

  const alertClass = type === "success" ? "alert-success" : "alert-error";
  const $alert = $(
    '<div class="alert ' + alertClass + '">' + escapeHtml(message) + "</div>"
  );


  const $container = $(containerSelector);
  if ($container.length > 0) {
    $container.prepend($alert);
  } else {

    $("body").prepend($alert);
  }


  if (autoHideDelay > 0) {
    setTimeout(function () {
      $alert.fadeOut(function () {
        $(this).remove();
      });
    }, autoHideDelay);
  }
}


/**
 * Setup password toggle visibility
 * @param {string} toggleId - Toggle button selector
 * @param {string} inputId - Password input selector
 */
function setupPasswordToggle(toggleId, inputId) {
  $(toggleId).on("click", function () {
    const passwordInput = $(inputId);
    const hiddenIcon = $(this).find(".eye-icon-hidden");
    const visibleIcon = $(this).find(".eye-icon-visible");

    if (passwordInput.attr("type") === "password") {
      passwordInput.attr("type", "text");
      hiddenIcon.hide();
      visibleIcon.show();
    } else {
      passwordInput.attr("type", "password");
      hiddenIcon.show();
      visibleIcon.hide();
    }
  });
}


/**
 * Handle form submission with loading state
 * @param {Object} options - Configuration options
 * @param {string} options.formSelector - Form selector
 * @param {string} options.buttonSelector - Submit button selector
 * @param {string} options.messageSelector - Message container selector
 * @param {string} options.apiUrl - API endpoint URL
 * @param {Function} options.onSuccess - Success callback
 * @param {Function} options.onError - Error callback
 * @param {Function} options.getFormData - Function to get form data
 */
function handleFormSubmission(options) {
  const {
    formSelector,
    buttonSelector,
    messageSelector,
    apiUrl,
    onSuccess,
    onError,
    getFormData,
  } = options;

  $(formSelector).on("submit", function (e) {
    e.preventDefault();

    const $form = $(this);
    const $btn = $(buttonSelector);
    const $btnText = $btn.find(".btn-text");
    const $btnLoading = $btn.find(".btn-loading");
    const $message = $(messageSelector);


    $message.hide().removeClass("success error").text("");


    $btn.prop("disabled", true);
    $btnText.hide();
    $btnLoading.show();


    const formData = getFormData ? getFormData($form) : $form.serialize();


    $.ajax({
      url: apiUrl,
      method: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        if (response.success) {
          if (onSuccess) {
            onSuccess(response, $form, $btn, $btnText, $btnLoading, $message);
          } else {
            $message
              .addClass("success")
              .text(response.message || "Thành công!")
              .show();
            $btn.prop("disabled", false);
            $btnText.show();
            $btnLoading.hide();
          }
        } else {
          if (onError) {
            onError(response, $form, $btn, $btnText, $btnLoading, $message);
          } else {
            $message
              .addClass("error")
              .text(response.message || "Có lỗi xảy ra. Vui lòng thử lại.")
              .show();
            $btn.prop("disabled", false);
            $btnText.show();
            $btnLoading.hide();
          }
        }
      },
      error: function (xhr, status, error) {
        console.error("Form submission error:", error);
        let errorMessage = "Có lỗi xảy ra. Vui lòng thử lại sau.";


        if (xhr.responseText) {
          try {
            const errorResponse = JSON.parse(xhr.responseText);
            if (errorResponse.message) {
              errorMessage = errorResponse.message;
            }
          } catch (e) {

          }
        }

        if (onError) {
          onError(
            { success: false, message: errorMessage },
            $form,
            $btn,
            $btnText,
            $btnLoading,
            $message
          );
        } else {
          $message.addClass("error").text(errorMessage).show();
          $btn.prop("disabled", false);
          $btnText.show();
          $btnLoading.hide();
        }
      },
    });
  });
}


/**
 * Debounce function to limit function calls
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}


/**
 * Handle window resize with debounce
 * @param {Function} callback - Callback function
 * @param {number} delay - Delay in milliseconds (default: 250)
 * @returns {Function} Resize handler function
 */
function handleResize(callback, delay = 250) {
  let resizeTimeout;
  return function () {
    if (resizeTimeout) {
      clearTimeout(resizeTimeout);
    }
    resizeTimeout = setTimeout(callback, delay);
  };
}
