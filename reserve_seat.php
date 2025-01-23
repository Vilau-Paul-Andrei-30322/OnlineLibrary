<?php
session_start();
include 'db.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    header("Location: login.php");
    exit;
}

$reservation_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $seat_id = $_POST['seat_id'];
    $reservation_id = mt_rand(1000, 9999);
    $reservation_time = date('Y-m-d H:i:s'); 

    $query = "SELECT COUNT(*) AS seat_reserved FROM SeatReservations WHERE seat_id = ? AND is_active = 1";
    $params = array($seat_id);
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt !== false) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if ($row['seat_reserved'] > 0) {
            $reservation_message = "Seat already reserved.";
        } else {
            $query = "SELECT COUNT(*) AS reservation_count FROM SeatReservations WHERE user_id = ?";
            $params = array($user_id);
            $stmt = sqlsrv_query($conn, $query, $params);

            if ($stmt !== false) {
                $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                if ($row['reservation_count'] > 0) {
                    $reservation_message = "You already have a reservation.";
                } else {
                    $query = "INSERT INTO SeatReservations (reservation_id, user_id, seat_id, reservation_time, is_active) VALUES (?, ?, ?, ?, 1)";
                    $params = array($reservation_id, $user_id, $seat_id, $reservation_time);
                    $stmt = sqlsrv_query($conn, $query, $params);
                    
                    if ($stmt) {
                        $updateQuery = "UPDATE Students SET has_seat_reservation = 1 WHERE user_id = ?";
                        $updateParams = array($user_id);
                        $updateStmt = sqlsrv_query($conn, $updateQuery, $updateParams);

                        if ($updateStmt) {
                            $reservation_message = "Reservation successful.";
                        } else {
                            $reservation_message = "Error updating has_reservation field.";
                        }
                    } else {
                        $reservation_message = "Error: " . sqlsrv_errors();
                    }
                }
            } else {
                $reservation_message = "Error: Database query failed.";
            }
        }
    } else {
        $reservation_message = "Error: Database query failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve Seat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 160px;
        }

        .reservation-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 300px;
            margin-top: 10px;
        }

        h3 {
            font-family: Lucida Handwriting, Cursive;
            font-size: 28px;
            color: #333;
            margin-top: 0;
            margin-bottom: 40px;
            text-align: center;
        }

        h3 a {
            text-decoration: none;
            color: #333;
        }

        h2 {
            margin-top: 0;
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-bottom: 10px;
        }

        label {
            font-size: 16px;
            color: #333;
            margin-bottom: 8px;
        }

        select, input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        input[type="submit"]{
            width: 100%;
            padding: 10px 20px;
            background-color: #4CAF50;
            margin-bottom: 20px;
            color: white;
            border: none;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .reservation-message {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h3> <a href="main.php">PV Library</a> - Seat Reservation</h3>
        <div class="reservation-container">
            <h2>Reserve Seat</h2>
            <div class="reservation-message"><?php echo $reservation_message; ?></div>
            <form method="post">
                <label for="seat_id">Select Seat:</label>
                <select name="seat_id" id="seat_id">
                    <?php
                    $query = "SELECT seat_id, seat_number FROM Seats WHERE is_reserved = 0";
                    $stmt = sqlsrv_query($conn, $query);

                    if ($stmt !== false) {
                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                            echo "<option value='{$row['seat_id']}'>{$row['seat_number']}</option>";
                        }
                    } else {
                        echo "<option value=''>Error: Failed to fetch seats</option>";
                    }
                    ?>
                </select>
                <br><br>
                <input type="submit" name="submit" value="Reserve">
            </form>
        </div>
    </div>
</body>
</html>
