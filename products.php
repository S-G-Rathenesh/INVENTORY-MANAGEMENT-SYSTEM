<?php
include('db.php');
include('functions.php');
check_login();

// Fetch categories
$categories_query = "SELECT DISTINCT category FROM products";
$categories_result = $conn->query($categories_query);

if ($categories_result->num_rows > 0) {
    echo "<div style='text-align: center; margin-top: 20px; font-family: Arial, sans-serif;'>";
    echo "<h1>Products</h1>";

    while ($category_row = $categories_result->fetch_assoc()) {
        $category = $category_row['category'];

        // Fetch products for the current category
        $products_query = "SELECT * FROM products WHERE category = ?";
        $stmt = $conn->prepare($products_query);
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $products_result = $stmt->get_result();

        if ($products_result->num_rows > 0) {
            echo "<div style='margin-top: 30px;'>";
            echo "<h2 style='color: #333;'>$category</h2>";
            echo "<div style='display: flex; flex-wrap: wrap; justify-content: center;'>";

            while ($product = $products_result->fetch_assoc()) {
                echo "<div style='border: 1px solid #ddd; border-radius: 5px; margin: 10px; padding: 15px; width: 200px; text-align: center;'>";
                echo "<img src='images/{$product['image']}' alt='{$product['name']}' style='width: 100%; height: 150px; object-fit: cover; border-radius: 5px;'>";
                echo "<h3 style='font-size: 16px; color: #555;'>{$product['name']}</h3>";
                echo "<p style='color: #888;'>Price: ₹{$product['price']}</p>";
                echo "<p style='color: #888;'>Stock: {$product['stock']}</p>";
                echo "<button style='background-color: #28a745; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;'>Add to Cart</button>";
                echo "</div>";
            }

            echo "</div>";
            echo "</div>";
        }
    }

    echo "</div>";
} else {
    echo "<p>No categories found.</p>";
}
?>
