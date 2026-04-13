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

// ADD USER
if (isset($_POST['add_user'])) {

    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!empty($name) && !empty($email) && !empty($password)) {

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

// DELETE USER
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    if ($id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE user_id = $id");
    }
}

// FETCH USERS
$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: url('../../assets/images/admin-bg.jpg') no-repeat center center/cover;
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

        .card {
            background: rgba(0,0,0,0.75);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: none;
        }

        button {
            padding: 10px;
            background: #28a745;
            border: none;
            color: white;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background: #218838;
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

        .delete-btn {
            color: red;
            font-weight: bold;
        }

        .success { color: lightgreen; }
        .error { color: red; }
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

<h2>Manage Users</h2>

<?php if ($success): ?>
    <p class="success"><?php echo $success; ?></p>
<?php endif; ?>

<?php if ($error): ?>
    <p class="error"><?php echo $error; ?></p>
<?php endif; ?>

<!-- ADD USER -->
<div class="card">
<h3>Add New User</h3>

<form method="POST">
    <input type="text" name="full_name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>

    <select name="role" required>
        <option value="citizen">Citizen</option>
        <option value="authority">Authority</option>
        <option value="admin">Admin</option>
    </select>

    <button type="submit" name="add_user">Add User</button>
</form>
</div>

<!-- USER LIST -->
<div class="card">
<h3>User List</h3>

<table>
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
            <a class="delete-btn" href="?delete=<?= $row['user_id'] ?>" onclick="return confirm('Delete this user?')">
                Delete
            </a>
        <?php else: ?>
            (You)
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>

</table>
</div>

</div>

</body>
</html>