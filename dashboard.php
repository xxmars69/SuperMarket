<?php
include 'connect.php';

$sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
$stmt = sqlsrv_query($conn, $sql);

$tables = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $tables[] = $row['TABLE_NAME'];
    }
    sqlsrv_free_stmt($stmt);
}
sqlsrv_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SuperMarket Dashboard</title>
    <link rel="stylesheet" href="style/style.css">
    <script src="script/script.js"></script>
</head>
<body>

<header id="header">
    <div class="header-content">
        <button class="menu-btn" onclick="toggleSidebar()">☰</button>
        <h1>SuperMarket DBMS Dashboard</h1>
    </div>
</header>



<div id="sidebar" class="sidebar">
    <h3>📂 Menu</h3>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="#">Members</a></li>
        <li><a href="architecture.php">Architecture</a></li>
        <li><a href="gantt.php">GANTT</a></li>
        <li><a href="#">Log Out</a></li>
    </ul>
</div>

<div class="container">
    <h2>Select a Table</h2>
    <form action="table.php" method="GET">
        <div class="dropdown-wrapper">
            <select name="name" required>
                <option value="" disabled selected>Choose a table...</option>
                <?php foreach ($tables as $table): ?>
                    <option value="<?= htmlspecialchars($table) ?>"><?= htmlspecialchars($table) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">View Table</button>
        </div>
    </form>
</div>


</body>
</html>
