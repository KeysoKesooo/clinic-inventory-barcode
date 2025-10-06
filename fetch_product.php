<?php
require_once('includes/load.php');

if (isset($_POST['product_id'])) {
    $product_id = $db->escape($_POST['product_id']);
    $mode       = $_POST['mode'] ?? 'sale';   // get mode from AJAX
    $product    = find_by_id('products', $product_id);

    if (!$product) {
        echo "<div class='alert alert-danger fw-bold fs-5 text-center py-3'>❌ Product not found.</div>";
        exit;
    }

    $photo = !empty($product['product_photo']) ? $product['product_photo'] : "no_image.png";
    $targetFile = ($mode === 'restock') ? "record_restock.php" : "record_sale.php";
?>
<div class="product-card shadow-lg p-3">
    <img src="uploads/products/<?php echo $photo; ?>" alt="Product Image" class="mb-3 border">

    <h3 class="fw-bold text-primary mb-2"><?php echo remove_junk($product['name']); ?></h3>
    <p class="fs-5 text-dark"><strong>Stock:</strong>
        <span class="badge bg-info text-dark fs-6"><?php echo (int)$product['quantity']; ?></span>
    </p>

    <form id="product-form" class="mt-3">
        <input type="hidden" name="s_id" value="<?php echo (int)$product['id']; ?>">
        <input type="hidden" name="status" value="<?php echo ($mode === 'restock') ? 'restock' : 'dispense'; ?>">

        <div class="mb-3 text-start">
            <label class="fw-semibold">Quantity</label>
            <input type="number" class="form-control form-control-lg" name="quantity" value="1" min="1" required>
        </div>

        <?php if ($mode === 'sale') { ?>
        <div class="mb-3 text-start">
            <label class="fw-semibold">Issued To</label>
            <input type="text" class="form-control form-control-lg" name="issued_to" placeholder="Enter recipient name"
                required>
        </div>
        <input type="hidden" name="issued_by" value="<?php echo (int)$_SESSION['user_id']; ?>">
        <?php } else { ?>
        <input type="hidden" name="restocked_by" value="<?php echo (int)$_SESSION['user_id']; ?>">
        <?php } ?>

        <div class="d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-success btn-lg px-4 fw-bold">✔ Confirm</button>
            <button type="button" class="btn btn-outline-secondary btn-lg px-4 fw-bold" onclick="cancelRecord()">✖
                Cancel</button>
        </div>
    </form>

    <div id="form-result" class="mt-4"></div>
</div>

<script>
$("#product-form").submit(function(e) {
    e.preventDefault();
    $.post("<?php echo $targetFile; ?>", $(this).serialize(), function(response) {
        if (response.includes("✅")) {
            $("#form-result").html('<div class="alert alert-success fs-5 fw-bold py-3 text-center">' +
                response + '</div>');
            setTimeout(() => {
                startScanner();
            }, 2000);
        } else if (response.includes("❌")) {
            $("#form-result").html('<div class="alert alert-danger fs-5 fw-bold py-3 text-center">' +
                response + '</div>');
        } else {
            $("#form-result").html('<div class="alert alert-warning fs-5 fw-bold py-3 text-center">' +
                response + '</div>');
        }
    });
});

function cancelRecord() {
    $("#sale-form-container").html(
        "<div class='alert alert-warning fs-5 fw-bold text-center py-3'>✖ Record cancelled. Ready to scan again.</div>"
    );
    setTimeout(() => {
        startScanner();
    }, 2000);
}
</script>
<?php
}
?>