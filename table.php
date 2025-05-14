<?php
include 'connect.php';

$tableName = $_GET['name'] ?? null;
$currentPage = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$rowsPerPage = 5;
$offset = ($currentPage - 1) * $rowsPerPage;

$customQuery = $_POST['sql_query'] ?? null;

if (!$tableName) {
    die("No table selected.");
}

$dataSql = "";
$title = "";
$paginated = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $customQuery) {
    if (
        stripos(trim($customQuery), 'SELECT') === 0 ||
        stripos(trim($customQuery), 'EXEC') === 0
    ) {
        $dataSql = $customQuery;
        $title = "Custom Query Result";
    } else {
        $errorMsg = "⚠️ Only SELECT and EXEC queries are allowed.";
    }
} else {
    $dataSql = "SELECT * FROM [$tableName] ORDER BY 1 OFFSET $offset ROWS FETCH NEXT $rowsPerPage ROWS ONLY";
    $title = "Viewing Table: $tableName (Page $currentPage)";
    $paginated = true;
}

$dataStmt = $dataSql ? sqlsrv_query($conn, $dataSql) : null;

// Count total rows for pagination
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
    <?php if (isset($errorMsg)): ?>
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
                <?php
                while ($row = sqlsrv_fetch_array($dataStmt, SQLSRV_FETCH_ASSOC)) {
                    echo "<tr>";
                    foreach ($row as $cell) {
                        echo "<td>" . formatCell($cell) . "</td>";
                    }
                    echo "</tr>";
                }
                ?>
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

    <h2>Run a Custom SQL Query</h2>
    <form method="post">
        <input type="hidden" name="name" value="<?= htmlspecialchars($tableName) ?>">
        <textarea name="sql_query" rows="5" placeholder="SELECT * FROM [<?= htmlspecialchars($tableName) ?>]"><?= htmlspecialchars($customQuery ?? '') ?></textarea><br><br>
        <button type="submit">Execute Query</button>
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
