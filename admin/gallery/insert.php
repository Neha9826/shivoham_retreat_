<?php
include '../session.php';
include '../db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'error' => ''];

if (isset($_POST['save_gallery'])) {
    $descriptions = $_POST['gallery_description'];
    
    // Directory where images will be saved (relative to this file)
    // admin/gallery/insert.php -> admin/uploads/
    $targetDir = "../uploads/"; 
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $successCount = 0;

    // Loop through the arrays
    foreach ($descriptions as $index => $desc) {
        $desc = mysqli_real_escape_string($conn, $desc);
        
        // Check if an image was uploaded for this index
        if (!empty($_FILES['gallery_image']['name'][$index])) {
            $fileName = basename($_FILES['gallery_image']['name'][$index]);
            $targetFilePath = $targetDir . time() . "_" . $fileName; // Unique name
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

            // Allow certain file formats
            $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
            if (in_array(strtolower($fileType), $allowTypes)) {
                // Upload file to server
                if (move_uploaded_file($_FILES['gallery_image']['tmp_name'][$index], $targetFilePath)) {
                    // Store path relative to admin base, e.g., "uploads/image.jpg"
                    $dbPath = "uploads/" . time() . "_" . $fileName;
                    
                    $sql = "INSERT INTO gallery (image_path, description) VALUES ('$dbPath', '$desc')";
                    if (mysqli_query($conn, $sql)) {
                        $successCount++;
                    }
                }
            }
        }
    }

    if ($successCount > 0) {
        $response['success'] = true;
        $response['msg'] = "$successCount items added successfully.";
        // Redirect back to the main page
        header("Location: ../addGallery.php");
        exit;
    } else {
        $response['error'] = "No valid images were uploaded.";
    }
}

echo json_encode($response);
?>