<?php
include 'db.php';

function endBookReservation($reservationId) {
    global $conn;

    $query = "SELECT * FROM ActiveBookReservations WHERE reservation_id = ?";
    $params = array($reservationId);
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        echo "Failed to fetch reservation data.";
    } else {
        $reservation = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        
        $reservation['end_book_reservation_time'] = date('Y-m-d H:i:s');
        
        appendBookReservationToJson($reservation);
        
        $deleteQuery = "DELETE FROM BookReservations WHERE reservation_id = ?";
        $deleteParams = array($reservationId);
        $deleteStmt = sqlsrv_query($conn, $deleteQuery, $deleteParams);

        if ($deleteStmt === false) {
            echo "Failed to delete reservation from database.";
        } else {
            $updateQuery = "UPDATE Students SET has_book_reservations = 0 WHERE user_id = ?";
            $updateParams = array($reservation['user_id']);
            $updateStmt = sqlsrv_query($conn, $updateQuery, $updateParams);

            if ($updateStmt === false) {
                echo "Failed to update has_book_reservations field.";
            } else {
                echo "Reservation ended and saved to JSON successfully.";
            }
        }
    }
}

function appendBookReservationToJson($reservation) {
    $filename = 'book_reservations.json';
    
    if (isset($reservation['reservation_id']) && isset($reservation['user_id']) && isset($reservation['name']) && isset($reservation['title']) && isset($reservation['author']) && isset($reservation['reservation_time'])) {
        $end_book_reservation_time = date('Y-m-d H:i:s');
        
        $formatted_reservation = [
            'reservation_id' => $reservation['reservation_id'],
            'user_id' => $reservation['user_id'],
            'name' => $reservation['name'],
            'title' => $reservation['title'],
            'author' => $reservation['author'],
            'reservation_time' => $reservation['reservation_time']->format('Y-m-d H:i:s'),
            'end_book_reservation_time' => $end_book_reservation_time
        ];

        $existingData = file_get_contents($filename);
        $reservations = json_decode($existingData, true);
        
        $reservations[] = $formatted_reservation;
        
        $newData = json_encode($reservations, JSON_PRETTY_PRINT);
        
        file_put_contents($filename, $newData);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reservation_id'])) {
    $reservationId = $_POST['reservation_id'];
    endBookReservation($reservationId); 
}

$query = "SELECT * FROM ActiveBookReservations";
$stmt = sqlsrv_query($conn, $query);

if ($stmt === false) {
    echo "Failed to fetch book reservations.";
} else {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Book Reservations - PV Library Admin</title>
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
        <h2> <a href="admin_main.php">PV Library Admin</a> - Book Reservations</h2>

        <div class="box">
            <h3>Active Book Reservations</h3>
            <table>
                <tr>
                    <th>Reservation ID</th>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Reservation Time</th>
                    <th>Action</th>
                </tr>
                <?php
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    ?>
                    <tr>
                        <td><?php echo $row['reservation_id']; ?></td>
                        <td><?php echo $row['user_id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['author']; ?></td>
                        <td><?php echo $row['reservation_time']->format('Y-m-d H:i:s'); ?></td>
                        <td>
                            <button class="end-btn" onclick="endBookReservation(<?php echo $row['reservation_id']; ?>)">End Reservation</button>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>

        <script>
            function endBookReservation(reservationId) {
                if (confirm("Are you sure you want to end and delete this reservation?")) {
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE) {
                            if (xhr.status === 200) {
                                location.reload();
                            } else {
                                console.error("Error ending and deleting reservation: " + xhr.responseText);
                            }
                        }
                    };
                    xhr.send("reservation_id=" + reservationId);
                }
            }
        </script>
    </body>
    </html>
    <?php
}
?>
