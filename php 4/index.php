<?php
session_start();

$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = 'roshan jatu';
$db_name = 'shoe_store4';

$pdo = null;
$db_error = null;

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Throwable $e) {
    $db_error = $e->getMessage();
}

// ── Handle Add to Cart ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_cart') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $sid = session_id();
    if ($product_id > 0) {
        if ($pdo) {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO cart_items (session_id, product_id, quantity)
                     VALUES (:sid, :pid, 1)
                     ON DUPLICATE KEY UPDATE quantity = quantity + 1"
                );
                $stmt->execute([':sid' => $sid, ':pid' => $product_id]);
            } catch (Throwable $e) { /* silently skip */ }
        } else {
            $key = 'p' . $product_id;
            $_SESSION['cart'][$key] = ($_SESSION['cart'][$key] ?? 0) + 1;
        }
    }
    header("Location: ?added=1#cart");
    exit;
}

// ── Handle Place Order ────────────────────────────
$order_success = false;
$order_error   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'place_order') {
    $cname   = trim($_POST['customer_name'] ?? '');
    $pid     = (int)($_POST['product_id']   ?? 0);
    $qty     = (int)($_POST['quantity']     ?? 1);
    $address = trim($_POST['address']       ?? '');
    if ($cname && $pid && $qty >= 1 && $address) {
        if ($pdo) {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO orders (customer_name, product_id, quantity, address)
                     VALUES (:cn, :pid, :qty, :addr)"
                );
                $stmt->execute([':cn' => $cname, ':pid' => $pid, ':qty' => $qty, ':addr' => $address]);
                $order_success = true;
            } catch (Throwable $e) {
                $order_error = 'Database error: ' . $e->getMessage();
            }
        } else {
            $order_success = true;
        }
    } else {
        $order_error = 'Please fill in all required fields.';
    }
}

// ── Fetch Products ────────────────────────────────
$products = [];
if ($pdo) {
    try {
        $rows = $pdo->query("SELECT * FROM products ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $row['features_arr'] = array_filter(array_map('trim', explode('|', $row['features'])));
            $products[] = $row;
        }
    } catch (Throwable $e) { /* fall through */ }
}
if (empty($products)) {
    $products = [
        ['id' => 1, 'name' => 'Nike Air Max',      'image' => 'nike.jpg',   'price' => 4999,
         'features_arr' => ['Lightweight', 'Air Cushion Technology', 'Perfect for Running', 'Breathable Mesh Upper']],
        ['id' => 2, 'name' => 'Adidas Ultraboost', 'image' => 'adidas.jpg', 'price' => 5499,
         'features_arr' => ['High Performance', 'Comfortable Fit', 'Durable Sole', 'Boost Energy Return']],
        ['id' => 3, 'name' => 'Puma Sneakers',     'image' => 'puma.jpg',   'price' => 3999,
         'features_arr' => ['Stylish Design', 'Daily Wear', 'Budget Friendly', 'SOFTFOAM+ Sockliner']],
    ];
}

// ── Fetch Cart ────────────────────────────────────
$cart_items = [];
$cart_total = 0;
$sid = session_id();
if ($pdo) {
    try {
        $stmt = $pdo->prepare(
            "SELECT p.id, p.name, p.price, SUM(c.quantity) AS quantity
             FROM cart_items c
             INNER JOIN products p ON p.id = c.product_id
             WHERE c.session_id = :sid
             GROUP BY p.id, p.name, p.price"
        );
        $stmt->execute([':sid' => $sid]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cart_items as $ci) { $cart_total += $ci['price'] * $ci['quantity']; }
    } catch (Throwable $e) { /* skip */ }
} elseif (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $key => $qty) {
        $pid_s = (int)substr($key, 1);
        foreach ($products as $p) {
            if ($p['id'] == $pid_s) {
                $cart_items[] = ['id' => $p['id'], 'name' => $p['name'], 'price' => $p['price'], 'quantity' => $qty];
                $cart_total += $p['price'] * $qty;
            }
        }
    }
}

