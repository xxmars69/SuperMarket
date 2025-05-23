<?php
include 'connect.php';

$tableName = $_POST['name'] ?? null;
$page = isset($_POST['page']) ? max((int)$_POST['page'], 1) : 1;
$rowsPerPage = 5;
$offset = ($page - 1) * $rowsPerPage;

$useProc = $_POST['use_proc'] ?? false;
$column = $_POST['column'] ?? null;
$value = $_POST['value'] ?? null;

try {
    if (!$tableName) throw new Exception("No table specified.");

    if ($useProc && $column && $value !== null) {
        $stmt = $conn->prepare("EXEC sp_SafeSelectFromTable :tableName, :column, :value");
        $stmt->bindParam(':tableName', $tableName);
        $stmt->bindParam(':column', $column);
        $stmt->bindParam(':value', $value);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("SELECT * FROM [$tableName] ORDER BY 1 OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY");
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $rowsPerPage, PDO::PARAM_INT);
        $stmt->execute();
    }

    $columns = [];
    for ($i = 0; $i < $stmt->columnCount(); $i++) {
        $meta = $stmt->getColumnMeta($i);
        $columns[] = $meta['name'];
    }

    ob_start();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td>" . (is_null($cell) ? "<i>NULL</i>" : htmlspecialchars((string)$cell)) . "</td>";
        }
        echo "</tr>";
    }
    $body = ob_get_clean();

    echo json_encode([
        'columns' => $columns,
        'body' => $body
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
