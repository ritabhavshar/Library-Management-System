<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Get member ID from request
$data = json_decode(file_get_contents('php://input'), true);
$message = isset($data['message']) ? strtolower($data['message']) : '';
$member_id = isset($data['member_id']) ? $data['member_id'] : null;

// Keywords for different types of recommendations
$keywords = [
    'recommend' => ['recommend', 'suggest', 'recommendation'],
    'category' => ['category', 'genre', 'type'],
    'author' => ['author', 'writer', 'by'],
    'year' => ['year', 'published', 'released'],
    'popular' => ['popular', 'best', 'top'],
    'new' => ['new', 'recent', 'latest'],
    'history' => ['history', 'past', 'previous'],
    'due' => ['due', 'return', 'due date'],
    'late' => ['late', 'overdue', 'delayed'],
    'profile' => ['profile', 'account', 'information'],
    'help' => ['help', 'guide', 'options'],
    'fine' => ['fine', 'penalty', 'charge'],
    'limit' => ['limit', 'maximum', 'max books']
];

// Extract keywords from message
$recommend = false;
$category = null;
$author = null;
$year = null;
$popular = false;
$new = false;
$history = false;
$due = false;
$late = false;
$profile = false;
$help = false;
$fine = false;
$limit = false;

foreach ($keywords as $type => $words) {
    foreach ($words as $word) {
        if (strpos($message, $word) !== false) {
            if ($type == 'recommend') {
                $recommend = true;
            } elseif ($type == 'category') {
                $category = get_category_from_message($message);
            } elseif ($type == 'author') {
                $author = get_author_from_message($message);
            } elseif ($type == 'year') {
                $year = get_year_from_message($message);
            } elseif ($type == 'popular') {
                $popular = true;
            } elseif ($type == 'new') {
                $new = true;
            } elseif ($type == 'history') {
                $history = true;
            } elseif ($type == 'due') {
                $due = true;
            } elseif ($type == 'late') {
                $late = true;
            } elseif ($type == 'profile') {
                $profile = true;
            } elseif ($type == 'help') {
                $help = true;
            } elseif ($type == 'fine') {
                $fine = true;
            } elseif ($type == 'limit') {
                $limit = true;
            }
        }
    }
}

// Get member's information and transactions
$member_info = null;
$current_books = [];
$late_books = [];

if ($member_id) {
    // Get member information
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$member_id]);
    $member_info = $stmt->fetch();
    
    // Get current books and late books
    $stmt = $pdo->prepare("
        SELECT 
            t.*, 
            b.title, 
            b.author, 
            b.category,
            b.quantity,
            DATEDIFF(NOW(), t.due_date) as days_late
        FROM transactions t
        JOIN books b ON t.book_id = b.id
        WHERE t.member_id = ? AND t.return_date IS NULL
        ORDER BY t.due_date ASC
    ");
    $stmt->execute([$member_id]);
    $transactions = $stmt->fetchAll();
    
    foreach ($transactions as $transaction) {
        if ($transaction['days_late'] > 0) {
            $late_books[] = $transaction;
        } else {
            $current_books[] = $transaction;
        }
    }
}

// Build query based on keywords
$query = "SELECT * FROM books WHERE quantity > 0 AND 1=1";
$params = [];

if ($category) {
    $query .= " AND category LIKE ?";
    $params[] = "%$category%";
}

if ($author) {
    $query .= " AND author LIKE ?";
    $params[] = "%$author%";
}

if ($year) {
    $query .= " AND publication_year = ?";
    $params[] = $year;
}

// Add member-specific recommendations
if ($member_id && !$category && !$author && !$year) {
    if ($current_books) {
        $categories = array_column($current_books, 'category');
        $popular_categories = array_count_values($categories);
        arsort($popular_categories);
        $top_category = key($popular_categories);
        
        if ($top_category) {
            $query .= " AND category LIKE ?";
            $params[] = "%$top_category%";
        }
    }
}

