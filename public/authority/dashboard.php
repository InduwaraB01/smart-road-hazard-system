<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'authority') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Authority Dashboard</title>
</head>
<body>

<h2>Welcome Authority: <?php echo $_SESSION['full_name']; ?></h2>

<br>
<a href="manage_reports.php">Manage Reports</a>
<br><br>
<a href="change_password.php">Change Password</a>
<br><br>
<a href="../logout.php">Logout</a>

</body>
</html>