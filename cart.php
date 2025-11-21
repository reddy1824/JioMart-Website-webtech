<?php
session_start();
require_once 'products.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$products = getProducts();

if (isset($_POST['update_cart'])) {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        die('Invalid request');
    }
    
    $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
    $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
    
    if ($product_id && getProductById($product_id) && $quantity !== false) {
        $quantity = max(0, min($quantity, 99));
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    header('Location: cart.php');
    exit;
}

if (isset($_POST['remove_item'])) {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        die('Invalid request');
    }
    
    $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
    if ($product_id) {
        unset($_SESSION['cart'][$product_id]);
    }
    header('Location: cart.php');
    exit;
}

$csrf_token = generateCSRFToken();

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
$cart_count = array_sum($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - JioMart</title>
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
            <h2>Shopping Cart</h2>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Your cart is empty</p>
                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <img src="<?php echo $item['product']['image']; ?>" alt="<?php echo htmlspecialchars($item['product']['name']); ?>">
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['product']['name']); ?></h3>
                                <p class="price">₹<?php echo number_format($item['product']['price'], 2); ?></p>
                            </div>
                            <div class="item-actions">
                                <form method="POST" action="cart.php" class="quantity-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <label>Qty:</label>
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="99">
                                    <button type="submit" name="update_cart" class="btn btn-secondary">Update</button>
                                </form>
                                <form method="POST" action="cart.php" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <button type="submit" name="remove_item" class="btn btn-remove">Remove</button>
                                </form>
                            </div>
                            <div class="item-total">
                                <strong>₹<?php echo number_format($item['item_total'], 2); ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>₹<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Charges:</span>
                        <span>₹<?php echo $total >= 500 ? '0.00' : '40.00'; ?></span>
                    </div>
                    <?php if ($total < 500): ?>
                        <p class="free-delivery-msg">Add ₹<?php echo number_format(500 - $total, 2); ?> more for FREE delivery</p>
                    <?php endif; ?>
                    <div class="summary-row total-row">
                        <span>Total:</span>
                        <span>₹<?php echo number_format($total + ($total >= 500 ? 0 : 40), 2); ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary btn-block">Proceed to Checkout</a>
                    <a href="index.php" class="btn btn-secondary btn-block">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 JioMart. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
