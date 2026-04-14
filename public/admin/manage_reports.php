<?php
session_start();
include(__DIR__ . "/../../config/db.php");

// Restrict access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// HANDLE STATUS UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {

    $hazard_id = intval($_POST['hazard_id']);
    $status = $_POST['status'];
    $remarks = trim($_POST['remarks']);
    $updated_by = $_SESSION['user_id'];

    // Insert into history
    $stmt1 = $conn->prepare("INSERT INTO status_updates (hazard_id, updated_by, status, remarks) VALUES (?, ?, ?, ?)");
    $stmt1->bind_param("iiss", $hazard_id, $updated_by, $status, $remarks);
    $stmt1->execute();
    $stmt1->close();

    // Update main table
    $stmt2 = $conn->prepare("UPDATE hazards SET status=? WHERE hazard_id=?");
    $stmt2->bind_param("si", $status, $hazard_id);
    $stmt2->execute();
    $stmt2->close();

    header("Location: manage_reports.php");
    exit();
}

// FETCH REPORTS
$sql = "SELECT h.*, c.category_name, u.full_name 
        FROM hazards h
        JOIN categories c ON h.category_id = c.category_id
        JOIN users u ON h.user_id = u.user_id
        ORDER BY h.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Reports</title>

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

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            color: black;
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

        button {
            padding: 6px 10px;
            background: green;
            color: white;
            border: none;
            cursor: pointer;
        }

        .history {
            font-size: 12px;
            text-align: left;
            background: #f5f5f5;
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
    <a href="manage_users.php">Users</a>
    <a href="manage_categories.php">Categories</a>
    <a href="manage_reports.php">Reports</a>
    <a href="change_password.php">Change Password</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="container">

<h2>Admin - Manage Reports</h2>

<table>
<tr>
    <th>ID</th>
    <th>Reporter</th>
    <th>Category</th>
    <th>Description</th>
    <th>Severity</th>
    <th>Location</th>
    <th>Image</th>
    <th>Status</th>
    <th>Update</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>

<td><?= $row['hazard_id'] ?></td>

<td><?= htmlspecialchars($row['full_name']) ?></td>

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

<!-- UPDATE FORM -->
<form method="POST">
    <input type="hidden" name="hazard_id" value="<?= $row['hazard_id'] ?>">

    <select name="status" required>
        <option value="In Progress">In Progress</option>
        <option value="Resolved">Resolved</option>
        <option value="Rejected">Rejected</option>
    </select>

    <br><br>

    <input type="text" name="remarks" placeholder="Remarks" required>

    <br><br>

    <button name="update_status">Update</button>
</form>

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
Remarks: <?= htmlspecialchars($h['remarks']) ?><br><br>

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