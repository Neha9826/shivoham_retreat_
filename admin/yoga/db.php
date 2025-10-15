<?php
// Database configuration
$host = "localhost";
$user = "u738048941_shivoham";
$pass = "Shivoham@25"; 
$db = "u738048941_shivoham_db";

// Create connection
$conn = mysqli_connect($host, $user, $password, $dbname);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
