<?php
// admin/about_us/update.php
include '../session.php';
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = intval($_POST['section_type']);
    $id = 1; // Assuming single row per section

    if ($type == 1) {
        $heading = mysqli_real_escape_string($conn, $_POST['heading']);
        $sub = mysqli_real_escape_string($conn, $_POST['sub_heading']);
        $desc = mysqli_real_escape_string($conn, $_POST['description']);
        $sql = "UPDATE about_us_section_1 SET heading='$heading', sub_heading='$sub', description='$desc' WHERE id=$id";
    }
    elseif ($type == 2 || $type == 3) {
        $heading = mysqli_real_escape_string($conn, $_POST['heading'] ?? '');
        
        // Build JSON from arrays
        $items = [];
        if (isset($_POST['titles'])) {
            foreach ($_POST['titles'] as $k => $t) {
                if (!empty($t)) {
                    $items[] = [
                        'title' => $t,
                        'desc' => $_POST['descs'][$k]
                    ];
                }
            }
        }
        $json = mysqli_real_escape_string($conn, json_encode($items));
        $table = ($type == 2) ? 'about_us_section_2' : 'about_us_section_3';
        
        $sql = "UPDATE $table SET heading='$heading', content_json='$json' WHERE id=$id";
    }
    elseif ($type == 4) {
        $heading = mysqli_real_escape_string($conn, $_POST['heading']);
        $desc = mysqli_real_escape_string($conn, $_POST['description']);
        
        // JSON Features
        $items = [];
        if (isset($_POST['titles'])) {
            foreach ($_POST['titles'] as $k => $t) {
                if (!empty($t)) {
                    $items[] = [
                        'title' => $t,
                        'desc' => $_POST['descs'][$k]
                    ];
                }
            }
        }
        $json = mysqli_real_escape_string($conn, json_encode($items));

        // Image Upload
        $imgSQL = "";
        if (!empty($_FILES['image']['name'])) {
            $targetDir = "../uploads/";
            $fileName = time() . "_" . basename($_FILES['image']['name']);
            $targetFilePath = $targetDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                $dbPath = "uploads/" . $fileName;
                $imgSQL = ", image_path='$dbPath'";
            }
        }

        $sql = "UPDATE about_us_section_4 SET heading='$heading', description='$desc', features_json='$json' $imgSQL WHERE id=$id";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: ../addAboutUs.php?msg=success");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>