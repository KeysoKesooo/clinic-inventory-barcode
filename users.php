<?php
  $page_title = 'All User';
  require_once('includes/load.php');
  page_require_level(1);
  $all_users = find_all_user();
  $groups = find_all('user_groups');

  // Edit User logic
if (isset($_POST['edit_user'])) {
    $id = (int)$_POST['user_id'];
    $first_name = remove_junk($db->escape($_POST['first_name']));
    $middle_name = remove_junk($db->escape($_POST['middle_name']));
    $last_name = remove_junk($db->escape($_POST['last_name']));
    $username = remove_junk($db->escape($_POST['username']));
    $level = (int)$db->escape($_POST['level']);
    $status = remove_junk($db->escape($_POST['status']));

    // Combine full name (omit middle name if empty)
    $full_name = $first_name . ' ' . ($middle_name ? $middle_name . ' ' : '') . $last_name;

    $sql = "UPDATE users SET 
                name ='{$full_name}',
                username ='{$username}',
                user_level='{$level}',
                status='{$status}'
            WHERE id='{$id}'";

    $result = $db->query($sql);
    if ($result && $db->affected_rows() === 1) {
        $session->msg('s', "User updated.");
        redirect('users.php', false);
    } else {
        $session->msg('d', "Update failed.");
        redirect('users.php', false);
    }
}


// Add User logic
if (isset($_POST['add_user'])) {
  $first_name = remove_junk($db->escape($_POST['first-name']));
  $middle_name = isset($_POST['middle-name']) ? remove_junk($db->escape($_POST['middle-name'])) : '';
  $last_name = remove_junk($db->escape($_POST['last-name']));
  $full_name = $first_name . ' ' . ($middle_name ? $middle_name . ' ' : '') . $last_name;
  $username = remove_junk($db->escape($_POST['username']));

  // Hash the password using bcrypt
  $raw_password = remove_junk($db->escape($_POST['password']));
  $password = password_hash($raw_password, PASSWORD_BCRYPT);

  $level = (int)$db->escape($_POST['level']);
  $sql = "INSERT INTO users (name, username, password, user_level, status) VALUES ('{$full_name}', '{$username}', '{$password}', '{$level}', '1')";
  $result = $db->query($sql);
  if ($result) {
    $session->msg('s', "User added.");
    redirect('users.php', false);
  } else {
    $session->msg('d', "Add user failed.");
    redirect('users.php', false);
  }
}


