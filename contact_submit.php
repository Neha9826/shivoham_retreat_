<?php
// contact_submit.php

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

    // 1. Validate Required Fields
    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['message'])) {
        die("Name, Email, and Message are required.");
    }

    // 2. Sanitize Inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']); // Added Phone
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // 3. Insert into Database
    // Note: 'created_at' is automatic if your DB column is set to CURRENT_TIMESTAMP
    $sql = "INSERT INTO contact_messages (name, phone, email, message) VALUES ('$name', '$phone', '$email', '$message')";

    // *Make sure to replace 'contacts' with your actual table name if it is different*

    if (mysqli_query($conn, $sql)) {
        // Success: Redirect back with success message
        echo "<script>alert('Thank you! Your message has been sent.'); window.location.href='contact.php';</script>";
        exit();
    } else {
        // Error
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
} else {
    header("Location: contact.php");
    exit();
}
?>