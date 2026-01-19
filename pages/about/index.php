<?php
/**
 * About Page - Về MeowTea Fresh
 * Trang giới thiệu về công ty
 */

require_once '../../functions.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Về MeowTea Fresh - MeowTea Fresh</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* About Page Specific Styles */
        .about-page {
            padding-top: 40px;
            padding-bottom: 60px;
        }

        .about-title {
            font-size: 48px;
            font-weight: bold;
            color: var(--primary-green);
            text-align: center;
            margin-bottom: 30px;
        }

        .about-intro {
            max-width: 900px;
            margin: 0 auto 50px;
            text-align: justify;
            color: var(--text-light);
            line-height: 1.8;
            font-size: 16px;
        }

        .about-intro p {
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .about-hero-image {
            width: 100%;
            max-width: 900px;
            margin: 0 auto 50px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .about-hero-image img {
            width: 100%;
            height: auto;
            display: block;
        }

        .about-content {
            max-width: 900px;
            margin: 0 auto;
            text-align: justify;
            color: var(--text-light);
            line-height: 1.8;
            font-size: 16px;
        }

        .about-content p {
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }


        @media (max-width: 768px) {
            .about-title {
                font-size: 36px;
            }

            .about-intro,
            .about-content {
                font-size: 14px;
                padding: 0 20px;
            }
        }
    </style>
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <!-- About Page Content -->
    <section class="about-page section">
        <div class="container">
            <h1 class="about-title">Về MeowTea Fresh</h1>
            
            <div class="about-intro">
                <p>
                    MeowTea Fresh là thương hiệu trà sữa và đồ uống tươi mát được yêu thích tại Việt Nam. 
                    Với cam kết mang đến những sản phẩm chất lượng cao, nguyên liệu tươi ngon và hương vị độc đáo, 
                    chúng tôi đã và đang tạo nên những trải nghiệm tuyệt vời cho khách hàng trên khắp cả nước.
                </p>
            </div>

            <div class="about-hero-image">
                <img src="../../assets/img/about/about.jpg" alt="MeowTea Fresh - Coffee Plantation">
            </div>

            <div class="about-content">
                <p>
                    Từ những ngày đầu thành lập, MeowTea Fresh đã đặt chất lượng và sự hài lòng của khách hàng lên hàng đầu. 
                    Chúng tôi tự hào sử dụng 100% nguyên liệu tự nhiên, không chất bảo quản, được tuyển chọn kỹ lưỡng từ những nhà cung cấp uy tín. 
                    Mỗi ly trà sữa, mỗi thức uống đều được pha chế tỉ mỉ bởi đội ngũ barista chuyên nghiệp, đảm bảo hương vị hoàn hảo và nhất quán. 
                    Không chỉ dừng lại ở trà sữa truyền thống, chúng tôi không ngừng sáng tạo với các dòng sản phẩm mới như matcha, fruit tea, coffee và nhiều loại topping độc đáo. 
                    MeowTea Fresh không chỉ là nơi để thưởng thức đồ uống, mà còn là không gian để bạn tận hưởng những khoảnh khắc thư giãn bên bạn bè và người thân.
                </p>
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