// Handling CSV import for multiple users
if (isset($_POST['import_users'])) {
    $csv_file = $_FILES['csv_file']['tmp_name'];

    if ($_FILES['csv_file']['error'] > 0) {
        $session->msg('d', 'Error uploading file.');
        redirect('users.php', false);
    }

    $success_count = 0;
    $duplicate_count = 0;
    $error_count = 0;
    $errors = [];
    $generated_credentials = [];

    $db->query("START TRANSACTION");

    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        // Skip header row
        fgetcsv($handle);

        // Role → user_level mapping (must match user_groups.group_level)
        $role_map = [
            'admin' => 1,
            'staff' => 2
        ];

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (!isset($data[0], $data[1])) { // Full Name, Role
                $error_count++;
                $errors[] = "Missing required fields in CSV row";
                continue;
            }

            $full_name = remove_junk($db->escape($data[0]));
            $user_role = trim(remove_junk($db->escape($data[1])));
            $normalized_role = strtolower($user_role);

            // Validate role
            if (!isset($role_map[$normalized_role])) {
                $error_count++;
                $errors[] = "Invalid role '$user_role' for $full_name. Only Admin or Staff allowed.";
                continue;
            }

            $user_level = $role_map[$normalized_role];

            // Split full name for username/password
            $name_parts = explode(' ', $full_name);
            $first_name = $name_parts[0];
            $last_name = end($name_parts);

            // Generate username: first letter + last name
            $username = strtolower(substr($first_name, 0, 1) . $last_name);
            $username = remove_junk($db->escape($username));

            // Generate password: last name + 123
            $raw_password = strtolower($last_name) . '123';
            $password = password_hash($raw_password, PASSWORD_BCRYPT);

            // Check for duplicate full name or username
            $check_query = "SELECT COUNT(*) as count FROM users WHERE username='{$username}' OR name='{$full_name}'";
            $result = $db->query($check_query);
            $row = $db->fetch_assoc($result);

            if ($row['count'] > 0) {
                $duplicate_count++;
                continue; // Skip this user
            }

            // Insert user
            $insert_query = "INSERT INTO users (name, username, password, user_level, status)
                             VALUES ('{$full_name}', '{$username}', '{$password}', {$user_level}, 1)";

            if ($db->query($insert_query)) {
                $success_count++;
                $generated_credentials[$full_name] = [
                    'username' => $username,
                    'password' => $raw_password
                ];
            } else {
                $error_count++;
                $errors[] = "Failed to import $full_name. Database error: " . $db->getLastError();
            }
        }

        fclose($handle);

        if ($error_count == 0) {
            $db->query("COMMIT");
            $session->msg('s', "Successfully imported {$success_count} users. Duplicates skipped: {$duplicate_count}");
        } else {
            $db->query("ROLLBACK");
            $error_msg = "Import completed with issues:<br>";
            $error_msg .= "- Successfully processed: {$success_count}<br>";
            $error_msg .= "- Duplicates skipped: {$duplicate_count}<br>";
            $error_msg .= "- Errors encountered: {$error_count}<br>";
            $error_msg .= "First 5 errors:<br>" . implode("<br>", array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $error_msg .= "<br>...and " . (count($errors) - 5) . " more";
            }
            $session->msg('d', $error_msg);
        }

    } else {
        $session->msg('d', 'Failed to open the CSV file.');
    }

    redirect('users.php', false);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg(isset($msg) ? $msg : ''); ?>
    </div>
</div>


