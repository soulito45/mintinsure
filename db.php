<?php
// Database configuration
$host = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password is empty
$database = "insuarance";

// Create database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set
mysqli_set_charset($conn, "utf8");
?>