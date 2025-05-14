<?php
$serverName = "CSIAOMIPC\\SQLEXPRESS"; 

$connectionOptions = array(
    "Database" => "SuperMarket",       
    "CharacterSet" => "UTF-8"
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}
?>
