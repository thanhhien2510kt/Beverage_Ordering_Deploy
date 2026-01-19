/**
 * Login Page JavaScript
 * Handles password toggle, form submission, and social login
 * Requires: common.js
 */

$(document).ready(function () {
  // Password toggle visibility
  setupPasswordToggle("#passwordToggle", "#password");

  // Login form submit
  $("#loginForm").on("submit", function (e) {
    e.preventDefault();

    const $form = $(this);
    const $btn = $("#loginBtn");
    const $btnText = $btn.find(".btn-text");
    const $btnLoading = $btn.find(".btn-loading");
    const $message = $("#loginMessage");

    // Reset message
    $message.hide().removeClass("success error").text("");

    // Disable button and show loading
    $btn.prop("disabled", true);
    $btnText.hide();
    $btnLoading.show();

    // Get form data
    const formData = {
      username_or_email: $("#username_or_email").val().trim(),
      password: $("#password").val(),
    };

    // AJAX request
    $.ajax({
      url: "../../api/auth/login.php",
      method: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        if (response.success) {
          showSnackBar('success', response.message || "Đăng nhập thành công!");
          setTimeout(function () {
            window.location.href = "../../index.php";
          }, 1500);
        } else {
          showSnackBar('failed', response.message || "Đăng nhập thất bại. Vui lòng thử lại.");
          $btn.prop("disabled", false);
          $btnText.show();
          $btnLoading.hide();
        }
      },
      error: function (xhr, status, error) {
        console.error("Login error:", error);
        showSnackBar('failed', "Có lỗi xảy ra. Vui lòng thử lại sau.");
        $btn.prop("disabled", false);
        $btnText.show();
        $btnLoading.hide();
      },
    });
  });

  // Social login buttons (placeholder)
  $("#facebookLogin").on("click", function () {
    showSnackBar('information', "Tính năng đăng nhập bằng Facebook sẽ được triển khai sau.");
  });

  $("#googleLogin").on("click", function () {
    showSnackBar('information', "Tính năng đăng nhập bằng Google sẽ được triển khai sau.");
  });
});
