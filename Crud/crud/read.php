<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../auth/login.php");
    exit;
}

// Include database connection
require_once "../config/database.php";

// Delete record
if (isset($_GET["delete"]) && !empty(trim($_GET["delete"]))) {
    // Prepare a delete statement
    $sql = "DELETE FROM items WHERE id = ? AND user_id = ?";

    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "ii", $param_id, $param_user_id);

        // Set parameters
        $param_id = trim($_GET["delete"]);
        $param_user_id = $_SESSION["id"];

        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            // Records deleted successfully. Redirect to landing page
            header("location: read.php");
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    }

    // Close statement
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard -- D'Plato</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/custom.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">D'Plato Sales Management Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="read.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="create.php">Create Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Item List</h4>
                        <a href="create.php" class="btn btn-success btn-sm">Add New Item</a>
                    </div>
                    <div class="card-body">
                        <?php
                        // Attempt select query execution
                        $sql = "SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC";

                        if ($stmt = mysqli_prepare($conn, $sql)) {
                            // Bind variables to the prepared statement as parameters
                            mysqli_stmt_bind_param($stmt, "i", $param_user_id);

                            // Set parameters
                            $param_user_id = $_SESSION["id"];

                            // Attempt to execute the prepared statement
                            if (mysqli_stmt_execute($stmt)) {
                                $result = mysqli_stmt_get_result($stmt);

                                // Check if there are records
                                if (mysqli_num_rows($result) > 0) {
                                    echo '<div class="table-responsive">';
                                    echo '<table class="table table-bordered table-striped">';
                                    echo '<thead class="bg-light">';
                                    echo '<tr>';
                                    echo '<th>ID</th>';
                                    echo '<th>Product Type</th>';
                                    echo '<th>Product Name</th>';
                                    echo '<th>Quantity Sold</th>';
                                    echo '<th>Price</th>';
                                    echo '<th>Total Price</th>';
                                    echo '<th>Created</th>';
                                    echo '<th>Action</th>';
                                    echo '</tr>';
                                    echo '</thead>';
                                    echo '<tbody>';

                                    $grand_total = 0; 

                                    while ($row = mysqli_fetch_array($result)) {
                                        echo '<tr>';
                                        echo '<td>' . $row['id'] . '</td>';
                                        echo '<td>' . htmlspecialchars($row['Product_Type']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['Product_Name']) . '</td>';
                                        echo '<td>' . number_format($row['Quantity_Sold']) . '</td>';
                                        echo '<td>' . number_format($row['price'], 2) . '</td>';

                                        $total_price = $row['price'] * $row['Quantity_Sold'];
                                        $grand_total += $total_price; // Accumulate

                                        echo '<td>' . number_format($total_price, 2) . '</td>';   
                                        echo '<td>' . date('M d, Y', strtotime($row['created_at'])) . '</td>';
                                        echo '<td>';
                                        echo '<a href="update.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm btn-crud">Edit</a>';
                                        echo '<a href="read.php?delete=' . $row['id'] . '" class="btn btn-danger btn-sm btn-crud" onclick="return confirm(\'Are you sure you want to delete this item?\')">Delete</a>';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    echo '<tr>';
                                    echo '<td colspan="5" class="text-end"><strong>Total</strong></td>';
                                    echo '<td><strong>' . number_format($grand_total, 2) . '</strong></td>';
                                    echo '<td colspan="2"></td>';
                                    echo '</tr>';
                                    echo '</tbody>';
                                    echo '</table>';
                                    echo '</div>';
                                    // Free result set
                                    mysqli_free_result($result);
                                } else {
                                    echo '<div class="alert alert-info">No items found. <a href="create.php" class="alert-link">Add an item</a>.</div>';
                                }
                            } else {
                                echo '<div class="alert alert-danger">ERROR: Could not execute query: ' . mysqli_error($conn) . '</div>';
                            }
                        }

                        // Close connection
                        mysqli_close($conn);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>