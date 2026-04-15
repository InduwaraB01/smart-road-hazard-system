<?php
session_start();
include("../../config/db.php");

// Only authority allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'authority') {
    header("Location: ../login.php");
    exit();
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Get current hashed password from DB
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    // Verify current password
    if (!password_verify($current_password, $hashed_password)) {
        $error = "Current password is incorrect.";
    }
    elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    }
    elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    }
    else {
        // Hash new password
        $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update->bind_param("si", $new_hashed, $user_id);

        if ($update->execute()) {
            $success = "Password changed successfully.";
        } else {
            $error = "Something went wrong.";
        }

        $update->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
</head>
<body>

<h2>Change Password</h2>

<?php if ($success): ?>
<p style="color: green;"><?php echo $success; ?></p>
<?php endif; ?>

<?php if ($error): ?>
<p style="color: red;"><?php echo $error; ?></p>
<?php endif; ?>

<form method="POST">
    <label>Current Password:</label><br>
    <input type="password" name="current_password" required><br><br>

    <label>New Password:</label><br>
    <input type="password" name="new_password" required><br><br>

    <label>Confirm New Password:</label><br>
    <input type="password" name="confirm_password" required><br><br>

    <button type="submit">Update Password</button>
    <button type="button" onclick="window.location.href='dashboard.php'">Cancel</button>
</form>

</body>
</html>