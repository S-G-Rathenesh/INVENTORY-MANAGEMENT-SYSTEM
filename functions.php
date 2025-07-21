<?php
// Start session for user authentication
session_start();

// Redirect if not logged in
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Check user role (admin or customer)
function get_user_role($user_id, $conn) {
    $query = "SELECT role FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($role);
    $stmt->fetch();
    return $role;
}
?>
