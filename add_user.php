<?php
  $page_title = 'Add User';
  require_once('includes/load.php');
  // Check what level user has permission to view this page
  page_require_level(1);
  $groups = find_all('user_groups');
?>

<?php
  if (isset($_POST['add_user'])) {
    $req_fields = array('first-name', 'last-name', 'username', 'password', 'level');
    validate_fields($req_fields);

    if (empty($errors)) {
      $first_name  = remove_junk($db->escape($_POST['first-name']));
      $middle_name = isset($_POST['middle-name']) ? remove_junk($db->escape($_POST['middle-name'])) : '';
      $last_name   = remove_junk($db->escape($_POST['last-name']));
      $username    = remove_junk($db->escape($_POST['username']));
      $password    = remove_junk($db->escape($_POST['password']));
      $user_level  = (int)$db->escape($_POST['level']);

      // Combine name into a single string
      $name = $first_name . ' ' . ($middle_name ? $middle_name . ' ' : '') . $last_name;
      $password = password_hash($password, PASSWORD_BCRYPT);


      $query  = "INSERT INTO users (";
      $query .= "name, username, password, user_level, status";
      $query .= ") VALUES (";
      $query .= "'{$name}', '{$username}', '{$password}', '{$user_level}', '1'";
      $query .= ")";

      if ($db->query($query)) {
        $session->msg('s', "User account has been created!");
        redirect('add_user.php', false);
      } else {
        $session->msg('d', 'Sorry, failed to create account!');
        redirect('add_user.php', false);
      }
    } else {
      $session->msg("d", $errors);
      redirect('add_user.php', false);
    }
  }
?>

<?php include_once('layouts/header.php'); ?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?></title>
    <link rel="stylesheet" href="libs/css/designs.css">
</head>



<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
    <!-- You can adjust the card position here -->
    <div class="card-center">
        <div class="card">
            <span class="card__title">Add User</span>
            <p class="card__content">Please fill out the form to create a new user.</p>

            <form method="post" action="add_user.php" class="card__form">
                <input type="text" name="first-name" placeholder="First Name" required>
                <input type="text" name="middle-name" placeholder="Middle Name (optional)">
                <input type="text" name="last-name" placeholder="Last Name" required>

                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>

                <select name="level" required>
                    <?php foreach ($groups as $group): ?>
                    <option value="<?php echo $group['group_level']; ?>">
                        <?php echo ucwords($group['group_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" name="add_user" class="sign-up">Add User</button>
            </form>
        </div>
    </div>




    <?php include_once('layouts/footer.php'); ?>