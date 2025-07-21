<?php
include('db.php');
include('functions.php');
check_login();

// Fetch all products for display
$product_query = "SELECT * FROM products";
$product_result = $conn->query($product_query);

// Add to cart functionality
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Fetch available stock
    $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $available_stock = $result['stock'];

    if ($quantity > $available_stock) {
        echo "<p style='color: red;'>Not enough stock available!</p>";
    } else {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $_SESSION['cart'][] = ['product_id' => $product_id, 'quantity' => $quantity];

        // Insert into cart table in database
        $user_id = $_SESSION['user_id'];
        // Check if product already in cart for this user
        $check_cart = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $check_cart->bind_param("ii", $user_id, $product_id);
        $check_cart->execute();
        $cart_row = $check_cart->get_result()->fetch_assoc();
        if ($cart_row) {
            // Update quantity if already in cart
            $new_quantity = $cart_row['quantity'] + $quantity;
            $update_cart = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
            $update_cart->bind_param("ii", $new_quantity, $cart_row['cart_id']);
            $update_cart->execute();
        } else {
            // Insert new cart item
            $insert_cart = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert_cart->bind_param("iii", $user_id, $product_id, $quantity);
            $insert_cart->execute();
        }
    }
}

// Checkout functionality
if (isset($_POST['checkout'])) {
    $order_total = 0;
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];

            // Fetch product price and stock
            $query = "SELECT price, stock FROM products WHERE product_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();

            if ($quantity > $product['stock']) {
                echo "<p style='color: red;'>Order failed: Not enough stock for product ID $product_id.</p>";
                exit;
            }

            $order_total += $product['price'] * $quantity;
        }
    }

    $payment_method = $_POST['payment_method'];
    $order_status = 'Completed';

    // Store order
    $customer_id = $_SESSION['user_id'];
    $order_query = "INSERT INTO orders (customer_id, total, payment_method, status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("idss", $customer_id, $order_total, $payment_method, $order_status);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Reduce stock
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];

            $update_stock_query = "UPDATE products SET stock = stock - ? WHERE product_id = ? AND stock >= ?";
            $stmt = $conn->prepare($update_stock_query);
            $stmt->bind_param("iii", $quantity, $product_id, $quantity);
            $stmt->execute();

            // Check for low stock and send email notification
            $check_stock_query = "SELECT name, stock FROM products WHERE product_id = ?";
            $stmt_check = $conn->prepare($check_stock_query);
            $stmt_check->bind_param("i", $product_id);
            $stmt_check->execute();
            $product_info = $stmt_check->get_result()->fetch_assoc();

            $low_stock_threshold = 5; // Set your threshold here
            if ($product_info['stock'] <= $low_stock_threshold) {
                $to = "admin@example.com"; // Change to your admin email
                $subject = "Low Stock Alert: " . $product_info['name'];
                $message = "The product '" . $product_info['name'] . "' is low on stock. Only " . $product_info['stock'] . " left.";
                $headers = "From: noreply@example.com";
                // Suppress warning and optionally handle failure
                if (!@mail($to, $subject, $message, $headers)) {
                    // Optionally log or handle the error here
                    // error_log("Low stock email failed to send for product: " . $product_info['name']);
                }
            }
        }
    }

    // Optional: Notify admin
    if ($payment_method == 'COD') {
        echo "<p style='color: blue;'>A new COD order has been placed. Total: ₹$order_total.</p>";
    }

    unset($_SESSION['cart']);
    // Clear the cart in the database for this user
    $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $clear_cart->bind_param("i", $customer_id);
    $clear_cart->execute();
    echo "<p style='color: green;'>Order placed successfully! Your order is <strong>$order_status</strong>.</p>";
}

// Fetch order history
$customer_id = $_SESSION['user_id'];
$order_query = "SELECT * FROM orders WHERE customer_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$order_result = $stmt->get_result();

