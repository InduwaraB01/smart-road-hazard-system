<?php
// navbar.php
if (!isset($_SESSION)) {
    session_start();
}
?>

<div class="navbar">
    <span>Welcome: <?php echo $_SESSION['full_name']; ?></span> |

    <?php if ($_SESSION['role'] == 'admin'): ?>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_users.php">Users</a>
        <a href="manage_categories.php">Categories</a>
        <a href="manage_reports.php">Reports</a>

    <?php elseif ($_SESSION['role'] == 'authority'): ?>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_reports.php">Manage Reports</a>

    <?php elseif ($_SESSION['role'] == 'citizen'): ?>
        <a href="dashboard.php">Dashboard</a>
        <a href="report_hazard.php">Report Hazard</a>
        <a href="my_reports.php">My Reports</a>
    <?php endif; ?>

    <a href="change_password.php">Change Password</a>
    <a href="../logout.php">Logout</a>
</div>