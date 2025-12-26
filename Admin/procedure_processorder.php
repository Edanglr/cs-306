<?php
session_start();


if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../admin_login.php");
    exit;
}


require_once __DIR__ . "/../User/db.php";

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
    <title>Process Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-dark mb-5">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🔧 Admin Panel</a>
        <a href="../logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
    </div>
</nav>

<!-- CONTENT -->
<div class="container">

    <div class="card shadow mx-auto" style="max-width: 600px;">
        <div class="card-body p-4">

            <h3 class="mb-3 text-center">📦 Process Order</h3>

            <p class="text-muted text-center">
                Runs the <b>ProcessOrder</b> stored procedure for a selected <b>Pending Order</b>.
            </p>

            <!-- MESSAGE -->
            <?php if (!empty($message)) : ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- FORM -->
            <form method="post">

                <div class="mb-3">
                    <label class="form-label fw-bold">Select Order ID</label>

                    <select name="orderID" class="form-select" required>
                        <option value="">-- Select Pending Order --</option>
                        <?php foreach ($orders as $oid): ?>
                            <option value="<?php echo $oid; ?>">
                                Order #<?php echo $oid; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="run" class="btn btn-primary">
                        ▶ Run Procedure
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">
                        ← Back to Dashboard
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>
