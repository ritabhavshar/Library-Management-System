<?php
session_start();
require_once 'config/database.php';

// Check if member is logged in
if (!isset($_SESSION['member_id'])) {
    header("location: member_login.php");
    exit();
}

// Get member's transactions
$stmt = $pdo->prepare("
    SELECT 
        t.*, 
        b.title, 
        b.author, 
        b.category
    FROM transactions t
    JOIN books b ON t.book_id = b.id
    WHERE t.member_id = ?
    ORDER BY t.issue_date DESC
");
$stmt->execute([$_SESSION['member_id']]);
$transactions = $stmt->fetchAll();

// Get statistics
$total_transactions = count($transactions);
$current_books = 0;
$late_returns = 0;

foreach ($transactions as $transaction) {
    if (!$transaction['return_date']) {
        $current_books++;
        if (strtotime($transaction['due_date']) < time()) {
            $late_returns++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <h3>Total Transactions</h3>
                    <h2><?php echo $total_transactions; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3>Current Books</h3>
                    <h2><?php echo $current_books; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3>Late Returns</h3>
                    <h2><?php echo $late_returns; ?></h2>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Transaction History</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Book Title</th>
                                        <th>Author</th>
                                        <th>Category</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Return Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($transactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($transaction['title']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['author']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['category']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($transaction['issue_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($transaction['due_date'])); ?></td>
                                        <td><?php echo $transaction['return_date'] ? date('M d, Y', strtotime($transaction['return_date'])) : '-'; ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $transaction['return_date'] ? 'success' : 
                                                (strtotime($transaction['due_date']) < time() ? 'danger' : 'primary');
                                            ?>">
                                                <?php 
                                                if ($transaction['return_date']) {
                                                    echo 'Returned';
                                                } elseif (strtotime($transaction['due_date']) < time()) {
                                                    echo 'Overdue';
                                                } else {
                                                    echo 'Issued';
                                                }
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
