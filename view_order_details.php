<?php
include('../includes/db.php');
include('../includes/functions.php');
check_login();

$order_id = $_GET['order_id']; // Get order ID from URL

// Fetch order details
$query = "SELECT o.order_id, o.total, o.payment_method, o.status, o.order_date, p.name, p.price, oi.quantity
          FROM orders o
          JOIN order_items oi ON o.order_id = oi.order_id
          JOIN products p ON oi.product_id = p.product_id
          WHERE o.order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_details = $stmt->get_result();
?>

<h3>Order Details</h3>
<table>
    <tr>
        <th>Product Name</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Total</th>
    </tr>
    <?php 
    $order_total = 0;
    while ($detail = $order_details->fetch_assoc()) {
        $total_price = $detail['price'] * $detail['quantity'];
        $order_total += $total_price;
    ?>
    <tr>
        <td><?php echo $detail['name']; ?></td>
        <td><?php echo $detail['quantity']; ?></td>
        <td><?php echo $detail['price']; ?></td>
        <td><?php echo $total_price; ?></td>
    </tr>
    <?php } ?>
</table>

<h4>Total: <?php echo $order_total; ?></h4>
<p>Status: <?php echo $detail['status']; ?></p>
<p>Payment Method: <?php echo $detail['payment_method']; ?></p>
<p>Order Date: <?php echo $detail['order_date']; ?></p>