// Add this block to fetch orders into an array
$orders = [];
if ($order_result && $order_result->num_rows > 0) {
    while ($row = $order_result->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Handle search functionality
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $product_query = "SELECT * FROM products WHERE name LIKE ?";
    $stmt = $conn->prepare($product_query);
    $search_term = "%" . $search_query . "%";
    $stmt->bind_param("s", $search_term);
    $stmt->execute();
    $product_result = $stmt->get_result();
} else {
    $product_query = "SELECT * FROM products";
    $product_result = $conn->query($product_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: green;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            animation: fadeIn 1s ease-in-out;
        }

        h2, h3 { 
            color: #333; 
            animation: slideIn 1s ease-out;
        }

        .product, .cart-item {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 1px 1px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            animation: fadeInUp 0.8s ease-in-out;
        }

        .product:hover { 
            transform: scale(1.05); 
            box-shadow: 3px 3px 10px rgba(0,0,0,0.2);
        }

        .product h4 { margin: 0 0 10px; }
        .product p { margin: 5px 0; }

        form { margin-top: 10px; }

        button {
            padding: 8px 15px;
            border: none;
            background: #28a745;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        button:hover { 
            background: #218838; 
            transform: scale(1.1);
        }

        select, input[type="number"] {
            padding: 5px;
            margin-right: 10px;
        }

        .cart { 
            margin-top: 40px; 
            animation: fadeIn 1s ease-in-out;
        }

        .total { font-weight: bold; color: #444; }

        .empty { color: gray; }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes fadeInUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome, Customer!</h2>
        <a href="order_history.php" style="display:inline-block;margin-bottom:18px;padding:8px 18px;background:#2874f0;color:#fff;border-radius:5px;text-decoration:none;font-weight:500;">View Order History</a>
        <!-- Search Box -->
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Product Listing -->
        <h3>Browse Products</h3>
        <div class="products">
            <?php
            if ($product_result->num_rows > 0):
                while ($product = $product_result->fetch_assoc()):
            ?>
                <div class="product">
                    <h4><?php echo $product['name']; ?></h4>
                    <img src="<?php echo $product['image']; ?>" 
                         alt="<?php echo $product['name']; ?>" 
                         style="max-width: 150px; max-height: 150px; object-fit: cover;">
                    <p><?php echo $product['description']; ?></p>
                    <p>Price: ₹<?php echo $product['price']; ?> | Stock: <?php echo $product['stock']; ?></p>
                    <?php if ($product['stock'] > 0): ?>
                        <form method="POST" action="">
                            <input type="number" name="quantity" min="1" max="<?php echo $product['stock']; ?>" required>
                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                            <button type="submit" name="add_to_cart">Add to Cart</button>
                        </form>
                    <?php else: ?>
                        <p style="color: red;">Out of Stock</p>
                    <?php endif; ?>
                </div>
            <?php
                endwhile;
            else:
                echo "<p>No products found.</p>";
            endif;
            ?>
        </div>

        <!-- Cart -->
        <div class="cart">
            <h3>Your Cart</h3>
            <ul>
                <?php 
                if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                    $total_price = 0;
                    foreach ($_SESSION['cart'] as $cart_item) {
                        $product_id = $cart_item['product_id'];
                        $quantity = $cart_item['quantity'];
                        $query = "SELECT name, price FROM products WHERE product_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $product_id);
                        $stmt->execute();
                        $product = $stmt->get_result()->fetch_assoc();
                        $total_price += $product['price'] * $quantity;
                        echo "<li class='cart-item'>{$product['name']} - Quantity: $quantity - ₹{$product['price']} each</li>";
                    }
                    echo "<p class='total'>Total Price: ₹$total_price</p>";
                } else {
                    echo "<p class='empty'>Your cart is empty!</p>";
                }
                ?>
            </ul>
            <!-- Checkout Form -->
            <?php if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                <form method="POST" action="">
                    <label for="payment_method">Choose a payment method:</label>
                    <select name="payment_method" required>
                        <option value="COD">Cash on Delivery (COD)</option>
                        <option value="Online">Online Payment (Simulated)</option>
                    </select>
                    <button type="submit" name="checkout">Checkout</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
