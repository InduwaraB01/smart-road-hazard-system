<?php
session_start();
include("../../config/db.php");

// Restrict to citizen only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch only logged-in user's hazards
$sql = "SELECT h.*, c.category_name 
        FROM hazards h
        JOIN categories c ON h.category_id = c.category_id
        WHERE h.user_id = ?
        ORDER BY h.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Reports</title>
</head>
<body>

<h2>My Hazard Reports</h2>

<table border="1" cellpadding="8">
<tr>
    <th>ID</th>
    <th>Category</th>
    <th>Description</th>
    <th>Severity</th>
    <th>Location</th>
    <th>Image</th>
    <th>Status</th>
    <th>Status History</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['hazard_id'] ?></td>
    <td><?= htmlspecialchars($row['category_name']) ?></td>
    <td><?= htmlspecialchars($row['description']) ?></td>
    <td><?= $row['severity'] ?></td>

    <td>
        <a href="https://www.google.com/maps?q=<?= $row['latitude'] ?>,<?= $row['longitude'] ?>" target="_blank">
            View Map
        </a>
    </td>

    <td>
        <img src="../../<?= $row['image_path'] ?>" width="100">
    </td>

    <td>
        <b><?= $row['status'] ?></b>
    </td>

    <td>
        <?php
        $hid = $row['hazard_id'];

        $history = $conn->prepare("SELECT su.*, u.full_name 
            FROM status_updates su
            JOIN users u ON su.updated_by = u.user_id
            WHERE su.hazard_id = ?
            ORDER BY su.updated_at DESC");

        $history->bind_param("i", $hid);
        $history->execute();
        $history_result = $history->get_result();

        while($h = $history_result->fetch_assoc()):
        ?>
            <div style="font-size:12px;">
                <?= $h['updated_at'] ?> -
                <b><?= $h['status'] ?></b> by
                <?= htmlspecialchars($h['full_name']) ?><br>
                Remarks: <?= htmlspecialchars($h['remarks']) ?>
            </div>
            <hr>
        <?php endwhile; ?>

    </td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>