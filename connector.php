<?php
// Load the key and IV
$key = file_get_contents('storage/encryption_key.bin');
$iv = file_get_contents('storage/encryption_iv.bin');

// Load the encrypted data
$encrypted_servername = file_get_contents('storage/encrypted_servername.bin');
$encrypted_username = file_get_contents('storage/encrypted_username.bin');
$encrypted_password = file_get_contents('storage/encrypted_password.bin');
$encrypted_database = file_get_contents('storage/encrypted_database.bin');

// Decrypt the data
$servername = openssl_decrypt($encrypted_servername, 'aes-256-cbc', $key, 0, $iv);
$username = openssl_decrypt($encrypted_username, 'aes-256-cbc', $key, 0, $iv);
$password = openssl_decrypt($encrypted_password, 'aes-256-cbc', $key, 0, $iv);
$database = openssl_decrypt($encrypted_database, 'aes-256-cbc', $key, 0, $iv);

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
