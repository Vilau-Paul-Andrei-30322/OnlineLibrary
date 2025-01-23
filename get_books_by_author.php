<?php
include 'db.php';

if (isset($_GET['author'])) {
    $author = $_GET['author'];
    
    $query = "SELECT title FROM Books WHERE author = ?";
    $params = array($author);
    $stmt = sqlsrv_query($conn, $query, $params);

    $books = [];
    if ($stmt !== false) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $books[] = $row['title'];
        }
        echo json_encode($books);
    } else {
        echo json_encode(["error" => "Failed to fetch books."]);
    }
} else {
    echo json_encode(["error" => "No author specified."]);
}
?>
