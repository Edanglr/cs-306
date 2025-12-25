<?php
session_start();
// Admin girişi yapılmış mı kontrol et
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

<nav class="navbar navbar-dark bg-dark mb-5">
  <div class="container">
    <a class="navbar-brand" href="#">🔧 Admin Panel</a>
    <a href="../logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
  </div>
</nav>

<div class="container text-center">
    <h1 class="display-4">Welcome, Administrator</h1>
    <p class="lead mb-5">Manage system settings and user tickets.</p>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow p-5">
                <h3>🎫 Ticket Management</h3>
                <p>View user complaints and reply to them.</p>
                <a href="admin_tickets.php" class="btn btn-primary btn-lg">Manage Tickets</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>