<?php

require_once __DIR__ . '/database/config.php';

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . '₫';
}

function getProductsByCategory($categoryId = null, $limit = null) {
    $pdo = getDBConnection();
    $sql = "SELECT sp.*, c.TenCategory 
            FROM SanPham sp 
            INNER JOIN Category c ON sp.MaCategory = c.MaCategory 
            WHERE sp.TrangThai = 1";
    
    $params = [];
    if ($categoryId) {
        $sql .= " AND sp.MaCategory = ?";
        $params[] = $categoryId;
    }
    
    $sql .= " ORDER BY sp.MaSP DESC";
    
    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getCategories() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM Category WHERE TrangThai = 1 ORDER BY TenCategory");
    return $stmt->fetchAll();
}

function getStores($limit = null) {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM Store WHERE TrangThai = 1 ORDER BY TenStore";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function searchStoresByName($keyword) {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM Store WHERE TrangThai = 1";
    
    $params = [];
    if (!empty($keyword)) {
        $sql .= " AND TenStore ILIKE ?";
        $params[] = "%{$keyword}%";
    }
    
    $sql .= " ORDER BY TenStore";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function searchStoresByLocation($province = null, $ward = null) {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM Store WHERE TrangThai = 1";
    
    $params = [];
    if (!empty($province)) {
        $sql .= " AND DiaChi ILIKE ?";
        $params[] = "%{$province}%";
    }
    if (!empty($ward)) {
        $sql .= " AND DiaChi ILIKE ?";
        $params[] = "%{$ward}%";
    }
    
    $sql .= " ORDER BY TenStore";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getStoresWithFilters($keyword = null, $province = null, $ward = null) {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM Store WHERE TrangThai = 1";
    
    $params = [];
    if (!empty($keyword)) {
        $sql .= " AND TenStore ILIKE ?";
        $params[] = "%{$keyword}%";
    }
    if (!empty($province)) {
        $sql .= " AND DiaChi ILIKE ?";
        $params[] = "%{$province}%";
    }
    if (!empty($ward)) {
        $sql .= " AND DiaChi ILIKE ?";
        $params[] = "%{$ward}%";
    }
    
    $sql .= " ORDER BY TenStore";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function countStores($keyword = null, $province = null, $ward = null) {
    $pdo = getDBConnection();
    $sql = "SELECT COUNT(*) as total FROM Store WHERE TrangThai = 1";
    
    $params = [];
    if (!empty($keyword)) {
        $sql .= " AND TenStore ILIKE ?";
        $params[] = "%{$keyword}%";
    }
    if (!empty($province)) {
        $sql .= " AND DiaChi ILIKE ?";
        $params[] = "%{$province}%";
    }
    if (!empty($ward)) {
        $sql .= " AND DiaChi ILIKE ?";
        $params[] = "%{$ward}%";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

function getPaymentMethods() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM Payment_Method ORDER BY MaPayment");
    return $stmt->fetchAll();
}

function getNews($limit = null) {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM News WHERE TrangThai = 1 ORDER BY NgayTao DESC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getNewsById($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM News WHERE MaNews = ? AND TrangThai = 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function readMarkdownFile($filePath) {
    $rootPath = realpath(__DIR__);
    $fullPath = $rootPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $filePath);
    
    if (file_exists($fullPath)) {
        return file_get_contents($fullPath);
    }
    return '';
}

function getMarkdownExcerpt($markdownContent, $length = 150) {
    if (empty($markdownContent)) {
        return 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...';
    }
    

    $text = preg_replace('/^#{1,6}\s+/m', '', $markdownContent);
    

    $text = preg_replace('/!\[.*?\]\(.*?\)/', '', $text);
    

    $text = preg_replace('/\[([^\]]+)\]\([^\)]+\)/', '$1', $text);
    

    $text = preg_replace('/\*\*([^\*]+)\*\*/', '$1', $text);
    $text = preg_replace('/\*([^\*]+)\*/', '$1', $text);
    

    $text = preg_replace('/^---$/m', '', $text);
    

    $text = strip_tags($text);
    

    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    

    if (mb_strlen($text) > $length) {
        $text = mb_substr($text, 0, $length);

        $lastSpace = mb_strrpos($text, ' ');
        if ($lastSpace !== false) {
            $text = mb_substr($text, 0, $lastSpace);
        }
        $text .= '...';
    }
    
    return $text;
}

function getNewsExcerpt($markdownPath, $length = 150) {
    $content = readMarkdownFile($markdownPath);
    return getMarkdownExcerpt($content, $length);
}

function getProductById($productId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT sp.*, c.TenCategory 
                          FROM SanPham sp 
                          INNER JOIN Category c ON sp.MaCategory = c.MaCategory 
                          WHERE sp.MaSP = ? AND sp.TrangThai = 1");
    $stmt->execute([$productId]);
    return $stmt->fetch();
}

function getProductOptions($productId) {
    $pdo = getDBConnection();
    $sql = "SELECT og.MaOptionGroup, og.TenNhom, og.IsMultiple,
                   ov.MaOptionValue, ov.TenGiaTri, ov.GiaThem, ov.HinhAnh
            FROM Product_Option_Group pog
            INNER JOIN Option_Group og ON pog.MaOptionGroup = og.MaOptionGroup
            INNER JOIN Option_Value ov ON og.MaOptionGroup = ov.MaOptionGroup
            WHERE pog.MaSP = ?
            ORDER BY og.MaOptionGroup, ov.MaOptionValue";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function enrichCartOptions($options) {
    if (empty($options) || !is_array($options)) {
        return [];
    }
    
    $pdo = getDBConnection();
    $enrichedOptions = [];
    
    foreach ($options as $option) {
        $optionValueId = isset($option['option_value_id']) ? (int)$option['option_value_id'] : 0;
        if (!$optionValueId) {
            continue;
        }
        

        $sql = "SELECT ov.MaOptionValue, ov.TenGiaTri, ov.GiaThem,
                       og.MaOptionGroup, og.TenNhom, og.IsMultiple
                FROM Option_Value ov
                INNER JOIN Option_Group og ON ov.MaOptionGroup = og.MaOptionGroup
                WHERE ov.MaOptionValue = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$optionValueId]);
        $optionDataRaw = $stmt->fetch();
        
        if ($optionDataRaw) {
            $optionData = array_change_key_case($optionDataRaw, CASE_LOWER);
            $enrichedOptions[] = [
                'option_value_id' => $optionValueId,
                'value_name' => $optionData['tengiatri'] ?? $optionDataRaw['tengiatri'] ?? '',
                'group_name' => $optionData['tennhom'] ?? $optionDataRaw['tennhom'] ?? '',
                'IsMultiple' => (bool)($optionData['ismultiple'] ?? $optionDataRaw['ismultiple'] ?? false),
                'price' => isset($option['price']) ? (float)$option['price'] : (float)($optionData['giathem'] ?? $optionDataRaw['giathem'] ?? 0)
            ];
        } else {

            $enrichedOptions[] = [
                'option_value_id' => $optionValueId,
                'value_name' => isset($option['value_name']) ? $option['value_name'] : '',
                'group_name' => isset($option['group_name']) ? $option['group_name'] : '',
                'IsMultiple' => isset($option['ismultiple']) ? (bool)$option['ismultiple'] : false,
                'price' => isset($option['price']) ? (float)$option['price'] : 0
            ];
        }
    }
    
    return $enrichedOptions;
}

function searchProducts($keyword, $categoryId = null, $page = 1, $perPage = 12) {
    $pdo = getDBConnection();
    $offset = ($page - 1) * $perPage;
    
    $sql = "SELECT sp.*, c.TenCategory 
            FROM SanPham sp 
            INNER JOIN Category c ON sp.MaCategory = c.MaCategory 
            WHERE sp.TrangThai = 1";
    
    $params = [];
    
    if (!empty($keyword)) {
        $sql .= " AND sp.TenSP ILIKE ?";
        $params[] = "%{$keyword}%";
    }
    
    if ($categoryId) {
        $sql .= " AND sp.MaCategory = ?";
        $params[] = $categoryId;
    }
    
    $sql .= " ORDER BY sp.MaSP DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function countProducts($keyword = null, $categoryId = null) {
    $pdo = getDBConnection();
    $sql = "SELECT COUNT(*) as total 
            FROM SanPham sp 
            WHERE sp.TrangThai = 1";
    
    $params = [];
    
    if (!empty($keyword)) {
        $sql .= " AND sp.TenSP ILIKE ?";
        $params[] = "%{$keyword}%";
    }
    
    if ($categoryId) {
        $sql .= " AND sp.MaCategory = ?";
        $params[] = $categoryId;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

function getCategoryIcon($categoryName) {
    $icons = [
        'Cà phê truyền thống' => 'coffee',
        'Trà sữa' => 'milk-tea',
        'Trà trái cây' => 'fruit-tea',
        'Đá xay' => 'blended',
        'Yogurt' => 'yogurt',
        'Topping' => 'topping'
    ];
    return $icons[$categoryName] ?? 'default';
}

function normalizeImagePath($imagePath, $currentDir = null) {
    if (empty($imagePath)) {
        return 'assets/img/products/product_one.png';
    }
    

    if (strpos($imagePath, '/') === 0) {
        return $imagePath;
    }
    

    if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
        return $imagePath;
    }
    

    if ($currentDir === null) {

        return $imagePath;
    }
    

    $rootPath = realpath(__DIR__);
    $currentPath = realpath($currentDir);
    
    if ($currentPath && strpos($currentPath, $rootPath) === 0) {

        $relativePath = str_replace($rootPath, '', $currentPath);
        $levels = substr_count($relativePath, DIRECTORY_SEPARATOR);
        
        if ($levels > 0) {
            $prefix = str_repeat('../', $levels);
            return $prefix . $imagePath;
        }
    }
    
    return $imagePath;
}

function getImagePath($imagePath) {
    if (empty($imagePath)) {
        return 'assets/img/products/product_one.png';
    }
    

    if (strpos($imagePath, '/') === 0) {
        return $imagePath;
    }
    

    if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
        return $imagePath;
    }
    


    $imagePath = ltrim($imagePath, './');
    
    return $imagePath;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {

    if (strpos($hash, '$2y$') === 0) {
        return password_verify($password, $hash);
    }

    return ($password === $hash);
}

function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'ho' => $_SESSION['user_ho'] ?? null,
        'ten' => $_SESSION['user_ten'] ?? null,
        'name' => $_SESSION['user_name'] ?? null,
        'gioi_tinh' => $_SESSION['user_gioi_tinh'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'phone' => $_SESSION['user_phone'] ?? null,
        'role' => $_SESSION['user_role'] ?? null,
        'role_name' => $_SESSION['user_role_name'] ?? null
    ];
}

function getFullName($ho, $ten) {
    return trim($ho . ' ' . $ten);
}

function logout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    session_destroy();
}

function getAvatarInitial($name) {
    if (empty($name)) {
        return 'U';
    }
    

    $name = trim($name);
    $firstChar = mb_substr($name, 0, 1, 'UTF-8');
    

    return mb_strtoupper($firstChar, 'UTF-8');
}

function getAvatarInitialFromName($ho, $ten) {

    if (!empty($ten)) {
        return getAvatarInitial($ten);
    }
    if (!empty($ho)) {
        return getAvatarInitial($ho);
    }
    return 'U';
}

function getAvatarImagePath($gioiTinh, $basePath = '') {
    $avatarFile = 'o.png'; // Default for Other or null
    
    if ($gioiTinh === 'M') {
        $avatarFile = 'm.jpg';
    } elseif ($gioiTinh === 'F') {
        $avatarFile = 'f.jpg';
    } elseif ($gioiTinh === 'O') {
        $avatarFile = 'o.png';
    }
    
    return $basePath . 'assets/img/avatar/' . $avatarFile;
}

function getBestSellerProducts($limit = 8) {
    $db = getDBConnection(); 
    $sql = "
        SELECT sp.*, c.TenCategory
        FROM SanPham sp
        INNER JOIN Category c ON sp.MaCategory = c.MaCategory
        WHERE sp.TrangThai = 1 
          AND sp.Rating IS NOT NULL
          AND sp.SoLuotRating > 0
        ORDER BY sp.Rating DESC, sp.SoLuotRating DESC
        LIMIT :limit
    ";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function renderStars($rating) {

    $rating = max(0, min(5, (float)$rating));
    
    $fullStars = floor($rating); // Số sao đầy
    $hasHalfStar = ($rating - $fullStars) >= 0.5; // Có nửa sao không (>= 0.5)
    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0); // Số sao rỗng
    
    $stars = str_repeat('★', $fullStars); // Sao đầy
    if ($hasHalfStar) {
        $stars .= '☆'; // Nửa sao (hiển thị như sao rỗng, có thể dùng CSS để style)
    }
    $stars .= str_repeat('☆', $emptyStars); // Sao rỗng
    
    return $stars;
}

function getToppings() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT ov.MaOptionValue, ov.TenGiaTri, ov.GiaThem, ov.HinhAnh
                        FROM Option_Value ov
                        INNER JOIN Option_Group og ON ov.MaOptionGroup = og.MaOptionGroup
                        WHERE og.MaOptionGroup = 3
                        ORDER BY ov.MaOptionValue");
    $toppings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    $defaultToppingImage = 'assets/img/products/topping/topping-tranchau.png';
    

    $formattedToppings = [];
    foreach ($toppings as $topping) {
        // Sử dụng maoptionvalue hoặc MaOptionValue tùy thuộc vào cấu hình PDO của server
        $ovId = $topping['maoptionvalue'] ?? $topping['MaOptionValue'] ?? 0;
        $name = $topping['tengiatri'] ?? $topping['TenGiaTri'] ?? '';
        $extraPrice = $topping['giathem'] ?? $topping['GiaThem'] ?? 0;
        $img = $topping['hinhanh'] ?? $topping['HinhAnh'] ?? $defaultToppingImage;

        $formattedToppings[] = [
            'masp' => 'topping_' . $ovId, // Prefix để phân biệt với sản phẩm thật
            'tensp' => $name,
            'giacoban' => $extraPrice,
            'hinhanh' => $img,
            'rating' => 4.5, // Default rating
            'soluotrating' => 0,
            'macategory' => 0, // Special category for topping
            'tencategory' => 'Topping',
            'istopping' => true // Flag to identify as topping
        ];
    }
    
    return $formattedToppings;
}

