<?php
session_start();
include "connect.php";
include "Includes/functions/functions.php";
include "Includes/templates/header.php";
include "Includes/templates/navbar.php";
?>
<!-- Appointment Page Stylesheet -->
<link rel="stylesheet" href="Design/css/appointment-page-style.css">

<!-- BOOKING APPOINTMENT SECTION -->

<section class="booking_section">
    <div class="container">

        <?php

        if (isset($_POST['submit_book_appointment_form']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            // Selected SERVICES
            $selected_services = $_POST['selected_services'];
            $_SESSION['selected_services'] = $selected_services;

            // Selected EMPLOYEE
            $selected_employee = $_POST['selected_employee'];
            $_SESSION['selected_employee'] = $selected_employee;

            // Selected DATE+TIME
            $selected_date_time = explode(' ', $_POST['desired_date_time']);
            $date_selected = $selected_date_time[0];
            $start_time = $date_selected . " " . $selected_date_time[1];
            $end_time = $date_selected . " " . $selected_date_time[2];
            $_SESSION['start_time'] = $start_time;
            $_SESSION['end_time'] = $end_time;

            // Client Details
            $client_first_name = test_input($_POST['client_first_name']);
            $client_last_name = test_input($_POST['client_last_name']);
            $client_phone_number = test_input($_POST['client_phone_number']);
            $client_email = test_input($_POST['client_email']);
            $_SESSION['client_first_name'] = $client_first_name;
            $_SESSION['client_last_name'] = $client_last_name;
            $_SESSION['client_phone_number'] = $client_phone_number;
            $_SESSION['client_email'] = $client_email;

            $payment_proof = $_FILES['payment_proof'];

            $upload_directory = 'uploads/payment_proofs/';
            $upload_file = $upload_directory . basename($payment_proof['name']);
            $upload_ok = 1;
            $image_file_type = strtolower(pathinfo($upload_file, PATHINFO_EXTENSION));

            // Check if file already exists
            if (file_exists($upload_file)) {
                echo "Sorry, file already exists.";
                $upload_ok = 0;
            }

            // Check file size
            if ($payment_proof['size'] > 500000) {
                echo "Sorry, your file is too large.";
                $upload_ok = 0;
            }

            // Allow certain file formats
            if ($image_file_type != "jpg" && $image_file_type != "png" && $image_file_type != "jpeg"
                && $image_file_type != "gif" ) {
                echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $upload_ok = 0;
            }

            // Check if $upload_ok is set to 0 by an error
            if ($upload_ok == 0) {
                echo "Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
            } else {
                if (move_uploaded_file($payment_proof['tmp_name'], $upload_file)) {
                    echo "The file ". htmlspecialchars(basename($payment_proof['name'])) . " has been uploaded.";
                } else {
                    echo "Sorry, there was an error uploading your file.";
                }
            }

            $con->beginTransaction();

            try {
                // Check if the client's email already exists in our database
                $stmtCheckClient = $con->prepare("SELECT * FROM clients WHERE client_email = ?");
                $stmtCheckClient->execute(array($client_email));
                $client_result = $stmtCheckClient->fetch();
                $client_count = $stmtCheckClient->rowCount();

                if ($client_count > 0) {
                    $client_id = $client_result["client_id"];
                } else {
                    $stmtClient = $con->prepare("INSERT INTO clients (first_name, last_name, phone_number, client_email, employee_id, kode_order, total_price, status, payment_proof) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmtClient->execute(array($client_first_name, $client_last_name, $client_phone_number, $client_email, $selected_employee, 'some_order_code', 0, 'pending', $upload_file));
                    $client_id = $con->lastInsertId();
                }

                // Insert appointment details into the appointments table
                $stmt_appointment = $con->prepare("INSERT INTO appointments (date_created, client_id, employee_id, start_time, end_time_expected) VALUES (?, ?, ?, ?, ?)");
                $stmt_appointment->execute(array(date("Y-m-d H:i:s"), $client_id, $selected_employee, $start_time, $end_time));
                $appointment_id = $con->lastInsertId();

                foreach ($selected_services as $service) {
                    $stmt = $con->prepare("INSERT INTO services_booked (appointment_id, service_id) VALUES (?, ?)");
                    $stmt->execute(array($appointment_id, $service));
                }

                $total_price = 0;
                foreach ($selected_services as $service) {
                    // Insert each service into the list_order table
                    $stmtListOrder = $con->prepare("INSERT INTO list_order (service_id, client_id, jumlah) VALUES (?, ?, ?)");
                    $stmtListOrder->execute(array($service, $client_id, 1));

                    // Get the service price
                    $stmtService = $con->prepare("SELECT service_price FROM services WHERE service_id = ?");
                    $stmtService->execute(array($service));
                    $service_price = $stmtService->fetchColumn();
                    $total_price += $service_price;
                }

                // Update the total price for the client
                $stmtUpdateClient = $con->prepare("UPDATE clients SET total_price = total_price + ? WHERE client_id = ?");
                $stmtUpdateClient->execute(array($total_price, $client_id));

                $_SESSION['total_price'] = $total_price;

                echo "<div class='alert alert-success'>";
                echo "Great! Your appointment has been created successfully.";
                echo "</div>";

                $con->commit();
            } catch (Exception $e) {
                $con->rollBack();
                echo "<div class='alert alert-danger'>";
                echo $e->getMessage();
                echo "</div>";
            }
        }

        ?>

        <!-- RESERVATION FORM -->
        <form method="post" id="appointment_form" action="appointment.php" enctype="multipart/form-data">

            <!-- SELECT SERVICE -->
            <div class="select_services_div tab_reservation" id="services_tab">
                <!-- ALERT MESSAGE -->
                <div class="alert alert-danger" role="alert" style="display: none">
                    Please, select at least one service!
                </div>

                <div class="text_header">
                    <span>
                        1. Choice of services
                    </span>
                </div>

                <!-- SERVICES TAB -->
                <div class="items_tab">
                    <?php
                    $stmt = $con->prepare("SELECT * FROM services");
                    $stmt->execute();
                    $rows = $stmt->fetchAll();

                    foreach ($rows as $row) {
                        echo "<div class='itemListElement'>";
                        echo "<div class='item_details'>";
                        echo "<div>";
                        echo $row['service_name'];
                        echo "</div>";
                        echo "<div class='item_select_part'>";
                        echo "<span class='service_duration_field'>";
                        echo $row['service_duration'] . " min";
                        echo "</span>";
                        echo "<div class='service_price_field'>";
                        echo "<span style='font-weight: bold;'>";
                        echo $row['service_price'];
                        echo "</span>";
                        echo "</div>";
                        ?>
                        <div class="select_item_bttn">
                            <div class="btn-group-toggle" data-toggle="buttons">
                                <label class="service_label item_label btn btn-secondary">
                                    <input type="checkbox" name="selected_services[]"
                                        value="<?php echo $row['service_id'] ?>" autocomplete="off">Select
                                </label>
                            </div>
                        </div>
                        <?php
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>

            <!-- SELECT EMPLOYEE -->
            <div class="select_employee_div tab_reservation" id="employees_tab">
                <!-- ALERT MESSAGE -->
                <div class="alert alert-danger" role="alert" style="display: none">
                    Please, select your employee!
                </div>

                <div class="text_header">
                    <span>
                        2. Choice of employee
                    </span>
                </div>

                <!-- EMPLOYEES TAB -->
                <div class="btn-group-toggle" data-toggle="buttons">
                    <div class="items_tab">
                        <?php
                        $stmt = $con->prepare("SELECT * FROM employees");
                        $stmt->execute();
                        $rows = $stmt->fetchAll();

                        foreach ($rows as $row) {
                            echo "<div class='itemListElement'>";
                            echo "<div class='item_details'>";
                            echo "<div>";
                            echo $row['first_name'] . " " . $row['last_name'];
                            echo "</div>";
                            echo "<div class='item_select_part'>";
                            ?>
                            <div class="select_item_bttn">
                                <label class="item_label btn btn-secondary active">
                                    <input type="radio" class="radio_employee_select" name="selected_employee"
                                        value="<?php echo $row['employee_id'] ?>">Select
                                </label>
                            </div>
                            <?php
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- SELECT DATE TIME -->
            <div class="select_date_time_div tab_reservation" id="calendar_tab">
                <!-- ALERT MESSAGE -->
                <div class="alert alert-danger" role="alert" style="display: none">
                    Please, select time!
                </div>

                <div class="text_header">
                    <span>
                        3. Choice of Date and Time
                    </span>
                </div>

                <div class="calendar_tab" style="overflow-x: auto;overflow-y: visible;" id="calendar_tab_in">
                    <div id="calendar_loading">
                        <img src="Design/images/ajax_loader_gif.gif"
                            style="display: block;margin-left: auto;margin-right: auto;">
                    </div>
                </div>
            </div>

            <!-- CLIENT DETAILS -->
            <div class="client_details_div tab_reservation" id="client_tab">
                <div class="text_header">
                    <span>
                        4. Client Details
                    </span>
                </div>

                <div>
                    <div class="form-group colum-row row">
                        <div class="col-sm-6">
                            <input type="text" name="client_first_name" id="client_first_name" class="form-control"
                                placeholder="First Name">
                            <span class="invalid-feedback">This field is required</span>
                        </div>
                        <div class="col-sm-6">
                            <input type="text" name="client_last_name" id="client_last_name" class="form-control"
                                placeholder="Last Name">
                            <span class="invalid-feedback">This field is required</span>
                        </div>
                        <div class="col-sm-6">
                            <input type="email" name="client_email" id="client_email" class="form-control"
                                placeholder="E-mail">
                            <span class="invalid-feedback">Invalid E-mail</span>
                        </div>
                        <div class="col-sm-6">
                            <input type="text" name="client_phone_number" id="client_phone_number" class="form-control"
                                placeholder="Phone number">
                            <span class="invalid-feedback">Invalid phone number</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PAYMENT DETAILS -->
            <div class="payment_details_div tab_reservation" id="payment_tab" style="display:none;">
                <div class="text_header">
                    <span>
                        5. Payment Details
                    </span>
                </div>
                <div>
                    <p>Client: <span id="client_name"></span></p>
                    <p>Appointment Time: <span id="appointment_time"></span></p>
                    <p>Total Price: <span id="total_price"></span></p>
                    <p>Selected Services: <span id="selected_services"></span></p>
                    <p>Transfer to: 0865131609 AN Khairul Huda (BNI)</p>
                    <div class="form-group colum-row row">
                        <div class="col-sm-12">
                            <input type="file" name="payment_proof" id="payment_proof" class="form-control">
                            <span class="invalid-feedback">This field is required</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NEXT AND PREVIOUS BUTTONS -->
            <div style="overflow:auto;padding: 30px 0px;">
                <div style="float:right;">
                    <input type="hidden" name="submit_book_appointment_form">
                    <button type="button" id="prevBtn" class="next_prev_buttons" style="background-color: #bbbbbb;"
                        onclick="nextPrev(-1)">Previous</button>
                    <button type="button" id="nextBtn" class="next_prev_buttons" onclick="nextPrev(1)">Next</button>
                    <button type="submit" id="submitBtn" class="next_prev_buttons" style="display:none;">Submit</button>
                </div>
            </div>

            <!-- Circles which indicates the steps of the form: -->
            <div style="text-align:center;margin-top:40px;">
                <span class="step"></span>
                <span class="step"></span>
                <span class="step"></span>
                <span class="step"></span>
                <span class="step"></span>
            </div>

        </form>
    </div>
</section>

<!-- FOOTER BOTTOM -->
<?php include "Includes/templates/footer.php"; ?>

<script>
    var currentTab = 0;
    showTab(currentTab);

    function showTab(n) {
        var x = document.getElementsByClassName("tab_reservation");
        x[n].style.display = "block";
        if (n == 0) {
            document.getElementById("prevBtn").style.display = "none";
        } else {
            document.getElementById("prevBtn").style.display = "inline";
        }
        if (n == (x.length - 1)) {
            document.getElementById("nextBtn").style.display = "none";
            document.getElementById("submitBtn").style.display = "inline";
        } else {
            document.getElementById("nextBtn").innerHTML = "Next";
            document.getElementById("submitBtn").style.display = "none";
        }
        fixStepIndicator(n);
    }

    function nextPrev(n) {
        var x = document.getElementsByClassName("tab_reservation");
        if (n == 1 && !validateForm()) return false;
        x[currentTab].style.display = "none";
        currentTab = currentTab + n;
        if (currentTab >= x.length) {
            document.getElementById("appointment_form").submit();
            return false;
        }
        showTab(currentTab);

        if (currentTab == 4) {
            var clientName = "<?php echo $_SESSION['client_first_name'] . ' ' . $_SESSION['client_last_name']; ?>";
            var appointmentTime = "<?php echo $_SESSION['start_time']; ?>";
            var totalPrice = "<?php echo $_SESSION['total_price']; ?>";
            var selectedServices = "<?php echo implode(', ', $_SESSION['selected_services']); ?>";

            document.getElementById('client_name').innerText = clientName;
            document.getElementById('appointment_time').innerText = appointmentTime;
            document.getElementById('total_price').innerText = totalPrice;
            document.getElementById('selected_services').innerText = selectedServices;
        }
    }

    function validateForm() {
        var x, y, i, valid = true;
        x = document.getElementsByClassName("tab_reservation");
        y = x[currentTab].getElementsByTagName("input");
        for (i = 0; i < y.length; i++) {
            if (y[i].value == "") {
                y[i].className += " invalid";
                valid = false;
            }
        }
        if (valid) {
            document.getElementsByClassName("step")[currentTab].className += " finish";
        }
        return valid;
    }

    function fixStepIndicator(n) {
        var i, x = document.getElementsByClassName("step");
        for (i = 0; i < x.length; i++) {
            x[i].className = x[i].className.replace(" active", "");
        }
        x[n].className += " active";
    }
</script>
