<?php
session_start();
include 'db.php';

$query = "SELECT DISTINCT author FROM Books";
$stmt = sqlsrv_query($conn, $query);

$authors = [];
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $authors[] = $row['author'];
    }
} else {
    die("Error: Failed to fetch authors.");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Books</title>
    <style>
        body {
            background-color: #f4f4f4;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
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
        .reserve-button {
            display: inline-block;
            padding: 4px 8px;
            background-color: #4CAF50;
            margin-bottom: 20px;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .reserve-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<h2> <a href="main.php">PV Library</a> - Books</h2>
    <form>
        <label for="author">Select Author:</label>
        <select id="author" name="author" onchange="updateBooks()">
            <option value="">Select Author</option>
            <?php foreach ($authors as $author): ?>
                <option value="<?php echo htmlspecialchars($author); ?>"><?php echo htmlspecialchars($author); ?></option>
            <?php endforeach; ?>
        </select>
        <a href="reserve_book.php" class="reserve-button">Reserve a Book</a>    

    </form>

    <table id="booksTable">
        <thead>
            <tr>
                <th>Title</th>
                <th>Available Copies</th>
                <th>Total Copies</th>
            </tr>
        </thead>
        <tbody id="bookRows">
            <tr id="placeholderRow">
                <td colspan="3">Select an author to see book information.</td>
            </tr>
        </tbody>
    </table>

    <script>
    function updateBooks() {
    var author = document.getElementById('author').value;
    var booksTable = document.getElementById('booksTable');
    var bookRows = document.getElementById('bookRows');

    bookRows.innerHTML = '';

    if (author !== '') {
        fetch('get_books_info.php?author=' + encodeURIComponent(author))
            .then(response => response.json())
            .then(books => {
                books.forEach(book => {
                    var row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${book.title}</td>
                        <td>${book.available}</td>
                        <td>${book.total}</td>
                    `;
                    bookRows.appendChild(row);
                });
            })
            .catch(error => console.error('Error:', error));
    } else {
        var placeholderRow = document.createElement('tr');
        placeholderRow.innerHTML = `
            <td colspan="3">Select an author to see book information.</td>
        `;
        bookRows.appendChild(placeholderRow);
    }
}

    </script>
</body>
</html>
