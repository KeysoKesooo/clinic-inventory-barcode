<?php
require_once('includes/load.php');
page_require_level(3);

if (isset($_POST['s_id'], $_POST['quantity'], $_POST['date'])) {
    $p_id   = (int)$db->escape($_POST['s_id']);
    $s_qty  = (int)$db->escape($_POST['quantity']);
    $s_date = $db->escape($_POST['date']);

    if ($s_qty <= 0) {
        echo "<div class='alert alert-warning'>⚠ Invalid quantity.</div>";
        exit;
    }

    // Insert sale
    $sql = "INSERT INTO sales (product_id, qty, date) VALUES ('{$p_id}', '{$s_qty}', '{$s_date}')";
    if ($db->query($sql)) {
        update_product_qty($s_qty, $p_id);
        echo "<div class='alert alert-success'>✅ Sale recorded successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ Failed to record sale.</div>";
    }
}
?>