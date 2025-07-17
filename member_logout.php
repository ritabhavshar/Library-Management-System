<?php
session_start();
require_once 'config/database.php';

// Clear member session from database
if (isset($_SESSION['member_id'])) {
    $sql = "DELETE FROM member_sessions WHERE session_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([session_id()]);
}

// Destroy PHP session
session_destroy();

header("location: member_login.php");
exit();
?>
