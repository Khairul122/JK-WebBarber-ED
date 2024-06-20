<?php
session_start();

// Page Title
$pageTitle = 'Clients';

// Includes
include 'connect.php';
include 'Includes/functions/functions.php';
include 'Includes/templates/header.php';
date_default_timezone_set('Asia/Jakarta');

// Check if the user is already logged in
if (isset($_SESSION['username_barbershop_Xw211qAAsq4']) && isset($_SESSION['password_barbershop_Xw211qAAsq4'])) {
    ?>
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Clients</h1>
            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-download fa-sm text-white-50"></i>
                Generate Report
            </a>
        </div>

        <!-- Clients Table -->
        <?php
        try {
            $stmt = $con->prepare("
                SELECT clients.*, 
                       IFNULL(SUM(services.service_price * list_order.jumlah), 0) AS total_price, 
                       employees.first_name AS employee_first_name, 
                       employees.last_name AS employee_last_name 
                FROM clients
                LEFT JOIN employees ON employees.employee_id = clients.employee_id
                LEFT JOIN list_order ON list_order.client_id = clients.client_id
                LEFT JOIN services ON services.service_id = list_order.service_id
                GROUP BY clients.client_id, employees.first_name, employees.last_name
            ");
            $stmt->execute();
            $rows_clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$rows_clients) {
                echo "<div class='alert alert-warning'>No clients found.</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
        ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Clients</h6>
            </div>
            <div class="card-body">
                <!-- Clients Table -->
                <div class="table-responsive">
                    <table class="table table-hover" style="bold; color: black">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">First Name</th>
                                <th scope="col">Last Name</th>
                                <th scope="col">Total Price</th>
                                <th scope="col">Employee</th>
                                <th scope="col">Status</th>
                                <th scope="col">Phone Number</th>
                                <th scope="col">E-mail</th>
                                <th scope="col">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($rows_clients as $client) {
                                echo "<tr>";
                                echo "<td>" . $no++ . "</td>";
                                echo "<td>" . htmlspecialchars($client['first_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($client['last_name']) . "</td>";
                                echo "<td>" . htmlspecialchars(number_format($client['total_price'])) . "</td>";
                                echo "<td>" . htmlspecialchars($client['employee_first_name'] . " " . $client['employee_last_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($client['status']) . "</td>";
                                echo "<td>" . htmlspecialchars($client['phone_number']) . "</td>";
                                echo "<td>" . htmlspecialchars($client['client_email']) . "</td>";
                                echo "<td>";
                                ?>
                                <!-- DELETE & EDIT BUTTONS -->
                                <ul class="list-inline">
                                    <li class="list-inline-item" data-toggle="tooltip" title="Edit">
                                        <button class="btn btn-warning btn-sm rounded-0" type="button" data-toggle="modal"
                                            data-target="#edit_<?php echo $client['client_id']; ?>" data-placement="top">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <!-- EDIT Modal -->
                                        <div class="modal fade" id="edit_<?php echo $client['client_id']; ?>" tabindex="-1" role="dialog"
                                            aria-labelledby="edit_<?php echo $client['client_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">Edit Client</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <!-- Form for editing client details -->
                                                        <!-- Example input fields, adjust as per your needs -->
                                                        <div class="form-group">
                                                            <label for="edit_first_name">First Name</label>
                                                            <input type="text" class="form-control" id="edit_first_name"
                                                                value="<?php echo htmlspecialchars($client['first_name']); ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="edit_last_name">Last Name</label>
                                                            <input type="text" class="form-control" id="edit_last_name"
                                                                value="<?php echo htmlspecialchars($client['last_name']); ?>">
                                                        </div>
                                                        <!-- More fields can be added here -->
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cancel</button>
                                                        <button type="button" class="btn btn-success edit_client_btn"
                                                            data-id="<?php echo $client['client_id']; ?>">Save</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-inline-item" data-toggle="tooltip" title="Delete">
                                        <button class="btn btn-danger btn-sm rounded-0" type="button" data-toggle="modal"
                                            data-target="#delete_<?php echo $client['client_id']; ?>" data-placement="top">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="delete_<?php echo $client['client_id']; ?>" tabindex="-1" role="dialog"
                                            aria-labelledby="delete_<?php echo $client['client_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">Delete Client</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete this client "<?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?>"?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cancel</button>
                                                        <button type="button" class="btn btn-danger delete_client_btn"
                                                            data-id="<?php echo $client['client_id']; ?>">Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <?php
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
    // Include Footer
    include 'Includes/templates/footer.php';
} else {
    header('Location: login.php');
    exit();
}
?>