$added = isset($_GET['added']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PATEL Premium Shoe Store — Top Sports & Casual Shoes">
    <title>PATEL Premium Shoe Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ── NAV ─────────────────────────────────────── -->
<nav>
    <div class="nav-brand">👟 PATEL Shoes</div>
    <ul class="nav-links">
        <li><a href="#products">Products</a></li>
        <li><a href="#perks">Why Us</a></li>
        <li><a href="#order">Order</a></li>
        <li><a href="#contact">Contact</a></li>
        <?php if (!empty($cart_items)): ?>
        <li><a href="#cart" style="color:var(--accent)">🛒 Cart (<?= count($cart_items) ?>)</a></li>
        <?php endif; ?>
    </ul>
</nav>

<!-- ── HERO ─────────────────────────────────────── -->
<section class="hero">
    <div class="hero-badge">✦ New Season Collection 2026</div>
    <h1>Step Into <span>Premium</span><br>Comfort</h1>
    <p>Explore our curated collection of top sports and casual shoes — authentic brands, unbeatable prices.</p>
    <div class="hero-cta">
        <a href="#products" class="btn-primary">Shop Now</a>
        <a href="#order" class="btn-outline">Place Order</a>
    </div>
</section>

<!-- ── CART ─────────────────────────────────────── -->
<?php if ($added || !empty($cart_items)): ?>
<section class="cart-section" id="cart">
    <span class="section-label">Your Cart</span>
    <h2 class="section-title">🛒 Shopping Cart</h2>
    <?php if ($added): ?>
    <div class="alert alert-success" style="max-width:900px;margin:0 auto 24px;">Item added to cart successfully!</div>
    <?php endif; ?>
    <?php if (!empty($cart_items)): ?>
    <table class="cart-table">
        <thead>
            <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            <?php foreach ($cart_items as $ci): ?>
            <tr>
                <td><?= htmlspecialchars($ci['name']) ?></td>
                <td>₹<?= number_format($ci['price']) ?></td>
                <td><?= (int)$ci['quantity'] ?></td>
                <td>₹<?= number_format($ci['price'] * $ci['quantity']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="cart-total">Total: <span>₹<?= number_format($cart_total) ?></span></div>
    <?php else: ?>
    <p style="color:var(--text-muted);max-width:900px;margin:0 auto">Your cart is empty. Add products below.</p>
    <?php endif; ?>
</section>
<?php endif; ?>

<!-- ── PRODUCTS ──────────────────────────────────── -->
<section id="products">
    <span class="section-label">Featured Collection</span>
    <h2 class="section-title">Our Products</h2>
    <div class="divider"></div>
    <div class="products-grid">
        <?php foreach ($products as $p):
            $imgPath = $p['image'] ?? '';
            $hasImg  = $imgPath && file_exists(__DIR__ . '/' . $imgPath);
        ?>
        <article class="product-card">
            <?php if ($hasImg): ?>
            <img class="product-img" src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
            <?php else: ?>
            <div class="product-img">👟</div>
            <?php endif; ?>
            <div class="product-body">
                <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
                <ul class="features">
                    <?php foreach ($p['features_arr'] as $f): ?>
                    <li><?= htmlspecialchars($f) ?></li>
                    <?php endforeach; ?>
                </ul>
                <div class="product-footer">
                    <span class="product-price">₹<?= number_format($p['price']) ?></span>
                    <form method="post">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                        <button class="btn-cart" type="submit">+ Cart</button>
                    </form>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>

<!-- ── PERKS ─────────────────────────────────────── -->
<section class="perks-section" id="perks">
    <span class="section-label">Why Choose Us</span>
    <h2 class="section-title">Our Promises</h2>
    <div class="divider"></div>
    <div class="perks-grid">
        <div class="perk-card">
            <div class="perk-icon">✅</div>
            <div class="perk-title">100% Original</div>
            <p class="perk-desc">All products are authentic and brand-verified.</p>
        </div>
        <div class="perk-card">
            <div class="perk-icon">🚚</div>
            <div class="perk-title">Free Home Delivery</div>
            <p class="perk-desc">Fast &amp; free shipping across India.</p>
        </div>
        <div class="perk-card">
            <div class="perk-icon">↩️</div>
            <div class="perk-title">7-Day Returns</div>
            <p class="perk-desc">Easy, no-questions-asked return policy.</p>
        </div>
        <div class="perk-card">
            <div class="perk-icon">💵</div>
            <div class="perk-title">Cash on Delivery</div>
            <p class="perk-desc">Pay only when your order arrives.</p>
        </div>
    </div>
</section>

<!-- ── ORDER ─────────────────────────────────────── -->
<section id="order">
    <span class="section-label">Place an Order</span>
    <h2 class="section-title">Order Now</h2>
    <div class="divider"></div>
    <div class="order-grid">
        <div class="order-steps">
            <h3>How It Works</h3>
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-info">
                    <strong>Pick Your Product</strong>
                    <p>Choose from our premium catalog above.</p>
                </div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-info">
                    <strong>Enter Your Details</strong>
                    <p>Provide your name, quantity, and delivery address.</p>
                </div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-info">
                    <strong>Confirm Order</strong>
                    <p>Click Submit — we dispatch within 24 hours.</p>
                </div>
            </div>
        </div>
        <div class="order-form-card">
            <h3>Fill Order Details</h3>
            <?php if ($order_success): ?>
            <div class="alert alert-success">🎉 Order placed successfully! We'll contact you shortly.</div>
            <?php endif; ?>
            <?php if ($order_error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($order_error) ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="action" value="place_order">
                <div class="form-group">
                    <label for="customer_name">Full Name</label>
                    <input type="text" id="customer_name" name="customer_name" placeholder="Monil Patel" required>
                </div>
                <div class="form-group">
                    <label for="product_id">Select Product</label>
                    <select id="product_id" name="product_id" required>
                        <option value="">-- Choose a product --</option>
                        <?php foreach ($products as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?> — ₹<?= number_format($p['price']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" min="1" max="99" value="1" required>
                </div>
                <div class="form-group">
                    <label for="address">Delivery Address</label>
                    <textarea id="address" name="address" placeholder="House No., Street, City, Pincode" required></textarea>
                </div>
                <button class="form-submit" type="submit">Submit Order →</button>
            </form>
        </div>
    </div>
</section>

<!-- ── CONTACT ────────────────────────────────────── -->
<section class="contact-section" id="contact">
    <span class="section-label">Get in Touch</span>
    <h2 class="section-title">Contact Us</h2>
    <div class="divider" style="margin:16px auto 32px;"></div>
    <div class="contact-cards">
        <div class="contact-card">
            <div class="ci">📧</div>
            <strong>Email</strong>
            <p>support@monilshoes.com</p>
        </div>
        <div class="contact-card">
            <div class="ci">📞</div>
            <strong>Phone</strong>
            <p>8849740412</p>
        </div>
        <div class="contact-card">
            <div class="ci">📍</div>
            <strong>Location</strong>
            <p>Gujarat, India</p>
        </div>
    </div>
</section>

<!-- ── FOOTER ─────────────────────────────────────── -->
<footer>
    <p>&copy; <?= date('Y') ?> <span>PATEL Premium Shoe Store</span> — All Rights Reserved</p>
</footer>

</body>
</html>
