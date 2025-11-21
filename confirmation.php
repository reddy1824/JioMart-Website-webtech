<?php
session_start();

if (!isset($_SESSION['order'])) {
    header('Location: index.php');
    exit;
}

$order = $_SESSION['order'];
$cart_count = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - JioMart</title>
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
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="confirmation-page">
            <div class="success-icon">✓</div>
            <h2>Order Confirmed!</h2>
            <p class="confirmation-message">Thank you for your order. Your order has been placed successfully.</p>
            
            <div class="order-details">
                <div class="order-info-box">
                    <h3>Order Information</h3>
                    <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
                    <p><strong>Order Date:</strong> <?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></p>
                    <p><strong>Estimated Delivery:</strong> <?php echo date('d M Y', strtotime('+3 days')); ?></p>
                </div>

                <div class="order-info-box">
                    <h3>Delivery Address</h3>
                    <p><strong><?php echo htmlspecialchars($order['checkout_info']['name']); ?></strong></p>
                    <p><?php echo htmlspecialchars($order['checkout_info']['address']); ?></p>
                    <p><?php echo htmlspecialchars($order['checkout_info']['city']); ?>, <?php echo htmlspecialchars($order['checkout_info']['state']); ?> - <?php echo htmlspecialchars($order['checkout_info']['pincode']); ?></p>
                    <p>Phone: <?php echo htmlspecialchars($order['checkout_info']['phone']); ?></p>
                </div>

                <div class="order-items-box">
                    <h3>Order Items</h3>
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="confirmation-item">
                            <img src="<?php echo $item['product']['image']; ?>" alt="<?php echo htmlspecialchars($item['product']['name']); ?>">
                            <div class="item-info">
                                <h4><?php echo htmlspecialchars($item['product']['name']); ?></h4>
                                <p>Quantity: <?php echo $item['quantity']; ?></p>
                            </div>
                            <div class="item-price">
                                ₹<?php echo number_format($item['item_total'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-summary-box">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>₹<?php echo number_format($order['total'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Charges:</span>
                        <span>₹<?php echo number_format($order['delivery_charge'], 2); ?></span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total Paid:</span>
                        <span>₹<?php echo number_format($order['grand_total'], 2); ?></span>
                    </div>
                </div>

                <div class="confirmation-actions">
                    <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                    <button onclick="window.print()" class="btn btn-secondary">Print Order</button>
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
