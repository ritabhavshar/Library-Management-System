<?php
require_once 'config/database.php';

// Add password field to members table
$sql = "ALTER TABLE members ADD COLUMN password VARCHAR(255)";
$pdo->exec($sql);

// Add member_id to sessions table for tracking member sessions
$sql = "CREATE TABLE IF NOT EXISTS member_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id)
)";
$pdo->exec($sql);

echo "Successfully added member authentication tables!";
?>
