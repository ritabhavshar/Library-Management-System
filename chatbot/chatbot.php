<?php
session_start();
require_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Recommendation Chatbot - Library Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chat-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
        }
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 1rem;
        }
        .message {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 10px;
        }
        .user-message {
            background: #007bff;
            color: white;
            margin-left: auto;
        }
        .bot-message {
            background: #f8f9fa;
        }
        .chat-input {
            padding: 1rem;
            border-top: 1px solid #dee2e6;
        }
        .book-card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="chat-container">
            <h2 class="text-center mb-4">Book Recommendation Chatbot</h2>
            <div class="chat-messages" id="chatMessages">
                <div class="message bot-message">
                    <p>Hello! I'm your book recommendation chatbot. How can I help you today?</p>
                </div>
            </div>
            <div class="chat-input">
                <div class="input-group">
                    <input type="text" id="userInput" class="form-control" placeholder="Type your message...">
                    <button class="btn btn-primary" onclick="sendMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let messages = [];

    function addMessage(message, isUser) {
        const container = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
        messageDiv.innerHTML = `<p>${message}</p>`;
        container.appendChild(messageDiv);
        container.scrollTop = container.scrollHeight;
    }

    function sendMessage() {
        const input = document.getElementById('userInput');
        const message = input.value.trim();
        
        if (message) {
            addMessage(message, true);
            input.value = '';
            
            fetch('chatbot_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message: message })
            })
            .then(response => response.json())
            .then(data => {
                addMessage(data.response, false);
                if (data.books && data.books.length > 0) {
                    data.books.forEach(book => {
                        addMessage(`
                            <div class="card book-card">
                                <div class="card-body">
                                    <h5 class="card-title">${book.title}</h5>
                                    <p class="card-text"><strong>Author:</strong> ${book.author}</p>
                                    <p class="card-text"><strong>Category:</strong> ${book.category}</p>
                                    <p class="card-text"><strong>Publication Year:</strong> ${book.publication_year}</p>
                                    <a href="../books.php" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        `, false);
                    });
                }
            })
            .catch(error => {
                addMessage('Sorry, I encountered an error. Please try again.', false);
            });
        }
    }

    document.getElementById('userInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    </script>
</body>
</html>
