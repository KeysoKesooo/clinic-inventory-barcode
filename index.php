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
    <title>Login</title>
</head>

<body>

    <div class="container">

        <!-- Login form -->
        <form class="form" method="post" action="auth.php">
            <!-- Logo at the top -->
            <div class="logo">
                <img src="logo1.png" alt="Logo">
            </div>
            <div class="title">
                Welcome,<br><span>Login to continue</span>
                <h5><?php echo display_msg($msg); ?></h5>
            </div>
            <input class="input" name="username" placeholder="Username" type="Username">
            <input class="input" name="password" placeholder="Password" type="Password">
            <div class="login-with"></div>
            <div class="forgot-password">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>
            <button class="button-confirm" type="submit">Login</button>
        </form>
    </div>

    <script>
    // JavaScript for fading images every 3 seconds
    let currentIndex = 0;
    const images = document.querySelectorAll('.fade-image');

    function changeImage() {
        // Remove the 'show' class from the current image
        images[currentIndex].classList.remove('show');

        // Increment the index (loop back to 0 if at the end)
        currentIndex = (currentIndex + 1) % images.length;

        // Add the 'show' class to the next image
        images[currentIndex].classList.add('show');
    }

    // Initialize the first image to be visible
    images[currentIndex].classList.add('show');

    // Change image every 3 seconds
    setInterval(changeImage, 3000);
    </script>
</body>

</html>