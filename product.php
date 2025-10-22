<?php
$page_title = 'All Product';
require_once('includes/load.php');
page_require_level(2);

$products = join_product_table();
$all_categories = find_all('categories');
$all_photo = find_all('media');


// === EDIT PRODUCT LOGIC ===
if (isset($_POST['edit_product'])) {
    $req_fields = array('product-title', 'product-categorie', 'product-quantity', 'product-box', 'product-dosage', 'product-expiration-date');
    validate_fields($req_fields);

    if (empty($errors)) {
        $id        = (int)$_POST['id'];
        $p_name    = remove_junk($db->escape($_POST['product-title']));
        $p_cat     = (int)$_POST['product-categorie'];
        $p_qty     = remove_junk($db->escape($_POST['product-quantity']));
        $p_box     = remove_junk($db->escape($_POST['product-box']));
        $p_dosage  = remove_junk($db->escape($_POST['product-dosage']));
        $p_desc    = remove_junk($db->escape($_POST['product-description']));
        $p_exp     = remove_junk($db->escape($_POST['product-expiration-date']));

        // === Expiration check ===
        if (strtotime($p_exp) < strtotime(date("Y-m-d"))) {
            $session->msg('d', "Invalid! You cannot update '{$p_name}' with an expired date ({$p_exp}).");
            redirect("product.php?id={$id}", false);
        }

        // Fetch current product to keep existing photo if no new file is uploaded
        $sql = "SELECT product_photo FROM products WHERE id='{$id}' LIMIT 1";
        $result = $db->query($sql);
        $current_product = $db->fetch_assoc($result);
        $product_photo = $current_product['product_photo'];

        // Handle new file upload
        if (!empty($_FILES['product-photo']['name'])) {
            $file_name = basename($_FILES['product-photo']['name']);
            $target_dir = "uploads/products/";
            $target_file = $target_dir . $file_name;

            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if (move_uploaded_file($_FILES['product-photo']['tmp_name'], $target_file)) {
                $product_photo = $file_name;
            }
        }

        $query  = "UPDATE products SET ";
        $query .= "name='{$p_name}', quantity='{$p_qty}', dosage='{$p_dosage}', description='{$p_desc}', pcs_per_box='{$p_box}', ";
        $query .= "categorie_id='{$p_cat}', product_photo='{$product_photo}', expiration_date='{$p_exp}' ";
        $query .= "WHERE id='{$id}'";

        if ($db->query($query) && $db->affected_rows() === 1) {
            $session->msg('s', "Medicine updated");
            redirect('product.php', false);
        } else {
            $session->msg('d', 'Sorry, failed to update!');
            redirect('product.php?id=' . $id, false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('product.php?id=' . $id, false);
    }
}

// === ADD PRODUCT LOGIC ===
if (isset($_POST['add_product'])) {
    $req_fields = array('product-title', 'product-categorie', 'product-quantity', 'product-box', 'product-dosage', 'product-expiration-date');
    validate_fields($req_fields);

    if (empty($errors)) {
        $p_name   = remove_junk($db->escape($_POST['product-title']));
        $p_cat    = remove_junk($db->escape($_POST['product-categorie']));
        $p_qty    = remove_junk($db->escape($_POST['product-quantity']));
        $p_box    = remove_junk($db->escape($_POST['product-box']));
        $p_dosage = remove_junk($db->escape($_POST['product-dosage']));
        $p_desc   = remove_junk($db->escape($_POST['product-description']));
        $p_exp    = remove_junk($db->escape($_POST['product-expiration-date']));
        $date     = make_date();

        // === Expiration check ===
        if (strtotime($p_exp) < strtotime(date("Y-m-d"))) {
            $session->msg('d', "Invalid! The product '{$p_name}' is already expired ({$p_exp}).");
            redirect('product.php', false);
        }

        // Handle File Upload
        $photo_name = '';
        if (!empty($_FILES['product-photo']['name'])) {
            $upload_dir = 'uploads/products/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = time() . '_' . basename($_FILES['product-photo']['name']);
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['product-photo']['tmp_name'], $file_path)) {
                $photo_name = $file_name;
            }
        }

        $query  = "INSERT INTO products (name, quantity, pcs_per_box, dosage, description, categorie_id, product_photo, expiration_date, date) ";
        $query .= "VALUES ('{$p_name}', '{$p_qty}', '{$p_dosage}', '{$p_desc}', '{$p_cat}', '{$photo_name}', '{$p_exp}', '{$date}')";

        if ($db->query($query)) {
            $session->msg('s', "Medicine added");
            redirect('product.php', false);
        } else {
            $session->msg('d', 'Sorry, failed to add!');
            redirect('product.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('product.php', false);
    }
}

// === IMPORT PRODUCTS LOGIC ===
if (isset($_POST['import_products'])) {
    $csv_file = $_FILES['csv_file']['tmp_name'];

    if ($_FILES['csv_file']['error'] > 0) {
        $session->msg('d', 'Error uploading file.');
        redirect('product.php', false);
    }

    $success_count = 0;
    $error_count = 0;
    $errors = [];

    $db->query("START TRANSACTION");

    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        // Skip header row
        fgetcsv($handle);

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Require at least 6 columns
            if (!isset($data[0], $data[1], $data[2], $data[3], $data[4], $data[5])) {
                $error_count++;
                $errors[] = "Missing required fields in CSV row";
                continue;
            }

            $p_name        = remove_junk($db->escape($data[0]));
            $category_name = remove_junk($db->escape($data[1]));
            $p_qty         = (int)$db->escape($data[2]);
            $p_dosage      = remove_junk($db->escape($data[3]));
            $p_desc        = remove_junk($db->escape($data[4]));
            $p_exp         = remove_junk($db->escape($data[5]));
            $date          = make_date();

            // === Expiration check ===
            if (strtotime($p_exp) < strtotime(date("Y-m-d"))) {
                $error_count++;
                $errors[] = "Medicine '{$p_name}' has expired date ({$p_exp}). Skipped.";
                continue; // Skip importing this row
            }

            // Get category ID by name
            $cat_query = "SELECT id FROM categories WHERE name='{$category_name}' LIMIT 1";
            $cat_res = $db->query($cat_query);

            if ($db->num_rows($cat_res) == 0) {
                $error_count++;
                $errors[] = "Category '{$category_name}' does not exist for medicine '{$p_name}'";
                continue;
            }

            $cat_row = $db->fetch_assoc($cat_res);
            $p_cat = (int)$cat_row['id'];

            // Insert product with expiration_date
            $query  = "INSERT INTO products (name, categorie_id, quantity, dosage, description, expiration_date, date) ";
            $query .= "VALUES ('{$p_name}', '{$p_cat}', '{$p_qty}', '{$p_dosage}', '{$p_desc}', '{$p_exp}', '{$date}')";

            if ($db->query($query)) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Failed to import '{$p_name}'. Database error: " . $db->getLastError();
            }
        }

        fclose($handle);

        if ($error_count == 0) {
            $db->query("COMMIT");
            $session->msg('s', "Successfully imported {$success_count} medicines.");
        } else {
            $db->query("ROLLBACK");
            $error_msg = "Import completed with issues:<br>";
            $error_msg .= "- Successfully processed: {$success_count}<br>";
            $error_msg .= "- Errors encountered: {$error_count}<br>";
            $error_msg .= "First 5 errors:<br>" . implode("<br>", array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $error_msg .= "<br>...and " . (count($errors) - 5) . " more";
            }
            $session->msg('d', $error_msg);
        }
    } else {
        $session->msg('d', 'Failed to open the CSV file.');
    }

    redirect('product.php', false);
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

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg(isset($msg) ? $msg : ''); ?>
    </div>
