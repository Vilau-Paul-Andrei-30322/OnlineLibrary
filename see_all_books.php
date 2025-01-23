<?php
session_start();
include 'db.php';

$query = "SELECT * FROM AllBooksOrdered ORDER BY title";
$stmt = sqlsrv_query($conn, $query);

$books = [];
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $books[] = $row;
    }
} else {
    echo "Error: Failed to fetch books.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PV Library - Books</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
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

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f2f2f2;
        }

        tr:first-child {
            border-top: 2px solid #ddd;
        }

        tr:last-child {
            border-bottom: 2px solid #ddd;
        }
    </style>
</head>
<body>
    <h2><a href="admin_main.php">PV Library Admin</a> - Books</h2>
    <table>
        <tr>
            <th>Book ID</th>
            <th>Title</th>
            <th>Author</th>
            <th>Available</th>
            <th>Total</th>
        </tr>
        <?php foreach ($books as $book): ?>
            <tr>
                <td><?php echo $book['book_id']; ?></td>
                <td><?php echo $book['title']; ?></td>
                <td><?php echo $book['author']; ?></td>
                <td><?php echo $book['available']; ?></td>
                <td><?php echo $book['total']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
