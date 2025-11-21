<?php
session_start();
require_once 'products.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$products = getProducts();

$cart_items = [];
$total = 0;
$valid_cart = [];

foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $product = getProductById($product_id);
    
    if ($product) {
        $quantity = min($quantity, 99);
        $item_total = $product['price'] * $quantity;
        $total += $item_total;
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'item_total' => $item_total
        ];
        $valid_cart[$product_id] = $quantity;
    }
}

$_SESSION['cart'] = $valid_cart;

$delivery_charge = $total >= 500 ? 0 : 40;
$grand_total = $total + $delivery_charge;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        die('Invalid request');
    }
    
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $state = sanitizeInput($_POST['state'] ?? '');
    $pincode = sanitizeInput($_POST['pincode'] ?? '');
    
    $errors = [];
    if (empty($name)) $errors[] = 'Name is required';
    if (!validateEmail($email)) $errors[] = 'Valid email is required';
    if (!validatePhone($phone)) $errors[] = 'Valid 10-digit phone number is required';
    if (empty($address)) $errors[] = 'Address is required';
    if (empty($city)) $errors[] = 'City is required';
    if (empty($state)) $errors[] = 'State is required';
    if (!validatePincode($pincode)) $errors[] = 'Valid 6-digit pincode is required';
    
    if (empty($errors)) {
        $_SESSION['checkout_info'] = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'pincode' => $pincode
        ];
        header('Location: payment.php');
        exit;
    }
}

$csrf_token = generateCSRFToken();

$cart_count = array_sum($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - JioMart</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><a href="index.php" style="color: white; text-decoration: none;">JioMart</a></h1>
                    <p class="tagline">Best Prices, Best Quality</p>
                </div>
                <div class="header-right">
                    <a href="cart.php" class="cart-btn">
                        <span>🛒 Cart</span>
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="page-header">
            <h2>Checkout</h2>
        </div>

        <div class="checkout-content">
            <div class="checkout-form">
                <h3>Delivery Information</h3>
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="error-box">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="checkout.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" required placeholder="10-digit mobile number">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Delivery Address *</label>
                        <textarea id="address" name="address" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City *</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="state">State *</label>
                            <input type="text" id="state" name="state" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="pincode">Pincode *</label>
                        <input type="text" id="pincode" name="pincode" pattern="[0-9]{6}" required placeholder="6-digit pincode">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Continue to Payment</button>
                </form>
            </div>

            <div class="order-summary-sidebar">
                <h3>Order Summary</h3>
                <div class="summary-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="summary-item">
                            <span><?php echo htmlspecialchars($item['product']['name']); ?> x <?php echo $item['quantity']; ?></span>
                            <span>₹<?php echo number_format($item['item_total'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="summary-totals">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>₹<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery:</span>
                        <span>₹<?php echo number_format($delivery_charge, 2); ?></span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total:</span>
                        <span>₹<?php echo number_format($grand_total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 JioMart. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
