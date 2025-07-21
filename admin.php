<?php
include('db.php');
include('functions.php');
check_login();

// Add product functionality
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $min_stock = $_POST['min_stock'];

    // Handle image upload
    $image = $_FILES['image']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image);
    move_uploaded_file($_FILES['image']['tmp_name'], $target_file);

    $image_path = "uploads/" . basename($image); // Store relative path
    $query = "INSERT INTO products (name, description, price, stock, min_stock, image) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdiss", $name, $description, $price, $stock, $min_stock, $image_path);
    $stmt->execute();

    // Check if stock is below or equal to minimum stock and send notification
    if ($stock <= $min_stock) {
        $owner_email = "owner@example.com"; // Replace with the owner's email address
        $subject = "Low Stock Alert: $name";
        $message = "Dear Owner,\n\nThe stock for the product '$name' has reached the minimum stock level.\n\nCurrent Stock: $stock\nMinimum Stock: $min_stock\n\nPlease restock the product to avoid running out of stock.\n\nThank you.";
        $headers = "From: no-reply@inventorysystem.com";

        mail($owner_email, $subject, $message, $headers);
    }

    echo "<script>alert('Product added successfully!');</script>";
}

// Delete product functionality
if (isset($_GET['delete_id'])) {
    $product_id = $_GET['delete_id'];
    $query = "DELETE FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    echo "<script>alert('Product deleted successfully!');</script>";
}

// Fetch all products
$query = "SELECT * FROM products";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-image: url("images/admin.jpg"); /* Add background image */
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            margin: 0;
            padding: 0;
            color: #333;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.9)); /* Enhanced overlay */
            z-index: 1;
        }

        h2, h3 {
            position: relative;
            z-index: 2;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8); /* Text shadow for better visibility */
        }

        h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            animation: fadeIn 1.5s ease-out;
        }

        h3 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            animation: fadeIn 1.5s ease-out;
        }

        /* Add Product Form */
        form {
            background-color: rgba(255, 255, 255, 0.9); /* Semi-transparent form background */
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            width: 350px;
            animation: slideIn 0.8s ease-out;
            margin-bottom: 40px;
            position: relative;
            z-index: 2;
        }

        input, textarea, button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
            box-sizing: border-box;
        }

        button {
            background: linear-gradient(to right, #ff7e5f, #feb47b);
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-weight: bold;
        }

        button:hover {
            background: linear-gradient(to right, #feb47b, #ff7e5f);
            transform: scale(1.05);
        }

        /* Product Table */
        table {
            width: 90%;
            margin: 0 auto;
            border-collapse: collapse;
            animation: fadeIn 1.5s ease-out;
            background-color: rgba(255, 255, 255, 0.9); /* Semi-transparent table background */
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }

        th, td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        th {
            background: linear-gradient(to right, #ff7e5f, #feb47b);
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.8);
        }

        tr:hover {
            background-color: rgba(255, 255, 255, 1);
        }

        /* Status Labels */
        .low-stock {
            color: red;
            font-weight: bold;
        }

        .out-of-stock {
            color: darkred;
            font-weight: bold;
        }

        /* Action Links */
        a {
            text-decoration: none;
            color: #ff7e5f;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #feb47b;
        }

        /* Product Image Styles */
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 10px;
        }

        .product-name {
            display: flex;
            align-items: center;
        }

        /* Animations */
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes slideIn {
            0% { transform: translateY(-50px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            table {
                width: 95%;
            }

            form {
                width: 90%;
            }

            .product-image {
                width: 50px;
                height: 50px;
            }
        }
    </style>
</head>
<body>
    <h2>Welcome, Admin!</h2>
    <h3>Manage Products</h3>

    <!-- Add Product Form -->
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Product Name" required>
        <textarea name="description" placeholder="Product Description" required></textarea>
        <input type="number" name="price" placeholder="Product Price" step="0.01" required>
        <input type="number" name="stock" placeholder="Stock" required>
        <input type="number" name="min_stock" placeholder="Min Stock" required>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit" name="add_product">Add Product</button>
    </form>

    <!-- Product List -->
    <h3>Product List</h3>
    <table>
        <tr>
            <th>Product ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
        <?php while ($product = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $product['product_id']; ?></td>
            <td class="product-name">
                <img src="<?php echo $product['image']; ?>" alt="Product Image" class="product-image">
                <?php echo $product['name']; ?>
            </td>
            <td><?php echo $product['description']; ?></td>
            <td><?php echo $product['price']; ?></td>
            <td>
                <?php 
                    $stock = $product['stock'];
                    $min_stock = $product['min_stock'];

                    if ($stock <= 0) {
                        echo "<span class='out-of-stock'>Out of Stock</span>";
                    } elseif ($stock <= $min_stock) {
                        echo "<span class='low-stock'>Low Stock</span>";
                    } else {
                        echo $stock;
                    }
                ?>
            </td>
            <td>
                <a href="?delete_id=<?php echo $product['product_id']; ?>">Delete</a> |
                <a href="update_product.php?product_id=<?php echo $product['product_id']; ?>">Edit</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
