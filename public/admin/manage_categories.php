<?php
session_start();
include("../../config/db.php");

// Only admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Add Category
if (isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);

    if (!empty($category_name)) {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        $stmt->close();
    }
}

// Delete Category
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM categories WHERE category_id = $id");
}

// Fetch categories
$result = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
</head>
<body>

<h2>Manage Categories</h2>

<h3>Add New Category</h3>
<form method="POST">
    <input type="text" name="category_name" placeholder="Enter category name" required>
    <button type="submit" name="add_category">Add</button>
</form>

<br>

<h3>Category List</h3>

<table border="1" cellpadding="8">
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
        <a href="?delete=<?= $row['category_id'] ?>" onclick="return confirm('Delete this category?')">
            Delete
        </a>
    </td>
</tr>
<?php endwhile; ?>

</table>

<br>
<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>