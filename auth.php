<?php
include_once('includes/load.php');

$req_fields = array('username', 'password');
validate_fields($req_fields);

$username = remove_junk($_POST['username']);
$password = remove_junk($_POST['password']);

if (empty($errors)) {
  $user = authenticate($username, $password);

  if ($user) {
    // Create session
    $session->login($user['id']);

    // Update login time
    updateLastLogIn($user['id']);

    $session->msg("s", "Welcome to Inventory Management System");

    // Redirect based on role if you want (optional)
    // if ($user['user_level'] == 1) {
    //   redirect('admin.php', false);
    // } else {
    //   redirect('home.php', false);
    // }

    redirect('admin.php', false);
  } else {
    $session->msg("d", "Sorry, Username/Password incorrect.");
    redirect('index.php', false);
  }
} else {
  $session->msg("d", $errors);
  redirect('index.php', false);
}
?>