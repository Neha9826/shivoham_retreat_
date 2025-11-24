<?php
include '../session.php';
include '../db.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$response = ['success' => false];

if ($id > 0) {
    $query = "SELECT * FROM gallery WHERE id = $id LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $response['success'] = true;
        $response['row'] = $row;
    }
}

echo json_encode($response);
?>