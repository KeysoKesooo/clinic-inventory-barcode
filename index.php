<?php
  ob_start();
  require_once('includes/load.php');
  if($session->isUserLoggedIn(true)) { redirect('index.php', false);}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="libs/css/login.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Login</title>
</head>

<body>
    <div class="background-animation">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="container">
        <!-- Login form -->
        <form class="form" method="post" action="auth.php">
            <!-- Logo at the top -->
            <div class="logo">
                <img src="logo.jpg" alt="Logo">
            </div>

            <div class="title">
                <h2>Welcome Back</h2>
                <p>Sign in to continue</p>
                <h5><?php echo display_msg($msg); ?></h5>
            </div>

            <div class="input-group">
                <input class="input" name="username" placeholder="Username" type="text" required>
                <i class="fas fa-user input-icon"></i>
            </div>

            <div class="input-group">
                <input class="input password-input" name="password" placeholder="Password" type="password" required>
                <i class="fas fa-eye-slash toggle-password input-icon"></i>
            </div>

            <button class="button-confirm" type="submit">Login</button>
        </form>
    </div>

    <script>
    // Toggle password visibility
    document.querySelector('.toggle-password').addEventListener('click', function() {
        const passwordInput = document.querySelector('.password-input');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        // Toggle eye icon
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
    </script>
</body>

</html>