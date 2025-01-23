<?php
$serverName = "PAUL";
$connectionOptions = array(
    "Database" => "OnlineLibrary",
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>
