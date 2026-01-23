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
    <title>Đăng nhập - MeowTea Fresh</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/main.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/login.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <!-- Login Section -->
    <section class="login-section">
        <div class="container">
            <div class="login-layout">
                <!-- Left: Image -->
                <div class="login-image-wrapper">
                    <img src="<?php echo $basePath; ?>assets/img/stores/stores_banner.png" alt="MeowTea Fresh Cafe" class="login-image">
                </div>

                <!-- Right: Login Form -->
                <div class="login-form-wrapper">
                    <div class="login-form-container">
                        <h1 class="login-title">Chào mừng bạn đến với MeowTeaFresh</h1>
                        <p class="login-subtitle">Hãy đăng nhập/đăng ký để tiếp tục nhé!</p>

                        <form id="loginForm" class="login-form" method="POST">
                            <!-- Username/Email Field -->
                            <div class="form-group">
                                <label for="username_or_email" class="form-label">Tên đăng nhập hoặc Email</label>
                                <input 
                                    type="text" 
                                    id="username_or_email" 
                                    name="username_or_email" 
                                    class="form-input" 
                                    placeholder="Nhập tên đăng nhập hoặc email"
                                    required
                                    autocomplete="username"
                                >
                            </div>

                            <!-- Password Field -->
                            <div class="form-group">
                                <label for="password" class="form-label">Mật khẩu</label>
                                <div class="password-input-wrapper">
                                    <input 
                                        type="password" 
                                        id="password" 
                                        name="password" 
                                        class="form-input" 
                                        placeholder="Nhập mật khẩu"
                                        required
                                        autocomplete="current-password"
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

                            <!-- Forgot Password Link -->
                            <div class="form-group form-group-inline">
                                <a href="#" class="forgot-password-link">Quên mật khẩu</a>
                            </div>

                            <!-- Error/Success Message -->
                            <div id="loginMessage" class="login-message" style="display: none;"></div>

                            <!-- Login Button -->
                            <button type="submit" class="login-btn" id="loginBtn">
                                <span class="btn-text">Đăng nhập</span>
                                <span class="btn-loading" style="display: none;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                                        <path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="0.75"/>
                                    </svg>
                                    Đang xử lý...
                                </span>
                            </button>
                        </form>

                        <!-- Social Login Divider -->
                        <div class="social-login-divider">
                            <span class="divider-line"></span>
                            <span class="divider-text">hoặc đăng nhập bằng</span>
                            <span class="divider-line"></span>
                        </div>

                        <!-- Social Login Buttons -->
                        <div class="social-login-buttons">
                            <button type="button" class="social-login-btn social-login-facebook" id="facebookLogin">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
                                </svg>
                            </button>
                            <button type="button" class="social-login-btn social-login-google" id="googleLogin">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Registration Link -->
                        <div class="register-link-wrapper">
                            <span class="register-text">Bạn chưa có tài khoản?</span>
                            <a href="register.php" class="register-link">Đăng ký</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../../components/back-to-top.php'; ?>

    <?php include '../../components/footer.php'; ?>

    <?php include '../../components/snack-bar.php'; ?>

    <script src="<?php echo $basePath; ?>assets/js/common.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/snack-bar.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/login.js"></script>
</body>
</html>