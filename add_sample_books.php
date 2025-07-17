<?php
require_once 'config/database.php';

$books = [
    [
        'title' => 'To Kill a Mockingbird',
        'author' => 'Harper Lee',
        'category' => 'Classic',
        'isbn' => '9780060935467',
        'publication_year' => 1960,
        'quantity' => 5
    ],
    [
        'title' => '1984',
        'author' => 'George Orwell',
        'category' => 'Dystopian',
        'isbn' => '9780451524935',
        'publication_year' => 1949,
        'quantity' => 4
    ],
    [
        'title' => 'The Great Gatsby',
        'author' => 'F. Scott Fitzgerald',
        'category' => 'Classic',
        'isbn' => '9780743273565',
        'publication_year' => 1925,
        'quantity' => 3
    ],
    [
        'title' => 'Pride and Prejudice',
        'author' => 'Jane Austen',
        'category' => 'Romance',
        'isbn' => '9780141439518',
        'publication_year' => 1813,
        'quantity' => 6
    ],
    [
        'title' => 'The Catcher in the Rye',
        'author' => 'J.D. Salinger',
        'category' => 'Classic',
        'isbn' => '9780316769488',
        'publication_year' => 1951,
        'quantity' => 4
    ],
    [
        'title' => 'Brave New World',
        'author' => 'Aldous Huxley',
        'category' => 'Dystopian',
        'isbn' => '9780060850524',
        'publication_year' => 1932,
        'quantity' => 5
    ],
    [
        'title' => 'The Hobbit',
        'author' => 'J.R.R. Tolkien',
        'category' => 'Fantasy',
        'isbn' => '9780547928227',
        'publication_year' => 1937,
        'quantity' => 7
    ],
    [
        'title' => 'The Lord of the Rings',
        'author' => 'J.R.R. Tolkien',
        'category' => 'Fantasy',
        'isbn' => '9780618640157',
        'publication_year' => 1954,
        'quantity' => 6
    ],
    [
        'title' => 'The Hitchhiker\'s Guide to the Galaxy',
        'author' => 'Douglas Adams',
        'category' => 'Science Fiction',
        'isbn' => '9780345391803',
        'publication_year' => 1979,
        'quantity' => 5
    ],
    [
        'title' => 'The Da Vinci Code',
        'author' => 'Dan Brown',
        'category' => 'Mystery',
        'isbn' => '9780307474278',
        'publication_year' => 2003,
        'quantity' => 4
    ],
    [
        'title' => 'The Alchemist',
        'author' => 'Paulo Coelho',
        'category' => 'Fiction',
        'isbn' => '9780062315007',
        'publication_year' => 1988,
        'quantity' => 6
    ],
    [
        'title' => 'The Little Prince',
        'author' => 'Antoine de Saint-Exupéry',
        'category' => 'Fiction',
        'isbn' => '9780156012065',
        'publication_year' => 1943,
        'quantity' => 5
    ],
    [
        'title' => 'Animal Farm',
        'author' => 'George Orwell',
        'category' => 'Allegory',
        'isbn' => '9780451526342',
        'publication_year' => 1945,
        'quantity' => 4
    ],
    [
        'title' => 'Moby Dick',
        'author' => 'Herman Melville',
        'category' => 'Adventure',
        'isbn' => '9780142437247',
        'publication_year' => 1851,
        'quantity' => 3
    ],
    [
        'title' => 'Wuthering Heights',
        'author' => 'Emily Brontë',
        'category' => 'Romance',
        'isbn' => '9780141439516',
        'publication_year' => 1847,
        'quantity' => 5
    ],
    [
        'title' => 'The Picture of Dorian Gray',
        'author' => 'Oscar Wilde',
        'category' => 'Philosophical',
        'isbn' => '9780141439585',
        'publication_year' => 1890,
        'quantity' => 4
    ],
    [
        'title' => 'The Adventures of Sherlock Holmes',
        'author' => 'Arthur Conan Doyle',
        'category' => 'Detective',
        'isbn' => '9780141439592',
        'publication_year' => 1892,
        'quantity' => 6
    ],
    [
        'title' => 'The Call of the Wild',
        'author' => 'Jack London',
        'category' => 'Adventure',
        'isbn' => '9780141439608',
        'publication_year' => 1903,
        'quantity' => 5
    ],
    [
        'title' => 'The War of the Worlds',
        'author' => 'H.G. Wells',
        'category' => 'Science Fiction',
        'isbn' => '9780141439615',
        'publication_year' => 1898,
        'quantity' => 4
    ],
    [
        'title' => 'The Time Machine',
        'author' => 'H.G. Wells',
        'category' => 'Science Fiction',
        'isbn' => '9780141439622',
        'publication_year' => 1895,
        'quantity' => 3
    ]
];

// Insert books into database
foreach ($books as $book) {
    $sql = "INSERT INTO books (title, author, category, isbn, publication_year, quantity) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $book['title'],
        $book['author'],
        $book['category'],
        $book['isbn'],
        $book['publication_year'],
        $book['quantity']
    ]);
}

echo "Successfully added sample books to the database!";
?>
