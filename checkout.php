<?php
include('db.php');
include('functions.php');
check_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_now'])) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo "<p>Your cart is empty!</p>";
        exit;
    }

    $customer_id = $_SESSION['user_id'];
    $order_total = 0;

    // Create a new order
    $order_query = "INSERT INTO orders (customer_id, total, payment_method, status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($order_query);

    $payment_method = isset($_POST['payment_method']) && $_POST['payment_method'] === 'online' ? 'Online' : 'COD';
    $order_status = 'Pending'; // Default order status

    // Calculate total price and insert order
    foreach ($_SESSION['cart'] as $cart_item) {
        $product_id = $cart_item['product_id'];
        $quantity = $cart_item['quantity'];

        $query = "SELECT price, stock FROM products WHERE product_id = ?";
        $stmt_product = $conn->prepare($query);
        $stmt_product->bind_param("i", $product_id);
        $stmt_product->execute();
        $product = $stmt_product->get_result()->fetch_assoc();

        if ($product) {
            $product_price = $product['price'];
            $stock = $product['stock'];

            if ($quantity > $stock) {
                echo "<p>Order failed: Not enough stock for product ID $product_id.</p>";
                exit;
            }

            $order_total += $product_price * $quantity;
        }
    }

    $stmt->bind_param("idss", $customer_id, $order_total, $payment_method, $order_status);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Deduct stock and clear cart
    foreach ($_SESSION['cart'] as $cart_item) {
        $product_id = $cart_item['product_id'];
        $quantity = $cart_item['quantity'];

        $update_stock_query = "UPDATE products SET stock = stock - ? WHERE product_id = ?";
        $stmt_update = $conn->prepare($update_stock_query);
        $stmt_update->bind_param("ii", $quantity, $product_id);
        $stmt_update->execute();
    }

    unset($_SESSION['cart']); // Clear the cart

    echo "<div style='text-align: center; margin-top: 50px;'>
            <p id='success-message' style='font-size: 20px; color: green; opacity: 0; transition: opacity 2s;'>Order placed successfully! Your order ID is $order_id.</p>";

    if ($payment_method === 'Online') {
        echo "<p style='font-size: 18px; color: green; margin-top: 20px;'>Payment received successfully!</p>";
    }

    echo "<a href='customer.php' id='dashboard-link' style='display: inline-block; margin-top: 20px; font-size: 18px; color: blue; text-decoration: none; opacity: 0; transition: opacity 2s;'>Go back to Dashboard</a>
            <div style='margin-top: 30px;'>
                <p style='font-size: 18px; color: black;'>Or make an online payment by scanning the QR code below:</p>
                <img src='images/qr.jpg' alt='Bank QR Code' style='width: 200px; height: 200px; margin-top: 10px;'>
            </div>
          </div>
          <script>
            // Add animation to fade in the message, link, and QR code
            window.onload = function() {
                const successMessage = document.getElementById('success-message');
                const dashboardLink = document.getElementById('dashboard-link');
                setTimeout(() => {
                    successMessage.style.opacity = 1;
                    dashboardLink.style.opacity = 1;
                }, 500); // Delay to start the fade-in effect
            };
          </script>";
} else {
    echo "<p>Invalid request.</p>";
}
?>
