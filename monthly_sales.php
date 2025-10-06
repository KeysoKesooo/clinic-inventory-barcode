<?php
  $page_title = 'Monthly Records';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(3);
?>
<?php
 $year = date('Y');
 $sales = monthlySales($year);
?>
<?php include_once('layouts/header.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?></title>
    <link rel="stylesheet" href="libs/css/roles.css">
</head>

<body>

    <div class="row">
        <div class="col-md-12">
            <?php echo display_msg(isset($msg) ? $msg : ''); ?>
        </div>
    </div>


    <div id="manageSales" class="content-section active">
        <div class="sales-container">
            <div class="action-buttons-container">
                <div class="search-bar-container">
                    <input type="text" id="search-bar-sales" class="search-bar" placeholder="Search records...">
                </div>

                <a class="export_button" id="download-btn">
                    <i class="fa-solid fa-download"></i>
                    <span class="export_button__text">Export</span>
                </a>

                <div class="filter-wrapper">
                    <button class="toggle-filter-btn" data-target="sales-filter-container">Show Filters</button>
                    <div id="sales-filter-container" class="filter-container">
                        <label for="sales-category-filter">Category:</label>
                        <div class="select">
                            <div class="selected" data-default="All">
                                <span id="sales-category-selected">All</span>
                                <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"
                                    class="arrow">
                                    <path
                                        d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z">
                                    </path>
                                </svg>
                            </div>
                            <div class="options">
                                <div title="all">
                                    <input id="sales-category-all" name="sales-category-option" type="radio" value=""
                                        checked />
                                    <label class="option" for="sales-category-all">All</label>
                                </div>
                                <?php 
                            $categories = find_all('categories');
                            foreach ($categories as $category): ?>
                                <div title="<?= htmlspecialchars($category['name']) ?>">
                                    <input id="sales-category-<?= htmlspecialchars($category['name']) ?>"
                                        name="sales-category-option" type="radio"
                                        value="<?= htmlspecialchars($category['name']) ?>" />
                                    <label class="option"
                                        for="sales-category-<?= htmlspecialchars($category['name']) ?>">
                                        <?= htmlspecialchars(ucwords($category['name'])) ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales management table -->
            <div class="table-content" id="table-content-sales">
                <?php foreach ($sales as $month => $entries): ?>
                <details class="monthly-group">
                    <summary>
                        <strong><?php echo $month; ?></strong> (<?php echo count($entries); ?> sales)
                    </summary>

                    <a class="export_button month-download-btn" style="display:inline-flex;align-items:center;gap:5px;background:#3498db;color:#fff;
       padding:4px 8px;font-size:12px;border:none;border-radius:4px;cursor:pointer;
       margin-left:10px;" data-month="<?php echo $month; ?>">
                        <i class="fa-solid fa-download"></i>
                        <span class="export_button__text">Export <?php echo $month; ?></span>
                    </a>

                    <div class="table month-sales-table" data-month="<?php echo $month; ?>">
                        <?php 
        $count = 1; 
        $total_dispense = 0; // track total dispense for this month
        ?>
                        <?php foreach ($entries as $entry): ?>
                        <div class="table-row" data-category="<?php echo $entry['category_name']; ?>">
                            <div class="table-data"><?php echo $count++; ?></div>
                            <div class="table-data"><?php echo remove_junk($entry['name']); ?></div>
                            <div class="table-data"><?php echo $entry['category_name'] ?: 'Uncategorized'; ?></div>
                            <div class="table-data"><?php echo $entry['qty']; ?></div>
                            <div class="table-data"><?php echo $entry['stocks']; ?></div>
                            <div class="table-data"><?php echo $entry['issued_by']; ?></div>
                            <div class="table-data"><?php echo $entry['issued_to']; ?></div>
                            <div class="table-data"><?php echo date('m-d-Y', strtotime($entry['date'])); ?></div>
                            <div class="table-data"><?php echo $entry['status']; ?></div>
                        </div>
                        <?php 
            $total_dispense += (int)$entry['qty']; 
        ?>
                        <?php endforeach; ?>

                        <!-- Display total dispense row -->
                        <div class="table-row" style="font-weight:bold;background:#f1f1f1;">
                            <div class="table-data" colspan="3">Total Dispense</div>
                            <div class="table-data"><?php echo $total_dispense; ?></div>
                            <div class="table-data" colspan="4"></div>
                        </div>
                    </div>
                </details>
                <?php endforeach; ?>


            </div>



        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle filter visibility
        const toggleFilterBtn = document.querySelector('.toggle-filter-btn');
        const filterContainer = document.getElementById('sales-filter-container');

        if (toggleFilterBtn && filterContainer) {
            toggleFilterBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                filterContainer.classList.toggle('open');
                this.textContent = filterContainer.classList.contains('open') ? 'Hide Filters' :
                    'Show Filters';
            });
        }

        // Dropdown logic
        const categorySelect = document.querySelector('#sales-filter-container .select');
        if (categorySelect) {
            const categorySelected = categorySelect.querySelector('.selected');
            const categoryOptions = categorySelect.querySelector('.options');
            const categoryArrow = categorySelect.querySelector('.arrow');
            const categorySelectedSpan = categorySelect.querySelector('#sales-category-selected');

            categorySelected.addEventListener('click', function(e) {
                e.stopPropagation();
                categoryOptions.classList.toggle('open');
                categoryArrow.classList.toggle('open');
            });

            categoryOptions.querySelectorAll('.option').forEach(option => {
                option.addEventListener('click', function() {
                    const radioId = this.getAttribute('for');
                    const radio = document.getElementById(radioId);
                    if (radio) {
                        radio.checked = true;
                        categorySelectedSpan.textContent = this.textContent;
                        filterSalesByCategory();
                    }
                    categoryOptions.classList.remove('open');
                    categoryArrow.classList.remove('open');
                });
            });
        }

        // Close dropdown if clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.options').forEach(options => {
                options.classList.remove('open');
            });
            document.querySelectorAll('.arrow').forEach(arrow => {
                arrow.classList.remove('open');
            });
        });

        // Filtering logic
        function filterSalesByCategory() {
            const selectedCategory = document.querySelector('input[name="sales-category-option"]:checked')
                ?.value ||
                '';
            const rows = document.querySelectorAll('#table-content-sales .table-row');

            rows.forEach(row => {
                const rowCategory = row.getAttribute('data-category');
                if (!selectedCategory || rowCategory === selectedCategory) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Set default selected label
        const categorySelectedSpan = document.querySelector('#sales-category-selected');
        if (categorySelectedSpan) {
            categorySelectedSpan.textContent = 'All';
        }

        // ======================
        // EXPORT FUNCTIONALITY
        // ======================

        // Export to CSV
        // ======================
        // EXPORT FUNCTIONALITY
        // ======================

        // Global export (all rows)
        const downloadBtn = document.getElementById('download-btn');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', downloadFilteredData);
        }

        function downloadFilteredData() {
            const visibleRows = document.querySelectorAll('.table-row:not([style*="display: none"])');
            let csvContent = "No.,Generic Name,Quantity,Stocks,Issued to,Issued by,Date,Status\n";

            visibleRows.forEach(row => {
                const columns = row.querySelectorAll('.table-data');
                const rowData = [
                    columns[0].textContent.trim(),
                    columns[1].textContent.trim(),
                    columns[2].textContent.trim(),
                    columns[3].textContent.trim(),
                    columns[4].textContent.trim(),
                    columns[5].textContent.trim(),
                    columns[6].textContent.trim(),
                    columns[7].textContent.trim(),
                    columns[8].textContent.trim()

                ];
                csvContent += rowData.map(data => `"${data.replace(/"/g, '""')}"`).join(',') + '\n';
            });

            downloadCSV(csvContent, 'monthly_records_data.csv');
        }

        // Per-month export
        document.querySelectorAll('.month-download-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const month = this.getAttribute('data-month').trim();
                const table = this.closest('.monthly-group').querySelector(
                    '.month-sales-table');
                const rows = table.querySelectorAll('.table-row');

                let csvContent =
                    "No.,Generic Name,Category,Quantity,Stocks,Issued By,Issued To,Date\n";

                rows.forEach(row => {
                    if (row.style.fontWeight === "bold" || row.textContent.includes(
                            "Total Dispense")) return;

                    const columns = row.querySelectorAll('.table-data');
                    const rowData = [
                        columns[0]?.textContent.trim(),
                        columns[1]?.textContent.trim(),
                        columns[2]?.textContent.trim(),
                        columns[3]?.textContent.trim(),
                        columns[4]?.textContent.trim(),
                        columns[5]?.textContent.trim(),
                        columns[6]?.textContent.trim(),
                        columns[7]?.textContent.trim()
                    ];
                    csvContent += rowData.map(data =>
                        `"${(data || '').replace(/"/g, '""')}"`).join(',') + '\n';
                });

                downloadCSV(csvContent, `records_${month}.csv`);
            });
        });


        // Helper function for downloading
        function downloadCSV(content, filename) {
            const blob = new Blob([content], {
                type: 'text/csv;charset=utf-8;'
            });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }


        // ======================
        // SEARCH FUNCTIONALITY
        // ======================

        const searchInput = document.getElementById('search-bar-sales');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.trim().toLowerCase();
                const rows = document.querySelectorAll('#table-content-sales .table-row');

                rows.forEach(row => {
                    let visible = false;
                    const columns = row.querySelectorAll('.table-data');

                    for (let j = 0; j < columns.length; j++) {
                        const txtValue = columns[j].textContent || columns[j].innerText;
                        if (txtValue.toLowerCase().includes(filter)) {
                            visible = true;
                            break;
                        }
                    }
                    row.style.display = visible ? "" : "none";
                });
            });
        }
    });
    </script>

    <?php include_once('layouts/footer.php'); ?>