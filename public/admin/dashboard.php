<?php
session_start();
include("../../config/db.php");

// Restrict access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Stats
$total_users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_reports = $conn->query("SELECT COUNT(*) as c FROM hazards")->fetch_assoc()['c'];
$reported = $conn->query("SELECT COUNT(*) as c FROM hazards WHERE status='Reported'")->fetch_assoc()['c'];
$progress = $conn->query("SELECT COUNT(*) as c FROM hazards WHERE status='In Progress'")->fetch_assoc()['c'];
$resolved = $conn->query("SELECT COUNT(*) as c FROM hazards WHERE status='Resolved'")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: url('../../assets/images/admin-bg.jpg') no-repeat center/cover;
        }

        .navbar {
            background: #111;
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
            background: rgba(0,0,0,0.75);
            padding: 20px;
            border-radius: 12px;
            width: 220px;
            text-align: center;
        }

        .card img {
            width: 60px;
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
            border-radius: 10px;
            margin-top: 30px;
            padding: 10px;
        }
    </style>
</head>

<body>

<div class="navbar">
    Welcome Admin: <?php echo $_SESSION['full_name']; ?> |
    <a href="dashboard.php">Dashboard</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_categories.php">Manage Categories</a>
    <a href="manage_reports.php">Manage Reports</a>
    <a href="change_password.php">Change Password</a>
    <a href="../login.php">Logout</a>
</div>

<div class="container">

<h2>Admin Dashboard</h2>

<div class="cards">

    <div class="card">
        <img src="../../assets/images/users2.png" alt="Users">
        <h3>Total Users</h3>
        <p><?php echo $total_users; ?></p>
    </div>

    <div class="card">
        <img src="../../assets/images/total-reported.png" alt="Reports">
        <h3>Total Reported cases</h3>
        <p><?php echo $total_reports; ?></p>
    </div>

    <div class="card">
        <img src="../../assets/images/hazard.png" alt="Reported">
        <h3>Reported cases</h3>
        <p><?php echo $reported; ?></p>
    </div>

    <div class="card">
        <img src="../../assets/images/in-progress.png" alt="In Progress">
        <h3>In Progress</h3>
        <p><?php echo $progress; ?></p>
    </div>

    <div class="card">
        <img src="../../assets/images/resolved.png" alt="Resolved">
        <h3>Resolved</h3>
        <p><?php echo $resolved; ?></p>
    </div>

</div>

<!-- GRAPH -->
<canvas id="adminChart" height="120"></canvas>

</div>

<script>
const ctx = document.getElementById('adminChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Reported', 'In Progress', 'Resolved'],
        datasets: [{
            label: 'Hazard Status',
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
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

</body>
</html>