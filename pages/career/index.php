<?php
/**
 * Career Page - Tuyển dụng
 * Trang thông báo tuyển dụng
 */

require_once '../../functions.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tuyển dụng - MeowTea Fresh</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Career Page Specific Styles */
        .career-page {
            padding-top: 40px;
            padding-bottom: 60px;
            min-height: 60vh;
        }

        .career-title {
            font-size: 48px;
            font-weight: bold;
            color: var(--primary-green);
            text-align: center;
            margin-bottom: 30px;
        }

        .career-empty-state {
            max-width: 700px;
            margin: 0 auto;
            text-align: center;
            padding: 60px 20px;
        }

        .career-empty-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, var(--primary-green), var(--light-green));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .career-empty-icon svg {
            width: 60px;
            height: 60px;
            color: white;
        }

        .career-empty-message {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 20px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .career-empty-description {
            font-size: 16px;
            color: var(--text-light);
            line-height: 1.8;
            margin-bottom: 40px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .career-contact-info {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 30px;
            margin-top: 40px;
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
        }

        .career-contact-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--primary-green);
            margin-bottom: 15px;
        }

        .career-contact-text {
            font-size: 15px;
            color: var(--text-light);
            line-height: 1.8;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .career-contact-text strong {
            color: var(--text-dark);
        }

        @media (max-width: 768px) {
            .career-title {
                font-size: 36px;
            }

            .career-empty-state {
                padding: 40px 15px;
            }

            .career-empty-icon {
                width: 100px;
                height: 100px;
            }

            .career-empty-icon svg {
                width: 50px;
                height: 50px;
            }

            .career-empty-message {
                font-size: 20px;
            }

            .career-empty-description {
                font-size: 14px;
            }

            .career-contact-info {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <!-- Career Page Content -->
    <section class="career-page section">
        <div class="container">
            <h1 class="career-title">Tuyển dụng</h1>
            
            <div class="career-empty-state">
                
                <h2 class="career-empty-message">Hiện tại chưa có tin tuyển dụng nào</h2>
              
                <div class="career-contact-info">
                    <h3 class="career-contact-title">Bạn muốn gửi hồ sơ ứng tuyển?</h3>
                    <p class="career-contact-text">
                        Nếu bạn quan tâm đến cơ hội làm việc tại MeowTea Fresh, 
                        vui lòng gửi hồ sơ của bạn đến email: <strong>info@meowteafresh.com</strong> 
                        hoặc liên hệ hotline: <strong>1900 1111</strong> để được tư vấn.
                    </p>
                </div>
            </div>
        </div>
        
    </section>

    <?php include '../../components/back-to-top.php'; ?>

    <?php include '../../components/footer.php'; ?>

    <?php include '../../components/snack-bar.php'; ?>

    <script src="../../assets/js/common.js"></script>
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/snack-bar.js"></script>
    
</body>
</html>
