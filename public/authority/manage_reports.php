<?php
session_start();
include("../../config/db.php");

// Restrict access to authority only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'authority') {
    header("Location: ../login.php");
    exit();
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {

    $hazard_id = intval($_POST['hazard_id']);
    $new_status = $_POST['status'];
    $remarks = trim($_POST['remarks']);
    $updated_by = $_SESSION['user_id'];

    // 1️⃣ Insert into status_updates table
    $stmt1 = $conn->prepare("INSERT INTO status_updates 
        (hazard_id, updated_by, status, remarks) 
        VALUES (?, ?, ?, ?)");

    $stmt1->bind_param("iiss", 
        $hazard_id, 
        $updated_by, 
        $new_status, 
        $remarks
    );
    $stmt1->execute();
    $stmt1->close();

    // 2️⃣ Update hazards main table
    $stmt2 = $conn->prepare("UPDATE hazards SET status=? WHERE hazard_id=?");
    $stmt2->bind_param("si", $new_status, $hazard_id);
    $stmt2->execute();
    $stmt2->close();

    header("Location: manage_reports.php");
    exit();
}

// Fetch all hazards
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
</head>
<body>

<h2>Authority - Manage Hazard Reports</h2>

<table border="1" cellpadding="8">
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
        <a href="https://www.google.com/maps?q=<?= $row['latitude'] ?>,<?= $row['longitude'] ?>" target="_blank">
    View on Map
</a>
    </td>
    <td>
        <img src="../../<?= $row['image_path'] ?>" width="100">
    </td>
    <td><?= $row['status'] ?></td>
    <td>
        <form method="POST">
            <input type="hidden" name="hazard_id" value="<?= $row['hazard_id'] ?>">

            <select name="status" required>
                <option value="In Progress">In Progress</option>
                <option value="Resolved">Resolved</option>
                <option value="Rejected">Rejected</option>
            </select>
            <br><br>

            <input type="text" name="remarks" placeholder="Enter remarks" required>
            <br><br>

            <button type="submit" name="update_status">Update</button>
        </form>

        <?php

        // Fetch status update history for this hazard
$hid = $row['hazard_id'];
$history = $conn->query("SELECT su.*, u.full_name 
    FROM status_updates su
    JOIN users u ON su.updated_by = u.user_id
    WHERE su.hazard_id = $hid
    ORDER BY su.updated_at DESC");
?>

<?php while($h = $history->fetch_assoc()): ?>
    <div style="font-size:12px;">
        <?= $h['updated_at'] ?> -
        <?= $h['status'] ?> by
        <?= htmlspecialchars($h['full_name']) ?> <br>
        Remarks: <?= htmlspecialchars($h['remarks']) ?>
    </div>
<?php endwhile; ?>
    </td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>