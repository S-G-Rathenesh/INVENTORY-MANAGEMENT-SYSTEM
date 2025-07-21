<?php
session_start();
include('db.php');

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "Cart is empty.";
    exit();
}

$customer_id = $_SESSION['user_id'] ?? 1; // Replace with actual logged-in customer ID
$order_total = 0;

$conn->begin_transaction();

try {
    // Check stock and calculate total
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $quantity = $item['quantity'];

        $stmt = $conn->prepare("SELECT stock, price FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if ($quantity > $product['stock']) {
            throw new Exception("Not enough stock for product ID $product_id");
        }

        $order_total += $product['price'] * $quantity;
    }

    // Insert order
    $payment_method = 'COD';
    $order_status = 'Pending';
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, total, payment_method, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $customer_id, $order_total, $payment_method, $order_status);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Insert order items and update stock
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $quantity = $item['quantity'];
        $price = $item['price'];

        // Reduce stock
        $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
        $stmt->bind_param("ii", $quantity, $product_id);
        $stmt->execute();

        // Insert order item
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
        $stmt->execute();
    }

    $conn->commit();
    unset($_SESSION['cart']);
    echo "<p style='color:green;'>Order placed successfully! Order ID: $order_id</p>";

} catch (Exception $e) {
    $conn->rollback();
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>
