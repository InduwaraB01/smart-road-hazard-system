<?php
session_start();
include(__DIR__ . "/../../config/db.php");

// Only admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch current password from DB
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect!";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match!";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
        $update->bind_param("si", $hashed, $user_id);

        if ($update->execute()) {
            $success = "Password changed successfully!";
        } else {
            $error = "Error updating password.";
        }

        $update->close();
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: url('../../assets/images/admin-bg.jpg') no-repeat center/cover;
            color: white;
        }

        .navbar {
            background: #111;
            padding: 15px;
        }

        .navbar a {
            color: white;
            margin-right: 15px;
            text-decoration: none;
            font-weight: bold;
        }

        .container {
            padding: 30px;
        }

        .card {
            background: rgba(0,0,0,0.8);
            padding: 25px;
            border-radius: 10px;
            width: 350px;
        }

        input, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }

        button {
            background: green;
            color: white;
            border: none;
            cursor: pointer;
        }

        .success {
            color: lightgreen;
        }

        .error {
            color: red;
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
    <a href="../login.php">Logout</a>
</div>

<div class="container">

<h2>Change Password</h2>

<div class="card">

<?php if ($success): ?>
    <p class="success"><?php echo $success; ?></p>
<?php endif; ?>

<?php if ($error): ?>
    <p class="error"><?php echo $error; ?></p>
<?php endif; ?>

<form method="POST">

    <input type="password" name="current_password" placeholder="Current Password" required>

    <input type="password" name="new_password" placeholder="New Password" required>

    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>

    <button type="submit">Update Password</button>

</form>

</div>

</div>

</body>
</html>