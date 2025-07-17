<?php
session_start();
require_once 'config/database.php';

// Check if member is logged in
if (!isset($_SESSION['member_id'])) {
    header("location: member_login.php");
    exit();
}

// Search functionality
$search = '';
$books = [];

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    $search = $_GET['search'];
    $sql = "SELECT * FROM books WHERE 
            title LIKE ? OR 
            author LIKE ? OR 
            category LIKE ? 
            ORDER BY title ASC";
    $stmt = $pdo->prepare($sql);
    $searchTerm = "%{$search}%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $books = $stmt->fetchAll();
} else {
    // Show available books
    $sql = "SELECT * FROM books WHERE quantity > 0 ORDER BY title ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $books = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Search - Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Book Search</h2>
                        
                        <!-- Search Form -->
                        <form method="GET" class="mb-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search books by title, author, or category">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </form>

                        <!-- Books Grid -->
                        <div class="row g-4">
                            <?php if(count($books) > 0): ?>
                                <?php foreach($books as $book): ?>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                                <p class="card-text"><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
                                                <p class="card-text"><strong>Category:</strong> <?php echo htmlspecialchars($book['category']); ?></p>
                                                <p class="card-text"><strong>Available:</strong> <?php echo $book['quantity']; ?> copies</p>
                                                <div class="d-grid">
                                                    <form method="POST" action="member_issue_book.php" style="display: inline;">
                                                        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-book"></i> Issue Book
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        No books found matching your search criteria.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
