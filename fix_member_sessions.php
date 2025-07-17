<?php
require_once 'config/database.php';

// Drop existing member_sessions table if it exists
$sql = "DROP TABLE IF EXISTS member_sessions";
$pdo->exec($sql);

// Create new member_sessions table
$sql = "CREATE TABLE member_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT,
    session_id VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
)";
$pdo->exec($sql);

echo "Successfully fixed member_sessions table!";
?>
