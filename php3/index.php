<?php
session_start();

$dbHost = '127.0.0.1';
$dbName = 'shoe_store3';
$dbUser = 'root';
$dbPass = 'roshan jatu';

$products    = [];
$dbConnected = false;
$formMsg     = '';
$formError   = false;

function e($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
function formatPrice($v) { return '₹' . number_format((float)$v, 0); }

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser, $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $dbConnected = true;

    // Handle order form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'place_order') {
        $name       = trim(filter_input(INPUT_POST, 'customer_name', FILTER_DEFAULT) ?? '');
        $productId  = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity   = filter_input(INPUT_POST, 'quantity',   FILTER_VALIDATE_INT);
        $address    = trim(filter_input(INPUT_POST, 'address', FILTER_DEFAULT) ?? '');

        if (!$name || !$productId || !$quantity || $quantity < 1 || !$address) {
            $formError = true;
            $formMsg   = 'Please fill in all fields correctly.';
        } else {
            $check = $pdo->prepare('SELECT id FROM products WHERE id = :id');
            $check->execute(['id' => $productId]);
            if (!$check->fetchColumn()) {
                $formError = true;
                $formMsg   = 'Selected product not found.';
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO orders (customer_name, product_id, quantity, address)
                     VALUES (:name, :pid, :qty, :addr)'
                );
                $stmt->execute([
                    'name' => $name,
                    'pid'  => $productId,
                    'qty'  => $quantity,
                    'addr' => $address,
                ]);
                $formMsg = 'Order placed successfully! We will contact you shortly.';
            }
        }

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($formMsg) . '&err=' . ($formError ? '1' : '0') . '#order');
        exit;
    }

    if (!empty($_GET['msg'])) {
        $formMsg   = (string) $_GET['msg'];
        $formError = ($_GET['err'] ?? '0') === '1';
    }

    $stmt    = $pdo->query('SELECT * FROM products ORDER BY sort_order ASC');
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $ex) {
    if (!empty($_GET['msg'])) {
        $formMsg   = (string) $_GET['msg'];
        $formError = ($_GET['err'] ?? '0') === '1';
    }

    // Fallback static data
    $products = [
        ['id'=>1,'name'=>'Nike Air Max',     'image'=>'nike.jpg',   'features'=>'Lightweight|Air Cushion Technology|Perfect for Running', 'price'=>4999],
        ['id'=>2,'name'=>'Adidas Ultraboost','image'=>'adidas.jpg', 'features'=>'High Performance|Comfortable Fit|Durable Sole',          'price'=>5499],
        ['id'=>3,'name'=>'Puma Sneakers',    'image'=>'puma.jpg',   'features'=>'Stylish Design|Daily Wear|Budget Friendly',              'price'=>3999],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PATEL Premium Shoe Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <div class="nav-brand">👟 PATEL Shoes</div>
    <div class="nav-links">
        <a href="#products">Products</a>
        <a href="#why-us">Why Us</a>
        <a href="#order">Order</a>
        <a href="#contact">Contact</a>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-badge">Free Delivery on All Orders</div>
    <h1>PATEL Premium Shoe Store</h1>
    <p>Your one-stop destination for sports &amp; casual shoes. Style. Comfort. Performance.</p>
    <div class="hero-cta">
        <a href="#products" class="btn-primary">Shop Now</a>
        <a href="#order"    class="btn-outline">Place an Order</a>
    </div>
</section>

<div class="divider"></div>

<!-- PRODUCTS -->
<section id="products" class="section">
    <span class="section-label">Our Collection</span>
    <h2>Featured Products</h2>
    <div class="products-grid">
        <?php foreach ($products as $product): ?>
            <?php $featureList = explode('|', $product['features']); ?>
            <div class="product-card">
                <img class="product-img"
                     src="<?php echo e($product['image']); ?>"
                     alt="<?php echo e($product['name']); ?>"
                     onerror="this.style.display='none'">
                <div class="product-body">
                    <h3><?php echo e($product['name']); ?></h3>
                    <ul class="features">
                        <?php foreach ($featureList as $feat): ?>
                            <li><?php echo e(trim($feat)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="product-footer">
                        <span class="price"><?php echo e(formatPrice($product['price'])); ?></span>
                        <a href="#order?pid=<?php echo e($product['id']); ?>" class="btn-cart">Order Now</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="divider"></div>

<!-- WHY US -->
<section id="why-us" class="section">
    <span class="section-label">Our Promise</span>
    <h2>Why Choose Us?</h2>
    <div class="perks-grid">
        <div class="perk-card">
            <div class="perk-icon">✅</div>
            <h3>100% Original Products</h3>
            <p>Every shoe is directly sourced from authorized brands.</p>
        </div>
        <div class="perk-card">
            <div class="perk-icon">🚚</div>
            <h3>Free Home Delivery</h3>
            <p>We deliver to your doorstep at zero extra cost.</p>
        </div>
        <div class="perk-card">
            <div class="perk-icon">🔄</div>
            <h3>7-Day Return Policy</h3>
            <p>No questions asked returns within 7 days of delivery.</p>
        </div>
        <div class="perk-card">
            <div class="perk-icon">💵</div>
            <h3>Cash on Delivery</h3>
            <p>Pay when you receive — no upfront payment required.</p>
        </div>
    </div>
</section>

<div class="divider"></div>

<!-- ORDER -->
<section id="order" class="section">
    <div class="order-grid">
        <div class="order-info">
            <span class="section-label">Easy Ordering</span>
            <h2>Place Your Order</h2>
            <p>Fill in the form and we will confirm your order within 24 hours.</p>
            <ol class="order-steps">
                <li><span class="step-num">1</span> Choose your product below</li>
                <li><span class="step-num">2</span> Enter your name &amp; address</li>
                <li><span class="step-num">3</span> Submit and await confirmation</li>
                <li><span class="step-num">4</span> Receive at your door 🎉</li>
            </ol>
        </div>

        <div class="order-form-card">
            <h3>Order Details</h3>
            <?php if (!empty($formMsg)): ?>
                <div class="form-notice <?php echo $formError ? 'error' : 'success'; ?>">
                    <?php echo e($formMsg); ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="action" value="place_order">
                <div class="form-row">
                    <div class="form-group">
                        <label for="f_name">Full Name</label>
                        <input id="f_name" type="text" name="customer_name" placeholder="Your full name" required>
                    </div>
                    <div class="form-group">
                        <label for="f_qty">Quantity</label>
                        <input id="f_qty" type="number" name="quantity" min="1" value="1" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="f_product">Select Product</label>
                    <select id="f_product" name="product_id" required>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo e($product['id']); ?>">
                                <?php echo e($product['name']); ?> — <?php echo e(formatPrice($product['price'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="f_addr">Delivery Address</label>
                    <textarea id="f_addr" name="address" rows="4" placeholder="House no, street, city, state, pincode" required></textarea>
                </div>
                <button type="submit" class="form-submit">Submit Order &rarr;</button>
            </form>
        </div>
    </div>
</section>

<div class="divider"></div>

<!-- CONTACT -->
<section id="contact" class="section" style="text-align:center;padding-bottom:60px">
    <span class="section-label">Reach Us</span>
    <h2>Contact Us</h2>
    <p style="color:var(--muted);margin-bottom:8px">Email: support@monilshoes.com</p>
    <p style="color:var(--muted)">Phone: 8849740412</p>
</section>

<footer>
    &copy; <?php echo date('Y'); ?> <span>PATEL Premium Shoe Store</span> &mdash; Web Technologies Project
</footer>

</body>
</html>
