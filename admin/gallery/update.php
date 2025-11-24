<?php
include '../session.php';
include '../db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'error' => ''];

// Check if inputs exist (accessing index 0 because the form sends arrays)
if (isset($_POST['gallery_id'][0])) {
    $id = intval($_POST['gallery_id'][0]);
    $desc = mysqli_real_escape_string($conn, $_POST['gallery_description'][0]);
    
    // 1. Update Description
    $sql = "UPDATE gallery SET description = '$desc' WHERE id = $id";
    $update = mysqli_query($conn, $sql);

    // 2. Check if a new image is being uploaded
    if (!empty($_FILES['gallery_image']['name'][0])) {
        $targetDir = "../uploads/";
        $fileName = basename($_FILES['gallery_image']['name'][0]);
        $targetFilePath = $targetDir . time() . "_" . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');

        if (in_array(strtolower($fileType), $allowTypes)) {
            // Retrieve old image path to delete it
            $queryOld = mysqli_query($conn, "SELECT image_path FROM gallery WHERE id = $id");
            $oldRow = mysqli_fetch_assoc($queryOld);
            
            if (move_uploaded_file($_FILES['gallery_image']['tmp_name'][0], $targetFilePath)) {
                $dbPath = "uploads/" . time() . "_" . $fileName;
                
                // Update DB with new path
                mysqli_query($conn, "UPDATE gallery SET image_path = '$dbPath' WHERE id = $id");

                // Remove old file if it exists
                if ($oldRow && !empty($oldRow['image_path'])) {
                    $oldFile = "../" . $oldRow['image_path'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                $update = true;
            }
        }
    }

    if ($update) {
        $response['success'] = true;
    } else {
        $response['error'] = "Database update failed: " . mysqli_error($conn);
    }
} else {
    $response['error'] = "Invalid data provided.";
}

echo json_encode($response);
?>