</div>

<div id="manageProducts" class="content-section active">
    <div class="product-container">
        <div class="action-buttons-container">
            <div class="search-bar-container">
                <input type="text" id="search-bar-products" class="search-bar" placeholder="Search medicine...">
            </div>

            <a class="export_button" id="download-btn-products">
                <i class="fa-solid fa-download"></i>
                <span class="export_button__text">Export</span>
            </a>

            <a class="import_button" id="openPopup-products">
                <i class="fa-solid fa-upload"></i>
                <span class="import_button__text">Import</span>
            </a>

            <!-- Import Popup Form -->
            <div id="popupForm-products" class="popup-form">
                <div class="editpopup_form_area">
                    <span id="closePopup-products" class="close-btn">&times;</span>
                    <form method="post" action="product.php" enctype="multipart/form-data">
                        <div class="editpopup_form_group">
                            <label class="editpopup_sub_title" for="csv_file">Choose CSV File</label>
                            <input type="file" name="csv_file" class="editpopup_form_style" required>
                        </div>
                        <div>
                            <button type="submit" name="import_products" class="editpopup_btn"
                                style="margin-left: 180px">Import Medicines</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="filter-wrapper">
                <button class="toggle-filter-btn" data-target="product-filter-container">Show Filters</button>
                <div id="product-filter-container" class="filter-container">
                    <!-- Category Filter -->
                    <label for="product-category-filter">Category:</label>
                    <div class="select">
                        <div class="selected" data-default="All">
                            <span id="product-category-selected">All</span>
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                                <path
                                    d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z">
                                </path>
                            </svg>
                        </div>
                        <div class="options">
                            <div title="all">
                                <input id="product-category-all" name="product-category-option" type="radio" value=""
                                    checked />
                                <label class="option" for="product-category-all">All</label>
                            </div>
                            <?php foreach ($all_categories as $category): ?>
                            <div title="<?php echo htmlspecialchars($category['name']); ?>">
                                <input id="product-category-<?php echo htmlspecialchars($category['id']); ?>"
                                    name="product-category-option" type="radio"
                                    value="<?php echo htmlspecialchars($category['id']); ?>" />
                                <label class="option"
                                    for="product-category-<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars(ucwords($category['name'])); ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Stock Status Filter -->
                    <label for="product-stock-filter">Stock Status:</label>
                    <div class="select">
                        <div class="selected" data-default="All">
                            <span id="product-stock-selected">All</span>
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                                <path
                                    d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z">
                                </path>
                            </svg>
                        </div>
                        <div class="options">
                            <div title="in-stock">
                                <input id="product-stock-in" name="product-stock-option" type="radio" value="1" />
                                <label class="option" for="product-stock-in">In Stock</label>
                            </div>
                            <div title="out-of-stock">
                                <input id="product-stock-out" name="product-stock-option" type="radio" value="0" />
                                <label class="option" for="product-stock-out">Out of Stock</label>
                            </div>
                            <div title="all">
                                <input id="product-stock-all" name="product-stock-option" type="radio" value=""
                                    checked />
                                <label class="option" for="product-stock-all">All</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <a class="add_button" href="#" data-toggle="modal" data-target="#addProductModal">
                <svg aria-hidden="true" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg" class="add_button__icon">
                    <path stroke-width="2" stroke="#ffffff"
                        d="M13.5 3H12H8C6.34315 3 5 4.34315 5 6V18C5 19.6569 6.34315 21 8 21H11M13.5 3L19 8.625M13.5 3V7.625C13.5 8.17728 13.9477 8.625 14.5 8.625H19M19 8.625V11.8125"
                        stroke-linejoin="round" stroke-linecap="round"></path>
                    <path stroke-linejoin="round" stroke-linecap="round" stroke-width="2" stroke="#ffffff"
                        d="M17 15V18M17 21V18M17 18H14M17 18H20"></path>
                </svg>
                <span class="add_button__text">Add Medicine</span>
            </a>
        </div>

        <!-- Product management table -->
        <div class="table">
            <div class="table-header">
                <div class="header__item">No.</div>
                <div class="header__item">Photo</div>
                <div class="header__item">Generic Name</div>
                <div class="header__item">Dosage</div>
                <div class="header__item">Description</div>
                <div class="header__item">Categories</div>
                <div class="header__item">In-Stock</div>
                <div class="header__item">Expiration Date</div>
                <div class="header__item">Date Added</div>
                <div class="header__item">Actions</div>
            </div>

            <div class="table-content" id="table-content-products">
                <?php foreach($products as $product): ?>
                <div class="table-row" data-category="<?php echo htmlspecialchars($product['categorie_id']); ?>"
                    data-stock="<?php echo ($product['quantity'] > 0) ? 1 : 0; ?>"
                    data-id="<?php echo $product['id']; ?>"
                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                    data-dosage="<?php echo htmlspecialchars($product['dosage']); ?>"
                    data-description="<?php echo htmlspecialchars($product['description']); ?>"
                    data-category-id="<?php echo $product['categorie_id']; ?>"
                    data-quantity="<?php echo $product['quantity']; ?>"
                    data-media-id="<?php echo $product['product_photo']; ?>">

                    <div class="table-data"><?php echo count_id(); ?></div>
                    <div class="table-data">
                        <?php if(empty($product['product_photo']) || $product['product_photo'] === '0'): ?>
                        <img class="img-avatar img-circle" src="uploads/products/no_image.png" alt="">
                        <?php else: ?>
                        <img class="img-avatar img-circle"
                            src="uploads/products/<?php echo $product['product_photo']; ?>" alt="">
                        <?php endif; ?>
                    </div>
                    <div class="table-data"><?php echo remove_junk($product['name']); ?></div>
                    <div class="table-data"><?php echo remove_junk($product['dosage']); ?></div>
                    <div class="table-data"><?php echo remove_junk($product['description']); ?></div>
                    <div class="table-data"><?php echo remove_junk($product['categorie']); ?></div>
                    <div class="table-data">
                        <?php if($product['quantity'] > 0): ?>
                        <span class="label label-success"><?php echo remove_junk($product['quantity']); ?></span>
                        <?php else: ?>
                        <span class="label label-danger">Out of stock</span>
                        <?php endif; ?>
                    </div>
                    <div class="table-data"><?php echo date('d-m-Y', strtotime($product['expiration_date'])); ?></div>
                    <div class="table-data"><?php echo date('m-d-Y', strtotime($product['date'])); ?></div>
                    <div class="table-data">
                        <button type="button" class="btn btn-warning edit-btn" data-id="<?php echo $product['id']; ?>">
                            <i class="glyphicon glyphicon-pencil"></i>
                        </button>

                        <a href="delete_product.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-danger"
                            onclick="return confirmDelete(event)">
                            <i class="glyphicon glyphicon-trash"></i>
                        </a>

                        <!-- Barcode Button -->
                        <button class="btn btn-info generate-barcode-btn"
                            data-product-id="<?php echo htmlspecialchars($product['id']); ?>"
                            data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                            <i class="glyphicon glyphicon-barcode"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>


    </div>
