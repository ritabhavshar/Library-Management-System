<?php
session_start();
require_once 'config/database.php';

// Check if member is logged in
if (!isset($_SESSION['member_id'])) {
    header("location: member_login.php");
    exit();
}

// Get member information
$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$_SESSION['member_id']]);
$member = $stmt->fetch();

// Get member's current transactions
$stmt = $pdo->prepare("
    SELECT 
        t.*, 
        b.title, 
        b.author, 
        b.category
    FROM transactions t
    JOIN books b ON t.book_id = b.id
    WHERE t.member_id = ? AND t.return_date IS NULL
    ORDER BY t.issue_date DESC
");
$stmt->execute([$_SESSION['member_id']]);
$current_books = $stmt->fetchAll();

// Get member's transaction history
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
    LIMIT 5
");
$stmt->execute([$_SESSION['member_id']]);
$transaction_history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Library Management System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="member_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="member_profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="member_transactions.php">Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="member_book_search.php">Search Books</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="member_logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h2 class="card-title mb-4">Welcome, <?php echo htmlspecialchars($member['name']); ?></h2>
                        
                        <!-- Chatbot Section -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3>Book Recommendation Chatbot</h3>
                            </div>
                            <div class="card-body">
                                <div class="chatbot-container">
                                    <div class="chatbot-messages" id="chatbotMessages">
                                        <div class="message bot">
                                            <div class="message-content">
                                                <p>Hello! I'm here to help you find books. Ask me about:</p>
                                                <ul>
                                                    <li>Books by author</li>
                                                    <li>Books in specific categories</li>
                                                    <li>Popular books</li>
                                                    <li>New arrivals</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="chatbot-input">
                                        <form id="chatbotForm" onsubmit="return sendMessage(event)">
                                            <div class="input-group">
                                                <input type="text" id="chatbotInput" class="form-control" placeholder="Ask about books..." required>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Books Section -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3>Current Books</h3>
                            </div>
                            <div class="card-body">
                                <?php if(count($current_books) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Book Title</th>
                                                    <th>Author</th>
                                                    <th>Category</th>
                                                    <th>Issue Date</th>
                                                    <th>Due Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($current_books as $book): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                                                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                                                    <td><?php echo htmlspecialchars($book['category']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($book['issue_date'])); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($book['due_date'])); ?></td>
                                                    <td>
                                                        <form method="POST" action="member_return_book.php" style="display: inline;">
                                                            <input type="hidden" name="transaction_id" value="<?php echo $book['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                <i class="fas fa-check"></i> Return
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">You currently don't have any books issued.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Recent Transactions Section -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Recent Transactions</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Book Title</th>
                                                <th>Author</th>
                                                <th>Issue Date</th>
                                                <th>Return Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($transaction_history as $transaction): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($transaction['title']); ?></td>
                                                <td><?php echo htmlspecialchars($transaction['author']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($transaction['issue_date'])); ?></td>
                                                <td><?php echo $transaction['return_date'] ? date('M d, Y', strtotime($transaction['return_date'])) : '-'; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $transaction['return_date'] ? 'success' : 'primary';
                                                    ?>">
                                                        <?php echo $transaction['return_date'] ? 'Returned' : 'Issued'; ?>
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
        </div>
    </div>

    <style>
    .chatbot-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .chatbot-messages {
        height: 400px;
        overflow-y: auto;
        padding: 1rem;
        border-radius: 10px;
        background-color: #f8f9fa;
    }
    .chatbot-input {
        margin-top: 1rem;
    }
    .message {
        margin-bottom: 1rem;
        padding: 1rem;
        border-radius: 10px;
        max-width: 80%;
    }
    .message.user {
        background-color: #e3f2fd;
        margin-left: auto;
    }
    .message.bot {
        background-color: #fff;
    }
    .message-content {
        word-wrap: break-word;
    }
    </style>

    <script>
    function sendMessage(event) {
        event.preventDefault();
        const input = document.getElementById('chatbotInput');
        const message = input.value.trim();
        
        if (message) {
            // Add user message to chat
            addMessage(message, 'user');
            
            // Show loading state
            addMessage('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>', 'bot');
            
            // Send message to chatbot
            fetch('chatbot/chatbot_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message,
                    member_id: <?php echo $_SESSION['member_id']; ?>
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Remove loading state
                const messages = document.getElementById('chatbotMessages');
                const lastMessage = messages.lastElementChild;
                if (lastMessage && lastMessage.querySelector('.spinner-border')) {
                    lastMessage.remove();
                }
                
                // Add bot response to chat
                addMessage(data.response, 'bot');
                
                // If there are book recommendations, show them
                if (data.books && data.books.length > 0) {
                    showBookRecommendations(data.books);
                }
                
                // Handle member-specific information
                if (data.member_info) {
                    showMemberInfo(data.member_info);
                }
                
                // Handle due dates
                if (data.current_books) {
                    showDueDates(data.current_books);
                }
                
                // Handle late books
                if (data.late_books) {
                    showLateBooks(data.late_books);
                }
                
                // Handle fines
                if (data.extra_data && data.extra_data.fines) {
                    showFines(data.extra_data.fines);
                }
            })
            .catch(error => {
                // Remove loading state
                const messages = document.getElementById('chatbotMessages');
                const lastMessage = messages.lastElementChild;
                if (lastMessage && lastMessage.querySelector('.spinner-border')) {
                    lastMessage.remove();
                }
                
                // Show error message with retry option
                const errorDiv = document.createElement('div');
                errorDiv.className = 'message bot';
                errorDiv.innerHTML = `
                    <div class="message-content">
                        <div class="alert alert-danger">
                            <p>Sorry, there was an error processing your request.</p>
                            <p>Error: ${error.message}</p>
                            <button onclick="retryLastMessage()" class="btn btn-sm btn-primary">
                                <i class="fas fa-redo"></i> Retry
                            </button>
                        </div>
                    </div>
                `;
                messages.appendChild(errorDiv);
                messages.scrollTop = messages.scrollHeight;
                console.error('Chatbot Error:', error);
            });
        }
        
        input.value = '';
        return false;
    }

    function addMessage(text, type) {
        const messages = document.getElementById('chatbotMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.innerHTML = `<p>${text}</p>`;
        
        messageDiv.appendChild(contentDiv);
        messages.appendChild(messageDiv);
        messages.scrollTop = messages.scrollHeight;
    }

    function showBookRecommendations(books) {
        const recommendations = document.getElementById('bookRecommendations');
        if (recommendations) {
            recommendations.innerHTML = `
                <h4 class="mt-4">Recommended Books</h4>
                <div class="row">
                    ${books.map(book => `
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">${book.title}</h5>
                                    <p class="card-text">
                                        <strong>Author:</strong> ${book.author}<br>
                                        <strong>Category:</strong> ${book.category}
                                    </p>
                                    <form method="POST" action="member_issue_book.php" style="display: inline;">
                                        <input type="hidden" name="book_id" value="${book.id}">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-book"></i> Issue Book
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }
    }

    function showMemberInfo(info) {
        const memberInfo = document.getElementById('memberInfo');
        if (memberInfo) {
            memberInfo.innerHTML = `
                <h4 class="mt-4">Member Information</h4>
                <div class="card">
                    <div class="card-body">
                        <p><strong>Name:</strong> ${info.name}</p>
                        <p><strong>Email:</strong> ${info.email}</p>
                        <p><strong>Phone:</strong> ${info.phone}</p>
                        <p><strong>Books Issued:</strong> ${info.books_issued || 0}</p>
                    </div>
                </div>
            `;
        }
    }

    function showDueDates(books) {
        const dueDates = document.getElementById('dueDates');
        if (dueDates) {
            dueDates.innerHTML = `
                <h4 class="mt-4">Books Due</h4>
                <div class="card">
                    <div class="card-body">
                        <ul class="list-group">
                            ${books.map(book => `
                                <li class="list-group-item">
                                    <strong>${book.title}</strong>
                                    <span class="badge bg-primary ms-2">Due: ${book.due_date}</span>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                </div>
            `;
        }
    }

    function showLateBooks(books) {
        const lateBooks = document.getElementById('lateBooks');
        if (lateBooks) {
            lateBooks.innerHTML = `
                <h4 class="mt-4">Late Books</h4>
                <div class="card">
                    <div class="card-body">
                        <ul class="list-group">
                            ${books.map(book => `
                                <li class="list-group-item">
                                    <strong>${book.title}</strong>
                                    <span class="badge bg-danger ms-2">${book.days_late} days late</span>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                </div>
            `;
        }
    }

    function showFines(amount) {
        const fines = document.getElementById('fines');
        if (fines) {
            fines.innerHTML = `
                <h4 class="mt-4">Fines</h4>
                <div class="card">
                    <div class="card-body">
                        <p class="h3 mb-0">Total Fines: ₹${amount}</p>
                    </div>
                </div>
            `;
        }
    }

    function retryLastMessage() {
        const messages = document.getElementById('chatbotMessages');
        const userMessages = Array.from(messages.getElementsByClassName('message user'));
        if (userMessages.length > 0) {
            const lastUserMessage = userMessages[userMessages.length - 1];
            const messageText = lastUserMessage.querySelector('.message-content p').textContent;
            document.getElementById('chatbotInput').value = messageText;
            sendMessage(new Event('submit'));
        }
    }
    </script>

    <div id="memberInfo"></div>
    <div id="dueDates"></div>
    <div id="lateBooks"></div>
    <div id="fines"></div>

    <div id="bookRecommendations"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
