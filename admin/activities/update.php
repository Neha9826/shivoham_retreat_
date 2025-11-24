<?php
// admin/activities/update.php
include '../session.php';
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $type = intval($_POST['section_type']); // 1, 2, 3, or 4
    $tableName = "activity_section_" . $type;
    
    // 1. Update Text Fields
    $heading = mysqli_real_escape_string($conn, $_POST['heading']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // Sub-heading exists for 1, 2, and 4. It does NOT exist for 3.
    if ($type != 3) {
        $sub_heading = mysqli_real_escape_string($conn, $_POST['sub_heading']);
        $sql = "UPDATE $tableName SET heading='$heading', sub_heading='$sub_heading', description='$description' WHERE id=1";
    } else {
        $sql = "UPDATE $tableName SET heading='$heading', description='$description' WHERE id=1";
    }
    
    if (!mysqli_query($conn, $sql)) {
        echo "Error updating text: " . mysqli_error($conn);
        exit;
    }

    // 2. Handle Image Uploads
    // Determine how many images based on section type
    $imageCount = 0;
    if ($type == 1) $imageCount = 2;
    if ($type == 2) $imageCount = 4;
    if ($type == 3) $imageCount = 3;
    if ($type == 4) $imageCount = 4;

    $targetDir = "../uploads/"; // Ensure this folder exists
    $allowedTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');

    for ($i = 1; $i <= $imageCount; $i++) {
        $inputName = "image_" . $i;
        
        if (!empty($_FILES[$inputName]['name'])) {
            $fileName = basename($_FILES[$inputName]['name']);
            $targetFilePath = $targetDir . time() . "_" . $i . "_" . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

            if (in_array(strtolower($fileType), $allowedTypes)) {
                // Get old image to delete
                $oldQuery = mysqli_query($conn, "SELECT $inputName FROM $tableName WHERE id=1");
                $oldRow = mysqli_fetch_assoc($oldQuery);
                $oldPath = "../" . $oldRow[$inputName];

                if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $targetFilePath)) {
                    $dbPath = "uploads/" . time() . "_" . $i . "_" . $fileName;
                    
                    // Update DB
                    mysqli_query($conn, "UPDATE $tableName SET $inputName='$dbPath' WHERE id=1");

                    // Delete old file
                    if (!empty($oldRow[$inputName]) && file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
            }
        }
    }

    // Redirect back
    header("Location: ../addActivities.php?msg=updated");
    exit;
}
?>