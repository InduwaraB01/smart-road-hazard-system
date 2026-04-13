<?php
session_start();
include("../../config/db.php");

// Restrict access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'authority') {
    header("Location: ../login.php");
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {

    $hazard_id = intval($_POST['hazard_id']);
    $status = $_POST['status'];
    $remarks = trim($_POST['remarks']);
    $updated_by = $_SESSION['user_id'];

    // Insert into status_updates
    $stmt1 = $conn->prepare("INSERT INTO status_updates (hazard_id, updated_by, status, remarks) VALUES (?, ?, ?, ?)");
    $stmt1->bind_param("iiss", $hazard_id, $updated_by, $status, $remarks);
    $stmt1->execute();
    $stmt1->close();

    // Update hazards table
    $stmt2 = $conn->prepare("UPDATE hazards SET status=? WHERE hazard_id=?");
    $stmt2->bind_param("si", $status, $hazard_id);
    $stmt2->execute();
    $stmt2->close();

    header("Location: manage_reports.php");
    exit();
}

// Fetch data
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
            font-family: Arial;
            margin: 0;
            background: #f4f6f9;
        }

        .navbar {
            background: #222;
            color: white;
            padding: 15px;
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
            text-align: center;
            border-bottom: 1px solid #ddd;
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

        /* FORM */
        select, input {
            padding: 5px;
            width: 90%;
        }

        button {
            padding: 6px 10px;
            background: #28a745;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background: #218838;
        }
    </style>
</head>

<body>

<div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="map_view.php">Map</a>
    <a href="change_password.php">Change Password</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="container">

<h2>Manage Hazard Reports</h2>

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
        <?= $row['latitude'] ?><br>
        <?= $row['longitude'] ?>
    </td>

    <td>
        <img src="../../<?= $row['image_path'] ?>" width="80">
    </td>

    <td class="status-<?= str_replace(' ', '\\ ', $row['status']) ?>">
        <?= $row['status'] ?>
    </td>

    <td>
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

            <button type="submit" name="update_status">Update</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>

</table>

</div>

</body>
</html>