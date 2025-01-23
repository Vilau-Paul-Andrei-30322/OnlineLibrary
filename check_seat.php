<?php
session_start(); 

include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

$query = "SELECT sr.*
          FROM SeatReservations sr
          INNER JOIN Seats s ON sr.seat_id = s.seat_id
          WHERE s.seat_number = 1";
$stmt = sqlsrv_query($conn, $query);

$isReserved = false;

if ($stmt !== false) {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if ($row) {
        $isReserved = true;
    }
} else {
    echo "Error: Failed to check reservation status.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Seat Availability</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f4f4f4;
        }
        .chair {
            width: 50px;
            height: 50px;
            background-color: <?php echo $isReserved ? 'red' : 'gray'; ?>; 
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            cursor: <?php echo $isReserved ? 'not-allowed' : 'pointer'; ?>; 
        }
        .chair.reserved {
            background-color: green;
        }
    </style>
</head>
<body>
    <div class="chair" id="chair1" data-seatnumber="1"><?php echo $isReserved ? 'Reserved' : 'Available'; ?></div>

    <script>
        var chair = document.getElementById('chair1');
        if (!chair.classList.contains('reserved')) {
            chair.addEventListener('click', function() {
                if (!chair.classList.contains('reserved')) {
                    if (chair.style.backgroundColor === 'red') {
                        return;
                    }
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'reserve_seat.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE) {
                            if (xhr.status === 200) {
                                chair.classList.add('reserved');
                                chair.textContent = 'Reserved';
                                chair.style.cursor = 'not-allowed';
                            } else {
                                alert(xhr.responseText);
                            }
                        }
                    };
                    xhr.send();
                }
            });
        }
    </script>
</body>
</html>
