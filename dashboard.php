<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SuperMarket Dashboard</title>
    <link rel="stylesheet" href="style/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

    <div id="table-container">
        <table>
            <thead id="table-headers"></thead>
            <tbody id="table-body"></tbody>
        </table>
    </div>
</div>

</body>
</html>
