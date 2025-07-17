<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/config/database.php';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/bhavu/index.php">Library Management System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/bhavu/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/bhavu/books.php">Books</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/bhavu/members.php">Members</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/bhavu/transactions.php">Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/bhavu/chatbot/chatbot.php">Book Chatbot</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/bhavu/logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/bhavu/login.php">Admin Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/bhavu/register.php">Admin Register</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/bhavu/member_login.php">Member Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
