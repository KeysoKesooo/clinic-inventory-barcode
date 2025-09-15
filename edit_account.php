<?php
require_once('includes/load.php');
$page_title = 'Profile';

// Check what level of permission the user has
page_require_level(4); // Ensure level 4 users can access this page

if (isset($_POST['update'])) {
    // Handle required fields validation
    $req_fields = array('first_name', 'last_name', 'username');
    validate_fields($req_fields);

    if (empty($errors)) {
        $id = (int)$_SESSION['user_id'];
        $first_name = remove_junk($db->escape($_POST['first_name']));
        $middle_name = remove_junk($db->escape($_POST['middle_name']));
        $last_name   = remove_junk($db->escape($_POST['last_name']));

        // Optional suffix
        $suffix = isset($_POST['suffix']) ? remove_junk($db->escape($_POST['suffix'])) : '';

        // Build full name with middle name
        $name = $suffix ? "{$first_name} {$middle_name} {$last_name}, {$suffix}" : "{$first_name} {$middle_name} {$last_name}";
        $username = remove_junk($db->escape($_POST['username']));
        $update_image = false;

        // Process image upload if a file is selected
        if (!empty($_FILES['file_upload']['name'])) {
            $photo = new Media();
            $user_id = (int)$_POST['user_id'];
            $photo->upload($_FILES['file_upload']);
            if($photo->process_user($user_id)){
                $session->msg('s','Succesfully Updated.');
                redirect('edit_account.php');
            } else {
                $session->msg('d', join($photo->errors));
                redirect('edit_account.php');
            }
        }

        // Update user details
        $sql = "UPDATE users SET name = '{$name}', username = '{$username}' WHERE id = '{$id}'";
        $result = $db->query($sql);

        // Handle password change
        if (!empty($_POST['old-password']) && !empty($_POST['new-password'])) {
            $old_password = $_POST['old-password'];
            $new_password = $_POST['new-password'];

            $current_user = current_user();
            $current_hashed_password = $current_user['password'];

            if (!password_verify($old_password, $current_hashed_password)) {
                $session->msg('d', "Your old password does not match.");
                redirect('edit_account.php', false);
            } else {
                $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $sql_password = "UPDATE users SET password = '{$new_hashed_password}' WHERE id = '{$id}'";
                $result_password = $db->query($sql_password);

                if ($result_password && $db->affected_rows() === 1) {
                    $session->logout();
                    $session->msg('s', "Login with your new password.");
                    redirect('edit_account.php', false);
                } else {
                    $session->msg('d', 'Sorry, failed to update the password!');
                    redirect('edit_account.php', false);
                }
            }
        }

        // Success or failure messages
        if ($result && $db->affected_rows() === 1) {
            $session->msg('s', "Account updated successfully.");
            redirect('edit_account.php', false);
        } else {
            $session->msg('d', 'Sorry, failed to update!');
            redirect('edit_account.php', false);
        }
    } else {
        $session->msg('d', $errors);
        redirect('edit_account.php', false);
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
    <link rel="stylesheet" href="libs/css/roles.css">
</head>

<?php
// Parse the full name into first, middle, last for form population
$full_name = isset($user['name']) ? $user['name'] : '';
$name_parts = explode(' ', $full_name);

$first_name = $name_parts[0] ?? '';
$middle_name = '';
$last_name = '';

if (count($name_parts) == 2) {
    $last_name = $name_parts[1];
} elseif (count($name_parts) > 2) {
    $middle_name = $name_parts[1];
    $last_name = implode(' ', array_slice($name_parts, 2));
}
?>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg(isset($msg) ? $msg : ''); ?>
    </div>
</div>

<button class="back_button" style="top: 20px; z-index:9999;" onclick="window.history.back()">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="20" height="20">
        <path d=" M14 2L6 10l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
    </svg>
    Back
</button>

<div class="editpopup_container">
    <div class="editpopup_form_area">
        <form method="post" action="edit_account.php" enctype="multipart/form-data">
            <div class="editpopup_form_group">
                <div class="profile_con">
                    <?php
                        $profile_image = 'uploads/users/';
                        if (isset($user['image']) && !empty($user['image']) && file_exists($profile_image . $user['image'])) {
                            $profile_image .= $user['image'];
                        } else {
                            $profile_image .= 'no_image.png';
                        }
                    ?>
                    <img class="profile_img" src="<?php echo $profile_image; ?>?t=<?php echo time(); ?>"
                        alt="Profile Image" onerror="this.onerror=null;this.src='uploads/users/default.png';">
                    <div class="img_text">
                        <input type="file" name="file_upload" class="text_img" accept="image/*">
                        <h1>CHANGE PROFILE</h1>
                    </div>
                </div>
            </div>

            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="first_name">First Name</label>
                <input placeholder="First Name" name="first_name" class="editpopup_form_style" type="text"
                    value="<?php echo htmlspecialchars($first_name); ?>">
            </div>

            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="middle_name">Middle Name</label>
                <input placeholder="Middle Name" name="middle_name" class="editpopup_form_style" type="text"
                    value="<?php echo htmlspecialchars($middle_name); ?>">
            </div>

            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="last_name">Last Name</label>
                <input placeholder="Last Name" name="last_name" class="editpopup_form_style" type="text"
                    value="<?php echo htmlspecialchars($last_name); ?>">
            </div>

            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="username">Username</label>
                <input placeholder="Username" name="username" class="editpopup_form_style" type="text"
                    value="<?php echo isset($user['username']) ? remove_junk(ucwords($user['username'])) : ''; ?>">
            </div>
            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="old-password">Old Password</label>
                <input placeholder="Old Password" name="old-password" class="editpopup_form_style" type="password">
            </div>
            <div class="editpopup_form_group">
                <label class="editpopup_sub_title" for="new-password">New Password</label>
                <input placeholder="New Password" name="new-password" class="editpopup_form_style" type="password">
            </div>
            <input type="hidden" name="user_id" value="<?php echo isset($user['id']) ? $user['id'] : ''; ?>">
            <button type="submit" name="update" class="editpopup_btn">UPDATE</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.querySelector('input[name="file_upload"]');
    const profileImg = document.querySelector('.profile_img');

    fileInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                profileImg.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>

<?php include_once('layouts/footer.php'); ?>