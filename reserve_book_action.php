<?php
session_start();
include 'db.php';

function log_sqlsrv_errors() {
    if (($errors = sqlsrv_errors()) != null) {
        foreach ($errors as $error) {
            error_log("SQLSTATE: " . $error['SQLSTATE']);
            error_log("Code: " . $error['code']);
            error_log("Message: " . $error['message']);
        }
    } else {
        error_log("No SQL Server errors.");
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['user_id']) || !isset($_POST['book_title']) || !isset($_POST['author'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
        exit;
    }

    $bookTitle = $_POST['book_title'];
    $author = $_POST['author'];
    $userId = $_POST['user_id'];

    $query = "SELECT book_id, available FROM Books WHERE title = ? AND author = ?";
    $params = array($bookTitle, $author);
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        log_sqlsrv_errors();
        echo json_encode(['success' => false, 'message' => 'Failed to fetch book information.']);
        exit;
    }

    $book = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if (!$book) {
        echo json_encode(['success' => false, 'message' => 'Book not found.']);
        exit;
    }

    if ($book['available'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'Book not available.']);
        exit;
    }

    $bookId = $book['book_id'];
    $available = $book['available'];

    if (sqlsrv_begin_transaction($conn) === false) {
        log_sqlsrv_errors();
        echo json_encode(['success' => false, 'message' => 'Failed to begin transaction.']);
        exit;
    }

    try {
        $insertQuery = "INSERT INTO BookReservations (user_id, book_id, is_taken, reservation_time) VALUES (?, ?, ?, ?)";
        $insertParams = array($userId, $bookId, 1, date('Y-m-d H:i:s'));  
        $insertStmt = sqlsrv_query($conn, $insertQuery, $insertParams);

        if ($insertStmt === false) {
            throw new Exception('Failed to reserve the book.');
        }
        $updateQuery = "UPDATE Books SET available = available - 1 WHERE book_id = ?";
        $updateParams = array($bookId);
        $updateStmt = sqlsrv_query($conn, $updateQuery, $updateParams);

        if ($updateStmt === false) {
            throw new Exception('Failed to update book availability.');
        }

        $checkReservationQuery = "SELECT COUNT(*) as count FROM BookReservations WHERE user_id = ?";
        $checkReservationParams = array($userId);
        $checkReservationStmt = sqlsrv_query($conn, $checkReservationQuery, $checkReservationParams);
        $reservationCount = sqlsrv_fetch_array($checkReservationStmt, SQLSRV_FETCH_ASSOC)['count'];

        if ($reservationCount == 1) {
            $updateStudentQuery = "UPDATE Students SET has_book_reservations = 1 WHERE user_id = ?";
            $updateStudentParams = array($userId);
            $updateStudentStmt = sqlsrv_query($conn, $updateStudentQuery, $updateStudentParams);

            if ($updateStudentStmt === false) {
                throw new Exception('Failed to update student information.');
            }
        }

        if (sqlsrv_commit($conn) === false) {
            throw new Exception('Failed to commit transaction.');
        }

        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        sqlsrv_rollback($conn);
        log_sqlsrv_errors();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
