<?php
include 'db.php';

$seat_id = $_GET['seat_id'];

$sql = "SELECT is_active FROM SeatReservations WHERE seat_id = ? AND is_active = 1";
$params = array($seat_id);
$stmt = sqlsrv_query($conn, $sql, $params);
$current_status = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if ($current_status) {
    $sql = "UPDATE SeatReservations SET is_active = 0 WHERE seat_id = ?";
} else {
    $sql = "INSERT INTO SeatReservations (seat_id, user_id, reservation_time, is_active)
            VALUES (?, 1, GETDATE(), 1)";
}
$params = array($seat_id);
$stmt = sqlsrv_query($conn, $sql, $params);

$response = array("success" => $stmt !== false);
echo json_encode($response);
?>