</div>

<!-- Barcode Modal -->
<div id="barcodeModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Medicine Barcode</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body text-center">
                <!-- Live preview -->
                <svg id="barcode"></svg>
                <p id="barcodeProductName"></p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" onclick="printBarcode()">Print</button>
                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<!-- Hidden printable area -->
<div id="printArea" style="display: none;">
    <div style="text-align: center; padding: 20px;">
        <svg id="printBarcode"></svg>
        <p id="printProductName" style="font-size: 18px; margin-top: 10px;"></p>
    </div>
</div>


<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="container">
            <form method="post" action="product.php" class="form_area" enctype="multipart/form-data">
                <span class="close-modal" data-dismiss="modal">&times;</span>
                <div class="title">Add New</div>

                <div class="form_group">
                    <label for="product-title">Generic Name</label>
                    <input class="form_style" type="text" name="product-title" id="product-title"
                        placeholder="Generic Name" required>
                </div>

                <div class="form_group">
                    <label for="product-categorie">Category</label>
                    <select class="form_style" name="product-categorie" id="product-categorie" required>
                        <option value="">Select Product Category</option>
                        <?php foreach ($all_categories as $cat): ?>
                        <option value="<?php echo (int)$cat['id']; ?>"><?php echo $cat['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form_group">
                    <label for="product-photo">Generic Photo</label>
                    <input class="form_style" type="file" name="product-photo" id="product-photo" accept="image/*">
                </div>

                <div class="form_group">
                    <label for="product-box">Units per box</label>
                    <input class="form_style" type="number" name="product-box" id="product-box"
                        placeholder="How many pcs per box" min="1" required>
                </div>

                <div class="form_group">
                    <label for="product-quantity">Quantity</label>
                    <input class="form_style" type="number" name="product-quantity" id="product-quantity"
                        placeholder="Product Quantity" min="1" required>
                </div>


                <div class="form_group">
                    <label for="product-dosage">Dosage</label>
                    <input class="form_style" type="text" name="product-dosage" id="product-dosage"
                        placeholder="e.g. 500mg, 10ml" required>
                </div>

                <div class="form_group">
                    <label for="product-expiration">Expiration Date</label>
                    <input class="form_style" type="date" name="product-expiration-date" id="product-expiration"
                        placeholder="e.g. 2023-12-31" required>
                </div>

                <div class="form_group">
                    <label for="product-description">Description</label>
                    <textarea class="form_style" name="product-description" id="product-description"
                        placeholder="Enter product description" rows="3"></textarea>
                </div>

                <button type="submit" name="add_product" class="form_btn">Add Medicine</button>
            </form>
        </div>
    </div>
</div>



<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="container">
            <!-- IMPORTANT: enctype for file uploads -->
            <form method="post" action="product.php" class="form_area" enctype="multipart/form-data">
                <span class="close-modal" data-dismiss="modal">&times;</span>
                <div class="title">Edit</div>
                <input type="hidden" name="id" id="edit-product-id">

                <div class="form_group">
                    <label for="edit-product-title">Generic Name</label>
                    <input class="form_style" type="text" name="product-title" id="edit-product-title" required>
                </div>

                <div class="form_group">
                    <label for="edit-product-categorie">Category</label>
                    <select class="form_style" name="product-categorie" id="edit-product-categorie" required>
                        <option value="">Select Medicine Category</option>
                        <?php foreach ($all_categories as $cat): ?>
                        <option value="<?php echo (int)$cat['id']; ?>"><?php echo $cat['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form_group">
                    <label for="edit-product-photo">Generic Photo</label>
                    <input class="form_style" type="file" name="product-photo" id="edit-product-photo" accept="image/*">
                    <!-- Optional: show current filename -->
                    <small id="current-photo-name" class="text-muted"></small>
                </div>

                <div class="form_group">
                    <label for="product-box">Units per box</label>
                    <input class="form_style" type="number" name="product-box" id="edit-product-box"
                        placeholder="How many pcs per box" min="1" required>
                </div>

                <div class="form_group">
                    <label for="edit-product-quantity">Quantity</label>
                    <input class="form_style" type="number" name="product-quantity" id="edit-product-quantity" required>
                </div>

                <div class="form_group">
                    <label for="edit-product-dosage">Dosage</label>
                    <input class="form_style" type="text" name="product-dosage" id="edit-product-dosage"
                        placeholder="e.g., 500mg" required>
                </div>

                <div class="form_group">
                    <label for="edit-product-expiration">Expiration Date</label>
                    <input class="form_style" type="date" name="product-expiration-date" id="edit-product-expiration"
                        placeholder="e.g., 2023-12-31" required>
                </div>


                <div class="form_group">
                    <label for="edit-product-description">Description</label>
                    <textarea class="form_style" name="product-description" id="edit-product-description" rows="3"
                        placeholder="Product description..."></textarea>
                </div>

                <button type="submit" name="edit_product" class="form_btn">Update Medicine</button>
            </form>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Edit Product Button Handler
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const row = btn.closest('.table-row');

        document.getElementById('edit-product-id').value = row.dataset.id;
        document.getElementById('edit-product-title').value = row.dataset.name;
        document.getElementById('edit-product-quantity').value = row.dataset.quantity;
        document.getElementById('edit-product-dosage').value = row.dataset.dosage;
        document.getElementById('edit-product-description').value = row.dataset.description;

        // Set Category select
        const catSelect = document.getElementById('edit-product-categorie');
        Array.from(catSelect.options).forEach(opt => {
            opt.selected = opt.value === row.dataset.categoryId;
        });

        // Show current photo name
        document.getElementById('current-photo-name').textContent = row.dataset.photo ||
            "No photo uploaded";

        // ✅ Finally show modal
        $('#editProductModal').modal('show');
    });
});



