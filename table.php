<?php
include 'connect.php';

$tableName = $_GET['name'] ?? null;
$currentPage = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$rowsPerPage = 5;
$offset = ($currentPage - 1) * $rowsPerPage;

if (!$tableName) {
    die("No table selected.");
}

$dataStmt = null;
$dataSql = "";
$title = "";
$paginated = false;
$errorMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['use_proc'])) {
    $column = $_POST['column'] ?? '';
    $value = $_POST['value'] ?? '';

    $sql = "{CALL sp_SafeSelectFromTable(?, ?, ?)}";
    $params = array($tableName, $column, $value);

    $dataStmt = sqlsrv_query($conn, $sql, $params);

    if ($dataStmt) {
        $title = "Search Results in [$tableName] where [$column] = '$value'";
    } else {
        $errorMsg = "⚠️ Query failed: " . print_r(sqlsrv_errors(), true);
    }
}

else {
    $dataSql = "SELECT * FROM [$tableName] ORDER BY 1 OFFSET $offset ROWS FETCH NEXT $rowsPerPage ROWS ONLY";
    $title = "Viewing Table: $tableName (Page $currentPage)";
    $paginated = true;
    $dataStmt = sqlsrv_query($conn, $dataSql);
}

$totalRows = 0;
$totalPages = 1;

if ($paginated) {
    $countQuery = "SELECT COUNT(*) AS total FROM [$tableName]";
    $countStmt = sqlsrv_query($conn, $countQuery);
    if ($countStmt && $row = sqlsrv_fetch_array($countStmt, SQLSRV_FETCH_ASSOC)) {
        $totalRows = $row['total'];
        $totalPages = ceil($totalRows / $rowsPerPage);
    }
}

function formatCell($value) {
    if ($value instanceof DateTime) {
        return $value->format('Y-m-d');
    } elseif (is_null($value)) {
        return "<i>NULL</i>";
    }
    return htmlspecialchars((string) $value);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="style/table.css">
</head>
<body>

<header>
    <h1><?= htmlspecialchars($title) ?></h1>
</header>

<div class="container">
    <?php if ($errorMsg): ?>
        <p style="color: red; font-weight: bold;"><?= $errorMsg ?></p>
    <?php elseif ($dataStmt): ?>
        <table>
            <thead>
                <tr>
                    <?php
                    $columns = sqlsrv_field_metadata($dataStmt);
                    foreach ($columns as $col) {
                        echo "<th>" . htmlspecialchars($col['Name']) . "</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = sqlsrv_fetch_array($dataStmt, SQLSRV_FETCH_ASSOC)): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                            <td><?= formatCell($cell) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($paginated && $totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a class="page-btn" href="?name=<?= urlencode($tableName) ?>&page=<?= $currentPage - 1 ?>">← Previous</a>
                <?php endif; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a class="page-btn" href="?name=<?= urlencode($tableName) ?>&page=<?= $currentPage + 1 ?>">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p style="color: red;">⚠️ Query failed: <?= print_r(sqlsrv_errors(), true) ?></p>
    <?php endif; ?>

    <hr style="margin: 40px 0;">

    <h2>Custom Select Query</h2>
    <form method="post">
        <label for="column">Search by Column:</label>
        <select name="column" required>
            <?php
            $colResult = sqlsrv_query($conn, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ?", array($tableName));
            while ($col = sqlsrv_fetch_array($colResult, SQLSRV_FETCH_ASSOC)) {
                echo '<option value="' . htmlspecialchars($col['COLUMN_NAME']) . '">' . htmlspecialchars($col['COLUMN_NAME']) . '</option>';
            }
            ?>
        </select><br><br>

        <label for="value">Value:</label>
        <input type="text" name="value" required><br><br>

        <input type="hidden" name="use_proc" value="1">
        <input type="hidden" name="name" value="<?= htmlspecialchars($tableName) ?>">
        <button type="submit">🔍 Search</button>
    </form>

    <br>
    <a href="dashboard.php"><button>🔙 Back to Dashboard</button></a>
</div>

</body>
</html>

<?php
if ($dataStmt) sqlsrv_free_stmt($dataStmt);
sqlsrv_close($conn);
?>