function saveCartToDB($userId, $storeId) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        error_log("saveCartToDB: Cart is empty, nothing to save");
        return true; // Nothing to save
    }

    error_log("saveCartToDB: Starting save for User ID: $userId, Store ID: $storeId, Items: " . count($_SESSION['cart']));

    $pdo = getDBConnection();
    
    try {
        $pdo->beginTransaction();
        

        $stmt = $pdo->prepare("SELECT MaCart FROM Cart WHERE MaUser = ? AND MaStore = ?");
        $stmt->execute([$userId, $storeId]);
        $existingCart = $stmt->fetch();
        
        if ($existingCart) {
            $cartId = $existingCart['macart'];
            error_log("saveCartToDB: Found existing cart ID: $cartId");

            $stmt = $pdo->prepare("DELETE FROM Cart_Item WHERE MaCart = ?");
            $stmt->execute([$cartId]);
        } else {

            error_log("saveCartToDB: Creating new cart");
            $stmt = $pdo->prepare("INSERT INTO Cart (MaUser, MaStore, NgayTao) VALUES (?, ?, NOW())");
            $stmt->execute([$userId, $storeId]);
            $cartId = $pdo->lastInsertId();
            error_log("saveCartToDB: Created new cart ID: $cartId");
        }
        

        foreach ($_SESSION['cart'] as $index => $item) {
            error_log("saveCartToDB: Processing item $index - Product ID: " . ($item['product_id'] ?? 'N/A'));
            

            $note = isset($item['note']) ? $item['note'] : null;
            $stmt = $pdo->prepare("INSERT INTO Cart_Item (MaCart, MaSP, SoLuong, GiaNiemYet, GhiChu) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $cartId,
                $item['product_id'],
                $item['quantity'],
                $item['base_price'],
                $note
            ]);
            $cartItemId = $pdo->lastInsertId();
            error_log("saveCartToDB: Inserted cart item ID: $cartItemId");
            

            if (!empty($item['options'])) {
                error_log("saveCartToDB: Item has " . count($item['options']) . " options");
                foreach ($item['options'] as $option) {
                    $stmt = $pdo->prepare("INSERT INTO Cart_Item_Option (MaCartItem, MaOptionValue, GiaThem) VALUES (?, ?, ?)");
                    $stmt->execute([
                        $cartItemId,
                        $option['option_value_id'],
                        $option['price']
                    ]);
                }
            }
        }
        
        $pdo->commit();
        error_log("saveCartToDB: Successfully saved cart to DB");
        return true;
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("saveCartToDB ERROR: " . $e->getMessage());
        error_log("saveCartToDB ERROR Trace: " . $e->getTraceAsString());
        return false;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("saveCartToDB PDO ERROR: " . $e->getMessage());
        error_log("saveCartToDB PDO ERROR Code: " . $e->getCode());
        return false;
    }
}

