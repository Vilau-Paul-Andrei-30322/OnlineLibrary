<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "SELECT sr.reservation_id, s.seat_number, sr.reservation_time
          FROM SeatReservations sr
          INNER JOIN Seats s ON sr.seat_id = s.seat_id
          WHERE sr.user_id = ?";
$params = array($user_id);
$stmt = sqlsrv_query($conn, $query, $params);

$reservations = [];
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $reservation_time = $row['reservation_time']->format('Y-m-d H:i:s');
        $row['reservation_time'] = $reservation_time;
        $reservations[] = $row;
    }
} else {
    echo "Error: Database query failed.";
}

function endReservation($reservationId) {
    global $conn;

    $query = "SELECT sr.reservation_id, sr.user_id, sr.seat_id, sr.reservation_time, st.name
              FROM SeatReservations sr
              INNER JOIN Students st ON sr.user_id = st.user_id
              WHERE sr.reservation_id = ?";
    $params = array($reservationId);
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        echo "Failed to fetch reservation data.";
        die(print_r(sqlsrv_errors(), true)); 
    } else {
        $reservation = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        $end_seat_reservation_time = date('Y-m-d H:i:s');

        $filtered_reservation = [
            'reservation_id' => $reservation['reservation_id'],
            'user_id' => $reservation['user_id'],
            'name' => $reservation['name'],
            'seat_id' => $reservation['seat_id'],
            'reservation_time' => $reservation['reservation_time']->format('Y-m-d H:i:s'),
            'end_seat_reservation_time' => $end_seat_reservation_time
        ];

        appendReservationToJson($filtered_reservation);

        $deleteQuery = "DELETE FROM SeatReservations WHERE reservation_id = ?";
        $deleteParams = array($reservationId);
        $deleteStmt = sqlsrv_query($conn, $deleteQuery, $deleteParams);

        if ($deleteStmt === false) {
            echo "Failed to delete reservation from database.";
            die(print_r(sqlsrv_errors(), true)); 
        } else {
            $updateQuery = "UPDATE Students SET has_seat_reservation = 0 WHERE user_id = ?";
            $updateParams = array($reservation['user_id']);
            $updateStmt = sqlsrv_query($conn, $updateQuery, $updateParams);
    
            if ($updateStmt === false) {
                echo "Failed to update has_reservation field.";
                die(print_r(sqlsrv_errors(), true));
            }
        }
    }
}

function appendReservationToJson($reservation) {
    $filename = 'reservations.json';
    
    $existingData = file_get_contents($filename);
    $reservations = json_decode($existingData, true);
    
    $reservations[] = $reservation;
    
    $newData = json_encode($reservations, JSON_PRETTY_PRINT);
    
    file_put_contents($filename, $newData);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['end_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    endReservation($reservation_id);
    header("Location: my_reservations.php");
    exit;
}

$query = "SELECT br.reservation_id, b.title, b.author, br.reservation_time
          FROM BookReservations br
          INNER JOIN Books b ON br.book_id = b.book_id
          WHERE br.user_id = ?";
$params = array($user_id);
$stmt = sqlsrv_query($conn, $query, $params);

$book_reservations = [];
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $reservation_time = $row['reservation_time']->format('Y-m-d H:i:s');
        $row['reservation_time'] = $reservation_time;
        $book_reservations[] = $row;
    }
} else {
    echo "Error executing query: " . print_r(sqlsrv_errors(), true);

    echo "Error: Database query failed.";
}

