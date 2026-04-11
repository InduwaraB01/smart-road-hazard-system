<?php
session_start();
include("../../config/db.php");

// Only admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = "";
$error = "";

// ➤ ADD USER
if (isset($_POST['add_user'])) {

    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!empty($name) && !empty($email) && !empty($password)) {

        // Check if email exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed, $role);

            if ($stmt->execute()) {
                $success = "User created successfully!";
            } else {
                $error = "Error creating user.";
            }

            $stmt->close();
        }

        $check->close();
    } else {
        $error = "All fields are required.";
    }
}

// ➤ DELETE USER
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    if ($id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE user_id = $id");
    }
}

// ➤ FETCH USERS
$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
</head>
<body>

<h2>Manage Users</h2>

<!-- SUCCESS / ERROR -->
<?php if ($success): ?>
    <p style="color:green;"><?php echo $success; ?></p>
<?php endif; ?>

<?php if ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php endif; ?>

<!-- ➤ ADD USER FORM -->
<h3>Add New User</h3>
<form method="POST">
    <input type="text" name="full_name" placeholder="Full Name" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>

    <select name="role" required>
        <option value="citizen">Citizen</option>
        <option value="authority">Authority</option>
        <option value="admin">Admin</option>
    </select>
    <br><br>

    <button type="submit" name="add_user">Add User</button>
</form>

<hr>

<!-- ➤ USER LIST -->
<h3>User List</h3>

<table border="1" cellpadding="8">
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Role</th>
    <th>Created</th>
    <th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['user_id'] ?></td>
    <td><?= htmlspecialchars($row['full_name']) ?></td>
    <td><?= htmlspecialchars($row['email']) ?></td>
    <td><?= $row['role'] ?></td>
    <td><?= $row['created_at'] ?></td>
    <td>
        <?php if ($row['user_id'] != $_SESSION['user_id']): ?>
            <a href="?delete=<?= $row['user_id'] ?>" onclick="return confirm('Delete this user?')">
                Delete
            </a>
        <?php else: ?>
            (You)
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>

</table>

<br>
<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>