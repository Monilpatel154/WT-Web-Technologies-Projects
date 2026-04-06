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
    <a href="#contact">Contact</a>
</nav>

<section class="hero">
    <h2>🔥 Flat 20% OFF On All Sports Shoes</h2>
    <p>Limited Time Offer</p>
    <a href="#products" class="hero-btn">Shop Now</a>
</section>

<!-- SALE -->
<section id="sale" class="section">
    <h2>🔥 Sale Collection</h2>
    <div class="products-grid">

        <div class="product-card">
            <span class="badge">20% OFF</span>
            <img src="nike.jpg" alt="Nike Air Max">
            <h3>Nike Air Max</h3>
            <p class="old-price">₹5,999</p>
            <p class="price">₹4,999</p>
            <button>Add to Cart</button>
        </div>

    </div>
</section>

<!-- NEW ARRIVALS -->
<section id="new" class="section dark">
    <h2>🆕 New Arrivals</h2>
    <div class="products-grid">

        <div class="product-card">
            <span class="badge new">NEW</span>
            <img src="adidas.jpg" alt="Adidas Ultraboost 2026">
            <h3>Adidas Ultraboost 2026</h3>
            <p class="price">₹5,499</p>
            <button>Add to Cart</button>
        </div>

    </div>
</section>

<!-- PRODUCTS -->
<section id="products" class="section">
    <h2>🛍 All Products</h2>
    <div class="products-grid">

        <div class="product-card">
            <img src="nike.jpg" alt="Nike Air Max">
            <h3>Nike Air Max</h3>
            <p class="price">₹4,999</p>
            <button>Add to Cart</button>
        </div>

        <div class="product-card">
            <img src="adidas.jpg" alt="Adidas Ultraboost">
            <h3>Adidas Ultraboost</h3>
            <p class="price">₹5,499</p>
            <button>Add to Cart</button>
        </div>

        <div class="product-card">
            <img src="puma.jpg" alt="Puma Sneakers">
            <h3>Puma Sneakers</h3>
            <p class="price">₹3,999</p>
            <button>Add to Cart</button>
        </div>

    </div>
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