<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

echo "Welcome Citizen: " . $_SESSION['full_name'];
?>
<a href="report_hazards.php">Report Hazards</a>
<a href="my_reports.php">My Reports</a>