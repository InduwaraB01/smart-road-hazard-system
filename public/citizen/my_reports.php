<?php
session_start();
include(__DIR__ . "/../../config/db.php");

// Restrict access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch reports
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
            margin: 0;
            font-family: Arial;
            background: url('../../assets/images/road.jpg') no-repeat center/cover;
            color: white;
        }

        .container {
            padding: 20px;
        }

        h2 {
            text-align: center;
        }

        .report-card {
            background: rgba(0,0,0,0.8);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
        }

        .report-image img {
            width: 150px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
        }

        .report-details {
            flex: 1;
        }

        .report-details h3 {
            margin: 0;
        }

        .status {
            font-weight: bold;
        }

        .Reported { color: orange; }
        .In\ Progress { color: blue; }
        .Resolved { color: lightgreen; }
        .Rejected { color: red; }

        .map-link {
            color: cyan;
            text-decoration: underline;
        }

        .history {
            margin-top: 10px;
            font-size: 13px;
            background: rgba(255,255,255,0.1);
            padding: 10px;
            border-radius: 8px;
        }

        .btn {
            padding: 10px;
            background: #444;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }
    </style>
</head>

<body>

<h2 style="color: #444;">My Hazard Reports</h2>

<div class="container">


<?php if ($result->num_rows == 0): ?>
    <p>No reports submitted yet.</p>
<?php endif; ?>

<?php while($row = $result->fetch_assoc()): ?>

<div class="report-card">

    <!-- IMAGE -->
    <div class="report-image">
        <img src="../../<?= $row['image_path'] ?>">
    </div>

    <!-- DETAILS -->
    <div class="report-details">

        <h3><?= htmlspecialchars($row['category_name']) ?></h3>

        <p><?= htmlspecialchars($row['description']) ?></p>

        <p><b>Severity:</b> <?= $row['severity'] ?></p>

        <p>
            <a class="map-link" target="_blank"
            href="https://www.google.com/maps?q=<?= $row['latitude'] ?>,<?= $row['longitude'] ?>">
            View Location
            </a>
        </p>

        <p class="status <?= str_replace(' ', '\\ ', $row['status']) ?>">
            Status: <?= $row['status'] ?>
        </p>

        <!-- HISTORY -->
        <div class="history">
            <b>History:</b><br>

            <?php
            $hid = $row['hazard_id'];
            $history = $conn->query("SELECT su.*, u.full_name 
                FROM status_updates su
                JOIN users u ON su.updated_by = u.user_id
                WHERE su.hazard_id = $hid
                ORDER BY su.updated_at DESC");

            while($h = $history->fetch_assoc()):
            ?>

                <?= $h['updated_at'] ?> -
                <?= $h['status'] ?> by
                <?= htmlspecialchars($h['full_name']) ?><br>
                <i><?= htmlspecialchars($h['remarks']) ?></i><br><br>

            <?php endwhile; ?>
        </div>

    </div>

</div>

<?php endwhile; ?>

<button class="btn" onclick="window.location.href='dashboard.php'">
    Back to Dashboard
</button>

</div>

</body>
</html>