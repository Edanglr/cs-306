<?php
session_start();


require_once "db.php"; 

error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', 0);

$message = "";
$message_type = "success";

if (isset($_POST["run"])) {
    $orderID = (int) $_POST["orderID"];

    if ($orderID <= 0) {
        $message = "Invalid Order ID.";
        $message_type = "danger";
    } else {
        // Stored Procedure Çağrısı
        $sql = "CALL ProcessOrder(?)";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $orderID);

            if (mysqli_stmt_execute($stmt)) {
                $message = "ProcessOrder procedure executed successfully for OrderID = $orderID.";
                $message_type = "success";
            } else {
                $message = "Error executing procedure: " . mysqli_stmt_error($stmt);
                $message_type = "danger";
            }

            mysqli_stmt_close($stmt);
        } else {
            $message = "Failed to prepare procedure call.";
            $message_type = "danger";
        }
    }
}

/* =========================
   GET PENDING ORDERS (DROPDOWN)
   ========================= */
// Veritabanından Status = 'Pending' olan siparişleri çekiyoruz
$orders = [];
$res = mysqli_query($conn, "SELECT OrderID FROM Orders WHERE Status = 'Pending'");

if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $orders[] = $row["OrderID"];
    }
    mysqli_free_result($res);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Process Order (Stored Procedure)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* User tarafı için biraz daha farklı bir renk tonu kullanılabilir */
        .navbar-custom { background-color: #2c3e50; }
    </style>
</head>

<body class="bg-light">

<nav class="navbar navbar-dark navbar-custom mb-5">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🏠 CS306 Project - User Side</a>
        <a href="index.php" class="btn btn-sm btn-outline-light">Home</a>
    </div>
</nav>

<div class="container">

    <div class="card shadow mx-auto" style="max-width: 600px;">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 text-center">Stored Procedure Demo</h4>
        </div>
        <div class="card-body p-4">

            <h3 class="mb-3 text-center">📦 Process Order</h3>

            <p class="text-muted text-center">
                This page demonstrates the <b>ProcessOrder</b> stored procedure. 
                <br><small>It selects a 'Pending' order, updates its status to 'Shipped', and decreases the product stock quantity automatically.</small>
            </p>

            <?php if (!empty($message)) : ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="post">

                <div class="mb-3">
                    <label class="form-label fw-bold">Select Order ID (Pending Only)</label>

                    <select name="orderID" class="form-select" required>
                        <option value="">-- Select a Pending Order --</option>
                        <?php if (empty($orders)): ?>
                            <option value="" disabled>No pending orders found.</option>
                        <?php else: ?>
                            <?php foreach ($orders as $oid): ?>
                                <option value="<?php echo $oid; ?>">
                                    Order #<?php echo $oid; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="run" class="btn btn-primary btn-lg">
                        ▶ Run ProcessOrder Procedure
                    </button>
                    
                    <a href="index.php" class="btn btn-outline-secondary">
                        ← Back to Homepage
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>