<div id="manageUsers" class="content-section active">
    <div class="user-container">
        <div class="action-buttons-container">
            <div class="search-bar-container">
                <input type="text" id="search-bar-users" class="search-bar" placeholder="Search user...">
            </div>

            <a class="export_button" id="download-btn">
                <i class="fa-solid fa-download"></i>
                <span class="export_button__text">Export</span>
            </a>

            <a class="import_button" id="openPopup">
                <i class="fa-solid fa-upload"></i>
                <span class="import_button__text">Import</span>
            </a>

            <!-- Popup Form -->
            <div id="popupForm" class="popup-form">
                <div class="editpopup_form_area">
                    <span id="closePopup" class="close-btn">&times;</span>
                    <form method="post" action="users.php" enctype="multipart/form-data">
                        <div class="editpopup_form_group">
                            <label class="editpopup_sub_title" for="csv_file">Choose CSV File</label>
                            <input type="file" name="csv_file" class="editpopup_form_style" required>
                        </div>
                        <div>
                            <button type="submit" name="import_users" class="editpopup_btn"
                                style="margin-left: 180px">Import Users</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="filter-wrapper">
                <button class="toggle-filter-btn" data-target="user-filter-container">Show Filters</button>
                <div id="user-filter-container" class="filter-container">
                    <!-- User Role Filter -->
                    <label for="user-role-filter">User Role:</label>
                    <div class="select">
                        <div class="selected" data-default="All">
                            <span id="user-role-selected">All</span>
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                                <path
                                    d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z">
                                </path>
                            </svg>
                        </div>
                        <div class="options">
                            <div title="all">
                                <input id="user-role-all" name="user-role-option" type="radio" value="" checked />
                                <label class="option" for="user-role-all">All</label>
                            </div>
                            <?php foreach ($groups as $group): ?>
                            <div title="<?php echo htmlspecialchars($group['group_name']); ?>">
                                <input id="user-role-<?php echo htmlspecialchars($group['group_name']); ?>"
                                    name="user-role-option" type="radio"
                                    value="<?php echo htmlspecialchars($group['group_name']); ?>" />
                                <label class="option"
                                    for="user-role-<?php echo htmlspecialchars($group['group_name']); ?>"><?php echo htmlspecialchars(ucwords($group['group_name'])); ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- User Status Filter -->
                    <label for="user-status-filter">User Status:</label>
                    <div class="select">
                        <div class="selected" data-default="All">
                            <span id="user-status-selected">All</span>
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                                <path
                                    d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z">
                                </path>
                            </svg>
                        </div>
                        <div class="options">
                            <div title="active">
                                <input id="user-status-active" name="user-status-option" type="radio" value="1" />
                                <label class="option" for="user-status-active">Active</label>
                            </div>
                            <div title="inactive">
                                <input id="user-status-inactive" name="user-status-option" type="radio" value="0" />
                                <label class="option" for="user-status-inactive">Inactive</label>
                            </div>
                            <div title="all">
                                <input id="user-status-all" name="user-status-option" type="radio" value="" checked />
                                <label class="option" for="user-status-all">All</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <a class="add_button" href="#" data-toggle="modal" data-target="#addUserModal">
                <svg aria-hidden="true" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg" class="add_button__icon">
                    <path stroke-width="2" stroke="#ffffff"
                        d="M13.5 3H12H8C6.34315 3 5 4.34315 5 6V18C5 19.6569 6.34315 21 8 21H11M13.5 3L19 8.625M13.5 3V7.625C13.5 8.17728 13.9477 8.625 14.5 8.625H19M19 8.625V11.8125"
                        stroke-linejoin="round" stroke-linecap="round"></path>
                    <path stroke-linejoin="round" stroke-linecap="round" stroke-width="2" stroke="#ffffff"
                        d="M17 15V18M17 21V18M17 18H14M17 18H20"></path>
                </svg>
                <span class="add_button__text">Add User</span>
            </a>
        </div>

        <!-- User management table -->
        <div class="table">
            <div class="table-header">
                <div class="header__item">No.</div>
                <div class="header__item">Name</div>
                <div class="header__item">Username</div>
                <div class="header__item">User Role</div>
                <div class="header__item">Status</div>
                <div class="header__item">Last Login</div>
                <div class="header__item">Actions</div>
            </div>

            <div class="table-content" id="table-content-users">
                <?php foreach($all_users as $user): ?>
                <div class="table-row" data-role="<?php echo htmlspecialchars($user['group_name']); ?>"
                    data-status="<?php echo $user['status']; ?>">
                    <div class="table-data"><?php echo count_id(); ?></div>
                    <div class="table-data"><?php echo remove_junk(ucwords($user['name'])); ?></div>
                    <div class="table-data"><?php echo remove_junk($user['username']); ?></div>
                    <div class="table-data"><?php echo remove_junk($user['group_name']); ?></div>
                    <div class="table-data">
                        <?php if($user['status'] === '1'): ?>
                        <span class="label label-success">Active</span>
                        <?php else: ?>
                        <span class="label label-danger">Deactive</span>
                        <?php endif; ?>
                    </div>
                    <div class="table-data"><?php echo read_date($user['last_login']); ?></div>
                    <div class="table-data">
                        <button type="button" class="btn btn-warning edit-btn" data-id="<?php echo $user['id']; ?>"
                            data-name="<?php echo $user['name']; ?>" data-username="<?php echo $user['username']; ?>"
                            data-level="<?php echo $user['user_level']; ?>"
                            data-status="<?php echo $user['status']; ?>">
                            <i class="glyphicon glyphicon-pencil"></i>
                        </button>



                        <a href="delete_user.php?id=<?php echo (int)$user['id']; ?>" class="btn btn-danger"
                            onclick="return confirmDelete(event)">
                            <i class="glyphicon glyphicon-trash"></i>

                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="container">
            <form method="post" action="users.php" class="form_area">
                <span class="close-modal" data-dismiss="modal">&times;</span>
                <div class="title">Add New User</div>

                <div class="form_group">
                    <label for="first-name">First Name</label>
                    <input class="form_style" type="text" name="first-name" id="first-name" placeholder="First Name"
                        required>
                </div>

                <div class="form_group">
                    <label for="middle-name">Middle Name</label>
                    <input class="form_style" type="text" name="middle-name" id="middle-name"
                        placeholder="Middle Name (optional)">
                </div>

                <div class="form_group">
                    <label for="last-name">Last Name</label>
                    <input class="form_style" type="text" name="last-name" id="last-name" placeholder="Last Name"
                        required>
                </div>

                <div class="form_group">
                    <label for="username">Username</label>
                    <input class="form_style" type="text" name="username" id="username" placeholder="Username" required>
                </div>

                <div class="form_group">
                    <label for="password">Password</label>
                    <div style="position: relative;">
                        <input class="form_style password-input" type="password" name="password" id="password"
                            placeholder="Password" required>
                        <i class="fa-solid fa-eye toggle-password"
                            style="position:absolute; right:10px; top:50%; transform:translateY(-50%); cursor:pointer;"></i>
                    </div>
                </div>

                <div class="form_group">
                    <label for="level">Role</label>
                    <select class="form_style" name="level" id="level">
                        <?php foreach ($groups as $group): ?>
                        <option value="<?php echo $group['group_level']; ?>">
                            <?php echo ucwords($group['group_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="add_user" class="form_btn">Add User</button>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="container">
            <form method="post" action="users.php" class="form_area">
                <span class="close-modal" data-dismiss="modal">&times;</span>
                <div class="title">Edit User</div>
                <input type="hidden" name="user_id" id="edit-user-id">

                <div class="form_group">
                    <label for="edit-first-name">First Name</label>
                    <input class="form_style" type="text" name="first_name" id="edit-first-name" required>
                </div>

                <div class="form_group">
                    <label for="edit-middle-name">Middle Name (optional)</label>
                    <input class="form_style" type="text" name="middle_name" id="edit-middle-name">
                </div>

                <div class="form_group">
                    <label for="edit-last-name">Last Name</label>
                    <input class="form_style" type="text" name="last_name" id="edit-last-name" required>
                </div>

                <div class="form_group">
                    <label for="edit-username">Username</label>
                    <input class="form_style" type="text" name="username" id="edit-username" placeholder="Username">
                </div>

                <div class="form_group">
                    <label for="edit-level">Role</label>
                    <select class="form_style" name="level" id="edit-level">
                        <?php foreach ($groups as $group): ?>
                        <option value="<?php echo $group['group_level']; ?>">
                            <?php echo ucwords($group['group_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form_group">
                    <label for="edit-status">Status</label>
                    <select class="form_style" name="status" id="edit-status">
                        <option value="1">Active</option>
                        <option value="0">Deactive</option>
                    </select>
                </div>

                <button type="submit" name="edit_user" class="form_btn">Update</button>
            </form>
        </div>
    </div>
</div>


<script>
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const fullName = btn.dataset.name.trim().split(' ');
        const firstName = fullName[0] || '';
        const lastName = fullName.length > 1 ? fullName[fullName.length - 1] : '';
        const middleName = fullName.slice(1, -1).join(' ');


        document.getElementById('edit-user-id').value = btn.dataset.id;
        document.getElementById('edit-first-name').value = firstName;
        document.getElementById('edit-middle-name').value = middleName;
        document.getElementById('edit-last-name').value = lastName;

        document.getElementById('edit-username').value = btn.dataset.username;
        document.getElementById('edit-level').value = btn.dataset.level;
        document.getElementById('edit-status').value = btn.dataset.status;

        $('#editUserModal').modal('show');
    });
});


