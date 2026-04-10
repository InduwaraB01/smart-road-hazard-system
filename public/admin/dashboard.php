<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Stats
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$citizens = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='citizen'")->fetch_assoc()['count'];
$authorities = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='authority'")->fetch_assoc()['count'];
$total_hazards = $conn->query("SELECT COUNT(*) as count FROM hazards")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>

<h2>Welcome Admin: <?php echo $_SESSION['full_name']; ?></h2>

<hr>

<h3>System Overview</h3>

<ul>
    <li><b>Total Users:</b> <?php echo $total_users; ?></li>
    <li><b>Citizens:</b> <?php echo $citizens; ?></li>
    <li><b>Authorities:</b> <?php echo $authorities; ?></li>
    <li><b>Total Hazards:</b> <?php echo $total_hazards; ?></li>
</ul>

<hr>

<h3>Admin Actions</h3>

<a href="manage_users.php">Manage Users</a><br><br>
<a href="manage_categories.php">Manage Categories</a><br><br>
<a href="../logout.php">Logout</a>

</body>
</html>