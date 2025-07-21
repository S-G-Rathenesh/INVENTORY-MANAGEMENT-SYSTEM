<?php
include('db.php');
include('functions.php');
check_login();

// Fetch order history
$customer_id = $_SESSION['user_id'];
$order_query = "SELECT * FROM orders WHERE customer_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$order_result = $stmt->get_result();

$orders = [];
if ($order_result && $order_result->num_rows > 0) {
    while ($row = $order_result->fetch_assoc()) {
        $orders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Order History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f3f6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 32px 32px 24px 32px;
        }
        .order-history-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #212121;
            margin-bottom: 24px;
            letter-spacing: 0.5px;
        }
        .order-card {
            border-bottom: 1px solid #eee;
            padding: 18px 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .order-card:last-child {
            border-bottom: none;
        }
        .order-id {
            color: #2874f0;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .order-status {
            font-size: 1rem;
            color: #388e3c;
            font-weight: 500;
        }
        .order-status.Pending {
            color: #f57c00;
        }
        .order-status.Completed {
            color: #388e3c;
        }
        .order-status.Cancelled {
            color: #d32f2f;
        }
        .order-date {
            color: #757575;
            font-size: 0.98rem;
        }
        .order-total {
            font-weight: 500;
            color: #212121;
        }
        .back-btn {
            display: inline-block;
            margin-bottom: 24px;
            padding: 8px 18px;
            background: #2874f0;
            color: #fff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }
        .back-btn:hover {
            background: #185ac6;
        }
        .empty-history {
            color: #888;
            font-size: 1.1rem;
            text-align: center;
            margin: 40px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="customer.php" class="back-btn">&larr; Back to Dashboard</a>
        <div class="order-history-title">Your Order History</div>
        <?php if (empty($orders)) { ?>
            <div class="empty-history">Your order history is empty!</div>
        <?php } else { ?>
            <?php foreach ($orders as $order) { ?>
                <div class="order-card">
                    <span class="order-id">Order #<?= htmlspecialchars($order['order_id']) ?></span>
                    <span class="order-total">Total: ₹<?= htmlspecialchars($order['total']) ?></span>
                    <span class="order-status <?= htmlspecialchars($order['status']) ?>">Status: <?= htmlspecialchars($order['status']) ?></span>
                    <span class="order-date">Date: <?= htmlspecialchars($order['order_date']) ?></span>
                    <span class="order-payment">Payment: <?= htmlspecialchars($order['payment_method']) ?></span>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</body>
</html>
