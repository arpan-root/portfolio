<?php
include 'connector.php'; // Include database connection details
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $number = $_POST['number'];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Check if username contains only alphabets
    if (!ctype_alpha($username)) {
        echo "<script>alert('Username should only contain alphabetic characters'); history.back();</script>";
        exit;
    }

    // Check if email ends with "@gmail.com"
    if (!endsWith($email, "@gmail.com")) {
        echo "<script>alert('Email should end with @gmail.com'); history.back();</script>";
        exit;
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match'); history.back();</script>";
        exit;
    }

    // Check password strength
    if (!isValidPassword($password)) {
        echo "<script>alert('Password must have at least 8 characters, 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character among @_-'); history.back();</script>";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO portfolio (username, email, password, number) VALUES (?, ?, ?,?)");
    $stmt->bind_param("sssi", $username, $email, $hashed_password, $number);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo "<script>alert('New record inserted successfully'); window.location.href='log.html';</script>";
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Function to check if string ends with a specific substring
function endsWith($string, $endString) {
    $len = strlen($endString);
    if ($len == 0) {
        return true;
    }
    return (substr($string, -$len) === $endString);
}

// Function to validate password
function isValidPassword($password) {
    $hasUpperCase = preg_match('/[A-Z]/', $password);
    $hasLowerCase = preg_match('/[a-z]/', $password);
    $hasNumber = preg_match('/[0-9]/', $password);
    $hasSpecialChar = preg_match('/[@_\-\.]/', $password);
    $hasMinLength = strlen($password) >= 8;

    return $hasUpperCase && $hasLowerCase && $hasNumber && $hasSpecialChar && $hasMinLength;
}
?>
