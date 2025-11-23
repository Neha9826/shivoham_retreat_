<?php
// admin/about_main/update.php
include '../session.php';
include '../db.php';

header('Content-Type: application/json');

$id = intval($_POST['id'] ?? 0);
if(!$id){
    echo json_encode(['success'=>false,'error'=>'Missing id']); exit;
}

$title = mysqli_real_escape_string($conn, $_POST['main_heading'] ?? '');
$sub_title = mysqli_real_escape_string($conn, $_POST['main_sub_heading'] ?? ''); // New Field
$desc  = mysqli_real_escape_string($conn, $_POST['main_description'] ?? '');

// Update without images
$stmt = $conn->prepare("UPDATE about_1 SET main_heading=?, main_sub_heading=?, main_description=? WHERE id=?");
$stmt->bind_param('sssi', $title, $sub_title, $desc, $id);
$ok = $stmt->execute();

echo json_encode(['success'=>!!$ok]);