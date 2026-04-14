<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = "";
$error = "";

// ➤ SEARCH
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// ➤ ADD USER
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

            if ($stmt->execute()) $success = "User created!";
            else $error = "Error creating user.";

            $stmt->close();
        }
        $check->close();
    }
}

// ➤ DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE user_id=$id");
    }
}

// ➤ UPDATE USER
if (isset($_POST['update_user'])) {
    $id = intval($_POST['user_id']);
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, role=? WHERE user_id=?");
    $stmt->bind_param("sssi", $name, $email, $role, $id);

    if ($stmt->execute()) $success = "User updated!";
    else $error = "Update failed.";

    $stmt->close();
}

// ➤ FETCH USERS (WITH SEARCH)
if ($search != "") {
    $stmt = $conn->prepare("SELECT * FROM users WHERE full_name LIKE ? OR email LIKE ?");
    $like = "%$search%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Users</title>

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
    padding: 8px;
    margin-bottom: 8px;
}

button {
    padding: 8px;
    background: green;
    color: white;
    border: none;
    cursor: pointer;
}

table {
    width: 100%;
    background: white;
    color: black;
    border-collapse: collapse;
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

/* ROLE COLORS */
.role-admin { color: limegreen; font-weight: bold; }
.role-authority { color: blue; font-weight: bold; }
.role-citizen { color: orange; font-weight: bold; }

.edit-form {
    display: none;
}
</style>

<script>
function toggleEdit(id) {
    var form = document.getElementById("edit-" + id);
    form.style.display = (form.style.display === "none") ? "block" : "none";
}
</script>

</head>
<body>

<div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="manage_users.php">Users</a>
    <a href="manage_categories.php">Categories</a>
    <a href="manage_reports.php">Reports</a>
    <a href="../logout.php">Logout</a>
</div>
<h2>Manage Users</h2>
<div class="container">



<p style="color:lightgreen;"><?php echo $success; ?></p>
<p style="color:red;"><?php echo $error; ?></p>

<!-- SEARCH -->
<div class="card">
<form method="GET">
    <input type="text" name="search" placeholder="Search users..." value="<?= $search ?>">
    <button type="submit">Search</button>
</form>
</div>

<!-- ADD USER -->
<div class="card">
<h3>Add User</h3>
<form method="POST">
    <input type="text" name="full_name" placeholder="Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>

    <select name="role">
        <option value="citizen">Citizen</option>
        <option value="authority">Authority</option>
        <option value="admin">Admin</option>
    </select>

    <button name="add_user">Add</button>
</form>
</div>

<!-- USERS TABLE -->
<div class="card">
<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?= $row['user_id'] ?></td>
<td><?= htmlspecialchars($row['full_name']) ?></td>
<td><?= htmlspecialchars($row['email']) ?></td>

<td class="role-<?= $row['role'] ?>">
    <?= ucfirst($row['role']) ?>
</td>

<td>
    <button onclick="toggleEdit(<?= $row['user_id'] ?>)">Edit</button>

    <?php if ($row['user_id'] != $_SESSION['user_id']): ?>
        <a href="?delete=<?= $row['user_id'] ?>" onclick="return confirm('Delete?')">Delete</a>
    <?php endif; ?>

    <!-- EDIT FORM -->
    <form method="POST" id="edit-<?= $row['user_id'] ?>" class="edit-form">
        <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
        <input type="text" name="full_name" value="<?= $row['full_name'] ?>">
        <input type="email" name="email" value="<?= $row['email'] ?>">

        <select name="role">
            <option value="citizen">Citizen</option>
            <option value="authority">Authority</option>
            <option value="admin">Admin</option>
        </select>

        <button name="update_user">Update</button>
    </form>
</td>
</tr>
<?php endwhile; ?>

</table>
</div>

</div>

</body>
</html>