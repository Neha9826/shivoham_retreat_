<?php
// admin/about_main/insert.php
include '../session.php';
include '../db.php';

$title = mysqli_real_escape_string($conn, $_POST['main_heading'] ?? '');
$sub_title = mysqli_real_escape_string($conn, $_POST['main_sub_heading'] ?? ''); // New Field
$desc  = mysqli_real_escape_string($conn, $_POST['main_description'] ?? '');

// Insert without images
$stmt = $conn->prepare("INSERT INTO about_1 (main_heading, main_sub_heading, main_description) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $title, $sub_title, $desc);
$stmt->execute();

header('Location: ../addAbout.php#section-main');
exit;