<?php
include('db.php');
include('functions.php');
check_login();

// Check if the request is valid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    // Update the order status
    $update_query = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        echo "<p>Order status updated successfully!</p>";
    } else {
        echo "<p>Failed to update order status. Please try again.</p>";
    }
} else {
    echo "<p>Invalid request.</p>";
}
?>
