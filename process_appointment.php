<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "insurance";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process appointment form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['fullName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $agreed_terms = isset($_POST['agreeTerms']) ? 1 : 0;
    
    $sql = "INSERT INTO appointments (name, phone, email, agreed_to_terms) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $phone, $email, $agreed_terms);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Appointment request submitted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
    }
    
    $stmt->close();
}

// Close database connection
$conn->close();
?>