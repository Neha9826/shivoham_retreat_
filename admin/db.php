<?php
// include __DIR__ . "/config.php";
$host = "localhost";
$user = "root";
$pass = ""; 
$db = "shivoham_retreat";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
