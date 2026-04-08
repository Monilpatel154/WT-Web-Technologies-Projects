<?php
session_start();

$dbHost = '127.0.0.1';
$dbName = 'shoe_store';
$dbUser = 'root';
$dbPass = 'roshan jatu';

$products = [];
$cartItems = [];
$cartTotal = 0;
$dbConnected = false;
$dbNotice = '';

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function formatPrice($value)
{
    return '₹' . number_format((float) $value, 0);
}

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $dbConnected = true;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_cart') {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

        if (!empty($productId)) {
            $productStmt = $pdo->prepare('SELECT id FROM products WHERE id = :id');
            $productStmt->execute(['id' => $productId]);

            if ($productStmt->fetchColumn()) {
                $cartStmt = $pdo->prepare(
                    'INSERT INTO cart_items (session_id, product_id, quantity)
                     VALUES (:session_id, :product_id, 1)
                     ON DUPLICATE KEY UPDATE quantity = quantity + 1'
                );
                $cartStmt->execute([
                    'session_id' => session_id(),
                    'product_id' => $productId,
                ]);
            }
        }

        header('Location: ' . $_SERVER['PHP_SELF'] . '?added=1#cart');
        exit;
    }

    $stmt = $pdo->query(
        'SELECT id, name, brand, price, old_price, image, is_new, on_sale, sale_badge
         FROM products
         ORDER BY id ASC'
    );
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cartQuery = $pdo->prepare(
        'SELECT p.id, p.name, p.image, p.price, SUM(c.quantity) AS quantity
         FROM cart_items c
         INNER JOIN products p ON p.id = c.product_id
         WHERE c.session_id = :session_id
         GROUP BY p.id, p.name, p.image, p.price
         ORDER BY p.name ASC'
    );
    $cartQuery->execute(['session_id' => session_id()]);
    $cartItems = $cartQuery->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cartItems as $item) {
        $cartTotal += ((float) $item['price']) * ((int) $item['quantity']);
    }
} catch (Throwable $e) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_cart') {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

        if (!empty($productId)) {
            if (!isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId] = 0;
            }
            $_SESSION['cart'][$productId]++;
        }

        header('Location: ' . $_SERVER['PHP_SELF'] . '?added=1#cart');
        exit;
    }

    $dbNotice = 'Database connection not available, showing demo products. Cart is session-only right now.';
    $products = [
        [
            'id' => 1,
            'name' => 'Nike Air Max',
            'brand' => 'Nike',
            'price' => 4999,
            'old_price' => 5999,
            'image' => 'nike.jpg',
            'is_new' => 0,
            'on_sale' => 1,
            'sale_badge' => '20% OFF',
        ],
        [
            'id' => 2,
            'name' => 'Adidas Ultraboost 2026',
            'brand' => 'Adidas',
            'price' => 5499,
            'old_price' => null,
            'image' => 'adidas.jpg',
            'is_new' => 1,
            'on_sale' => 0,
            'sale_badge' => null,
        ],
        [
            'id' => 3,
            'name' => 'Puma Sneakers',
            'brand' => 'Puma',
            'price' => 3999,
            'old_price' => null,
            'image' => 'puma.jpg',
            'is_new' => 0,
            'on_sale' => 0,
            'sale_badge' => null,
        ],
    ];

    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        $productsById = [];
        foreach ($products as $product) {
            $productsById[(int) $product['id']] = $product;
        }

        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $productId = (int) $productId;
            $quantity = (int) $quantity;

            if ($quantity > 0 && isset($productsById[$productId])) {
                $item = $productsById[$productId];
                $cartItems[] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'image' => $item['image'],
                    'price' => $item['price'],
                    'quantity' => $quantity,
                ];
                $cartTotal += ((float) $item['price']) * $quantity;
            }
        }
    }
}

$saleProducts = array_values(array_filter(
    $products,
    static function ($product) {
        return !empty($product['on_sale'])
            || (!empty($product['old_price']) && (float) $product['old_price'] > (float) $product['price']);
    }
));

$newProducts = array_values(array_filter(
    $products,
    static fn($product) => !empty($product['is_new'])
));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Monil's Premium Shoe Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header id="home">
    <h1>👟 PATEL Premium Shoe Store</h1>
    <p>Style. Comfort. Performance.</p>
