<?php
session_start();
include 'db.php';

if (isset($_GET['author']) && $_GET['author'] !== '') {
    $author = $_GET['author'];
    $query = "SELECT title, available, total FROM Books WHERE author = ?";
    $params = array($author);

    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        die("Error: Failed to fetch books information.");
    }

    $books = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $books[] = $row;
    }

    echo json_encode($books);
} else {
    echo "Error: Author not specified.";
}
?>
