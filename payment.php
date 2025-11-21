<?php
session_start();
require_once 'products.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart']) || !isset($_SESSION['checkout_info'])) {
    header('Location: checkout.php');
    exit;
}

$products = getProducts();
$checkout_info = $_SESSION['checkout_info'];

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
    
    $payment_method = sanitizeInput($_POST['payment_method'] ?? '');
    $errors = [];
    
    if (empty($payment_method)) {
        $errors[] = 'Payment method is required';
    }
    
    $payment_data = ['payment_method' => $payment_method];
    
    if ($payment_method === 'card') {
        $card_holder = sanitizeInput($_POST['card_holder'] ?? '');
        $card_number = sanitizeInput($_POST['card_number'] ?? '');
        $expiry = sanitizeInput($_POST['expiry'] ?? '');
        $cvv = sanitizeInput($_POST['cvv'] ?? '');
        $card_type = sanitizeInput($_POST['card_type'] ?? '');
        
        if (empty($card_holder)) {
            $errors[] = 'Card holder name is required';
        }
        if (!validateCardNumber($card_number)) {
            $errors[] = 'Invalid card number';
        }
        if (!validateExpiry($expiry)) {
            $errors[] = 'Invalid or expired card expiry date (MM/YY)';
        }
        if (!validateCVV($cvv)) {
            $errors[] = 'Invalid CVV (must be 3 digits)';
        }
        if (empty($card_type)) {
            $errors[] = 'Card type is required';
        }
        
        $payment_data['card_holder'] = $card_holder;
        $payment_data['card_type'] = $card_type;
        $payment_data['card_last4'] = substr(preg_replace('/\s+/', '', $card_number), -4);
        
    } elseif ($payment_method === 'upi') {
        $upi_id = sanitizeInput($_POST['upi_id'] ?? '');
        
        if (!validateUPI($upi_id)) {
            $errors[] = 'Invalid UPI ID (format: username@upi)';
        }
        
        $payment_data['upi_id'] = $upi_id;
        
    } elseif ($payment_method === 'cod') {
        $payment_data['note'] = 'Cash on Delivery';
    }
    
    if (empty($errors)) {
        $order_id = 'ORD' . time() . rand(1000, 9999);
        $_SESSION['order'] = [
            'order_id' => $order_id,
            'items' => $cart_items,
            'checkout_info' => $checkout_info,
            'payment_info' => $payment_data,
            'total' => $total,
            'delivery_charge' => $delivery_charge,
            'grand_total' => $grand_total,
            'order_date' => date('Y-m-d H:i:s')
        ];
        
        session_regenerate_id(true);
        $_SESSION['cart'] = [];
        
        header('Location: confirmation.php');
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
    <title>Payment - JioMart</title>
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
            <h2>Payment Details</h2>
        </div>

        <div class="checkout-content">
            <div class="checkout-form">
                <div class="delivery-info-summary">
                    <h3>Delivery Address</h3>
                    <p><strong><?php echo htmlspecialchars($checkout_info['name']); ?></strong></p>
                    <p><?php echo htmlspecialchars($checkout_info['address']); ?></p>
                    <p><?php echo htmlspecialchars($checkout_info['city']); ?>, <?php echo htmlspecialchars($checkout_info['state']); ?> - <?php echo htmlspecialchars($checkout_info['pincode']); ?></p>
                    <p>Phone: <?php echo htmlspecialchars($checkout_info['phone']); ?></p>
                    <p>Email: <?php echo htmlspecialchars($checkout_info['email']); ?></p>
                </div>

                <h3>Payment Information</h3>
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="error-box">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="payment.php" id="payment-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-group">
                        <label for="payment_method">Payment Method *</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="upi">UPI</option>
                            <option value="cod">Cash on Delivery</option>
                        </select>
                    </div>

                    <div id="card-details" style="display: none;">
                        <div class="form-group">
                            <label for="card_holder">Card Holder Name *</label>
                            <input type="text" id="card_holder" name="card_holder">
                        </div>
                        
                        <div class="form-group">
                            <label for="card_number">Card Number *</label>
                            <input type="text" id="card_number" name="card_number" maxlength="19" placeholder="1234 5678 9012 3456">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="expiry">Expiry Date *</label>
                                <input type="text" id="expiry" name="expiry" placeholder="MM/YY" maxlength="5">
                            </div>
                            
                            <div class="form-group">
                                <label for="cvv">CVV *</label>
                                <input type="text" id="cvv" name="cvv" maxlength="3" placeholder="123">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="card_type">Card Type *</label>
                            <select id="card_type" name="card_type">
                                <option value="visa">Visa</option>
                                <option value="mastercard">Mastercard</option>
                                <option value="rupay">RuPay</option>
                            </select>
                        </div>
                    </div>

                    <div id="upi-details" style="display: none;">
                        <div class="form-group">
                            <label for="upi_id">UPI ID *</label>
                            <input type="text" id="upi_id" name="upi_id" placeholder="yourname@upi">
                        </div>
                    </div>

                    <div id="cod-details" style="display: none;">
                        <div class="info-box">
                            <p>You have selected Cash on Delivery. Please keep exact change ready for smooth delivery.</p>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Place Order</button>
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

    <script src="js/payment.js"></script>
</body>
</html>