document.addEventListener('DOMContentLoaded', function() {
    // Add click event to download button
    document.getElementById('download-btn').addEventListener('click', downloadFilteredData);

    function downloadFilteredData() {
        // Get all visible rows (filtered rows)
        const visibleRows = document.querySelectorAll('.table-row:not([style*="display: none"])');

        // Prepare CSV content
        let csvContent = "No.,Name,Username,User Role,Status,Last Login\n";

        visibleRows.forEach(row => {
            const columns = row.querySelectorAll('.table-data');
            const rowData = [
                columns[0].textContent.trim(),
                columns[1].textContent.trim(),
                columns[2].textContent.trim(),
                columns[3].textContent.trim(),
                columns[4].textContent.trim(),
                columns[5].textContent.trim()
            ];

            // Escape quotes and add to CSV
            csvContent += rowData.map(data => `"${data.replace(/"/g, '""')}"`).join(',') + '\n';
        });

        // Create download link
        const blob = new Blob([csvContent], {
            type: 'text/csv;charset=utf-8;'
        });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', 'filtered_users.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
});


document.addEventListener('DOMContentLoaded', () => {
    const headers = document.querySelectorAll('.header__item');
    const tableContent = document.getElementById('table-content-users');

    headers.forEach((header, index) => {
        header.addEventListener('click', () => {
            const rows = Array.from(tableContent.querySelectorAll('.table-row'));
            const isAscending = header.classList.toggle('asc');

            rows.sort((a, b) => {
                const cellA = a.children[index].textContent.trim()
                    .toLowerCase();
                const cellB = b.children[index].textContent.trim()
                    .toLowerCase();

                // Check if it's a number (e.g., "faculty 10" and "faculty 2")
                const numberA = parseInt(cellA.replace(/\D/g, ''));
                const numberB = parseInt(cellB.replace(/\D/g, ''));

                if (!isNaN(numberA) && !isNaN(numberB)) {
                    // Sort numerically if it's a number
                    return isAscending ? numberA - numberB : numberB -
                        numberA;
                } else {
                    // Sort alphabetically if it's not a number
                    return isAscending ? cellA.localeCompare(cellB) : cellB
                        .localeCompare(cellA);
                }
            });

            rows.forEach(row => tableContent.appendChild(row));
        });
    });
});



document.addEventListener('DOMContentLoaded', function() {
    function initializeDropdownFilters(gradeLevelName, sectionName, selectedGradeElementId,
        selectedSectionElementId, tableId) {
        // Set up grade level dropdown
        document.querySelectorAll(`input[name="${gradeLevelName}"]`).forEach(input => {
            input.addEventListener('change', () => {
                const selectedText = document.querySelector(`label[for="${input.id}"]`)
                    .innerText;
                document.getElementById(selectedGradeElementId).innerText = selectedText;
                filterTable(gradeLevelName, sectionName, tableId);
            });
        });

        // Set up section dropdown
        document.querySelectorAll(`input[name="${sectionName}"]`).forEach(input => {
            input.addEventListener('change', () => {
                const selectedText = document.querySelector(`label[for="${input.id}"]`)
                    .innerText;
                document.getElementById(selectedSectionElementId).innerText = selectedText;
                filterTable(gradeLevelName, sectionName, tableId);
            });
        });
    }

    function filterTable(gradeLevelName, sectionName, tableId) {
        const gradeLevel = document.querySelector(`input[name="${gradeLevelName}"]:checked`)?.value || '';
        const section = document.querySelector(`input[name="${sectionName}"]:checked`)?.value || '';

        const rows = document.querySelectorAll(`#${tableId} .table-row`);
        rows.forEach(row => {
            const rowGradeLevel = row.getAttribute('data-grade-level');
            const rowSection = row.getAttribute('data-section');

            // Check if row matches selected filters
            const matchesGradeLevel = !gradeLevel || rowGradeLevel === gradeLevel;
            const matchesSection = !section || rowSection === section;

            // Show or hide row based on matches
            row.style.display = matchesGradeLevel && matchesSection ? '' : 'none';
        });
    }

    document.querySelectorAll('.toggle-filter-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Get the target filter container ID from the data-target attribute
            const filterContainerId = this.getAttribute('data-target');
            const filterContainer = document.getElementById(filterContainerId);

            // Toggle the 'open' class to show or hide the filter container
            filterContainer.classList.toggle('open');

            // Update button text based on the container's state
            if (filterContainer.classList.contains('open')) {
                this.textContent = 'Hide Filters'; // Change button text to 'Hide Filters'
            } else {
                this.textContent = 'Show Filters'; // Change button text to 'Show Filters'
            }
        });
    });




    function filterUserTable() {
        const roleInputs = document.querySelectorAll(`input[name="user-role-option"]`);
        const statusInputs = document.querySelectorAll(`input[name="user-status-option"]`);

        const selectedRole = Array.from(roleInputs).find(input => input.checked)?.nextElementSibling
            .innerText || 'All Roles';
        const selectedStatus = Array.from(statusInputs).find(input => input.checked)?.nextElementSibling
            .innerText || 'All Statuses';

        document.getElementById('user-role-selected').innerText = selectedRole;
        document.getElementById('user-status-selected').innerText = selectedStatus;

        const role = document.querySelector(`input[name="user-role-option"]:checked`)?.value || '';
        const status = document.querySelector(`input[name="user-status-option"]:checked`)?.value || '';

        const rows = document.querySelectorAll(`#table-content-users .table-row`);
        rows.forEach(row => {
            const rowRole = row.getAttribute('data-role'); // Data attribute for role
            const rowStatus = row.getAttribute('data-status'); // Data attribute for status

            const matchesRole = !role || rowRole === role;
            const matchesStatus = !status || rowStatus === status;

            // Show or hide row based on matches
            row.style.display = matchesRole && matchesStatus ? '' : 'none';
        });
    }

    // Initialize filters for users
    document.querySelectorAll(`input[name="user-role-option"], input[name="user-status-option"]`).forEach(
        input => {
            input.addEventListener('change', filterUserTable);
        });
});



