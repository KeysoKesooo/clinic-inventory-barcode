<?php
$page_title = 'All Records';
require_once('includes/load.php');
page_require_level(3);
// Default query
$query = "SELECT s.id, p.name, c.name AS category_name, s.qty, s.date, s.product_id
          FROM sales s
          LEFT JOIN products p ON s.product_id = p.id
          LEFT JOIN categories c ON p.categorie_id = c.id
          WHERE 1=1";

// Apply filters if set
if (isset($_GET['filter'])) {
    $start_date = $_GET['start_date'] ?? '';
    $end_date   = $_GET['end_date'] ?? '';

    if (!empty($start_date) && !empty($end_date)) {
        $query .= " AND DATE(s.date) BETWEEN '{$start_date}' AND '{$end_date}'";
    } elseif (!empty($start_date)) {
        $query .= " AND DATE(s.date) >= '{$start_date}'";
    } elseif (!empty($end_date)) {
        $query .= " AND DATE(s.date) <= '{$end_date}'";
    }
}

$query .= " ORDER BY s.date DESC";

$sales = find_by_sql($query);


// Handle the edit sale form submission
if(isset($_POST['edit_sale'])){
    $sale_id = (int)$db->escape($_POST['sale_id']);
    $quantity = (int)$db->escape($_POST['quantity']);

    $date = $db->escape($_POST['date']);
    $s_date = date("Y-m-d", strtotime($date));

    $req_fields = array('quantity','date');
    validate_fields($req_fields);
    
    if(empty($errors)){
        $sale = find_by_id('sales', $sale_id);
        if(!$sale || !isset($sale['product_id'])){
            $session->msg("d","Invalid sale record.");
            redirect('sales.php');
        }
        
        $product = find_by_id('products', $sale['product_id']);
        if(!$product){
            $session->msg("d","Product not found.");
            redirect('sales.php');
        }

        $sql = "UPDATE sales SET";
        $sql .= " qty={$quantity}, price='{$price}', date='{$s_date}'";
        $sql .= " WHERE id ='{$sale_id}'";

        $result = $db->query($sql);
        if($result && $db->affected_rows() === 1){
            update_product_qty($quantity, $product['id']);
            $session->msg('s', "Record updated successfully.");
            redirect('sales.php', false);
        } else {
            $session->msg('d', 'Sorry, failed to update record!');
            redirect('sales.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('sales.php', false);
    }
}

// 🔹 Only load all sales if no filter was applied
if (!isset($_GET['filter'])) {
    $sales = find_all_sale();
}

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
                    <button class="toggle-filter-btn" data-target="custom-date-filter-container">Select
                        Date</button>

                    <!-- Date Filter Container -->
                    <div id="custom-date-filter-container" class="filter-container">
                        <form method="GET" action="" id="date-form">
                            <div class="filter-group">
                                <label for="start_date">Start Date:</label>
                                <input type="date" id="start_date" name="start_date" class="date-input"
                                    value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : '' ?>" />
                            </div>
                            <div class="filter-group">
                                <label for="end_date">End Date:</label>
                                <input type="date" id="end_date" name="end_date" class="date-input"
                                    value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : '' ?>" />
                            </div>
                            <button type="submit" name="filter">Filter</button>
                            <a href="sales.php" class="btn-clear">Clear Filter</a>
                        </form>
                    </div>
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

                <a href="add_sale.php" class="add_button">
                    <svg aria-hidden="true" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg" class="add_button__icon">
                        <path stroke-width="2" stroke="#ffffff"
                            d="M13.5 3H12H8C6.34315 3 5 4.34315 5 6V18C5 19.6569 6.34315 21 8 21H11M13.5 3L19 8.625M13.5 3V7.625C13.5 8.17728 13.9477 8.625 14.5 8.625H19M19 8.625V11.8125"
                            stroke-linejoin="round" stroke-linecap="round"></path>
                        <path stroke-linejoin="round" stroke-linecap="round" stroke-width="2" stroke="#ffffff"
                            d="M17 15V18M17 21V18M17 18H14M17 18H20"></path>
                    </svg>
                    <span class="add_button__text">Add Record</span>
                </a>
            </div>

            <!-- Records management table -->
            <div class="table">
                <div class="table-header">
                    <div class="header__item">No.</div>
                    <div class="header__item">Product Name</div>
                    <div class="header__item">Category</div>
                    <div class="header__item">Quantity</div>
                    <div class="header__item">Date</div>
                    <div class="header__item">Actions</div>
                </div>

                <div class="table-content" id="table-content-sales">
                    <?php foreach($sales as $sale): ?>
                    <div class="table-row" data-id="<?= (int)$sale['id'] ?>"
                        data-category="<?= isset($sale['category_name']) ? htmlspecialchars($sale['category_name']) : 'Uncategorized' ?>">
                        <div class="table-data"><?= count_id() ?></div>
                        <div class="table-data"><?= remove_junk($sale['name']) ?></div>
                        <div class="table-data">
                            <?= isset($sale['category_name']) ? remove_junk($sale['category_name']) : 'Uncategorized' ?>
                        </div>
                        <div class="table-data"><?= (int)$sale['qty'] ?></div>
                        <div class="table-data"><?php echo date('m-d-Y', strtotime($sale['date'])); ?></div>
                        <div class="table-data">
                            <button type="button" class="btn btn-warning edit-btn" data-id="<?= (int)$sale['id'] ?>"
                                data-name="<?= remove_junk($sale['name']) ?>" data-qty="<?= (int)$sale['qty'] ?>"
                                data-date="<?= $sale['date'] ?>">
                                <i class="glyphicon glyphicon-pencil"></i>
                            </button>
                            <a href="delete_sale.php?id=<?= (int)$sale['id'] ?>" class="btn btn-danger"
                                onclick="return confirmDelete(event)">
                                <i class="glyphicon glyphicon-trash"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- Edit Sale Modal -->
        <div class="modal fade" id="editSaleModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="container">
                    <form method="post" action="sales.php" class="form_area">
                        <span class="close-modal" data-dismiss="modal">&times;</span>
                        <div class="title">Edit Sale</div>
                        <input type="hidden" name="sale_id" id="edit-sale-id">

                        <div class="form_group">
                            <label>Product Name</label>
                            <div class="form_style" id="edit-sale-name-display"
                                style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;"></div>
                            <input type="hidden" id="edit-sale-name">
                        </div>

                        <div class="form_group">
                            <label for="edit-sale-qty">Quantity</label>
                            <input class="form_style" type="number" name="quantity" id="edit-sale-qty" required>
                        </div>

                        <div class="form_group">
                            <label for="edit-sale-date">Date</label>
                            <input class="form_style datepicker" type="date" name="date" id="edit-sale-date" required>
                        </div>

                        <button type="submit" name="edit_sale" class="form_btn">Update Sale</button>
                    </form>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ================================
            // Handle multiple filter toggle buttons
            // ================================
            const toggleButtons = document.querySelectorAll('.toggle-filter-btn');

            toggleButtons.forEach(btn => {
                const targetId = btn.getAttribute('data-target');
                const targetContainer = document.getElementById(targetId);

                if (targetContainer) {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        targetContainer.classList.toggle('open');

                        // Change button text depending on open state
                        if (targetId === 'sales-filter-container') {
                            this.textContent = targetContainer.classList.contains('open') ?
                                'Hide Filters' :
                                'Show Filters';
                        } else if (targetId === 'custom-date-filter-container') {
                            this.textContent = targetContainer.classList.contains('open') ?
                                'Hide Date' :
                                'Select Date';
                        }
                    });
                }
            });

            // ================================
            // Category dropdown logic
            // ================================
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

            // Set default category label
            const categorySelectedSpan = document.querySelector('#sales-category-selected');
            if (categorySelectedSpan) {
                categorySelectedSpan.textContent = 'All';
            }



            // ======================
            // EDIT MODAL FUNCTIONALITY
            // ======================

            // Edit button click handler
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('edit-sale-id').value = btn.dataset.id;
                    document.getElementById('edit-sale-name-display').textContent = btn.dataset
                        .name;
                    document.getElementById('edit-sale-name').value = btn.dataset.name;
                    document.getElementById('edit-sale-qty').value = btn.dataset.qty;
                    document.getElementById('edit-sale-price').value = btn.dataset.price;
                    document.getElementById('edit-sale-total').value = btn.dataset.total;
                    document.getElementById('edit-sale-date').value = btn.dataset.date;

                    $('#editSaleModal').modal('show');
                });
            });

            // Calculate total when quantity or price changes
            const qtyInput = document.getElementById('edit-sale-qty');
            const priceInput = document.getElementById('edit-sale-price');

            if (qtyInput && priceInput) {
                qtyInput.addEventListener('input', calculateTotal);
                priceInput.addEventListener('input', calculateTotal);
            }

            function calculateTotal() {
                const qty = parseFloat(document.getElementById('edit-sale-qty').value) || 0;
                const price = parseFloat(document.getElementById('edit-sale-price').value) || 0;
                document.getElementById('edit-sale-total').value = (qty * price).toFixed(2);
            }

            // ======================
            // EXPORT FUNCTIONALITY
            // ======================

            // Export to CSV
            const downloadBtn = document.getElementById('download-btn');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', downloadFilteredData);
            }

            function downloadFilteredData() {
                const visibleRows = document.querySelectorAll('.table-row:not([style*="display: none"])');
                let csvContent = "No.,Product Name,Category,Quantity,Date\n";

                visibleRows.forEach(row => {
                    const columns = row.querySelectorAll('.table-data');
                    const rowData = [
                        columns[0].textContent.trim(),
                        columns[1].textContent.trim(),
                        columns[2].textContent.trim(),
                        columns[3].textContent.trim(),
                        columns[4].textContent.trim()
                    ];

                    csvContent += rowData.map(data => `"${data.replace(/"/g, '""')}"`).join(',') + '\n';
                });

                const blob = new Blob([csvContent], {
                    type: 'text/csv;charset=utf-8;'
                });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', 'Record_data.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            // ======================
            // SORTING FUNCTIONALITY
            // ======================

            const headers = document.querySelectorAll('.header__item');
            const tableContent = document.getElementById('table-content-sales');

            if (headers && tableContent) {
                headers.forEach((header, index) => {
                    header.addEventListener('click', () => {
                        const rows = Array.from(tableContent.querySelectorAll('.table-row'));
                        const isAscending = header.classList.toggle('asc');

                        rows.sort((a, b) => {
                            const cellA = a.children[index].textContent.trim()
                                .toLowerCase();
                            const cellB = b.children[index].textContent.trim()
                                .toLowerCase();

                            const numberA = parseFloat(cellA.replace(/[^0-9.-]+/g, ""));
                            const numberB = parseFloat(cellB.replace(/[^0-9.-]+/g, ""));

                            if (!isNaN(numberA) && !isNaN(numberB)) {
                                return isAscending ? numberA - numberB : numberB -
                                    numberA;
                            } else {
                                return isAscending ? cellA.localeCompare(cellB) : cellB
                                    .localeCompare(cellA);
                            }
                        });

                        rows.forEach(row => tableContent.appendChild(row));
                    });
                });
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

            // ======================
            // DELETE CONFIRMATION
            // ======================

            function confirmDelete(event) {
                if (!confirm('Are you sure you want to delete this sale?')) {
                    event.preventDefault();
                }
            }
        });
        </script>

        <?php include_once('layouts/footer.php'); ?>