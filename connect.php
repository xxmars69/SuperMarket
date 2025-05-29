<?php
$serverName = "(localdb)\\Local";
$database = "SuperMarket";

try {
    $conn = new PDO("sqlsrv:Server=$serverName;Database=$database");

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


?>
