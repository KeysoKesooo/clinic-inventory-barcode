<?php
$page_title = 'Daily Records';
require_once('includes/load.php');
page_require_level(3);

$year = date('Y');
$month = date('m');
$sales = dailySales($year, $month);
?>

<?php include_once('layouts/header.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
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
                    <input type="text" id="search-bar-sales" class="search-bar" placeholder="Search sales...">
                </div>

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



                <a class="export_button" id="download-btn">
                    <i class="fa-solid fa-download"></i>
                    <span class="export_button__text">Export</span>
                </a>


            </div>

            <div class="table">
                <div class="table-header">
                    <div class="header__item">No.</div>
                    <div class="header__item">Product Name</div>
                    <div class="header__item">Category</div>
                    <div class="header__item">Quantity</div>
                    <div class="header__item">Date</div>
                </div>

                <?php foreach($sales as $sale): ?>
                <div class="table-row" data-id="<?php echo (int)$sale['id']; ?>"
                    data-category="<?php echo isset($sale['category_name']) ? htmlspecialchars($sale['category_name']) : 'Uncategorized'; ?>">

                    <div class="table-data"><?php echo count_id(); ?></div>
                    <div class="table-data"><?php echo remove_junk($sale['name']); ?></div>
                    <div class="table-data"><?php echo remove_junk($sale['category_name']); ?></div> <!-- ✅ ADD THIS -->
                    <div class="table-data"><?php echo (int)$sale['qty']; ?></div>
                    <div class="table-data"><?php echo $sale['date']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // ========== FILTER DROPDOWN LOGIC ==========
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

        // ========== FILTER FUNCTION ==========
        function filterSalesByCategory() {
            const selectedCategory = document.querySelector('input[name="sales-category-option"]:checked')
                ?.value || '';
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

        // Set default label text
        const categorySelectedSpan = document.querySelector('#sales-category-selected');
        if (categorySelectedSpan) {
            categorySelectedSpan.textContent = 'All';
        }

        // Close dropdown if clicked outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.options').forEach(options => options.classList.remove(
                'open'));
            document.querySelectorAll('.arrow').forEach(arrow => arrow.classList.remove('open'));
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-bar-sales');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.trim().toLowerCase();
                const rows = document.querySelectorAll('#table-content-sales .table-row');

                rows.forEach(row => {
                    let visible = false;
                    const columns = row.querySelectorAll('.table-data');

                    for (let i = 0; i < columns.length; i++) {
                        const txtValue = columns[i].textContent || columns[i].innerText;
                        if (txtValue.toLowerCase().includes(filter)) {
                            visible = true;
                            break;
                        }
                    }
                    row.style.display = visible ? '' : 'none';
                });
            });
        }

        const downloadBtn = document.getElementById('download-btn');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', function() {
                const visibleRows = document.querySelectorAll(
                    '.table-row:not([style*="display: none"])');
                let csvContent = "No.,Product Name,Quantity,Total,Date\n";

                visibleRows.forEach(row => {
                    const columns = row.querySelectorAll('.table-data');
                    const rowData = Array.from(columns).map(col =>
                        `"${col.textContent.trim().replace(/"/g, '""')}"`);
                    csvContent += rowData.join(',') + '\n';
                });

                const blob = new Blob([csvContent], {
                    type: 'text/csv;charset=utf-8;'
                });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', 'daily_sales.csv');
                link.click();
            });
        }
    });
    </script>

    <?php include_once('layouts/footer.php'); ?>