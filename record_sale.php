<?php
require_once('includes/load.php');
page_require_level(3);

if (isset($_POST['s_id'], $_POST['quantity'], $_POST['issued_to'], $_POST['issued_by'], $_POST['unit_type'])) {
    $p_id       = (int)$db->escape($_POST['s_id']);
    $unit_type  = $db->escape($_POST['unit_type']);
    $s_qty      = (int)$db->escape($_POST['quantity']);
    $issued_to  = $db->escape($_POST['issued_to']);
    $issued_by  = (int)$db->escape($_POST['issued_by']);
    $status     = "dispense";

    $product = find_by_id('products', $p_id);
    if (!$product) {
        echo "<div class='alert alert-danger'>❌ Medicine not found.</div>";
        exit;
    }

    // If dispensing per box, calculate total pcs
    if ($unit_type === 'box') {
        $box_count = (int)$db->escape($_POST['box_count']);
        $pcs_per_box = (int)$product['pcs_per_box'];
        $s_qty = $box_count * $pcs_per_box;
    }

    if ($s_qty <= 0) {
        echo "<div class='alert alert-warning'>⚠ Invalid quantity.</div>";
        exit;
    }

    if ($s_qty > (int)$product['quantity']) {
        echo "<div class='alert alert-danger'>❌ Not enough stock. Available: {$product['quantity']} only.</div>";
        exit;
    }

    // Insert sale record
    $sql = "INSERT INTO sales (product_id, qty, date, issued_to, issued_by, status) 
            VALUES ('{$p_id}', '{$s_qty}', CURDATE(), '{$issued_to}', '{$issued_by}', '{$status}')";

    if ($db->query($sql)) {
        update_product_qty($s_qty, $p_id); // subtract stock
        echo "<div class='alert alert-success'>✅ Dispensed successfully. Total pcs: {$s_qty}</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ Failed to record dispense.</div>";
    }
}
?>