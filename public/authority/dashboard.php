<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'authority') {
    header("Location: ../login.php");
    exit();
}

$total = $conn->query("SELECT COUNT(*) as c FROM hazards")->fetch_assoc()['c'];
$reported = $conn->query("SELECT COUNT(*) as c FROM hazards WHERE status='Reported'")->fetch_assoc()['c'];
$progress = $conn->query("SELECT COUNT(*) as c FROM hazards WHERE status='In Progress'")->fetch_assoc()['c'];
$resolved = $conn->query("SELECT COUNT(*) as c FROM hazards WHERE status='Resolved'")->fetch_assoc()['c'];
$rejected = $conn->query("SELECT COUNT(*) as c FROM hazards WHERE status='Rejected'")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Authority Dashboard</title>
    <link rel="stylesheet" href="../assets/css/authority.css">
</head>
<body>

<div class="container">

    <header>
        <h2>Authority Dashboard</h2>
        <p>Welcome, <?php echo $_SESSION['full_name']; ?></p>
    </header>

    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_reports.php">Manage Reports</a>
        <a href="map_view.php">Map</a>
        <a href="change_password.php">Change Password</a>
        <a href="../logout.php">Logout</a>
    </nav>

    <section class="stats">
        <div class="card">Total<br><?php echo $total; ?></div>
        <div class="card">Reported<br><?php echo $reported; ?></div>
        <div class="card">In Progress<br><?php echo $progress; ?></div>
        <div class="card">Resolved<br><?php echo $resolved; ?></div>
        <div class="card">Rejected<br><?php echo $rejected; ?></div>
    </section>

    <section class="chart">
        <canvas id="statusChart"></canvas>
    </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('statusChart'), {
    type: 'bar',
    data: {
        labels: ['Reported', 'In Progress', 'Resolved', 'Rejected'],
        datasets: [{
            label: 'Hazards',
            data: [
                <?php echo $reported; ?>,
                <?php echo $progress; ?>,
                <?php echo $resolved; ?>,
                <?php echo $rejected; ?>
            ]
        }]
    }
});
</script>

</body>
</html>