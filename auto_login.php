<?php
session_start();
include 'connector.php';

if (!isset($_SESSION['email']) && isset($_COOKIE['email']) && isset($_COOKIE['password'])) {
    $email = $_COOKIE['email'];
    $password = $_COOKIE['password'];

    // Validate user credentials
    $sql = "SELECT * FROM portfolio WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['email'] = $user['email'];
    } else {
        setcookie('email', '', time() - 3600, "/");
        setcookie('password', '', time() - 3600, "/");
    }

    $stmt->close();
}

$conn->close();
?>
