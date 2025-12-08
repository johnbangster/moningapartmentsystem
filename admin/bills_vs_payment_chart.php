<?php
require('config/dbcon.php');

// Fetch distinct years for dropdown
$yearQuery = "SELECT DISTINCT YEAR(due_date) AS year FROM bills ORDER BY year DESC";
$yearResult = mysqli_query($con, $yearQuery);

// Fetch branches for dropdown
$branchQuery = "SELECT id, name FROM branch WHERE status='Active'";
$branchResult = mysqli_query($con, $branchQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billed vs Paid Chart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">📈 Monthly Billed vs Paid Summary</h4>
            <div class="d-flex gap-2">
                <!-- Year Filter -->
                <select id="yearFilter" class="form-select form-select-sm">
                    <option value="">All Years</option>
                    <?php while($y = mysqli_fetch_assoc($yearResult)): ?>
                        <option value="<?= $y['year'] ?>"><?= $y['year'] ?></option>
                    <?php endwhile; ?>
                </select>

                <!-- Branch Filter -->
                <select id="branchFilter" class="form-select form-select-sm">
                    <option value="">All Branches</option>
                    <?php while($b = mysqli_fetch_assoc($branchResult)): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div class="card-body">
            <canvas id="billsChart" height="100"></canvas>
        </div>
    </div>
</div>

<script>
let chart;
function loadChart(year = '', branch_id = '') {
    $.ajax({
        url: 'fetch_bills_chart_data.php',
        type: 'GET',
        data: { year, branch_id },
        dataType: 'json',
        success: function(data) {
            const ctx = document.getElementById('billsChart').getContext('2d');

            if (chart) chart.destroy(); // destroy old chart before redraw

            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.months,
                    datasets: [
                        {
                            label: 'Total Billed (₱)',
                            data: data.billed,
                            backgroundColor: 'rgba(255, 99, 132, 0.6)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Total Paid (₱)',
                            data: data.paid,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        title: {
                            display: true,
                            text: `Monthly Comparison: Billed vs Paid ${year ? '(' + year + ')' : ''}`
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }
    });
}

// Load initial chart
loadChart();

// Listen for filter changes
$('#yearFilter, #branchFilter').on('change', function() {
    const year = $('#yearFilter').val();
    const branch_id = $('#branchFilter').val();
    loadChart(year, branch_id);
});
</script>
</body>
</html>
