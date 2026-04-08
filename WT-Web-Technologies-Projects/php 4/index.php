<!DOCTYPE html>
<html>
<head>
    <title>Monil's Premium Shoe Store</title>
</head>
<body>

    <center>
        <h1>👟 PATEL Premium Shoe Store</h1>
        <p>Your One Stop Destination for Sports & Casual Shoes</p>
        <hr width="80%">
    </center>

    <h2>🔥 Featured Products</h2>

    <table border="1" cellpadding="15" width="100%">
        <tr>
            <th>Product</th>
            <th>Image</th>
            <th>Description</th>
            <th>Price</th>
            <th>Order</th>
        </tr>

        <tr>
            <td><b>Nike Air Max</b></td>
            <td><img src="nike.jpg" width="150" alt="Nike Air Max"></td>
            <td>
                ✔ Lightweight<br>
                ✔ Air Cushion Technology<br>
                ✔ Perfect for Running
            </td>
            <td><b>₹4,999</b></td>
            <td>
                <button>Add to Cart</button><br><br>
                <button>Buy Now</button>
            </td>
        </tr>

        <tr>
            <td><b>Adidas Ultraboost</b></td>
            <td><img src="adidas.jpg" width="150" alt="Adidas Ultraboost"></td>
            <td>
                ✔ High Performance<br>
                ✔ Comfortable Fit<br>
                ✔ Durable Sole
            </td>
            <td><b>₹5,499</b></td>
            <td>
                <button>Add to Cart</button><br><br>
                <button>Buy Now</button>
            </td>
        </tr>

        <tr>
            <td><b>Puma Sneakers</b></td>
            <td><img src="puma.jpg" width="150" alt="Puma Sneakers"></td>
            <td>
                ✔ Stylish Design<br>
                ✔ Daily Wear<br>
                ✔ Budget Friendly
            </td>
            <td><b>₹3,999</b></td>
            <td>
                <button>Add to Cart</button><br><br>
                <button>Buy Now</button>
            </td>
        </tr>

    </table>

    <hr>

    <h2>🎯 Why Choose Us?</h2>
    <ul>
        <li>100% Original Products</li>
        <li>Free Home Delivery</li>
        <li>Easy 7-Day Return Policy</li>
        <li>Cash on Delivery Available</li>
    </ul>

    <hr>

    <h2>🛒 Place Your Order</h2>

    <form>
        <label>Full Name:</label><br>
        <input type="text"><br><br>

        <label>Select Product:</label><br>
        <select>
            <option>Nike Air Max</option>
            <option>Adidas Ultraboost</option>
            <option>Puma Sneakers</option>
        </select><br><br>

        <label>Quantity:</label><br>
        <input type="number" min="1"><br><br>

        <label>Address:</label><br>
        <textarea rows="4" cols="40"></textarea><br><br>

        <button type="submit">Submit Order</button>
    </form>

    <hr>

    <center>
        <h3>📞 Contact Us</h3>
        <p>Email: support@monilshoes.com</p>
        <p>Phone: 8849740412</p>
        <p>&copy; <?php echo date('Y'); ?> PATEL Shoe Store</p>
    </center>

</body>
</html>
