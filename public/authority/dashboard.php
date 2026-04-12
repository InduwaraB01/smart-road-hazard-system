<?php
session_start();
include("../../config/db.php");

// Restrict access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'authority') {
    header("Location: ../login.php");
    exit();
}

// Fetch stats
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
</head>
<body>

<h2>Welcome Authority: <?php echo $_SESSION['full_name']; ?></h2>

<hr>

<h3>System Overview</h3>

<ul>
    <li><b>Total Reports:</b> <?php echo $total; ?></li>
    <li><b>Reported:</b> <?php echo $reported; ?></li>
    <li><b>In Progress:</b> <?php echo $progress; ?></li>
    <li><b>Resolved:</b> <?php echo $resolved; ?></li>
    <li><b>Rejected:</b> <?php echo $rejected; ?></li>
</ul>

<hr>

<h3>Report Status Chart</h3>

<canvas id="statusChart" width="400" height="200"></canvas>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('statusChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Reported', 'In Progress', 'Resolved', 'Rejected'],
        datasets: [{
            label: 'Hazard Reports',
            data: [
                <?php echo $reported; ?>,
                <?php echo $progress; ?>,
                <?php echo $resolved; ?>,
                <?php echo $rejected; ?>
            ]
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<hr>

<h3>Actions</h3>

<a href="manage_reports.php">Manage Reports</a><br><br>
<a href="change_password.php">Change Password</a><br><br>
<a href="../login.php">Logout</a>

</body>
</html>