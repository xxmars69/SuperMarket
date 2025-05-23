<?php
include 'connect.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
    $stmt = $conn->query($sql);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['tables' => $tables]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
