<?php
session_start();
require_once 'products.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$products = getProducts();

if (isset($_POST['add_to_cart'])) {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        die('Invalid request');
    }
    
    $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
    if ($product_id && getProductById($product_id)) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] = min($_SESSION['cart'][$product_id] + 1, 99);
        } else {
            $_SESSION['cart'][$product_id] = 1;
        }
    }
    header('Location: index.php');
    exit;
}

$csrf_token = generateCSRFToken();
$search_query = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';

$filtered_products = array_filter($products, function($product) use ($search_query, $category_filter) {
    $name_match = empty($search_query) || strpos(strtolower($product['name']), $search_query) !== false;
    $category_match = empty($category_filter) || $product['category'] === $category_filter;
    return $name_match && $category_match;
});

$cart_count = array_sum($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JioMart - Online Grocery Shopping</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>JioMart</h1>
                    <p class="tagline">Best Prices, Best Quality</p>
                </div>
                <div class="header-right">
                    <form class="search-form" method="GET" action="index.php">
                        <input type="text" name="search" placeholder="Search for products..." value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit">Search</button>
                    </form>
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                        <div class="user-menu">
                            <span class="user-name">👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            <a href="logout.php" class="btn btn-secondary">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-secondary">Login</a>
                        <a href="signup.php" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
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

    <nav class="category-nav">
        <div class="container">
            <a href="index.php" class="<?php echo empty($category_filter) ? 'active' : ''; ?>">All Products</a>
            <a href="index.php?category=vegetables" class="<?php echo $category_filter === 'vegetables' ? 'active' : ''; ?>">Vegetables</a>
            <a href="index.php?category=fruits" class="<?php echo $category_filter === 'fruits' ? 'active' : ''; ?>">Fruits</a>
            <a href="index.php?category=snacks" class="<?php echo $category_filter === 'snacks' ? 'active' : ''; ?>">Snacks</a>
            <a href="index.php?category=breakfast" class="<?php echo $category_filter === 'breakfast' ? 'active' : ''; ?>">Breakfast</a>
            <a href="index.php?category=dairy" class="<?php echo $category_filter === 'dairy' ? 'active' : ''; ?>">Dairy</a>
        </div>
    </nav>

    <main class="container">
        <?php if (isset($_GET['login']) && $_GET['login'] === 'success'): ?>
            <div class="success-notification">
                ✓ Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! You have successfully logged in.
            </div>
        <?php endif; ?>
        
        <section class="hero">
            <h2>Welcome to JioMart</h2>
            <p>Fresh groceries delivered to your doorstep</p>
        </section>

        <section class="products">
            <h2>Our Products</h2>
            <div class="product-grid">
                <?php foreach ($filtered_products as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="price">₹<?php echo number_format($product['price'], 2); ?></p>
                            <form method="POST" action="index.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 JioMart. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