function loadCartFromDB($userId, $storeId) {
    $pdo = getDBConnection();
    
    try {

        $stmt = $pdo->prepare("SELECT MaCart FROM Cart WHERE MaUser = ? AND MaStore = ?");
        $stmt->execute([$userId, $storeId]);
        $cart = $stmt->fetch();
        
        if (!$cart) {
            return true; // No cart in database
        }
        
        $cartId = $cart['macart'];
        

        $stmt = $pdo->prepare("
            SELECT ci.*, sp.TenSP, sp.HinhAnh, sp.GiaCoBan
            FROM Cart_Item ci
            INNER JOIN SanPham sp ON ci.MaSP = sp.MaSP
            WHERE ci.MaCart = ?
        ");
        $stmt->execute([$cartId]);
        $cartItems = $stmt->fetchAll();
        

        $_SESSION['cart'] = [];
        

        foreach ($cartItems as $item) {

            $stmt = $pdo->prepare("
                SELECT cio.*, ov.TenGiaTri, og.TenNhom
                FROM Cart_Item_Option cio
                INNER JOIN Option_Value ov ON cio.MaOptionValue = ov.MaOptionValue
                INNER JOIN Option_Group og ON ov.MaOptionGroup = og.MaOptionGroup
                WHERE cio.MaCartItem = ?
            ");
            $stmt->execute([$item['macartitem']]);
            $options = $stmt->fetchAll();
            

            $formattedOptions = [];
            $totalPrice = $item['gianiemyet'];
            
            foreach ($options as $option) {
                $formattedOptions[] = [
                    'option_value_id' => $option['maoptionvalue'],
                    'option_name' => $option['tengiatri'],
                    'group_name' => $option['tennhom'],
                    'price' => (float)$option['giathem']
                ];
                $totalPrice += (float)$option['giathem'];
            }
            
            $totalPrice *= $item['soluong'];
            

            $_SESSION['cart'][] = [
                'product_id' => $item['masp'],
                'product_name' => $item['tensp'],
                'product_image' => $item['hinhanh'] ?? 'assets/img/products/product_one.png',
                'quantity' => $item['soluong'],
                'base_price' => (float)$item['gianiemyet'],
                'total_price' => $totalPrice,
                'reference_price' => (float)$item['giacoban'],
                'options' => $formattedOptions,
                'note' => $item['ghichu'] ?? '',
                'added_at' => date('Y-m-d H:i:s')
            ];
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error loading cart from DB: " . $e->getMessage());
        return false;
    }
}

function mergeCartWithDB($userId, $storeId) {

    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return loadCartFromDB($userId, $storeId);
    }
    

    return saveCartToDB($userId, $storeId);
}

?>
