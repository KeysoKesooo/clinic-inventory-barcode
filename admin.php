<?php
  $page_title = 'Admin Home Page';
  require_once('includes/load.php');
  page_require_level(2);
  
  // Get date range from URL or default to week
  $range = isset($_GET['range']) ? $_GET['range'] : 'week';
  
  // Count data for cards
  $c_categorie = count_by_id('categories');
  $c_product   = count_by_id('products');
  $c_sale      = count_by_id('sales');
  $c_user      = count_by_id('users');
  
// Get chart data
$stock_levels = find_stock_level_status();
$dispensing_trends = find_medicine_dispensing_trends();
?>

<?php include_once('layouts/header.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?></title>
    <link rel="stylesheet" href="libs/css/admin.css">
</head>



<div class="dashboard">
    <?php echo display_msg($msg); ?>

    <div class="card-container">
        <a href="users.php" class="card card-users">
            <div class="card-value"><?php echo $c_user['total']; ?></div>
            <div class="card-label">Users</div>
        </a>

        <a href="categorie.php" class="card card-categories">
            <div class="card-value"><?php echo $c_categorie['total']; ?></div>
            <div class="card-label">Categories</div>
        </a>

        <a href="product.php" class="card card-products">
            <div class="card-value"><?php echo $c_product['total']; ?></div>
            <div class="card-label">Products</div>
        </a>

        <a href="sales.php" class="card card-sales">
            <div class="card-value"><?php echo $c_sale['total']; ?></div>
            <div class="card-label">Dispensed</div>
        </a>
    </div>

    <!-- Charts -->
    <div class="chart-container">
        <!-- Stock Level Status -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Stock Level Status</div>
                <p>Shows which items are running low..</p>
            </div>
            <div class="chart">
                <canvas id="stockLevelChart"></canvas>
            </div>
        </div>

        <!-- Medicine Dispensing Trends -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Medicine Dispensing Trends</div>
                <p>How the dispensing of certain medicines changes over months.</p>
            </div>
            <div class="chart">
                <canvas id="medicineTrendsChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        /* -------------------------
           Stock Level Status Chart
        ------------------------- */
        const stockLabels = [<?php foreach ($stock_levels as $row) { echo "'" . $row['name'] . "',"; } ?>];
        const stockData = [<?php foreach ($stock_levels as $row) { echo (int)$row['stock_qty'] . ","; } ?>];

        // Color bars based on low stock threshold
        const stockColors = stockData.map(qty => qty < 20 ? '#e74c3c' : '#4cafef');

        const stockCtx = document.getElementById('stockLevelChart').getContext('2d');
        new Chart(stockCtx, {
            type: 'bar',
            data: {
                labels: stockLabels,
                datasets: [{
                    label: 'Stock Quantity',
                    data: stockData,
                    backgroundColor: stockColors
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.parsed.y + ' units in stock'
                        }
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantity in Stock'
                        }
                    },
                    x: {
                        ticks: {
                            autoSkip: false
                        },
                        title: {
                            display: true,
                            text: 'Product Name'
                        }
                    }
                }
            }
        });


        /* -------------------------
           Medicine Dispensing Trends
        ------------------------- */
        const trendsData = <?php
        $data = [];
        foreach ($dispensing_trends as $row) {
            $data[$row['medicine_name']][$row['month']] = (int)$row['total_dispensed'];
        }
        echo json_encode($data);
    ?>;

        const months = [...new Set(Object.values(trendsData).flatMap(obj => Object.keys(obj)))];
        const colors = [
            '#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231',
            '#911eb4', '#46f0f0', '#f032e6', '#bcf60c', '#fabebe',
            '#008080', '#e6beff', '#9a6324', '#fffac8', '#800000'
        ];
        let colorIndex = 0;

        const datasets = Object.entries(trendsData).map(([medicine, values]) => ({
            label: medicine,
            data: months.map(m => values[m] ?? 0),
            borderColor: colors[colorIndex++ % colors.length],
            backgroundColor: 'transparent',
            borderWidth: 2,
            tension: 0.1
        }));

        const trendsCtx = document.getElementById('medicineTrendsChart').getContext('2d');
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: datasets
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.parsed.y + ' units dispensed'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantity Dispensed'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    });
    </script>

    <?php include_once('layouts/footer.php'); ?>