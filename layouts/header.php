<?php 
  $user = current_user(); 

  // === Function to get expiring products within 7 days ===
  function get_expiring_products($db) {
      $today = date('Y-m-d');
      $seven_days_later = date('Y-m-d', strtotime('+7 days'));

      $sql = "SELECT id, name, expiration_date 
              FROM products 
              WHERE expiration_date BETWEEN '{$today}' AND '{$seven_days_later}'";

      $result = $db->query($sql);
      $expiring = [];

      if ($db->num_rows($result) > 0) {
          while ($row = $db->fetch_assoc($result)) {
              $expiring[] = $row;
          }
      }
      return $expiring;
  }

  // Fetch expiring products
  $expiring_medicines = get_expiring_products($db);
  $exp_count = count($expiring_medicines);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>
        <?php 
          if (!empty($page_title))
            echo remove_junk($page_title);
          elseif(!empty($user))
            echo ucfirst($user['name']);
          else 
            echo "Inventory Management System"; 
        ?>
    </title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" />
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker3.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="libs/css/main.css" />

    <style>
    .notifications .fa-bell {
        font-size: 18px;
        color: #555;
    }

    .notifications .badge {
        background-color: red;
        position: absolute;
        top: 5px;
        right: 2px;
    }
    </style>
</head>

<body>
    <?php if ($session->isUserLoggedIn(true)): ?>
    <header id="header">
        <div class="header-content">
            <div class="header-date pull-left">
                <strong><?php echo date("F j, Y, g:i a");?></strong>
            </div>
            <div class="pull-right clearfix">
                <ul class="info-menu list-inline list-unstyled">

                    <!-- 🔔 Notification Bell -->
                    <li class="notifications dropdown" style="position:relative;">
                        <a href="#" class="toggle" data-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-bell"></i>
                            <?php if ($exp_count > 0): ?>
                            <span class="badge"><?php echo $exp_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu notifications">
                            <?php if ($exp_count > 0): ?>
                            <?php foreach ($expiring_medicines as $med): ?>
                            <li>
                                <a href="product.php?id=<?php echo $med['id']; ?>">
                                    ⚠ <?php echo remove_junk($med['name']); ?>
                                    <br><small>Expires:
                                        <?php echo date('m-d-Y', strtotime($med['expiration_date'])); ?></small>
                                </a>
                            </li>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <li><span class="dropdown-item">No upcoming expirations</span></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <!-- 👤 User Profile -->
                    <li class="profile">
                        <a href="#" data-toggle="dropdown" class="toggle" aria-expanded="false">
                            <img src="uploads/users/<?php echo $user['image'];?>" alt="user-image"
                                class="img-circle img-inline">
                            <span><?php echo remove_junk(ucfirst($user['name'])); ?> <i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="edit_account.php" title="edit account">
                                    <i class="glyphicon glyphicon-cog"></i> Settings
                                </a>
                            </li>
                            <li class="last">
                                <a href="logout.php">
                                    <i class="glyphicon glyphicon-off"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div class="sidebar">
        <?php if($user['user_level'] === '1'): ?>
        <!-- admin menu -->
        <?php include_once('admin_menu.php');?>

        <?php elseif($user['user_level'] === '2'): ?>
        <!-- Special user -->
        <?php include_once('special_menu.php');?>

        <?php elseif($user['user_level'] === '3'): ?>
        <!-- User menu -->
        <?php include_once('user_menu.php');?>
        <?php endif;?>
    </div>
    <?php endif; ?>

    <div class="page">
        <div class="container-fluid">