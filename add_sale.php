<?php
  $page_title = 'Add Record';
  require_once('includes/load.php');
  page_require_level(3);

  if (isset($_POST['add_sale'])) {
    $req_fields = ['s_id', 'quantity', 'date'];
    validate_fields($req_fields);

    if (empty($errors)) {
      $p_id    = (int)$db->escape($_POST['s_id']);
      $s_qty   = (int)$db->escape($_POST['quantity']);
      $s_date  = $db->escape($_POST['date']);

      $sql = "INSERT INTO sales (product_id, qty, date) 
              VALUES ('{$p_id}', '{$s_qty}', '{$s_date}')";

      if ($db->query($sql)) {
        update_product_qty($s_qty, $p_id);
        $session->msg('s', "Record added.");
      } else {
        $session->msg('d', 'Failed to add record.');
      }
    } else {
      $session->msg("d", $errors);
    }

    redirect('add_sale.php', false);
  }

  $all_products = join_product_table();
  $all_categories = find_all('categories');
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

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>

<div id="manageSales" class="content-section active">
    <div class="sales-container">
        <div class="action-buttons-container">
            <div class="search-bar-container">
                <input type="text" id="search-bar-products" class="search-bar" placeholder="Search product...">
            </div>
        </div>

        <!-- Sales management table -->
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
                <?php foreach ($all_products as $index => $product): ?>
                <form method="post" action="add_sale.php" class="table-row"
                    data-category="<?php echo $product['categorie_id']; ?>">
                    <div class="table-data"><?php echo $index + 1; ?></div>
                    <div class="table-data pname">
                        <?php echo remove_junk($product['name']); ?>
                        <input type="hidden" name="s_id" value="<?php echo (int)$product['id']; ?>">
                    </div>
                    <div class="table-data">
                        <?php echo remove_junk($product['categorie']); ?>
                    </div>
                    <div class="table-data" style="min-width:120px;">
                        <input type="number" class="form-control" name="quantity" value="1" min="1" style="width:100%;"
                            onchange="updateTotal(this)">
                    </div>
                    <div class="table-data" style="min-width:140px;">
                        <input type="date" class="form-control" name="date" value="<?php echo date('Y-m-d'); ?>"
                            style="width:100%;">
                    </div>
                    <div class="table-data">
                        <button type="submit" name="add_sale" class="btn btn-success">Add Record</button>
                    </div>
                </form>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
// ======================
// SEARCH FUNCTIONALITY
// ======================

const searchInput = document.getElementById('search-bar-products');
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

// Filter Products
function filterProducts() {
    const searchTerm = document.getElementById('search-bar-products').value.trim().toLowerCase();
    const priceFilter = document.querySelector('input[name="price-option"]:checked').value;
    const categoryFilter = document.querySelector('input[name="category-option"]:checked').value;

    document.querySelectorAll("#table-content-sales .table-row").forEach(row => {
        const name = row.querySelector(".pname").textContent.toLowerCase();
        const price = parseFloat(row.dataset.price);
        const category = row.dataset.category;

        // Check search term
        const matchesSearch = name.includes(searchTerm);

        // Check price filter
        let matchesPrice = true;
        switch (priceFilter) {
            case 'low':
                matchesPrice = price < 1000;
                break;
            case 'medium':
                matchesPrice = price >= 1000 && price <= 5000;
                break;
            case 'high':
                matchesPrice = price > 5000;
                break;
        }

        // Check category filter
        const matchesCategory = !categoryFilter || category === categoryFilter;

        row.style.display = (matchesSearch && matchesPrice && matchesCategory) ? "" : "none";
    });
}

// Update filter labels when selection changes
document.querySelectorAll('input[name="price-option"], input[name="category-option"]').forEach(input => {
    input.addEventListener('change', function() {
        if (this.name === 'price-option') {
            document.getElementById('price-range-selected').textContent =
                document.querySelector('label[for="' + this.id + '"]').textContent;
        } else {
            document.getElementById('category-selected').textContent =
                document.querySelector('label[for="' + this.id + '"]').textContent;
        }
        filterProducts();
    });
});

// Update total on quantity change
function updateTotal(input) {
    const row = input.closest('.table-row');
    const priceInput = row.querySelector('[name="price"]');
    const totalInput = row.querySelector('[name="total"]');

    // Remove peso sign and commas for calculation
    const price = parseFloat(priceInput.value.replace(/[^\d.]/g, ''));
    const qty = parseInt(input.value);

    if (!isNaN(price) && !isNaN(qty)) {
        const total = (price * qty).toFixed(2);
        // Format with commas and add back peso sign in display
        totalInput.value = total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
}

// Toggle filter container
document.querySelector('.toggle-filter-btn').addEventListener('click', function() {
    const filterContainer = document.getElementById(this.getAttribute('data-target'));
    filterContainer.classList.toggle('open');
    this.textContent = filterContainer.classList.contains('open') ? 'Hide Filters' : 'Show Filters';
});

// Export functionality
document.getElementById('download-btn-sales').addEventListener('click', function() {
    // Implement your export logic here
    alert('Export functionality will be implemented here');
});
</script>

<?php include_once('layouts/footer.php'); ?>