<?php
function getProducts() {
    return [
        ['id' => 1, 'name' => 'Fresh Vegetables Mix', 'price' => 149, 'image' => 'images/grocery_items_vegeta_825a2d27.jpg', 'category' => 'vegetables'],
        ['id' => 2, 'name' => 'Fresh Fruits Basket', 'price' => 299, 'image' => 'images/grocery_items_vegeta_ece322df.jpg', 'category' => 'fruits'],
        ['id' => 3, 'name' => 'Organic Vegetables', 'price' => 199, 'image' => 'images/grocery_items_vegeta_8970fe4a.jpg', 'category' => 'vegetables'],
        ['id' => 4, 'name' => 'Premium Fruits', 'price' => 399, 'image' => 'images/grocery_items_vegeta_276007b9.jpg', 'category' => 'fruits'],
        ['id' => 5, 'name' => 'Snacks Combo Pack', 'price' => 249, 'image' => 'images/packaged_food_snacks_ec5dd40b.jpg', 'category' => 'snacks'],
        ['id' => 6, 'name' => 'Chips Variety Pack', 'price' => 179, 'image' => 'images/packaged_food_snacks_d4a61960.jpg', 'category' => 'snacks'],
        ['id' => 7, 'name' => 'Breakfast Cereals', 'price' => 229, 'image' => 'images/packaged_food_snacks_cd8b8453.jpg', 'category' => 'breakfast'],
        ['id' => 8, 'name' => 'Cookies Assortment', 'price' => 159, 'image' => 'images/packaged_food_snacks_c9d6e9a1.jpg', 'category' => 'snacks'],
        ['id' => 9, 'name' => 'Fresh Dairy Products', 'price' => 189, 'image' => 'images/dairy_products_milk__a98327ab.jpg', 'category' => 'dairy'],
        ['id' => 10, 'name' => 'Milk & Eggs Combo', 'price' => 129, 'image' => 'images/dairy_products_milk__514a650b.jpg', 'category' => 'dairy'],
    ];
}

function getProductById($id) {
    $products = getProducts();
    foreach ($products as $product) {
        if ($product['id'] == $id) {
            return $product;
        }
    }
    return null;
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[0-9]{10}$/', $phone);
}

function validatePincode($pincode) {
    return preg_match('/^[0-9]{6}$/', $pincode);
}

function validateCardNumber($card_number) {
    $card_number = preg_replace('/\s+/', '', $card_number);
    if (!preg_match('/^[0-9]{13,19}$/', $card_number)) {
        return false;
    }
    
    $sum = 0;
    $length = strlen($card_number);
    for ($i = 0; $i < $length; $i++) {
        $digit = (int)$card_number[$length - 1 - $i];
        if ($i % 2 === 1) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        $sum += $digit;
    }
    return $sum % 10 === 0;
}

function validateExpiry($expiry) {
    if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry, $matches)) {
        return false;
    }
    
    $month = (int)$matches[1];
    $year = 2000 + (int)$matches[2];
    $current_year = (int)date('Y');
    $current_month = (int)date('m');
    
    if ($year < $current_year || ($year === $current_year && $month < $current_month)) {
        return false;
    }
    
    return true;
}

function validateCVV($cvv) {
    return preg_match('/^[0-9]{3}$/', $cvv);
}

function validateUPI($upi_id) {
    return preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9]+$/', $upi_id);
}
