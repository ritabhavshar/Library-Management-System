<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit();
}

// Handle book operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $sql = "INSERT INTO books (title, author, category, isbn, publication_year, quantity) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['title'],
                    $_POST['author'],
                    $_POST['category'],
                    $_POST['isbn'],
                    $_POST['publication_year'],
                    $_POST['quantity']
                ]);
                break;
            case 'edit':
                $sql = "UPDATE books SET title=?, author=?, category=?, isbn=?, publication_year=?, quantity=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['title'],
                    $_POST['author'],
                    $_POST['category'],
                    $_POST['isbn'],
                    $_POST['publication_year'],
                    $_POST['quantity'],
                    $_POST['id']
                ]);
                break;
            case 'delete':
                $sql = "DELETE FROM books WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_POST['id']]);
                break;
        }
    }
}

// Get all books
$books = $pdo->query("SELECT * FROM books ORDER BY title")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books - Library Management System</title>
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
                        <h3>Books Management</h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
                            <i class="fas fa-plus"></i> Add New Book
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Category</th>
                                        <th>ISBN</th>
                                        <th>Year</th>
                                        <th>Quantity</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($books as $book): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td><?php echo htmlspecialchars($book['category']); ?></td>
                                        <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                        <td><?php echo htmlspecialchars($book['publication_year']); ?></td>
                                        <td><?php echo htmlspecialchars($book['quantity']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                                    data-bs-target="#editBookModal" 
                                                    onclick="editBook(<?php echo json_encode($book); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this book?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $book['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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

    <!-- Add Book Modal -->
    <div class="modal fade" id="addBookModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label>Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label>Author</label>
                            <input type="text" class="form-control" name="author" required>
                        </div>
                        <div class="mb-3">
                            <label>Category</label>
                            <input type="text" class="form-control" name="category" required>
                        </div>
                        <div class="mb-3">
                            <label>ISBN</label>
                            <input type="text" class="form-control" name="isbn" required>
                        </div>
                        <div class="mb-3">
                            <label>Publication Year</label>
                            <input type="number" class="form-control" name="publication_year" required>
                        </div>
                        <div class="mb-3">
                            <label>Quantity</label>
                            <input type="number" class="form-control" name="quantity" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Book Modal -->
    <div class="modal fade" id="editBookModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label>Title</label>
                            <input type="text" class="form-control" name="title" id="editTitle" required>
                        </div>
                        <div class="mb-3">
                            <label>Author</label>
                            <input type="text" class="form-control" name="author" id="editAuthor" required>
                        </div>
                        <div class="mb-3">
                            <label>Category</label>
                            <input type="text" class="form-control" name="category" id="editCategory" required>
                        </div>
                        <div class="mb-3">
                            <label>ISBN</label>
                            <input type="text" class="form-control" name="isbn" id="editISBN" required>
                        </div>
                        <div class="mb-3">
                            <label>Publication Year</label>
                            <input type="number" class="form-control" name="publication_year" id="editYear" required>
                        </div>
                        <div class="mb-3">
                            <label>Quantity</label>
                            <input type="number" class="form-control" name="quantity" id="editQuantity" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function editBook(book) {
        document.getElementById('editId').value = book.id;
        document.getElementById('editTitle').value = book.title;
        document.getElementById('editAuthor').value = book.author;
        document.getElementById('editCategory').value = book.category;
        document.getElementById('editISBN').value = book.isbn;
        document.getElementById('editYear').value = book.publication_year;
        document.getElementById('editQuantity').value = book.quantity;
    }
    </script>
</body>
</html>
