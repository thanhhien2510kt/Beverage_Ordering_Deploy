<?php
header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => ''];
try {
    if (!isLoggedIn()) {
        throw new Exception('Bạn cần đăng nhập để đặt hàng');
    }

    $user = getCurrentUser();
    $userId = $user['id'];

    $storeId = isset($_POST['store_id']) ? (int) $_POST['store_id'] : 0;
    $paymentMethod = isset($_POST['payment_method']) ? (int) $_POST['payment_method'] : 0;
    $orderNote = isset($_POST['order_note']) ? trim($_POST['order_note']) : '';
    $vatInvoice = isset($_POST['vat_invoice']) ? (int) $_POST['vat_invoice'] : 0;
    $vatEmail = isset($_POST['vat_email']) ? trim($_POST['vat_email']) : '';
    $vatTaxId = isset($_POST['vat_tax_id']) ? trim($_POST['vat_tax_id']) : '';
    $vatCompany = isset($_POST['vat_company']) ? trim($_POST['vat_company']) : '';
    $vatAddress = isset($_POST['vat_address']) ? trim($_POST['vat_address']) : '';
    $promotionCode = isset($_POST['promotion_code']) ? trim($_POST['promotion_code']) : '';
    $promotionId = isset($_POST['promotion_id']) ? (int) $_POST['promotion_id'] : 0;
    $promotionDiscount = isset($_POST['promotion_discount']) ? (float) $_POST['promotion_discount'] : 0;

    if (!$storeId) {
        throw new Exception('Cửa hàng là bắt buộc');
    }

    if (!$paymentMethod) {
        throw new Exception('Phương thức thanh toán là bắt buộc');
    }

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || empty($_SESSION['cart'])) {
        throw new Exception('Giỏ hàng trống');
    }

    $cartItems = $_SESSION['cart'];

    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += isset($item['total_price']) ? (float) $item['total_price'] : 0;
    }

    $pdo = getDBConnection();

    $deliveryAddress = isset($_POST['delivery_address']) ? trim($_POST['delivery_address']) : '';
    if ($deliveryAddress === '') {
        throw new Exception('Địa chỉ giao hàng không được để trống. Vui lòng nhập địa chỉ.');
    }

    // Tính phí vận chuyển dựa trên địa chỉ
    $shippingFee = 30000;
    if ($storeId > 0) {
        $stmtStore = $pdo->prepare("SELECT Diachi FROM Store WHERE MaStore = ?");
        $stmtStore->execute([$storeId]);
        $store = $stmtStore->fetch();
        if ($store) {
            $storeAddr = mb_strtolower($store['diachi'], 'UTF-8');
            $deliveryAddr = mb_strtolower($deliveryAddress, 'UTF-8');

            // Regex tìm Quận/Huyện/Thành phố thuộc tỉnh (giả lập)
            $districtPattern = '/(quận|huyện|thị xã|tp\.)\s+([a-z0-9\sàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂ]+?)(?=,|\s|$)/iu';

            preg_match($districtPattern, $storeAddr, $storeMatch);
            preg_match($districtPattern, $deliveryAddr, $deliveryMatch);

            if (isset($storeMatch[2]) && isset($deliveryMatch[2])) {
                if (trim($storeMatch[2]) === trim($deliveryMatch[2])) {
                    $shippingFee = 15000; // Cùng quận
                }
            }
        }
    }


    $nguoiNhan = getFullName($user['ho'] ?? '', $user['ten'] ?? '');
    $dienThoaiGiao = $user['phone'] ?? '';
    if (!empty($promotionCode) && $promotionId > 0) {
        $sql = "SELECT * FROM Promotion 
                WHERE MaPromotion = ? AND Code = ? AND TrangThai = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$promotionId, $promotionCode]);
        $promotion = $stmt->fetch();

        if ($promotion) {
            $now = new DateTime();
            $isValid = true;

            if (!empty($promotion['ngaybatdau'])) {
                $startDate = new DateTime($promotion['ngaybatdau']);
                if ($now < $startDate) {
                    $isValid = false;
                }
            }

            if (!empty($promotion['ngayketthuc'])) {
                $endDate = new DateTime($promotion['ngayketthuc']);
                if ($now > $endDate) {
                    $isValid = false;
                }
            }

            if (!$isValid) {
                $promotionDiscount = 0;
                $promotionCode = '';
                $promotionId = 0;
            } else {
                $loaiGiamGia = $promotion['loaigiamgia'] ?? 'Percentage';
                $giaTri = (float) $promotion['giatri'];
                $giaTriToiDa = isset($promotion['giatritoida']) && $promotion['giatritoida'] !== null ? (float) $promotion['giatritoida'] : null;

                if ($loaiGiamGia === 'Percentage') {
                    $promotionDiscount = ($subtotal * $giaTri) / 100;

                    if ($giaTriToiDa !== null && $giaTriToiDa > 0) {
                        if ($promotionDiscount > $giaTriToiDa) {
                            $promotionDiscount = $giaTriToiDa;
                        }
                    }

                    if ($promotionDiscount > $subtotal) {
                        $promotionDiscount = $subtotal;
                    }
                } else {
                    $promotionDiscount = $giaTri;
                    if ($promotionDiscount > $subtotal) {
                        $promotionDiscount = $subtotal;
                    }
                }
            }
        } else {
            $promotionDiscount = 0;
            $promotionCode = '';
            $promotionId = 0;
        }
    } else {
        $promotionDiscount = 0;
    }

    $totalAmount = $subtotal + $shippingFee - $promotionDiscount;

    $pdo->beginTransaction();

    try {
        $status = ($paymentMethod == 1) ? 'Payment_Received' : 'Pending';
        $sql = "INSERT INTO Orders (MaUser, MaStore, MaPayment, DiaChiGiao, NguoiNhan, DienThoaiGiao, PhiVanChuyen, MaPromotion, GiamGia, TongTien, TrangThai) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $promotionIdForDB = ($promotionId > 0 && !empty($promotionCode)) ? $promotionId : null;
        $stmt->execute([$userId, $storeId, $paymentMethod, $deliveryAddress, $nguoiNhan, $dienThoaiGiao, $shippingFee, $promotionIdForDB, $promotionDiscount, $totalAmount, $status]);
        $orderId = $pdo->lastInsertId();
        $_SESSION['order_payment_' . $orderId] = $paymentMethod;

        foreach ($cartItems as $item) {
            $productId = isset($item['product_id']) ? (int) $item['product_id'] : 0;
            $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 1;
            $basePrice = isset($item['base_price']) ? (float) $item['base_price'] : 0;

            if (!$productId) {
                continue;
            }

            $sql = "INSERT INTO Order_Item (MaOrder, MaSP, SoLuong, GiaNiemYet) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$orderId, $productId, $quantity, $basePrice]);
            $orderItemId = $pdo->lastInsertId();

            if (isset($item['options']) && is_array($item['options'])) {
                foreach ($item['options'] as $option) {
                    $optionValueId = 0;
                    if (isset($option['value_id'])) {
                        $optionValueId = (int) $option['value_id'];
                    } elseif (isset($option['option_value_id'])) {
                        $optionValueId = (int) $option['option_value_id'];
                    }

                    if ($optionValueId > 0) {
                        $optionPrice = isset($option['price']) ? (float) $option['price'] : 0;

                        $sql = "INSERT INTO Order_Item_Option (MaOrderItem, MaOptionValue, GiaThem) 
                                VALUES (?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$orderItemId, $optionValueId, $optionPrice]);
                    }
                }
            }
        }

        $stmt = $pdo->prepare("SELECT MaCart FROM Cart WHERE MaUser = ?");
        $stmt->execute([$userId]);
        $cartIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($cartIds)) {
            $placeholders = implode(',', array_fill(0, count($cartIds), '?'));

            $stmt = $pdo->prepare("
                DELETE FROM Cart_Item_Option 
                WHERE MaCartItem IN (
                    SELECT MaCartItem FROM Cart_Item WHERE MaCart IN ($placeholders)
                )
            ");
            $stmt->execute($cartIds);

            $stmt = $pdo->prepare("DELETE FROM Cart_Item WHERE MaCart IN ($placeholders)");
            $stmt->execute($cartIds);

            $stmt = $pdo->prepare("DELETE FROM Cart WHERE MaCart IN ($placeholders)");
            $stmt->execute($cartIds);
        }

        $pdo->commit();

        $_SESSION['cart'] = [];

        $orderCode = '#' . str_pad($orderId, 9, '0', STR_PAD_LEFT);

        $response = [
            'success' => true,
            'message' => 'Đặt hàng thành công',
            'order_id' => $orderId,
            'order_code' => $orderCode,
            'total_amount' => $totalAmount,
            'payment_method' => $paymentMethod
        ];

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
