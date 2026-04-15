<?php
session_start();
include(__DIR__ . "/../../config/db.php");

// Restrict access
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

            $new_image_name = time() . "_" . rand(1000,9999) . "." . $image_ext;
            $upload_path = $upload_dir . $new_image_name;

            if (move_uploaded_file($image_tmp, $upload_path)) {

                $image_path = "uploads/" . $new_image_name;

                $stmt = $conn->prepare("INSERT INTO hazards 
                    (user_id, category_id, description, severity, latitude, longitude, image_path) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param("iissdds",
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
                    $error = "Database error!";
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
    <title>Report Hazard</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: url('../../assets/images/road.jpg') no-repeat center/cover;
            color: white;
        }

        .container {
            padding: 20px;
        }

        .card {
            background: rgba(0,0,0,0.8);
            padding: 25px;
            border-radius: 12px;
            max-width: 700px;
            margin: auto;
        }

        h2 {
            text-align: center;
        }

        input, select, textarea, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: none;
        }

        button {
            background: green;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .back-btn {
            background: #555;
        }

        .success {
            color: lightgreen;
        }

        .error {
            color: red;
        }

        #map {
            width: 100%;
            height: 350px;
            border-radius: 10px;
            margin-top: 10px;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<?php include(__DIR__ . "/../includes/navbar.php"); ?>

<div class="container">

<div class="card">

<h2>Report Road Hazard</h2>

<?php if ($success): ?>
    <p class="success"><?php echo $success; ?></p>
<?php endif; ?>

<?php if ($error): ?>
    <p class="error"><?php echo $error; ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <label>Category</label>
    <select name="category_id" required>
        <option value="">Select Category</option>
        <?php while($row = $categories->fetch_assoc()): ?>
            <option value="<?= $row['category_id']; ?>">
                <?= $row['category_name']; ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Description</label>
    <textarea name="description" placeholder="Describe the hazard..." required></textarea>

    <label>Severity</label>
    <select name="severity" required>
        <option value="Low">Low</option>
        <option value="Medium">Medium</option>
        <option value="High">High</option>
    </select>

    <label>Select Location on Map</label>
    <div id="map"></div>

    <input type="hidden" name="latitude" id="latitude" required>
    <input type="hidden" name="longitude" id="longitude" required>

    <label>Upload Image</label>
    <input type="file" name="hazard_image" accept="image/*" required>

    <button type="submit">Submit Report</button>
    <button type="button" class="back-btn" onclick="window.location.href='dashboard.php'">
        Back to Dashboard
    </button>

</form>

</div>

</div>

<script>
let map;
let marker;

function initMap() {
    
const defaultLocation = { lat: 51.4545, lng: -2.5879 };

    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 12,
        center: defaultLocation,
    });

    map.addListener("click", function(event) {
        const lat = event.latLng.lat();
        const lng = event.latLng.lng();

        document.getElementById("latitude").value = lat;
        document.getElementById("longitude").value = lng;

        if (marker) marker.setMap(null);

        marker = new google.maps.Marker({
            position: { lat, lng },
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