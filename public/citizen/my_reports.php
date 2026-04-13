<?php
session_start();
include("../../config/db.php");

// Restrict access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user reports
$sql = "SELECT h.*, c.category_name 
        FROM hazards h
        JOIN categories c ON h.category_id = c.category_id
        WHERE h.user_id = $user_id
        ORDER BY h.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Reports</title>

    <style>
        body {
            font-family: Arial;
            margin: 0;
            background: #f4f6f9;
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
        }

        .container {
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #333;
            color: white;
        }

        img {
            border-radius: 6px;
        }

        /* STATUS COLORS */
        .status-Reported { color: orange; font-weight: bold; }
        .status-In\ Progress { color: blue; font-weight: bold; }
        .status-Resolved { color: green; font-weight: bold; }
        .status-Rejected { color: red; font-weight: bold; }

        .history {
            font-size: 12px;
            text-align: left;
            background: #f9f9f9;
            padding: 5px;
            border-radius: 5px;
            margin-top: 5px;
        }

        .map-link {
            color: blue;
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="report_hazard.php">Report Hazard</a>
    <a href="my_reports.php">My Reports</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="container">

<h2>My Hazard Reports</h2>

<table>
<tr>
    <th>ID</th>
    <th>Category</th>
    <th>Description</th>
    <th>Severity</th>
    <th>Location</th>
    <th>Image</th>
    <th>Status</th>
    <th>History</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['hazard_id'] ?></td>
    <td><?= htmlspecialchars($row['category_name']) ?></td>
    <td><?= htmlspecialchars($row['description']) ?></td>
    <td><?= $row['severity'] ?></td>

    <td>
        <a class="map-link" target="_blank"
        href="https://www.google.com/maps?q=<?= $row['latitude'] ?>,<?= $row['longitude'] ?>">
        View Map
        </a>
    </td>

    <td>
        <img src="../../<?= $row['image_path'] ?>" width="80">
    </td>

    <td class="status-<?= str_replace(' ', '\\ ', $row['status']) ?>">
        <?= $row['status'] ?>
    </td>

    <td>
        <div class="history">
        <?php
        $hid = $row['hazard_id'];
        $history = $conn->query("SELECT su.*, u.full_name 
            FROM status_updates su
            JOIN users u ON su.updated_by = u.user_id
            WHERE su.hazard_id = $hid
            ORDER BY su.updated_at DESC");

        while($h = $history->fetch_assoc()):
        ?>
            <?= $h['updated_at'] ?><br>
            <?= $h['status'] ?> by <?= htmlspecialchars($h['full_name']) ?><br>
            <i><?= htmlspecialchars($h['remarks']) ?></i><br><br>
        <?php endwhile; ?>
        </div>
    </td>
</tr>
<?php endwhile; ?>

</table>

<br>
<button onclick="window.location.href='dashboard.php'">Back to Dashboard</button>

</div>

</body>
</html>