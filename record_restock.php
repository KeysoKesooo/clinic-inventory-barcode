<?php
require_once('includes/load.php');
page_require_level(3);

if (isset($_POST['s_id'], $_POST['quantity'], $_POST['date'])) {
    $p_id   = (int)$db->escape($_POST['s_id']);
    $r_qty  = (int)$db->escape($_POST['quantity']);
    $r_date = $db->escape($_POST['date']); // not really needed if no restock table

    if ($r_qty <= 0) {
        echo "<div class='alert alert-warning'>⚠ Invalid quantity.</div>";
        exit;
    }

    // ✅ Just update product stock directly
    $update = "UPDATE products 
               SET quantity = quantity + {$r_qty} 
               WHERE id = '{$p_id}'";

    if ($db->query($update)) {
        echo "<div class='alert alert-success'>✅ Stock updated successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ Failed to update stock.</div>";
    }
}
?>