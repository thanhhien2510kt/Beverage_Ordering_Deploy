<?php
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
if (!$orderId) {
    header('Location: ../../index.php');
    exit;
}

$user = getCurrentUser();
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT o.*, p.TenPayment FROM Orders o JOIN Payment_Method p ON o.MaPayment = p.MaPayment WHERE o.MaOrder = ? AND o.MaUser = ?");
$stmt->execute([$orderId, $user['id']]);
$order = $stmt->fetch();

if (!$order || $order['trangthai'] !== 'Pending') {
    // If order is paid or invalid, redirect
    header("Location: order_result.php?order_id={$orderId}");
    exit;
}

$paymentMethod = $order['tenpayment'];
$totalPrice = formatCurrency((float) $order['tongtien']);
$orderCode = '#' . str_pad($orderId, 9, '0', STR_PAD_LEFT);

$logoPath = '../../assets/img/payment/bank.png';
if (stripos($paymentMethod, 'momo') !== false) {
    $logoPath = 'https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png';
} elseif (stripos($paymentMethod, 'vnpay') !== false) {
    $logoPath = 'https://vinadesign.vn/uploads/images/2023/05/vnpay-logo-vinadesign-25-12-57-55.jpg';
} else {
    $logoPath = 'https://cdn-icons-png.flaticon.com/512/2830/2830284.png';
}

$basePath = '../../';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cổng Thanh Toán - MeowTea Fresh</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/main.css">
    <style>
        .payment-gateway-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 200px);
            background-color: #f8f9fa;
            padding: 40px 20px;
        }

        .payment-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            text-align: center;
        }

        .payment-header {
            background-color: #f8f9fa;
            padding: 25px 20px;
            border-bottom: 1px solid #eee;
        }

        .payment-logo {
            height: 60px;
            object-fit: contain;
            margin-bottom: 15px;
        }

        .payment-title {
            color: var(--text-dark);
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .payment-body {
            padding: 30px 20px;
        }

        .qr-placeholder {
            width: 220px;
            height: 220px;
            margin: 0 auto 25px;
            background: #fff;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 10px;
        }

        .order-details {
            background: rgba(26, 77, 46, 0.04);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 25px;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 15px;
        }

        .detail-row:last-child {
            margin-bottom: 0;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
            font-weight: bold;
            font-size: 18px;
            color: var(--primary-green);
        }

        .detail-label {
            color: var(--text-light);
        }

        .detail-value {
            color: var(--text-dark);
            font-weight: 600;
        }

        .instruction-text {
            color: var(--text-light);
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 25px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn-simulate-pay {
            background-color: var(--primary-green);
            color: white;
            border: none;
            padding: 14px 25px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-simulate-pay:hover {
            background-color: #0f3a1f;
            transform: translateY(-2px);
        }

        .btn-simulate-cancel {
            background-color: #f1f1f1;
            color: var(--text-dark);
            border: none;
            padding: 14px 25px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-simulate-cancel:hover {
            background-color: #e2e2e2;
        }

    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <?php include '../../components/header.php'; ?>

    <main class="payment-gateway-wrapper">
        <div class="payment-card">
            <div class="payment-header">

                <img src="<?php echo $logoPath; ?>" alt="<?php echo e($paymentMethod); ?>" class="payment-logo">
                <h2 class="payment-title">Thanh toán qua
                    <?php echo e($paymentMethod); ?>
                </h2>
            </div>
            <div class="payment-body">

                <div class="qr-placeholder">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=Payment_Order_<?php echo $orderId; ?>" alt="Mã QR Thanh Toán">
                </div>

                <div class="order-details">
                    <div class="detail-row">
                        <span class="detail-label">Mã đơn hàng:</span>
                        <span class="detail-value">
                            <?php echo $orderCode; ?>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Nhà cung cấp:</span>
                        <span class="detail-value">MeowTea Fresh</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Tổng tiền:</span>
                        <span class="detail-value">
                            <?php echo $totalPrice; ?>
                        </span>
                    </div>
                </div>

                <p class="instruction-text">
                    Hãy quét mã QR trên (nếu là thật) để hoàn tất thanh toán. Quá trình thanh toán có thể mất vài phút
                    để hệ thống xác nhận.
                </p>

                <div class="action-buttons">
                    <button id="btn-cancel" class="btn-simulate-cancel">Hủy Giao Dịch</button>
                    <button id="btn-success" class="btn-simulate-pay" data-id="<?php echo $orderId; ?>">Đã Thanh
                        Toán</button>
                </div>
            </div>
        </div>
    </main>

    <?php include '../../components/footer.php'; ?>
    <?php include '../../components/snack-bar.php'; ?>

    <script src="<?php echo $basePath; ?>assets/js/common.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/snack-bar.js"></script>
    <script>
        $(document).ready(function () {
            $('#btn-success').on('click', function () {
                var orderId = $(this).data('id');
                var $btn = $(this);

                $btn.prop('disabled', true).text('Đang xử lý...');

                $.ajax({
                    url: getApiPath('order/simulate_payment.php'),
                    method: 'POST',
                    data: { order_id: orderId },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            showSnackBar('success', 'Mô phỏng thanh toán thành công!');
                            setTimeout(function () {
                                window.location.href = 'order_result.php?order_id=' + orderId;
                            }, 1500);
                        } else {
                            showSnackBar('failed', response.message || 'Có lỗi xảy ra');
                            $btn.prop('disabled', false).text('Đã Thanh Toán');
                        }
                    },
                    error: function () {
                        showSnackBar('failed', 'Có lỗi kết nối. Vui lòng thử lại.');
                        $btn.prop('disabled', false).text('Đã Thanh Toán');
                    }
                });
            });

            $('#btn-cancel').on('click', function () {
                if (confirm('Bạn có chắc chắn muốn hủy thanh toán và sửa lại đơn hàng?')) {
                    var orderId = $('#btn-success').data('id');
                    var $btn = $(this);
                    $btn.prop('disabled', true).text('Đang hủy...');

                    $.ajax({
                        url: getApiPath('order/cancel_payment.php'),
                        method: 'POST',
                        data: { order_id: orderId },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                window.location.href = 'checkout.php';
                            } else {
                                showSnackBar('failed', response.message || 'Có lỗi xảy ra');
                                $btn.prop('disabled', false).text('Hủy Giao Dịch');
                            }
                        },
                        error: function () {
                            showSnackBar('failed', 'Có lỗi kết nối. Vui lòng thử lại.');
                            $btn.prop('disabled', false).text('Hủy Giao Dịch');
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>