<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DB Architecture</title>
    <link rel="stylesheet" href="style/architecture.css">
    <script src="script/script.js"></script>
</head>
<body>

<header id="header">
    <div class="header-content">
        <button class="menu-btn" onclick="toggleSidebar()">☰</button>
        <h1>SuperMarket DBMS Architecture</h1>
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
    <h2>📄 Database Architecture</h2>
    <embed src="docs/DB_Architecture.pdf" type="application/pdf" width="100%" height="800px" />
</div>

</body>
</html>
