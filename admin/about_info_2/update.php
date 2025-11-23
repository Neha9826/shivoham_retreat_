<?php
// admin/about_info_2/update.php
include '../session.php';
include '../db.php';
header('Content-Type: application/json');

$ids = $_POST['info_id'] ?? [];
$titles = $_POST['info_title'] ?? [];
$descs = $_POST['info_description'] ?? [];

for($i=0;$i<count($titles);$i++){
    $id = isset($ids[$i]) && $ids[$i] ? intval($ids[$i]) : 0;
    $title = mysqli_real_escape_string($conn, $titles[$i]);
    $desc  = mysqli_real_escape_string($conn, $descs[$i]);
    if($id){
        $stmt = $conn->prepare("UPDATE about_info_2 SET info_title=?, info_description=? WHERE id=?");
        $stmt->bind_param('ssi', $title, $desc, $id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO about_info_2 (info_title, info_description, image) VALUES (?, ?, '')");
        $stmt->bind_param('ss', $title, $desc);
        $stmt->execute();
    }
}
echo json_encode(['success'=>true]);