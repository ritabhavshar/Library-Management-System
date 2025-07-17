<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit();
}

// Handle transaction operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'issue':
                $sql = "INSERT INTO transactions (member_id, book_id, issue_date, due_date) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['member_id'],
                    $_POST['book_id'],
                    date('Y-m-d'),
                    date('Y-m-d', strtotime('+14 days'))
                ]);
                
                // Decrease book quantity
                $sql = "UPDATE books SET quantity = quantity - 1 WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_POST['book_id']]);
                break;
            case 'return':
                $sql = "UPDATE transactions SET return_date = ?, status = 'returned' WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    date('Y-m-d'),
                    $_POST['id']
                ]);
                
                // Increase book quantity
                $sql = "UPDATE books SET quantity = quantity + 1 WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_POST['book_id']]);
                break;
        }
    }
}

// Get transactions
$transactions = $pdo->query("
    SELECT 
        t.*,
        b.title,
        m.name,
        b.quantity as book_quantity
    FROM transactions t
    JOIN books b ON t.book_id = b.id
    JOIN members m ON t.member_id = m.id
    ORDER BY t.issue_date DESC
")->fetchAll();

// Get books for dropdown
$books = $pdo->query("SELECT id, title FROM books WHERE quantity > 0 ORDER BY title")->fetchAll();

// Get members for dropdown
$members = $pdo->query("SELECT id, name FROM members ORDER BY name")->fetchAll();
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
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3>Transactions Management</h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#issueBookModal">
                            <i class="fas fa-plus"></i> Issue Book
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Book</th>
                                        <th>Member</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Return Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($transactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($transaction['title']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['name']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($transaction['issue_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($transaction['due_date'])); ?></td>
                                        <td><?php echo $transaction['return_date'] ? date('M d, Y', strtotime($transaction['return_date'])) : '-'; ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $transaction['status'] == 'issued' ? 'primary' : 
                                                ($transaction['status'] == 'returned' ? 'success' : 'danger');
                                            ?>">
                                                <?php echo ucfirst($transaction['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($transaction['status'] == 'issued'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="return">
                                                    <input type="hidden" name="id" value="<?php echo $transaction['id']; ?>">
                                                    <input type="hidden" name="book_id" value="<?php echo $transaction['book_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="fas fa-check"></i> Return
                                                    </button>
                                                </form>
                                            <?php endif; ?>
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

    <!-- Issue Book Modal -->
    <div class="modal fade" id="issueBookModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Issue Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="issue">
                        <div class="mb-3">
                            <label>Member</label>
                            <select class="form-control" name="member_id" required>
                                <option value="">Select Member</option>
                                <?php foreach($members as $member): ?>
                                <option value="<?php echo $member['id']; ?>">
                                    <?php echo htmlspecialchars($member['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Book</label>
                            <select class="form-control" name="book_id" required>
                                <option value="">Select Book</option>
                                <?php foreach($books as $book): ?>
                                <option value="<?php echo $book['id']; ?>">
                                    <?php echo htmlspecialchars($book['title']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Issue Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
