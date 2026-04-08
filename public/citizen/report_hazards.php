<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'citizen') {
    header("Location: ../login.php");
    exit();
}

$success = "";
$error = "";

// Fetch categories
$categories = $conn->query("SELECT * FROM categories");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];
    $category_id = $_POST['category_id'];
    $description = trim($_POST['description']);
    $severity = $_POST['severity'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Validate image upload
    if (!isset($_FILES['hazard_image']) || $_FILES['hazard_image']['error'] != 0) {
        $error = "Please upload a valid image.";
    } else {

        $image_name = $_FILES['hazard_image']['name'];
        $image_tmp = $_FILES['hazard_image']['tmp_name'];
        $image_size = $_FILES['hazard_image']['size'];

        $upload_dir = "../../uploads/";
        $allowed_types = ['jpg', 'jpeg', 'png'];
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        if (!in_array($image_ext, $allowed_types)) {
            $error = "Only JPG, JPEG, PNG files allowed.";
        } elseif ($image_size > 2 * 1024 * 1024) {
            $error = "File size must be less than 2MB.";
        } else {

            // Create unique filename
            $new_image_name = time() . "_" . rand(1000,9999) . "." . $image_ext;
            $upload_path = $upload_dir . $new_image_name;

            if (move_uploaded_file($image_tmp, $upload_path)) {

                $image_path = "uploads/" . $new_image_name;

                $stmt = $conn->prepare("INSERT INTO hazards 
                    (user_id, category_id, description, severity, latitude, longitude, image_path) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param(
                    "iissdds",
                    $user_id,
                    $category_id,
                    $description,
                    $severity,
                    $latitude,
                    $longitude,
                    $image_path
                );

                if ($stmt->execute()) {
                    $success = "Hazard reported successfully!";
                } else {
                    $error = "Database error: " . $stmt->error;
                }

                $stmt->close();

            } else {
                $error = "Image upload failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Road Hazard</title>
</head>
<body>

<h2>Report Road Hazard</h2>

<?php if ($success): ?>
    <p style="color: green;"><?php echo $success; ?></p>
<?php endif; ?>

<?php if ($error): ?>
    <p style="color: red;"><?php echo $error; ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <label>Category:</label><br>
    <select name="category_id" required>
        <option value="">Select Category</option>
        <?php while($row = $categories->fetch_assoc()): ?>
            <option value="<?= $row['category_id']; ?>">
                <?= $row['category_name']; ?>
            </option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <label>Description:</label><br>
    <textarea name="description" required></textarea>
    <br><br>

    <label>Severity:</label><br>
    <select name="severity" required>
        <option value="Low">Low</option>
        <option value="Medium">Medium</option>
        <option value="High">High</option>
    </select>
    <br><br>

    <label>Select Hazard Location:</label><br>
<div id="map" style="width:100%; height:400px;"></div>
<br>

<input type="hidden" name="latitude" id="latitude" required>
<input type="hidden" name="longitude" id="longitude" required>


    <label>Upload Photo (Proof):</label><br>
    <input type="file" name="hazard_image" accept="image/*" required>
    <br><br>

    <button type="submit">Submit Report</button>

</form>

<script>
let map;
let marker;

function initMap() {
    const defaultLocation = { lat: 6.9271, lng: 79.8612 };

    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 12,
        center: defaultLocation,
    });

    map.addListener("click", function(event) {
        const clickedLocation = event.latLng;

        document.getElementById("latitude").value = clickedLocation.lat();
        document.getElementById("longitude").value = clickedLocation.lng();

        if (marker) {
            marker.setMap(null);
        }

        marker = new google.maps.Marker({
            position: clickedLocation,
            map: map,
        });
    });
}
</script>

<script async
src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDhRBe6aJUCBV2ue8RJdocUdh3xiGhuHE4&callback=initMap">
</script>



</body>
</html>