// Toggle password visibility
document.querySelector('.toggle-password').addEventListener('click', function() {
    const passwordInput = document.querySelector('.password-input');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);

    // Toggle eye icon
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
});

function searchTable(searchBarId, tableContentId) {
    var input, filter, table, rows, i, j, txtValue, visible;
    input = document.getElementById(searchBarId);
    filter = input.value.trim().toLowerCase(); // Use trim to avoid leading/trailing spaces
    table = document.getElementById(tableContentId);
    rows = table.getElementsByClassName("table-row");

    for (i = 0; i < rows.length; i++) {
        visible = false;
        columns = rows[i].getElementsByClassName("table-data");

        for (j = 0; j < columns.length; j++) {
            txtValue = columns[j].textContent || columns[j].innerText;

            // Check if the txtValue can be parsed to an integer
            var numericValue = parseFloat(txtValue); // Use parseFloat for date as well
            var filterNumeric = parseFloat(filter); // Convert filter to a number for comparison

            // Check if filter matches a numeric value or text
            if (
                (txtValue.toLowerCase().includes(filter)) || // Text matching
                (txtValue === filter) || // Exact match for numbers
                (!isNaN(numericValue) && !isNaN(filterNumeric) && numericValue === filterNumeric) // Numeric matching
            ) {
                visible = true;
                break; // Stop checking once a match is found
            }
        }
        rows[i].style.display = visible ? "" : "none"; // Show or hide row
    }
}


document.addEventListener("DOMContentLoaded", function() {
    // Event listeners for search bars
    document.getElementById("search-bar-users").addEventListener("keyup", function() {
        searchTable("search-bar-users", "table-content-users");
    });


});

// Open Popup
const openPopup = document.getElementById('openPopup');
const popupForm = document.getElementById('popupForm');
const closePopup = document.getElementById('closePopup');

openPopup.addEventListener('click', () => {
    popupForm.style.display = 'flex';
});

// Close Popup
closePopup.addEventListener('click', () => {
    popupForm.style.display = 'none';
});

// Close Popup on Outside Click
window.addEventListener('click', (event) => {
    if (event.target === popupForm) {
        popupForm.style.display = 'none';
    }
});
</script>

<?php include_once('layouts/footer.php'); ?>