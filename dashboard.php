<?php
include 'connect.php';

if (isset($_GET['metrics'])) {
    header('Content-Type: application/json');

    function getWindowsCpuLoad() {
        $output = shell_exec('wmic cpu get loadpercentage /value');
        preg_match('/LoadPercentage=(\d+)/', $output, $matches);
        return isset($matches[1]) ? (int)$matches[1] : 0;
    }

    $cpuPercent = getWindowsCpuLoad();

    try {
        $stmt = $conn->query("SELECT COUNT(*) AS current_conn FROM sys.dm_exec_sessions WHERE is_user_process = 1");
        $currentConnections = (int) $stmt->fetch(PDO::FETCH_ASSOC)['current_conn'];

        $maxConnections = 100; 

        $dbLoad = min(100, round(($currentConnections / $maxConnections) * 100));
    } catch (PDOException $e) {
        $dbLoad = 0; 
    }


    function getWindowsMemoryUsage() {
        $output = shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value');
        preg_match_all('/(\w+)=(\d+)/', $output, $matches);
        $memory = array_combine($matches[1], $matches[2]);
        if (!isset($memory['FreePhysicalMemory'], $memory['TotalVisibleMemorySize'])) return 0;
        $free = (int)$memory['FreePhysicalMemory'];
        $total = (int)$memory['TotalVisibleMemorySize'];
        return round(100 - (($free / $total) * 100));
    }

    $memoryPercent = getWindowsMemoryUsage();

    echo json_encode([
        'cpu' => $cpuPercent,
        'db' => $dbLoad,
        'memory' => $memoryPercent
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SuperMarket Dashboard</title>
    <link rel="stylesheet" href="style/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script/script.js" defer></script>
</head>
<body>

<header id="header">
    <div class="header-content">
        <button class="menu-btn" onclick="toggleSidebar()">☰</button>
        <h1>SuperMarket DBMS Dashboard</h1>
        <img class="img" src="images/logo.png" alt="Nokia Logo">
    </div>
</header>

<div id="sidebar" class="sidebar">
    <h3>📂 Menu</h3>
    <ul>
        <li><a href="#">Dashboard</a></li>
        <li><a href="#">Members</a></li>
        <li><a href="architecture.php">Architecture</a></li>
        <li><a href="gantt.php">GANTT</a></li>
        <li><a href="#">Log Out</a></li>
    </ul>
</div>

<div class="container">
    <h2>Select a Table</h2>
    <form action="table.php" method="POST">
        <div class="dropdown-wrapper">
            <select name="name" id="table-dropdown" required>
                <option value="" disabled selected>Loading tables...</option>
            </select>
            <button type="submit">View Table</button>
        </div>
    </form>

    <!-- KPI Pie Charts Section -->
    <div class="kpi-section">
        <h2>System KPIs</h2>
        <div class="kpi-charts">
            <div class="chart-container">
                <canvas id="cpuChart"></canvas>
                <p>CPU Load</p>
            </div>
            <div class="chart-container">
                <canvas id="dbChart"></canvas>
                <p>DB Load</p>
            </div>
            <div class="chart-container">
                <canvas id="memoryChart"></canvas>
                <p>Memory Usage</p>
            </div>
        </div>
    </div>

    <div id="table-container">
        <table>
            <thead id="table-headers"></thead>
            <tbody id="table-body"></tbody>
        </table>
    </div>
</div>

</body>
</html>
