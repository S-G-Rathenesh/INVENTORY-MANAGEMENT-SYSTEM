<?php
include('db.php');
if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND token = ?");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $update = $conn->prepare("UPDATE users SET verified = 1, token = NULL WHERE email = ?");
        $update->bind_param("s", $email);
        $update->execute();
        echo "Email verified! You can now log in.";
    } else {
        echo "Invalid verification link.";
    }
} else {
    echo "Invalid request.";
}
?>
