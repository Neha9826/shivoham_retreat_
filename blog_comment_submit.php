<?php
// blog_comment_submit.php

// Connect to database
if(file_exists('admin/db.php')){
    include 'admin/db.php';
} elseif (file_exists('db.php')){
    include 'db.php';
} else {
    die("Database connection file not found.");
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Validate Inputs
    if (empty($_POST['blog_id']) || empty($_POST['name']) || empty($_POST['email']) || empty($_POST['comment'])) {
        die("All fields are required.");
    }

    // 2. Sanitize Inputs
    $blog_id = intval($_POST['blog_id']); // Ensure ID is an integer
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // 3. Insert into Database
    $sql = "INSERT INTO blog_comments (blog_id, name, email, comment) VALUES ('$blog_id', '$name', '$email', '$comment')";

    if (mysqli_query($conn, $sql)) {
        // Success: Redirect back to the blog post with a success parameter
        header("Location: single-post.php?id=$blog_id&msg=comment_success#impx-comments");
        exit();
    } else {
        // Error: Display error
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
} else {
    // If someone tries to access this file directly without submitting
    header("Location: blog.php");
    exit();
}
?>