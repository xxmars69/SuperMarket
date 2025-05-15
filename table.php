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

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['use_proc'])) {
        $column = $_POST['column'] ?? '';
        $value = $_POST['value'] ?? '';

        $stmt = $conn->prepare("EXEC sp_SafeSelectFromTable :tableName, :column, :value");
        $stmt->bindParam(':tableName', $tableName);
        $stmt->bindParam(':column', $column);
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        $dataStmt = $stmt;
        $title = "Search Results in [$tableName] where [$column] = '$value'";
    } else {
        $dataSql = "SELECT * FROM [$tableName] ORDER BY 1 OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY";
        $stmt = $conn->prepare($dataSql);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $rowsPerPage, PDO::PARAM_INT);
        $stmt->execute();
        $dataStmt = $stmt;
        $title = "Viewing Table: $tableName (Page $currentPage)";
        $paginated = true;
    }

    $totalRows = 0;
    $totalPages = 1;

    if ($paginated) {
        $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM [$tableName]");
        $countStmt->execute();
        $totalRows = $countStmt->fetchColumn();
        $totalPages = ceil($totalRows / $rowsPerPage);
    }

} catch (PDOException $e) {
    $errorMsg = "⚠️ Query failed: " . $e->getMessage();
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
                    $columnCount = $dataStmt->columnCount();
                    for ($i = 0; $i < $columnCount; $i++) {
                        $meta = $dataStmt->getColumnMeta($i);
                        echo "<th>" . htmlspecialchars($meta['name']) . "</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)): ?>
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
        <p style="color: red;">⚠️ No data to display.</p>
    <?php endif; ?>

    <hr style="margin: 40px 0;">

    <h2>Custom Select Query</h2>
    <form method="post">
        <label for="column">Search by Column:</label>
        <select name="column" required>
            <?php
            try {
                $colStmt = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = :tbl");
                $colStmt->bindParam(':tbl', $tableName);
                $colStmt->execute();
                while ($col = $colStmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . htmlspecialchars($col['COLUMN_NAME']) . '">' . htmlspecialchars($col['COLUMN_NAME']) . '</option>';
                }
            } catch (PDOException $e) {
                echo '<option disabled>Error loading columns</option>';
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
if (isset($dataStmt)) {
    $dataStmt = null;
}
$conn = null;
?>