</header>

<nav>
    <a href="#home">Home</a>
    <a href="#sale">Sale</a>
    <a href="#new">New Arrivals</a>
    <a href="#products">Products</a>
    <a href="#cart">Cart</a>
    <a href="#contact">Contact</a>
</nav>

<section class="hero">
    <h2>🔥 Flat 20% OFF On All Sports Shoes</h2>
    <p>Limited Time Offer</p>
    <a href="#products" class="hero-btn">Shop Now</a>
</section>

<?php if (!empty($dbNotice)): ?>
<section class="section">
    <p><?php echo e($dbNotice); ?></p>
</section>
<?php endif; ?>

<?php if (!empty($_GET['added'])): ?>
<section class="section">
    <p>Product added to cart successfully.</p>
</section>
<?php endif; ?>

<!-- SALE -->
<section id="sale" class="section">
    <h2>🔥 Sale Collection</h2>
    <div class="products-grid">
        <?php if (empty($saleProducts)): ?>
            <p>Sale items will appear here.</p>
        <?php endif; ?>

        <?php foreach ($saleProducts as $product): ?>
            <div class="product-card">
                <span class="badge"><?php echo e($product['sale_badge'] ?: 'SALE'); ?></span>
                <img src="<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>">
                <h3><?php echo e($product['name']); ?></h3>
                <?php if (!empty($product['old_price'])): ?>
                    <p class="old-price"><?php echo e(formatPrice($product['old_price'])); ?></p>
                <?php endif; ?>
                <p class="price"><?php echo e(formatPrice($product['price'])); ?></p>
                <form method="post">
                    <input type="hidden" name="action" value="add_to_cart">
                    <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
                    <button type="submit">Add to Cart</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- NEW ARRIVALS -->
<section id="new" class="section dark">
    <h2>🆕 New Arrivals</h2>
    <div class="products-grid">
        <?php if (empty($newProducts)): ?>
            <p>New arrivals will appear here.</p>
        <?php endif; ?>

        <?php foreach ($newProducts as $product): ?>
            <div class="product-card">
                <span class="badge new">NEW</span>
                <img src="<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>">
                <h3><?php echo e($product['name']); ?></h3>
                <p class="price"><?php echo e(formatPrice($product['price'])); ?></p>
                <form method="post">
                    <input type="hidden" name="action" value="add_to_cart">
                    <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
                    <button type="submit">Add to Cart</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- PRODUCTS -->
<section id="products" class="section">
    <h2>🛍 All Products</h2>
    <div class="products-grid">
        <?php if (empty($products)): ?>
            <p>No products found.</p>
        <?php endif; ?>

        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>">
                <h3><?php echo e($product['name']); ?></h3>
                <p class="price"><?php echo e(formatPrice($product['price'])); ?></p>
                <form method="post">
                    <input type="hidden" name="action" value="add_to_cart">
                    <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
                    <button type="submit">Add to Cart</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- CART -->
<section id="cart" class="section dark">
    <h2>🛒 Your Cart</h2>
    <div class="products-grid">
        <?php if (empty($cartItems)): ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>

        <?php foreach ($cartItems as $item): ?>
            <div class="product-card">
                <img src="<?php echo e($item['image']); ?>" alt="<?php echo e($item['name']); ?>">
                <h3><?php echo e($item['name']); ?></h3>
                <p>Qty: <?php echo e($item['quantity']); ?></p>
                <p class="price"><?php echo e(formatPrice($item['price'])); ?></p>
                <p>Subtotal: <?php echo e(formatPrice(((float) $item['price']) * ((int) $item['quantity']))); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <h3>Total: <?php echo e(formatPrice($cartTotal)); ?></h3>
</section>

<!-- CONTACT -->
<section id="contact" class="section contact">
    <h2>📞 Contact Us</h2>
    <p>Email: support@monilshoes.com</p>
    <p>Phone: 8849740412</p>
    <p>Bengaluru, India</p>
</section>

<footer>
    © <?php echo date('Y'); ?> PATEL Premium Shoe Store | Web Technologies Project
</footer>

</body>
</html>