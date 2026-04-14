<?php
session_start();
include(__DIR__ . "/../../config/db.php");

// Only admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = "";

// ➤ ADD CATEGORY
if (isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);

    if (!empty($category_name)) {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $category_name);

        if ($stmt->execute()) {
            $success = "Category added successfully!";
        }

        $stmt->close();
    }
}

// ➤ DELETE CATEGORY
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM categories WHERE category_id = $id");
}

// ➤ FETCH
$result = $conn->query("SELECT * FROM categories ORDER BY category_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>

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
           
        }

        .container {
            padding: 20px;
        }

        .card {
            background: rgba(0,0,0,0.75);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            width: 400px;
        }

        input, button {
            padding: 10px;
            width: 100%;
            margin-top: 10px;
        }

        button {
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
            margin-top: 20px;
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
            text-decoration: none;
        }

        .success {
            color: lightgreen;
            margin-bottom: 10px;
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

<h2>Manage Categories</h2>

<?php if ($success): ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<!-- ADD CATEGORY CARD -->
<div class="card">
    <h3>Add New Category</h3>

    <form method="POST">
        <input type="text" name="category_name" placeholder="Enter category name" required>
        <button type="submit" name="add_category">Add Category</button>
    </form>
</div>

<!-- CATEGORY TABLE -->
<h3>Category List</h3>

<table>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['category_id'] ?></td>
    <td><?= htmlspecialchars($row['category_name']) ?></td>
    <td>
        <a class="delete-btn"
           href="?delete=<?= $row['category_id'] ?>"
           onclick="return confirm('Delete this category?')">
           Delete
        </a>
    </td>
</tr>
<?php endwhile; ?>

</table>

<br>
<button onclick="window.location.href='dashboard.php'">Back to Dashboard</button>

</div>

</body>
</html>