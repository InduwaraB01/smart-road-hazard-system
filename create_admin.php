<?php
include("config/db.php");

$full_name = "System Admin";
$email = "admin@gmail.com";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$role = "admin";

$stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $full_name, $email, $password, $role);

if ($stmt->execute()) {
    echo "Admin created successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
?>