if ($popular) {
    $query .= " ORDER BY quantity DESC";
} elseif ($new) {
    $query .= " ORDER BY created_at DESC";
} else {
    $query .= " ORDER BY title ASC";
}

$query .= " LIMIT 10";

// Get books
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll();

// Generate response based on keywords
$response = "";
$extra_data = [];

if ($help) {
    $response = "Welcome to the Library Chatbot! Here are some things you can ask about:
    - Book recommendations (by author, category, year)
    - Your current books and due dates
    - Late books and fines
    - Your profile information
    - Book limits and policies
    - Popular and new books";
} elseif ($profile && $member_info) {
    $response = "Here's your profile information:
    Name: " . htmlspecialchars($member_info['name']) . "
    Email: " . htmlspecialchars($member_info['email']) . "
    Phone: " . htmlspecialchars($member_info['phone']) . "
    
    You currently have " . count($current_books) . " books issued.";
} elseif ($due && $current_books) {
    $response = "Here are your current books and their due dates:";
    foreach ($current_books as $book) {
        $response .= "\n- " . htmlspecialchars($book['title']) . " (Due: " . date('M d, Y', strtotime($book['due_date'])) . ")";
    }
} elseif ($late && $late_books) {
    $response = "You have " . count($late_books) . " late books:";
    foreach ($late_books as $book) {
        $response .= "\n- " . htmlspecialchars($book['title']) . " (" . $book['days_late'] . " days late)";
    }
} elseif ($fine && $late_books) {
    $response = "You have fines for " . count($late_books) . " late books:";
    $total_fine = 0;
    foreach ($late_books as $book) {
        $fine = $book['days_late'] * 2; // $2 per day fine
        $total_fine += $fine;
        $response .= "\n- " . htmlspecialchars($book['title']) . " (" . $fine . " INR fine)";
    }
    $response .= "\nTotal fine: " . $total_fine . " INR";
} elseif ($limit) {
    $response = "Each member can borrow up to 5 books at a time. Books must be returned within 14 days. You can extend the due date once for an additional 7 days.";
} elseif (count($books) > 0) {
    if ($member_books) {
        $response = "Here are some personalized book recommendations for you:";
    } else {
        $response = "Here are some book recommendations for you:";
    }
} else {
    $response = "I couldn't find any books matching your criteria. Would you like me to suggest something else?";
}

// Prepare response data
$response_data = [
    'response' => $response,
    'books' => $books,
    'member_info' => $member_info,
    'current_books' => $current_books,
    'late_books' => $late_books,
    'extra_data' => $extra_data
];

// Add book recommendations to response if available
if (count($books) > 0) {
    $response_data['extra_data']['recommendations'] = "Here are some book recommendations for you:";
}

// Add due date information to response if available
if ($current_books) {
    $response_data['extra_data']['due_dates'] = "Here are your current books and their due dates:";
}

// Add late book information to response if available
if ($late_books) {
    $response_data['extra_data']['late_books'] = "You have " . count($late_books) . " late books:";
}

// Add fine information to response if available
if ($late_books) {
    $response_data['extra_data']['fines'] = "You have fines for " . count($late_books) . " late books:";
}

echo json_encode($response_data);

// Helper functions
function get_category_from_message($message) {
    preg_match('/category|genre|type\s+([\w\s]+)/i', $message, $matches);
    return isset($matches[1]) ? trim($matches[1]) : null;
}

function get_author_from_message($message) {
    preg_match('/author|writer|by\s+([\w\s]+)/i', $message, $matches);
    return isset($matches[1]) ? trim($matches[1]) : null;
}

function get_year_from_message($message) {
    preg_match('/year|published|released\s+(\d{4})/i', $message, $matches);
    return isset($matches[1]) ? (int)$matches[1] : null;
}

function get_year_from_message($message) {
    preg_match('/year|published|released\s+(\d{4})/i', $message, $matches);
    return isset($matches[1]) ? (int)$matches[1] : null;
}
?>
