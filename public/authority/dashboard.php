<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'authority') {
    header("Location: ../login.php");
    exit();
}

// Stats
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

    <!-- HEADER -->
    <header>
        <div class="header-content">
            <img src="../assets/images/dashboard.png" class="icon">
            <h2>Authority Dashboard</h2>
        </div>
        <p>Welcome, <?php echo $_SESSION['full_name']; ?></p>
    </header>

    <!-- NAVIGATION -->
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_reports.php">Manage Reports</a>
        <a href="map_view.php">Map</a>
        <a href="change_password.php">Change Password</a>
        <a href="../logout.php">Logout</a>
    </nav>

    <!-- STATS -->
    <section class="stats">
        <div class="card">
            <img src="../assets/images/hazard.png">
            <p>Total</p>
            <h3><?php echo $total; ?></h3>
        </div>

        <div class="card">
            <img src="../assets/images/hazard.png">
            <p>Reported</p>
            <h3><?php echo $reported; ?></h3>
        </div>

        <div class="card">
            <img src="../assets/images/hazard.png">
            <p>In Progress</p>
            <h3><?php echo $progress; ?></h3>
        </div>

        <div class="card">
            <img src="../assets/images/hazard.png">
            <p>Resolved</p>
            <h3><?php echo $resolved; ?></h3>
        </div>

        <div class="card">
            <img src="../assets/images/hazard.png">
            <p>Rejected</p>
            <h3><?php echo $rejected; ?></h3>
        </div>
    </section>

    <!-- CHART -->
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
    },
    options: {
        responsive: true
    }
});
</script>

</body>
</html>