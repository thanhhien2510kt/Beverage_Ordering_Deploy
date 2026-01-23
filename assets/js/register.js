/**
 * Register Page JavaScript
 * Handles password toggle, validation, OTP verification, and form submission
 * Requires: common.js
 */

// Global variables for OTP
let otpTimer = null;
let otpCountdown = 60;
let registrationData = null;

$(document).ready(function () {
  // Password toggle visibility
  setupPasswordToggle("#passwordToggle", "#password");

  // Register form submit
  $("#registerForm").on("submit", function (e) {
    e.preventDefault();

    const $form = $(this);
    const $btn = $("#registerBtn");
    const $btnText = $btn.find(".btn-text");
    const $btnLoading = $btn.find(".btn-loading");
    const $message = $("#registerMessage");

    // Reset message
    $message.hide().removeClass("success error").text("");

    // Disable button and show loading
    $btn.prop("disabled", true);
    $btnText.hide();
    $btnLoading.show();

    // Get form data
    const formData = {
      username: $("#username").val().trim(),
      password: $("#password").val(),
      ho: $("#ho").val().trim(),
      ten: $("#ten").val().trim(),
      dien_thoai: $("#dien_thoai").val().trim() || null,
      email: $("#email").val().trim() || null,
    };

    // Store registration data for later use
    registrationData = formData;

    // Validate registration data (client-side)
    if (!formData.username || !formData.password || !formData.ho || !formData.ten) {
      showSnackBar("failed", "Vui lòng điền đầy đủ thông tin bắt buộc.");
      $btn.prop("disabled", false);
      $btnText.show();
      $btnLoading.hide();
      return;
    }

    // Show OTP verification screen
    showOTPScreen(formData.email || "abc***@gmail.com");
    
    // Re-enable button
    $btn.prop("disabled", false);
    $btnText.show();
    $btnLoading.hide();
  });

  // OTP input handling
  setupOTPInputs();

  // OTP form submit
  $("#otpForm").on("submit", function (e) {
    e.preventDefault();
    verifyOTP();
  });

  // Back to login button
  $("#backToLoginBtn").on("click", function () {
    window.location.href = "login.php";
  });
});

/**
 * Show OTP verification screen
 */
function showOTPScreen(email) {
  // Hide register form
  $("#registerFormWrapper").hide();
  
  // Show OTP screen
  $("#otpVerifyWrapper").show();
  
  // Set email display
  $("#userEmail").text(email);
  
  // Start countdown timer
  startOTPTimer();
  
  // Focus first OTP input
  $(".otp-input").first().focus();
}

/**
 * Setup OTP inputs behavior
 */
function setupOTPInputs() {
  const $otpInputs = $(".otp-input");

  $otpInputs.on("input", function () {
    const $this = $(this);
    const value = $this.val();
    
    // Only allow numbers
    if (!/^\d*$/.test(value)) {
      $this.val("");
      return;
    }

    // Remove error class
    $this.removeClass("error");

    // Move to next input if value entered
    if (value.length === 1) {
      const index = parseInt($this.data("index"));
      if (index < 5) {
        $otpInputs.eq(index + 1).focus();
      }
    }
  });

  $otpInputs.on("keydown", function (e) {
    const $this = $(this);
    const index = parseInt($this.data("index"));

    // Handle backspace
    if (e.key === "Backspace") {
      if ($this.val() === "" && index > 0) {
        $otpInputs.eq(index - 1).focus();
      }
    }
  });

  // Handle paste
  $otpInputs.first().on("paste", function (e) {
    e.preventDefault();
    const pastedData = e.originalEvent.clipboardData.getData("text");
    const digits = pastedData.replace(/\D/g, "").slice(0, 6);
    
    digits.split("").forEach((digit, index) => {
      $otpInputs.eq(index).val(digit);
    });
    
    if (digits.length === 6) {
      $otpInputs.last().focus();
    }
  });
}

/**
 * Start OTP countdown timer
 */
