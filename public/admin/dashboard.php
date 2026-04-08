<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

echo "Welcome Citizen: " . $_SESSION['full_name'];
?>
