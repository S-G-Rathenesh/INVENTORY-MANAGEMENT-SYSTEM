<?php
// Start session and connect to the database if needed
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            overflow: hidden;
            background-image: url("images/index.jpg");
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            color: white;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Dark overlay for better readability */
            z-index: 1;
        }

        .content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .content h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
        }

        .content p {
            font-size: 18px;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
            margin-bottom: 30px;
        }

        .actions {
            display: flex;
            gap: 20px;
        }

        .actions a {
            padding: 12px 24px;
            background: linear-gradient(to right, #4caf50, #81c784);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            transition: transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .actions a:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .content {
                padding: 20px;
                max-width: 90%;
            }
        }
    </style>
</head>
<body>

<div class="content">
    <h1>Inventory Management System</h1>
    <p>Effortlessly manage your inventory with features like stock tracking, sales reports, and analytics.</p>
    <div class="actions">
        <a href="register.php">Register</a>
        <a href="login.php">Login</a>
    </div>
</div>

</body>
</html>
