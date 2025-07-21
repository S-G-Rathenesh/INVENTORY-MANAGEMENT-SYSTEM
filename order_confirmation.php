<?php
session_start();
include('../includes/db.php');

// Insert order details into the database (for demo purposes, we'll assume success)
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $total = 0;
    $user_id = $_SESSION['user_id'];  // Assume user is logged in
    foreach ($_SESSION['cart'] as $product_id => $product_details) {
        $total += $product_details['price'] * $product_details['quantity'];
    }
    
    // Insert order into the database (for simplicity, we'll skip detailed order logic here)
    $sql = "INSERT INTO orders (user_id, total_price, status) VALUES ($user_id, $total, 'Pending')";
    $conn->query($sql);
    
    // Clear the cart after successful order
    unset($_SESSION['cart']);
    
    echo "Order placed successfully! You will receive a confirmation email shortly.";
}
?>
