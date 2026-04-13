<?php
session_start();
include("../../config/db.php");

// Restrict access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'authority') {
    header("Location: ../login.php");
    exit();
}

// Fetch stats
$total = $conn->query("SELECT COUNT(*) as count FROM hazards")->fetch_assoc()['count'];
$reported = $conn->query("SELECT COUNT(*) as count FROM hazards WHERE status='Reported'")->fetch_assoc()['count'];
$progress = $conn->query("SELECT COUNT(*) as count FROM hazards WHERE status='In Progress'")->fetch_assoc()['count'];
$resolved = $conn->query("SELECT COUNT(*) as count FROM hazards WHERE status='Resolved'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Authority Dashboard</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../../assets/css/authority.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: url('../../assets/images/road.jpg') no-repeat center center/cover;
        }

        .navbar {
            background: #222;
            padding: 15px;
            color: white;
        }

        .navbar a {
            color: white;
            margin-right: 15px;
            text-decoration: none;
            font-weight: bold;
        }

        .container {
            padding: 20px;
            color: white;
        }

        .cards {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .card {
            background: rgba(0,0,0,0.7);
            padding: 20px;
            border-radius: 10px;
            width: 200px;
            text-align: center;
        }

        .card img {
            width: 50px;
            margin-bottom: 10px;
        }

        .card h3 {
            margin: 10px 0;
        }

        .card p {
            font-size: 22px;
            font-weight: bold;
        }

        canvas {
            background: white;
            margin-top: 30px;
            border-radius: 10px;
            padding: 10px;
        }
    </style>
</head>

<body>

<div class="navbar">
    Welcome, <?php echo $_SESSION['full_name']; ?> |
    <a href="dashboard.php">Dashboard</a>
    <a href="manage_reports.php">Manage Reports</a>
    <a href="map_view.php">Map</a>
    <a href="change_password.php">Change Password</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="container">
    <h2>Authority Dashboard</h2>

    <!-- Cards -->
    <div class="cards">

        <div class="card">
            <img src="../../assets/images/dashboard.png">
            <h3>Total Reports</h3>
            <p><?php echo $total; ?></p>
        </div>

        <div class="card">
            <img src="../../assets/images/hazard.png">
            <h3>Reported</h3>
            <p><?php echo $reported; ?></p>
        </div>

        <div class="card">
            <img src="../../assets/images/in_progress.png">
            <h3>In Progress</h3>
            <p><?php echo $progress; ?></p>
        </div>

        <div class="card">
            <img src="../../assets/images/resolved.png">
            <h3>Resolved</h3>
            <p><?php echo $resolved; ?></p>
        </div>

    </div>

    <!-- Graph -->
    <canvas id="hazardChart" height="120"></canvas>

</div>

<!-- Chart Script -->
<script>
const ctx = document.getElementById('hazardChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Reported', 'In Progress', 'Resolved'],
        datasets: [{
            label: 'Hazard Status Overview',
            data: [
                <?php echo $reported; ?>,
                <?php echo $progress; ?>,
                <?php echo $resolved; ?>
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: {
                    color: 'black'
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

</body>
</html>