function startOTPTimer() {
  otpCountdown = 60;
  updateTimerDisplay();

  otpTimer = setInterval(function () {
    otpCountdown--;
    updateTimerDisplay();

    if (otpCountdown <= 0) {
      clearInterval(otpTimer);
      showResendLink();
    }
  }, 1000);
}

/**
 * Update timer display
 */
function updateTimerDisplay() {
  $("#otpTimer").text(otpCountdown);
}

/**
 * Show resend OTP link
 */
function showResendLink() {
  $(".otp-resend-wrapper").html(
    '<a href="#" class="otp-resend-link" id="resendOTPLink">Gửi lại mã</a>'
  );

  $("#resendOTPLink").on("click", function (e) {
    e.preventDefault();
    resendOTP();
  });
}

/**
 * Resend OTP
 */
function resendOTP() {
  // Clear all OTP inputs
  $(".otp-input").val("").removeClass("error");
  
  // Hide message
  $("#otpMessage").hide().removeClass("success error").text("");
  
  // Restart timer
  $(".otp-resend-wrapper").html(
    '<span class="otp-resend-text">Gửi lại mã sau: <span id="otpTimer">60</span>s</span>'
  );
  startOTPTimer();
  
  // Focus first input
  $(".otp-input").first().focus();
}

/**
 * Verify OTP
 */
function verifyOTP() {
  const $btn = $("#otpVerifyBtn");
  const $btnText = $btn.find(".btn-text");
  const $btnLoading = $btn.find(".btn-loading");
  const $message = $("#otpMessage");
  const $otpInputs = $(".otp-input");

  // Get OTP value
  let otpValue = "";
  $otpInputs.each(function () {
    otpValue += $(this).val();
  });

  // Reset message
  $message.hide().removeClass("success error").text("");

  // Validate OTP length
  if (otpValue.length !== 6) {
    showSnackBar("failed", "Vui lòng nhập đầy đủ 6 chữ số.");
    $otpInputs.addClass("error");
    return;
  }

  // Disable button and show loading
  $btn.prop("disabled", true);
  $btnText.hide();
  $btnLoading.show();

  // Verify OTP (hardcoded as 123456)
  if (otpValue === "123456") {
    // OTP is correct, proceed with registration
    registerUser();
  } else {
    // OTP is incorrect
    showSnackBar("failed", "Mã xác minh không chính xác. Vui lòng thử lại.");
    $otpInputs.addClass("error").val("");
    $otpInputs.first().focus();

    $btn.prop("disabled", false);
    $btnText.show();
    $btnLoading.hide();
  }
}

/**
 * Register user after OTP verification
 */
function registerUser() {
  const $btn = $("#otpVerifyBtn");
  const $btnText = $btn.find(".btn-text");
  const $btnLoading = $btn.find(".btn-loading");
  const $message = $("#otpMessage");

  // AJAX request to register
  $.ajax({
    url: "../../api/auth/register.php",
    method: "POST",
    data: registrationData,
    dataType: "json",
    success: function (response) {
      if (response.success) {
        // Stop timer
        if (otpTimer) {
          clearInterval(otpTimer);
        }

        // Show success screen
        showSuccessScreen();
      } else {
        var msg = response.message || "Đăng ký thất bại. Vui lòng thử lại.";
        var type = (msg.indexOf("Không tìm thấy role") !== -1) ? "warm" : "failed";
        showSnackBar(type, msg);
        $btn.prop("disabled", false);
        $btnText.show();
        $btnLoading.hide();
      }
    },
    error: function (xhr, status, error) {
      console.error("Register error:", error);
      let errorMessage = "Có lỗi xảy ra. Vui lòng thử lại sau.";
      if (xhr.responseText) {
        try {
          const errorResponse = JSON.parse(xhr.responseText);
          if (errorResponse.message) {
            errorMessage = errorResponse.message;
          }
        } catch (e) {}
      }
      showSnackBar("failed", errorMessage);
      $btn.prop("disabled", false);
      $btnText.show();
      $btnLoading.hide();
    },
  });
}

/**
 * Show success screen
 */
function showSuccessScreen() {
  // Hide OTP screen
  $("#otpVerifyWrapper").hide();
  
  // Show success screen
  $("#successWrapper").show();
}
