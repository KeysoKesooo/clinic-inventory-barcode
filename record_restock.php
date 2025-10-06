<?php
require_once('includes/load.php');
page_require_level(3);

if (isset($_POST['s_id'], $_POST['quantity'], $_POST['restocked_by'])) {
    $p_id        = (int)$db->escape($_POST['s_id']);
    $r_qty       = (int)$db->escape($_POST['quantity']);
    $restocked_by = (int)$db->escape($_POST['restocked_by']);
    $status      = "restock"; // Always restock for this file

    if ($r_qty <= 0) {
        echo "<div class='alert alert-warning'>⚠ Invalid quantity.</div>";
        exit;
    }

    // ✅ Insert restock as "restock" in sales table
    $sql = "INSERT INTO sales (product_id, qty, date, issued_to, issued_by, status) 
            VALUES ('{$p_id}', '{$r_qty}', CURDATE(), NULL, '{$restocked_by}', '{$status}')";

    if ($db->query($sql)) {
        // Update product stock
        $update = "UPDATE products 
                   SET quantity = quantity + {$r_qty} 
                   WHERE id = '{$p_id}'";
        $db->query($update);

        echo "<div class='alert alert-success'>✅ Restocked successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ Failed to record restock.</div>";
    }
}
?>