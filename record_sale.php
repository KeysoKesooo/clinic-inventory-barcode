<?php
require_once('includes/load.php');
page_require_level(3);

if (isset($_POST['s_id'], $_POST['quantity'], $_POST['issued_to'], $_POST['issued_by'])) {
    $p_id       = (int)$db->escape($_POST['s_id']);
    $s_qty      = (int)$db->escape($_POST['quantity']);
    $issued_to  = $db->escape($_POST['issued_to']);
    $issued_by  = (int)$db->escape($_POST['issued_by']);
    $status     = "dispense"; // Always dispense for this file

    if ($s_qty <= 0) {
        echo "<div class='alert alert-warning'>⚠ Invalid quantity.</div>";
        exit;
    }

    // 🔎 Check current stock
    $product = find_by_id('products', $p_id);
    if (!$product) {
        echo "<div class='alert alert-danger'>❌ Medicine not found.</div>";
        exit;
    }

    if ($s_qty > (int)$product['quantity']) {
        echo "<div class='alert alert-danger'>❌ Not enough stock. Available: {$product['quantity']} only.</div>";
        exit;
    }

    // ✅ Insert sale with status = dispense
    $sql = "INSERT INTO sales (product_id, qty, date, issued_to, issued_by, status) 
            VALUES ('{$p_id}', '{$s_qty}', CURDATE(), '{$issued_to}', '{$issued_by}', '{$status}')";

    if ($db->query($sql)) {
        update_product_qty($s_qty, $p_id); // subtracts stock
        echo "<div class='alert alert-success'>✅ Dispensed successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ Failed to record dispense.</div>";
    }
}
?>