<?php
include('db.php');
include('functions.php');

// Fetch product details based on product_id
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $sql = "SELECT * FROM products WHERE product_id = $product_id";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Product Details</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="product-details">
        <h1><?php echo $product['name']; ?></h1>
        <img src="../assets/images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" />
        <p><?php echo $product['description']; ?></p>
        <p>Price: $<?php echo $product['price']; ?></p>
        <a href="add_to_cart.php?product_id=<?php echo $product['product_id']; ?>" class="btn">Add to Cart</a>
    </div>
</body>
</html>
