<?php
include('db.php');
include('functions.php');
check_login();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<p>Your cart is empty!</p>";
    exit;
}

// Fetch product details for items in the cart
$cart_items = [];
$total_price = 0;

foreach ($_SESSION['cart'] as $cart_item) {
    $product_id = $cart_item['product_id'];
    $quantity = $cart_item['quantity'];

    $query = "SELECT name, price, image FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if ($product) {
        $product_name = $product['name'];
        $product_price = $product['price'];
        $product_image = $product['image'];
        $item_total = $product_price * $quantity;

        $cart_items[] = [
            'name' => $product_name,
            'price' => $product_price,
            'image' => $product_image,
            'quantity' => $quantity,
            'total' => $item_total,
        ];

        $total_price += $item_total;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: #f4f4f4;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #28a745;
            color: white;
        }

        .total {
            font-weight: bold;
            color: #333;
        }

        button {
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #218838;
        }

        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Cart</h2>
        <table>
            <tr>
                <th>Image</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
            <?php foreach ($cart_items as $item): ?>
            <tr>
                <td><img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="product-image"></td>
                <td><?php echo $item['name']; ?></td>
                <td>₹<?php echo $item['price']; ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>₹<?php echo $item['total']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p class="total">Total: ₹<?php echo $total_price; ?></p>
        <form method="POST" action="checkout.php">
            <button type="submit" name="buy_now">Buy Now</button>
        </form>
    </div>
</body>
</html>
