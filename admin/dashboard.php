<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HTML CSS JavaScript</title>
    <link href="https://cdn.datatables.net/v/dt/dt-2.3.1/datatables.min.css" rel="stylesheet"
        integrity="sha384-euvbLDizNhjdB+SK/Ai+GY3WCCHaDJM1tnnh2IqvUY9zjhlo21JkywSg8X5hlMY8" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css"
        integrity="sha512-cn16Qw8mzTBKpu08X0fwhTSv02kK/FojjNLz0bwp2xJ4H+yalwzXKFw/5cLzuBZCxGWIA+95X4skzvo8STNtSg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
</head>

<body class="dark">
    <header>
        <button type="button" class="sidebar-toggle" onclick="sidebarToggle()">
            <i class='bx  bx-menu'></i>
        </button>
        <div class="logo">
            <img src="logo.png" alt="">
            <span>
                CodzSword
            </span>
        </div>
        <nav>
            <button type="button" class="theme-toggle">
                <i class="bx bx-sun"></i>
                <i class="bx bx-moon"></i>
            </button>
            <ul class="nav-account">

            </ul>
        </nav>
    </header>
    <aside id="sidebar">

    </aside>
    <div class="main">
        <div class="content">
            <h2>Admin Dashboard</h2>
            <div class="dashboard-container">
                <div class="main-cards">




                </div>
            </div>
            <div class="dashboard-container">
                <div class="reports">
                    <h2>Bar Chart</h2>
                    <canvas id="bar-chart"></canvas>
                </div>
                <div class="reports">
                    <h2>Mix Chart</h2>
                    <canvas id="mixed-chart" width="800" height="450"></canvas>
                </div>
            </div>
            <div class="dashboard-container">
                <div class="dashboard-table">
                    <h2>Table</h2>

                </div>
            </div>
        </div>
        <footer class="footer">

        </footer>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/v/dt/dt-2.3.1/datatables.min.js"
        integrity="sha384-1LmfH5u7+DRwux/q4YYqAi+OjwkIVYJdPQijPS9S28cj8AeFnpNCkSVlZgvRdOzb"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
</body>

</html>