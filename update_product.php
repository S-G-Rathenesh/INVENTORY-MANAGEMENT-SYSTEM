<?php
include('db.php');
include('functions.php');
check_login();


$product_id = $_GET['product_id'];

// Fetch product details for editing
$query = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $min_stock = $_POST['min_stock'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $image_name = basename($_FILES['image']['name']);
        $target_dir = "uploads/";
        
        // Ensure the uploads directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create the directory with proper permissions
        }

        $target_file = $target_dir . $image_name;

        // Move uploaded file to target directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        } else {
            echo "Error uploading image.";
            exit;
        }
    } else {
        $image_path = $product['image']; // Keep existing image if no new image is uploaded
    }

    $query = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, min_stock = ?, image = ? WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdiisi", $name, $description, $price, $stock, $min_stock, $image_path, $product_id);
    $stmt->execute();

    echo "Product updated successfully!";
    header("Location: admin.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('images/product.jpg'); /* Replace with your background image path */
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
            color: #333;
            animation: fadeIn 2s ease-in-out;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            animation: slideIn 1s ease-out;
        }
        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            animation: zoomIn 1s ease-in-out;
        }
        @keyframes zoomIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input, textarea, button {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        input:focus, textarea:focus {
            transform: scale(1.02);
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
        }
        textarea {
            resize: none;
        }
        button {
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
        img {
            display: block;
            margin: 10px auto;
            max-width: 100%;
            max-height: 200px;
            border: 1px solid #ccc;
            border-radius: 5px;
            animation: fadeIn 2s ease-in-out;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Update Product</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="text" name="name" value="<?php echo $product['name']; ?>" placeholder="Product Name" required>
            <textarea name="description" placeholder="Product Description" required><?php echo $product['description']; ?></textarea>
            <input type="number" name="price" value="<?php echo $product['price']; ?>" step="0.01" placeholder="Price" required>
            <input type="number" name="stock" value="<?php echo $product['stock']; ?>" placeholder="Stock" required>
            <input type="number" name="min_stock" value="<?php echo $product['min_stock']; ?>" placeholder="Minimum Stock" required>
            <img src="<?php echo !empty($product['image']) ? $product['image'] : 'images/default.jpg'; ?>" alt="Product Image">
            <input type="file" name="image" accept="image/*">
            <button type="submit">Update Product</button>
        </form>
    </div>
</body>
</html>
