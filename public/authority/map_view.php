<?php
session_start();
include("../../config/db.php");

// Restrict access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'authority') {
    header("Location: ../login.php");
    exit();
}

// Fetch hazards
$result = $conn->query("SELECT * FROM hazards");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hazard Map</title>
</head>
<body>

<h2>All Hazard Locations</h2>

<div id="map" style="width:100%; height:500px;"></div>

<script>
function initMap() {

    const center = { lat: 6.9271, lng: 79.8612 }; // default center

    const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 6,
        center: center
    });

    const hazards = <?php
        $rows = [];
        while($r = $result->fetch_assoc()){
            $rows[] = $r;
        }
        echo json_encode($rows);
    ?>;

    hazards.forEach(h => {

        const position = {
            lat: parseFloat(h.latitude),
            lng: parseFloat(h.longitude)
        };
        let iconUrl = "";

// Set color based on severity
if (h.severity === "Low") {
    iconUrl = "http://maps.google.com/mapfiles/ms/icons/green-dot.png";
} 
else if (h.severity === "Medium") {
    iconUrl = "http://maps.google.com/mapfiles/ms/icons/orange-dot.png";
} 
else if (h.severity === "High") {
    iconUrl = "http://maps.google.com/mapfiles/ms/icons/red-dot.png";
} 
else {
    iconUrl = "http://maps.google.com/mapfiles/ms/icons/blue-dot.png";
}

const marker = new google.maps.Marker({
    position: position,
    map: map,
    icon: iconUrl
});

        

        const info = new google.maps.InfoWindow({
            content: `
                <b>Description:</b> ${h.description}<br>
                <b>Severity:</b> ${h.severity}<br>
                <b>Status:</b> ${h.status}<br>
                <img src="../../${h.image_path}" width="100">
            `
        });

        marker.addListener("click", () => {
            info.open(map, marker);
        });
    });
}
</script>

<script async
src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDhRBe6aJUCBV2ue8RJdocUdh3xiGhuHE4&callback=initMap">
</script>
<button type="button" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
</body>
</html>