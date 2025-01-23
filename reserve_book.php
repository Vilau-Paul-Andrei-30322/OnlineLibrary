<?php
session_start();
include 'db.php';

$query = "SELECT title, author FROM Books";
$stmt = sqlsrv_query($conn, $query);

$books = [];
$authors = [];
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $books[] = array(
            'title' => $row['title'],
            'author' => $row['author']
        );
        if (!in_array($row['author'], $authors)) {
            $authors[] = $row['author'];
        }
    }
} else {
    echo "Error: Failed to fetch books and authors.";
}

$user_id = $_SESSION['user_id']; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve Book</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 100px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-family: Lucida Handwriting, Cursive;
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
        }
        h2 a {
            text-decoration: none;
            color: #333;
        }
        form {
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><a href="main.php">PV Library</a> - Reserve a Book</h2>
        <form id="reserveBookForm" method="POST" action="reserve_book_action.php">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>" id="user_id">
            <label for="book_title">Select Book Title:</label>
            <select id="book_title" name="book_title" onchange="updateAuthors()">
                <option value="">Select Book Title</option>
                <?php foreach ($books as $book): ?>
                    <option value="<?php echo htmlspecialchars($book['title']); ?>"><?php echo htmlspecialchars($book['title']); ?></option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <label for="author">Select Author:</label>
            <select id="author" name="author" onchange="updateBooks()">
                <option value="">Select Author</option>
                <?php foreach ($authors as $author): ?>
                    <option value="<?php echo htmlspecialchars($author); ?>"><?php echo htmlspecialchars($author); ?></option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <button type="button" onclick="reserveBook()">Reserve</button>
        </form>
    </div>

    <script>
        var books = <?php echo json_encode($books); ?>;
        var authors = <?php echo json_encode($authors); ?>;

        function updateAuthors() {
            var bookTitle = document.getElementById('book_title').value;
            var authorDropdown = document.getElementById('author');
            
            authorDropdown.innerHTML = '';
            
            var allAuthorsOption = document.createElement('option');
            allAuthorsOption.value = '';
            allAuthorsOption.text = 'Select Author';
            authorDropdown.appendChild(allAuthorsOption);
            
            authors.forEach(function(author) {
                var option = document.createElement('option');
                option.value = author;
                option.text = author;
                authorDropdown.appendChild(option);
            });

            if (bookTitle !== "") {
                for (var i = 0; i < books.length; i++) {
                    if (books[i].title === bookTitle) {
                        authorDropdown.value = books[i].author;
                        break;
                    }
                }
            }
        }

        function updateBooks() {
            var author = document.getElementById('author').value;
            var bookDropdown = document.getElementById('book_title');
            bookDropdown.innerHTML = '<option value="">Select Book Title</option>';

            if (author === "") {
                return;
            }

            var filteredBooks = books.filter(function(book) {
                return book.author === author;
            });

            filteredBooks.forEach(function(book) {
                var option = document.createElement('option');
                option.value = book.title;
                option.text = book.title;
                bookDropdown.appendChild(option);
            });
        }

        function reserveBook() {
            var bookTitle = document.getElementById('book_title').value;
            var author = document.getElementById('author').value;
            var userId = document.getElementById('user_id').value;

            var formData = new FormData();
            formData.append('book_title', bookTitle);
            formData.append('author', author);
            formData.append('user_id', userId);

            fetch('reserve_book_action.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Raw Response:', response);
                return response.text();
            })
            .then(data => {
                console.log('Response Body:', data);
                return JSON.parse(data);
            })
            .then(parsedData => {
                if (parsedData.success) {
                    alert('Book reserved successfully.');
                } else {
                    alert('Error: ' + parsedData.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred.');
            });
        }
    </script>
</body>
</html>
