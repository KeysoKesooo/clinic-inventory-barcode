<?php
require_once('includes/load.php');
page_require_level(3);

if (isset($_POST['s_id'], $_POST['quantity'], $_POST['restocked_by'], $_POST['unit_type'])) {
    $p_id          = (int)$db->escape($_POST['s_id']);
    $unit_type     = $db->escape($_POST['unit_type']);
    $r_qty         = (int)$db->escape($_POST['quantity']);
    $restocked_by  = (int)$db->escape($_POST['restocked_by']);
    $status        = "restock";

    $product = find_by_id('products', $p_id);
    if (!$product) {
        echo "<div class='alert alert-danger'>❌ Medicine not found.</div>";
        exit;
    }

    // If restocking per box, calculate total pcs
    if ($unit_type === 'box') {
        $box_count = (int)$db->escape($_POST['box_count']);
        $pcs_per_box = (int)$product['pcs_per_box'];
        $r_qty = $box_count * $pcs_per_box;
    }

    if ($r_qty <= 0) {
        echo "<div class='alert alert-warning'>⚠ Invalid quantity.</div>";
        exit;
    }

    // Insert restock record
    $sql = "INSERT INTO sales (product_id, qty, date, issued_to, issued_by, status) 
            VALUES ('{$p_id}', '{$r_qty}', CURDATE(), NULL, '{$restocked_by}', '{$status}')";

    if ($db->query($sql)) {
        // Update product stock
        $update = "UPDATE products SET quantity = quantity + {$r_qty} WHERE id = '{$p_id}'";
        $db->query($update);
        echo "<div class='alert alert-success'>✅ Restocked successfully. Total pcs: {$r_qty}</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ Failed to record restock.</div>";
    }
}
?>