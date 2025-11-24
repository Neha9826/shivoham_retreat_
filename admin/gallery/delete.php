<?php
include '../session.php';
include '../db.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$response = ['success' => false, 'error' => ''];

if ($id > 0) {
    // 1. Get the image path to unlink the file
    $query = mysqli_query($conn, "SELECT image_path FROM gallery WHERE id = $id");
    if ($row = mysqli_fetch_assoc($query)) {
        $filePath = "../" . $row['image_path']; // Adjust path relative to this file
        
        // 2. Delete from Database
        if (mysqli_query($conn, "DELETE FROM gallery WHERE id = $id")) {
            $response['success'] = true;
            
            // 3. Delete the actual file
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        } else {
            $response['error'] = mysqli_error($conn);
        }
    } else {
        $response['error'] = "Record not found.";
    }
}

echo json_encode($response);
?>