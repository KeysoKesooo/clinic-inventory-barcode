<?php
$page_title = 'All Categories';
require_once('includes/load.php');
page_require_level(2);

$all_categories = find_all('categories');

// Add Category
if(isset($_POST['add_cat'])) {
    $req_field = array('categorie-name');
    validate_fields($req_field);
    $cat_name = remove_junk($db->escape($_POST['categorie-name']));
    if(empty($errors)) {
        $sql = "INSERT INTO categories (name) VALUES ('{$cat_name}')";
        if($db->query($sql)) {
            $session->msg("s", "Successfully Added New Category");
            redirect('categorie.php', false);
        } else {
            $session->msg("d", "Sorry Failed to insert.");
            redirect('categorie.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('categorie.php', false);
    }
}

// Edit Category
if (isset($_POST['edit_cat'])) {
    $cat_id = (int)$db->escape($_POST['cat_id']);
    $cat_name = remove_junk($db->escape($_POST['categorie-name']));
    $req_fields = array('categorie-name');
    validate_fields($req_fields);

    if (empty($errors)) {
        $sql = "UPDATE categories SET name = '{$cat_name}' WHERE id = '{$cat_id}'";
        $result = $db->query($sql);

        if ($result && $db->affected_rows() === 1) {
            $session->msg("s", "Category updated successfully.");
            redirect('categorie.php', false);
        } else {
            $session->msg("d", "Failed to update category or no changes made.");
            redirect('categorie.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('categorie.php', false);
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

<body>

    <div class="row">
        <div class="col-md-12">
            <?php echo display_msg($msg); ?>
        </div>
    </div>

    <div id="manageCategories" class="content-section active">
        <div class="user-container">
            <div class="action-buttons-container">
                <div class="search-bar-container">
                    <input type="text" id="search-bar-categories" class="search-bar" placeholder="Search categories...">
                </div>

                <a class="export_button" id="download-categories-btn">
                    <i class="fa-solid fa-download"></i>
                    <span class="export_button__text">Export</span>
                </a>

                <!-- Categories Import Popup -->
                <div id="categoriesPopupForm" class="popup-form">
                    <div class="editpopup_form_area">
                        <span id="closeCategoriesPopup" class="close-btn">&times;</span>
                        <form method="post" action="categories.php" enctype="multipart/form-data">
                            <div class="editpopup_form_group">
                                <label class="editpopup_sub_title" for="categories_csv_file">Choose CSV File</label>
                                <input type="file" name="categories_csv_file" class="editpopup_form_style" required>
                            </div>
                            <div>
                                <button type="submit" name="import_categories" class="editpopup_btn"
                                    style="margin-left: 180px">Import Categories</button>
                            </div>
                        </form>
                    </div>
                </div>

                <a class="add_button" href="#" data-toggle="modal" data-target="#addCategoryModal">
                    <svg aria-hidden="true" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg" class="add_button__icon">
                        <path stroke-width="2" stroke="#ffffff"
                            d="M13.5 3H12H8C6.34315 3 5 4.34315 5 6V18C5 19.6569 6.34315 21 8 21H11M13.5 3L19 8.625M13.5 3V7.625C13.5 8.17728 13.9477 8.625 14.5 8.625H19M19 8.625V11.8125"
                            stroke-linejoin="round" stroke-linecap="round"></path>
                        <path stroke-linejoin="round" stroke-linecap="round" stroke-width="2" stroke="#ffffff"
                            d="M17 15V18M17 21V18M17 18H14M17 18H20"></path>
                    </svg>
                    <span class="add_button__text">Add Category</span>
                </a>
            </div>

            <!-- Categories management table -->
            <div class="table">
                <div class="table-header">
                    <div class="header__item">No</div>
                    <div class="header__item">Category Name</div>
                    <div class="header__item">Actions</div>
                </div>

                <div class="table-content" id="table-content-categories">
                    <?php foreach ($all_categories as $cat): ?>
                    <div class="table-row">
                        <div class="table-data"><?php echo count_id();?></div>
                        <div class="table-data"><?php echo remove_junk(ucfirst($cat['name'])); ?></div>
                        <div class="table-data">
                            <button type="button" class="btn btn-warning edit-btn"
                                data-id="<?php echo (int)$cat['id']; ?>"
                                data-name="<?php echo remove_junk(ucfirst($cat['name'])); ?>" title="Edit">
                                <i class="glyphicon glyphicon-pencil"></i>
                            </button>

                            <a href="delete_categorie.php?id=<?php echo (int)$cat['id'];?>" class="btn btn-danger"
                                onclick="return confirmDelete(event)" data-toggle="tooltip" title="Remove">
                                <i class="glyphicon glyphicon-trash"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>



    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="container">
                <form method="post" action="categorie.php" class="form_area">
                    <span class="close-modal" data-dismiss="modal">&times;</span>
                    <div class="title">Edit Category</div>
                    <input type="hidden" name="cat_id" id="modal-cat-id" value="">

                    <div class="form_group">
                        <label for="categorie-name">Category Name</label>
                        <input class="form_style" type="text" name="categorie-name" id="edit-category-name" required>
                    </div>

                    <button type="submit" name="edit_cat" class="form_btn">Update</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="container">
                <form method="post" action="categorie.php" class="form_area">
                    <span class="close-modal" data-dismiss="modal">&times;</span>
                    <div class="title">Add New Category</div>

                    <div class="form_group">
                        <label for="category-name">Category Name</label>
                        <input class="form_style" type="text" name="categorie-name" id="category-name"
                            placeholder="Category Name" required>
                    </div>

                    <button type="submit" name="add_cat" class="form_btn">Add Category</button>
                </form>
            </div>
        </div>
    </div>


    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('modal-cat-id').value = btn.dataset.id;
                document.getElementById('edit-category-name').value = btn.dataset.name;
                $('#editCategoryModal').modal('show');
            });
        });
    });



    document.addEventListener('DOMContentLoaded', function() {
        // Add click event to download button
        document.getElementById('download-categories-btn').addEventListener('click', downloadFilteredData);

        function downloadFilteredData() {
            // Get all visible rows (filtered rows)
            const visibleRows = document.querySelectorAll('.table-row:not([style*="display: none"])');

            // Prepare CSV content
            let csvContent = "No.,Category Name\n";

            visibleRows.forEach(row => {
                const columns = row.querySelectorAll('.table-data');
                const rowData = [
                    columns[0].textContent.trim(),
                    columns[1].textContent.trim()
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
            link.setAttribute('download', 'filtered_categories.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    });


    document.addEventListener('DOMContentLoaded', () => {
        const headers = document.querySelectorAll('.header__item');
        const tableContent = document.getElementById('table-content-categories');

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

            const rows = document.querySelectorAll(`#table-content-categories .table-row`);
            rows.forEach(row => {
                const rowRole = row.getAttribute('data-role'); // Data attribute for role
                const rowStatus = row.getAttribute('data-status'); // Data attribute for status

                const matchesRole = !role || rowRole === role;
                const matchesStatus = !status || rowStatus === status;

                // Show or hide row based on matches
                row.style.display = matchesRole && matchesStatus ? '' : 'none';
            });
        }

        // Initialize filters for categories
        document.querySelectorAll(`input[name="user-role-option"], input[name="user-status-option"]`).forEach(
            input => {
                input.addEventListener('change', filterUserTable);
            });
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
                    (!isNaN(numericValue) && !isNaN(filterNumeric) && numericValue ===
                        filterNumeric) // Numeric matching
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
        document.getElementById("search-bar-categories").addEventListener("keyup", function() {
            searchTable("search-bar-categories", "table-content-categories");
        });


    });
    </script>
    <?php include_once('layouts/footer.php'); ?>