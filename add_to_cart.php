<?php
session_start();
include('../includes/db.php');

// Check if product_id is set
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    
    // Fetch the product details
    $sql = "SELECT * FROM products WHERE product_id = $product_id";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();
    
    // Add the product to the session cart
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    // Add product to cart (quantity = 1 by default)
    $_SESSION['cart'][$product_id] = array(
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => 1
    );
    
    // Redirect to shopping cart page
    header('Location: cart.php');
    exit();
}
?>
