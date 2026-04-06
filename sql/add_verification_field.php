<?php
require_once '../includes/config.php';

// Add is_verified field to users table
$sql = "ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0";

if ($conn->query($sql) === TRUE) {
    echo "Verification field added successfully to users table";
} else {
    echo "Error adding verification field: " . $conn->error;
}
?>