// Rest of your existing JavaScript code for filtering, searching, etc.
document.addEventListener('DOMContentLoaded', function() {
    // Add click event to download button
    document.getElementById('download-btn-products').addEventListener('click', downloadFilteredProducts);

    function downloadFilteredProducts() {
        // Get all visible rows (filtered rows)
        const visibleRows = document.querySelectorAll(
            '#table-content-products .table-row:not([style*="display: none"])');

        // Prepare CSV content
        let csvContent = "No.,Generic Name,Category,In-Stock,Dosage,Description,Date Added\n";

        visibleRows.forEach(row => {
            const columns = row.querySelectorAll('.table-data');
            const rowData = [
                columns[0].textContent.trim(), // No.
                columns[2].textContent.trim(), // Product Title
                columns[3].textContent.trim(), // Category
                columns[4].textContent.trim(), // In-Stock
                columns[5].textContent.trim(), // Dosage
                columns[6].textContent.trim(), // Description
                columns[7].textContent.trim() // Date Added
            ];

            // Escape quotes and add to CSV
            csvContent += rowData.map(data => `"${data.replace(/"/g, '""')}"`).join(',') + '\n';
        });

        // Create download link
        const blob = new Blob([csvContent], {
            type: 'text/csv;charset=utf-8;'
        });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', 'products_list.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const headers = document.querySelectorAll('#manageProducts .header__item');
    const tableContent = document.getElementById('table-content-products');

    headers.forEach((header, index) => {
        header.addEventListener('click', () => {
            const rows = Array.from(tableContent.querySelectorAll('.table-row'));
            const isAscending = header.classList.toggle('asc');

            rows.sort((a, b) => {
                const cellA = a.children[index].textContent.trim().toLowerCase();
                const cellB = b.children[index].textContent.trim().toLowerCase();

                // Check if it's a number (for price, quantity columns)
                const numberA = parseFloat(cellA.replace(/[^\d.-]/g, ''));
                const numberB = parseFloat(cellB.replace(/[^\d.-]/g, ''));

                if (!isNaN(numberA) && !isNaN(numberB)) {
                    return isAscending ? numberA - numberB : numberB - numberA;
                } else {
                    return isAscending ? cellA.localeCompare(cellB) : cellB
                        .localeCompare(cellA);
                }
            });

            rows.forEach(row => tableContent.appendChild(row));
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    function filterProductTable() {
        const categoryInputs = document.querySelectorAll('input[name="product-category-option"]');
        const stockInputs = document.querySelectorAll('input[name="product-stock-option"]');

        const selectedCategory = Array.from(categoryInputs).find(input => input.checked)?.nextElementSibling
            .innerText || 'All Categories';
        const selectedStock = Array.from(stockInputs).find(input => input.checked)?.nextElementSibling
            .innerText || 'All Stock';

        document.getElementById('product-category-selected').innerText = selectedCategory;
        document.getElementById('product-stock-selected').innerText = selectedStock;

        const category = document.querySelector('input[name="product-category-option"]:checked')?.value || '';
        const stock = document.querySelector('input[name="product-stock-option"]:checked')?.value || '';

        const rows = document.querySelectorAll('#table-content-products .table-row');
        rows.forEach(row => {
            const rowCategory = row.getAttribute('data-category');
            const rowStock = row.getAttribute('data-stock');

            const matchesCategory = !category || rowCategory === category;
            const matchesStock = !stock || rowStock === stock;

            row.style.display = matchesCategory && matchesStock ? '' : 'none';
        });
    }

    // Initialize filters for products
    document.querySelectorAll('input[name="product-category-option"], input[name="product-stock-option"]')
        .forEach(
            input => {
                input.addEventListener('change', filterProductTable);
            });
});

function searchTable(searchBarId, tableContentId) {
    var input, filter, table, rows, i, j, txtValue, visible;
    input = document.getElementById(searchBarId);
    filter = input.value.trim().toLowerCase();
    table = document.getElementById(tableContentId);
    rows = table.getElementsByClassName("table-row");

    for (i = 0; i < rows.length; i++) {
        visible = false;
        columns = rows[i].getElementsByClassName("table-data");

        for (j = 0; j < columns.length; j++) {
            txtValue = columns[j].textContent || columns[j].innerText;
            var numericValue = parseFloat(txtValue);
            var filterNumeric = parseFloat(filter);

            if (
                (txtValue.toLowerCase().includes(filter)) ||
                (txtValue === filter) ||
                (!isNaN(numericValue) && !isNaN(filterNumeric) && numericValue === filterNumeric)
            ) {
                visible = true;
                break;
            }
        }
        rows[i].style.display = visible ? "" : "none";
    }
}

document.addEventListener("DOMContentLoaded", function() {
    // Event listeners for search bars
    document.getElementById("search-bar-products").addEventListener("keyup", function() {
        searchTable("search-bar-products", "table-content-products");
    });

    // Toggle filter containers
    document.querySelectorAll('.toggle-filter-btn').forEach(button => {
        button.addEventListener('click', function() {
            const filterContainerId = this.getAttribute('data-target');
            const filterContainer = document.getElementById(filterContainerId);
            filterContainer.classList.toggle('open');

            if (filterContainer.classList.contains('open')) {
                this.textContent = 'Hide Filters';
            } else {
                this.textContent = 'Show Filters';
            }
        });
    });
});

// Open Popup
const openProductPopup = document.getElementById('openPopup-products');
const productPopupForm = document.getElementById('popupForm-products');
const closeProductPopup = document.getElementById('closePopup-products');

openProductPopup.addEventListener('click', () => {
    productPopupForm.style.display = 'flex';
});

// Close Popup
closeProductPopup.addEventListener('click', () => {
    productPopupForm.style.display = 'none';
});

// Close Popup on Outside Click
window.addEventListener('click', (event) => {
    if (event.target === productPopupForm) {
        productPopupForm.style.display = 'none';
    }
});

function confirmDelete(event) {
    if (!confirm('Are you sure you want to delete this product?')) {
        event.preventDefault();
        return false;
    }
    return true;
}

document.querySelectorAll('.generate-barcode-btn').forEach(function(button) {
    button.addEventListener('click', function() {
        const productId = this.getAttribute('data-product-id');
        const productName = this.getAttribute('data-product-name');

        JsBarcode("#barcode", productId, {
            format: "CODE128",
            lineColor: "#000",
            fontSize: 20,
            width: 4,
            height: 80,
            displayValue: false
        });

        document.getElementById('barcodeProductName').textContent = productName;

        $('#barcodeModal').modal('show');
    });
});

function printBarcode() {
    // Copy name
    document.getElementById('printProductName').innerText = document.getElementById('barcodeProductName').innerText;

    // Copy barcode SVG
    const barcodeSvg = document.getElementById('barcode').outerHTML;
    document.getElementById('printBarcode').outerHTML = barcodeSvg;

    // Print only the printArea
    const printContents = document.getElementById('printArea').innerHTML;
    const originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload(); // restore JS
}
</script>

<?php include_once('layouts/footer.php'); ?>