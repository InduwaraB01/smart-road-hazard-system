<?php
session_start();

// Restrict access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Citizen Dashboard</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: url('../../assets/images/road2.jpg') no-repeat center center/cover;
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
            margin-top: 20px;
        }

        .card {
            background: rgba(0,0,0,0.7);
            padding: 20px;
            border-radius: 10px;
            width: 220px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
        }

        .card:hover {
            transform: scale(1.05);
            background: rgba(0,0,0,0.85);
        }

        .card img {
            width: 60px;
            margin-bottom: 10px;
        }

        .card h3 {
            margin: 10px 0;
        }
    </style>
</head>

<body>

<div class="navbar">
    Welcome, <?php echo $_SESSION['full_name']; ?> |
    <a href="dashboard.php">Dashboard</a>
    <a href="report_hazard.php">Report Hazard</a>
    <a href="my_reports.php">My Reports</a>
    <a href="../login.php">Logout</a>
</div>

<div class="container">
    <h2>Citizen Dashboard</h2>

    <div class="cards">

        <!-- Report Hazard -->
        <div class="card" onclick="window.location.href='report_hazards.php'">
            <img src="../../assets/images/hazard.png">
            <h3>Report Hazard</h3>
            <p>Submit a new road issue</p>
        </div>

        <!-- My Reports -->
        <div class="card" onclick="window.location.href='my_reports.php'">
            <img src="../../assets/images/dashboard.png">
            <h3>My Reports</h3>
            <p>View your submitted reports</p>
        </div>

       
    </div>
</div>

</body>
</html>