function endBookReservation($reservationId) {
    global $conn;

    $query = "SELECT br.reservation_id, br.user_id, b.title, b.author, br.reservation_time, st.name
              FROM BookReservations br
              INNER JOIN Books b ON br.book_id = b.book_id
              INNER JOIN Students st ON br.user_id = st.user_id
              WHERE br.reservation_id = ?";
    $params = array($reservationId);
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        echo "Failed to fetch book reservation data.";
        die(print_r(sqlsrv_errors(), true));
    } else {
        $reservation = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        $end_book_reservation_time = date('Y-m-d H:i:s');

        $filtered_reservation = [
            'reservation_id' => $reservation['reservation_id'],
            'user_id' => $reservation['user_id'],
            'name' => $reservation['name'],
            'title' => $reservation['title'],
            'author' => $reservation['author'],
            'reservation_time' => $reservation['reservation_time']->format('Y-m-d H:i:s'),
            'end_book_reservation_time' => $end_book_reservation_time
        ];

        $updateQuery = "UPDATE Books SET available = available + 1 WHERE book_id = ?";
        $updateParams = array($reservation['book_id']);
        $updateStmt = sqlsrv_query($conn, $updateQuery, $updateParams);
    
        if ($updateStmt === false) {
            echo "Failed to update available count for the book.";
            die(print_r(sqlsrv_errors(), true));
        }

        appendBookReservationToJson($filtered_reservation);

        $deleteQuery = "DELETE FROM BookReservations WHERE reservation_id = ?";
        $deleteParams = array($reservationId);
        $deleteStmt = sqlsrv_query($conn, $deleteQuery, $deleteParams);

        if ($deleteStmt === false) {
            echo "Failed to delete book reservation from database.";
            die(print_r(sqlsrv_errors(), true));
        } else {
            $updateQuery = "UPDATE Students SET has_book_reservations = 0 WHERE user_id = ?";
            $updateParams = array($reservation['user_id']);
            $updateStmt = sqlsrv_query($conn, $updateQuery, $updateParams);
    
            if ($updateStmt === false) {
                echo "Failed to update has_reservation field.";
                die(print_r(sqlsrv_errors(), true)); 
            }
        }
    }
}


function appendBookReservationToJson($reservation) {
    $filename = 'book_reservations.json';
    
    $existingData = file_get_contents($filename);
    $reservations = json_decode($existingData, true);   
    
    $reservations[] = $reservation;
    
    $newData = json_encode($reservations, JSON_PRETTY_PRINT);
    
    file_put_contents($filename, $newData);
}



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['end_book_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    endBookReservation($reservation_id); 
    header("Location: my_reservations.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations - PV Library</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .box {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
            max-width: 800px;
            margin-bottom: 20px;
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
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        form {
            margin: 0;
        }

        .end-btn {
                padding: 5px 10px;
                background-color: red;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
    </style>
</head>
<body>
    <h2> <a href="main.php">PV Library</a> - My Reservations</h2>

    <div class="box">
        <h3>Seat Reservations</h3>
        <table>
            <tr>
                <th>Reservation ID</th>
                <th>Seat Number</th>
                <th>Reservation Time</th>
                <th>Action</th>
            </tr>
            <?php
            if (!empty($reservations)) {
                foreach ($reservations as $reservation) {
                    echo "<tr>";
                    echo "<td>" . $reservation['reservation_id'] . "</td>";
                    echo "<td>" . $reservation['seat_number'] . "</td>";
                    echo "<td>" . $reservation['reservation_time'] . "</td>";
                    echo "<td>
                            <form method='post'>
                                <input type='hidden' name='reservation_id' value='{$reservation['reservation_id']}'>
                                <input type='submit' name='end_reservation' value='End Reservation' class='end-btn'>
                            </form>
                        </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No reservations found.</td></tr>";
            }
            ?>
        </table>
    </div>

    <div class="box">
    <h3>Book Reservations</h3>
    <table>
        <tr>
            <th>Reservation ID</th>
            <th>Title</th>
            <th>Author</th>
            <th>Reservation Time</th>
            <th>Action</th>
        </tr>
        <?php
        if (!empty($book_reservations)) {
            foreach ($book_reservations as $reservation) {
                echo "<tr>";
                echo "<td>" . $reservation['reservation_id'] . "</td>";
                echo "<td>" . $reservation['title'] . "</td>";
                echo "<td>" . $reservation['author'] . "</td>";
                echo "<td>" . $reservation['reservation_time'] . "</td>";
                echo "<td>
                        <form method='post'>
                            <input type='hidden' name='reservation_id' value='{$reservation['reservation_id']}'>
                            <input type='submit' name='end_book_reservation' value='End Reservation' class='end-btn'>
                        </form>
                    </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No book reservations found.</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>

