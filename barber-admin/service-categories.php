<?php
session_start();

//Page Title
$pageTitle = 'Service Categories';

//Includes
include 'connect.php';
include 'Includes/functions/functions.php';
include 'Includes/templates/header.php';

//Check If user is already logged in
if (isset($_SESSION['username_barbershop_Xw211qAAsq4']) && isset($_SESSION['password_barbershop_Xw211qAAsq4'])) {
    ?>
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Service Categories Table -->
        <?php
        $stmt = $con->prepare("SELECT * FROM service_categories");
        $stmt->execute();
        $rows_categories = $stmt->fetchAll();
        ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Service Categories</h6>
            </div>
            <div class="card-body">

                <!-- ADD NEW CATEGORY BUTTON -->
                <button class="btn btn-primary btn-sm" style="margin-bottom: 10px;" type="button" data-toggle="modal"
                    data-target="#add_new_category" data-placement="top">
                    <i class="fa fa-plus"></i>
                    Add Category
                </button>

                <!-- Add New Category Modal -->
                <div class="modal fade" id="add_new_category" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Category</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="category_name">Category name</label>
                                    <input type="text" id="category_name_input" class="form-control"
                                        placeholder="Category Name" name="category_name">
                                    <div class="invalid-feedback" id="required_category_name" style="display: none;">
                                        Category name is required!
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-info" id="add_category_bttn">Add Category</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col" style="font-weight: bold; color: black;">No</th>
                                <th scope="col" style="font-weight: bold; color: black;">Category Name</th>
                                <th scope="col" style="font-weight: bold; color: black;">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1; // Inisialisasi variabel nomor urut
                            foreach ($rows_categories as $category) {
                                echo "<tr style='color: black;'>";
                                echo "<td>" . $no++ . "</td>"; // Cetak dan tambahkan nomor urut
                                echo "<td>{$category['category_name']}</td>";
                                echo "<td>";
                                if (strtolower($category["category_name"]) != "uncategorized") {
                                    $delete_data = "delete_" . $category["category_id"];
                                    $edit_data = "edit_" . $category["category_id"];
                                    ?>
                                    <!-- DELETE & EDIT BUTTONS -->
                                    <ul class="list-inline">
                                        <li class="list-inline-item" data-toggle="tooltip" title="Edit">
                                            <button class="btn btn-warning btn-sm rounded-0" type="button" data-toggle="modal"
                                                data-target="#<?php echo $edit_data; ?>" data-placement="top">
                                                <i class="fa fa-edit"></i>
                                            </button>

                                            <!-- EDIT Modal -->
                                            <div class="modal fade" id="<?php echo $edit_data; ?>" tabindex="-1" role="dialog"
                                                aria-labelledby="<?php echo $edit_data; ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="exampleModalLabel">Edit Category</h5>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label for="category_name">Category Name</label>
                                                                <input type="text" class="form-control"
                                                                    id="<?php echo "input_category_name_" . $category["category_id"]; ?>"
                                                                    value="<?php echo $category["category_name"]; ?>">
                                                                <div class="invalid-feedback"
                                                                    id="<?php echo "invalid_input_" . $category["category_id"]; ?>">
                                                                    Category name is required.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">Cancel</button>
                                                            <button type="button" data-id="<?php echo $category['category_id']; ?>"
                                                                class="btn btn-success edit_category_bttn">Save</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </li>
                                        <li class="list-inline-item" data-toggle="tooltip" title="Delete">
                                            <button class="btn btn-danger btn-sm rounded-0" type="button" data-toggle="modal"
                                                data-target="#<?php echo $delete_data; ?>" data-placement="top">
                                                <i class="fa fa-trash"></i>
                                            </button>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="<?php echo $delete_data; ?>" tabindex="-1" role="dialog"
                                                aria-labelledby="<?php echo $delete_data; ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="exampleModalLabel">Delete Category</h5>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Are you sure you want to delete this category
                                                            "<?php echo $category['category_name']; ?>"?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">Cancel</button>
                                                            <button type="button" data-id="<?php echo $category['category_id']; ?>"
                                                                class="btn btn-danger delete_category_bttn">Delete</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    <?php
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php
    //Include Footer
    include 'Includes/templates/footer.php';
} else {
    header('Location: login.php');
    exit();
}
?>