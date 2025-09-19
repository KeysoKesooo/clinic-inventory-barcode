<?php
require_once('includes/load.php');

if (isset($_POST['product_id'])) {
    $product_id = $db->escape($_POST['product_id']);
    $product = find_by_id('products', $product_id);

    if (!$product) {
        echo "<div class='alert alert-danger fw-bold fs-5 text-center py-3'>❌ Product not found.</div>";
        exit;
    }

    // Get product photo from products table
    $photo = !empty($product['product_photo']) ? $product['product_photo'] : "no_image.png";
?>
<div class="product-card shadow-lg p-3">
    <!-- Product photo -->
    <img src="uploads/products/<?php echo $photo; ?>" alt="Product Image" class="mb-3 border">

    <!-- Product name -->
    <h3 class="fw-bold text-primary mb-2"><?php echo remove_junk($product['name']); ?></h3>
    <p class="fs-5 text-dark"><strong>Stock:</strong>
        <span class="badge bg-info text-dark fs-6"><?php echo (int)$product['quantity']; ?></span>
    </p>

    <!-- Sale form -->
    <form id="sale-form" class="mt-3">
        <input type="hidden" name="s_id" value="<?php echo (int)$product['id']; ?>">

        <div class="mb-3 text-start">
            <label class="fw-semibold">Quantity</label>
            <input type="number" class="form-control form-control-lg" name="quantity" value="1" min="1" required>
        </div>

        <div class="mb-3 text-start">
            <label class="fw-semibold">Date</label>
            <input type="date" class="form-control form-control-lg" name="date" value="<?php echo date('Y-m-d'); ?>">
        </div>

        <div class="d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-success btn-lg px-4 fw-bold">✔ Confirm</button>
            <button type="button" class="btn btn-outline-secondary btn-lg px-4 fw-bold" onclick="cancelSale()">✖
                Cancel</button>
        </div>
    </form>

    <div id="sale-result" class="mt-4"></div>
</div>

<script>
$("#sale-form").submit(function(e) {
    e.preventDefault();
    $.post("record_sale.php", $(this).serialize(), function(response) {
        // Style response message
        if (response.includes("✅")) {
            $("#sale-result").html('<div class="alert alert-success fs-5 fw-bold py-3 text-center">' +
                response + '</div>');
            setTimeout(() => {
                startScanner();
            }, 2000);
        } else if (response.includes("❌")) {
            $("#sale-result").html('<div class="alert alert-danger fs-5 fw-bold py-3 text-center">' +
                response + '</div>');
        } else {
            $("#sale-result").html('<div class="alert alert-warning fs-5 fw-bold py-3 text-center">' +
                response + '</div>');
        }
    });
});

function cancelSale() {
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