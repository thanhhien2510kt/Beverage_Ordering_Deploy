<?php

require_once '../../functions.php';


session_start();
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ../../index.php');
    exit;
}


$basePath = '../../';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - MeowTea Fresh</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/main.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/login.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <!-- Register Section -->
    <section class="login-section">
        <div class="container">
            <div class="login-layout">
                <!-- Left: Image -->
                <div class="login-image-wrapper">
                    <img src="<?php echo $basePath; ?>assets/img/stores/stores_banner.png" alt="MeowTea Fresh Cafe" class="login-image">
                </div>

                <!-- Right: Register Form -->
                <div class="login-form-wrapper" id="registerFormWrapper">
                    <div class="login-form-container">
                        <h1 class="login-title">Tạo tài khoản mới</h1>
                        <p class="login-subtitle">Đăng ký để nhận nhiều ưu đãi hấp dẫn từ MeowTea Fresh!</p>

                        <form id="registerForm" class="login-form" method="POST">
                            <!-- Ho and Ten (Last Name and First Name) Fields - Inline -->
                            <div class="form-group-inline-row">
                                <div class="form-group form-group-half">
                                    <label for="ho" class="form-label">Họ <span class="required">*</span></label>
                                    <input 
                                        type="text" 
                                        id="ho" 
                                        name="ho" 
                                        class="form-input" 
                                        placeholder="Nhập họ"
                                        required
                                        maxlength="50"
                                        autocomplete="family-name"
                                    >
                                </div>
                                <div class="form-group form-group-half">
                                    <label for="ten" class="form-label">Tên <span class="required">*</span></label>
                                    <input 
                                        type="text" 
                                        id="ten" 
                                        name="ten" 
                                        class="form-input" 
                                        placeholder="Nhập tên"
                                        required
                                        maxlength="50"
                                        autocomplete="given-name"
                                    >
                                </div>
                            </div>

                            <!-- DienThoai and Email Fields - Inline -->
                            <div class="form-group-inline-row">
                                <div class="form-group form-group-half">
                                    <label for="dien_thoai" class="form-label">Số điện thoại</label>
                                    <input 
                                        type="tel" 
                                        id="dien_thoai" 
                                        name="dien_thoai" 
                                        class="form-input" 
                                        placeholder="Nhập số điện thoại"
                                        maxlength="10"
                                        pattern="0[0-9]{9}"
                                        autocomplete="tel"
                                    >
                                </div>
                                <div class="form-group form-group-half">
                                    <label for="email" class="form-label">Email</label>
                                    <input 
                                        type="email" 
                                        id="email" 
                                        name="email" 
                                        class="form-input" 
                                        placeholder="Nhập email"
                                        maxlength="100"
                                        autocomplete="email"
                                    >
                                </div>
                            </div>

                            <!-- Username Field -->
                            <div class="form-group">
                                <label for="username" class="form-label">Tên đăng nhập <span class="required">*</span></label>
                                <input 
                                    type="text" 
                                    id="username" 
                                    name="username" 
                                    class="form-input" 
                                    placeholder="Nhập tên đăng nhập (3-100 ký tự, chỉ chữ, số và _)"
                                    required
                                    autocomplete="username"
                                    minlength="3"
                                    maxlength="100"
                                    pattern="[a-zA-Z0-9_]+"
                                >
                                <small class="form-hint">Chỉ được chứa chữ cái, số và dấu gạch dưới</small>
                            </div>

                            <!-- Password Field -->
                            <div class="form-group">
                                <label for="password" class="form-label">Mật khẩu <span class="required">*</span></label>
                                <div class="password-input-wrapper">
                                    <input 
                                        type="password" 
                                        id="password" 
                                        name="password" 
                                        class="form-input" 
                                        placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)"
                                        required
                                        autocomplete="new-password"
                                        minlength="6"
                                    >
                                    <button 
                                        type="button" 
                                        class="password-toggle" 
                                        id="passwordToggle"
                                        aria-label="Hiển thị mật khẩu"
                                    >
                                        <svg class="eye-icon eye-icon-hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                        <svg class="eye-icon eye-icon-visible" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                                            <line x1="1" y1="1" x2="23" y2="23"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Error/Success Message -->
                            <div id="registerMessage" class="login-message" style="display: none;"></div>

                            <!-- Register Button -->
                            <button type="submit" class="login-btn" id="registerBtn">
                                <span class="btn-text">Đăng ký</span>
                                <span class="btn-loading" style="display: none;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                                        <path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="0.75"/>
                                    </svg>
                                    Đang xử lý...
                                </span>
                            </button>
                        </form>

                        <!-- Login Link -->
                        <div class="register-link-wrapper">
                            <span class="register-text">Bạn đã có tài khoản?</span>
                            <a href="login.php" class="register-link">Đăng nhập</a>
                        </div>
                    </div>
                </div>

                <!-- OTP Verification Screen (Hidden by default) -->
                <div class="login-form-wrapper" id="otpVerifyWrapper" style="display: none;">
                    <div class="login-form-container">
                        <h1 class="login-title">Xác minh</h1>
                        <p class="login-subtitle">Nhập mã xác minh gồm 6 chữ số được gửi đến <span id="userEmail"></span></p>

                        <form id="otpForm" class="login-form">
                            <!-- OTP Input Fields -->
                            <div class="otp-input-group">
                                <input type="text" class="otp-input" maxlength="1" data-index="0" autocomplete="off">
                                <input type="text" class="otp-input" maxlength="1" data-index="1" autocomplete="off">
                                <input type="text" class="otp-input" maxlength="1" data-index="2" autocomplete="off">
                                <input type="text" class="otp-input" maxlength="1" data-index="3" autocomplete="off">
                                <input type="text" class="otp-input" maxlength="1" data-index="4" autocomplete="off">
                                <input type="text" class="otp-input" maxlength="1" data-index="5" autocomplete="off">
                            </div>

                            <!-- Resend OTP Timer -->
                            <div class="otp-resend-wrapper">
                                <span class="otp-resend-text">Gửi lại mã sau: <span id="otpTimer">60</span>s</span>
                            </div>

                            <!-- Error Message -->
                            <div id="otpMessage" class="login-message" style="display: none;"></div>

                            <!-- Verify Button -->
                            <button type="submit" class="login-btn" id="otpVerifyBtn">
                                <span class="btn-text">Đăng ký</span>
                                <span class="btn-loading" style="display: none;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                                        <path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="0.75"/>
                                    </svg>
                                    Đang xử lý...
                                </span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Success Screen (Hidden by default) -->
                <div class="login-form-wrapper" id="successWrapper" style="display: none;">
                    <div class="login-form-container success-container">
                        <h1 class="login-title">Đăng ký thành công</h1>
                        <p class="login-subtitle">Chúc mừng bạn đã đăng ký tài khoản thành công!</p>

                        <!-- Success Icon Animation -->
                        <div class="success-icon-wrapper">
                            <svg class="success-icon" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                                <!-- Coffee cup -->
                                <path class="success-cup" d="M60,80 L60,140 Q60,150 70,150 L130,150 Q140,150 140,140 L140,80 Z" fill="#1a4d2e" opacity="0.2"/>
                                <path class="success-cup" d="M60,80 L60,140 Q60,150 70,150 L130,150 Q140,150 140,140 L140,80 Z" fill="none" stroke="#1a4d2e" stroke-width="3"/>
                                <!-- Handle -->
                                <path class="success-handle" d="M140,95 Q155,95 155,110 Q155,125 140,125" fill="none" stroke="#1a4d2e" stroke-width="3"/>
                                <!-- Steam lines -->
                                <path class="success-steam steam-1" d="M75,70 Q75,55 85,55" fill="none" stroke="#f4a261" stroke-width="2" stroke-linecap="round"/>
                                <path class="success-steam steam-2" d="M100,65 Q100,50 110,50" fill="none" stroke="#f4a261" stroke-width="2" stroke-linecap="round"/>
                                <path class="success-steam steam-3" d="M125,70 Q125,55 115,55" fill="none" stroke="#f4a261" stroke-width="2" stroke-linecap="round"/>
                                <!-- Leaves decoration -->
                                <ellipse class="success-leaf leaf-1" cx="70" cy="100" rx="8" ry="12" fill="#52b788" transform="rotate(-30 70 100)"/>
                                <ellipse class="success-leaf leaf-2" cx="130" cy="110" rx="8" ry="12" fill="#52b788" transform="rotate(30 130 110)"/>
                            </svg>
                        </div>

                        <!-- Back to Login Button -->
                        <button type="button" class="login-btn" id="backToLoginBtn">
                            <span class="btn-text">Trở về đăng nhập</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../../components/back-to-top.php'; ?>

    <?php include '../../components/footer.php'; ?>

    <script src="<?php echo $basePath; ?>assets/js/common.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/snack-bar.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/register.js"></script>
</body>
</html>
