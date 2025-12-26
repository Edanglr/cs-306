<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-dark mb-5">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">🔧 Admin Panel</a>
        <a href="../logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
    </div>
</nav>

<!-- MAIN CONTENT -->
<div class="container text-center">
    <h1 class="display-5 fw-bold mb-2">Welcome, Administrator</h1>
    <p class="lead mb-5 text-muted">Manage tickets, orders, and system operations.</p>

    <div class="row justify-content-center g-4">

        <!-- Ticket Management -->
        <div class="col-md-5">
            <div class="card shadow h-100">
                <div class="card-body p-4">
                    <h3 class="mb-3">🎫 Ticket Management</h3>
                    <p class="text-muted">
                        View user complaints, reply to tickets, and mark them as resolved.
                    </p>
                    <a href="admin_tickets.php" class="btn btn-primary btn-lg w-100">
                        Manage Tickets
                    </a>
                </div>
            </div>
        </div>

        <!-- Order Processing -->
        <div class="col-md-5">
            <div class="card shadow h-100">
                <div class="card-body p-4">
                    <h3 class="mb-3">📦 Order Processing</h3>
                    <p class="text-muted">
                        Run stored procedures such as <b>ProcessOrder</b> for orders.
                    </p>
                    <a href="procedure_processorder.php" class="btn btn-success btn-lg w-100">
                        Process Orders
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
