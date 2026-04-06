<?php
require_once '../includes/config.php';

$sql = "CREATE TABLE IF NOT EXISTS live_streams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    video_url VARCHAR(255) NOT NULL,
    event_id INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Live streams table created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}
