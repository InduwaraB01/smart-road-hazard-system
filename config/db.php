<?php
$servername = "localhost";
$username = "root";
$password = ""; // default XAMPP password is empty
$database = "road_hazard_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
