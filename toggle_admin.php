<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    http_response_code(403); 
    exit;
}

if (!isset($_GET['user_id'])) {
    http_response_code(400);
    exit;
}

$userId = $_GET['user_id'];

$query = "SELECT is_admin FROM Students WHERE user_id = ?";
$params = array($userId);
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt === false) {
    http_response_code(500); 
    exit;
}

$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if (!$user) {
    http_response_code(404); 
    exit;
}

$newAdminStatus = $user['is_admin'] ? 0 : 1;

$query = "UPDATE Students SET is_admin = ? WHERE user_id = ?";
$params = array($newAdminStatus, $userId);
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt === false) {
    http_response_code(500);
    exit;
}

echo $newAdminStatus;
