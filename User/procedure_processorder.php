<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

$message = "";

if (isset($_POST["run"])) {
    $orderID = $_POST["orderID"];

    $sql = "CALL ProcessOrder($orderID)";

    if (mysqli_query($conn, $sql)) {
        $message = "ProcessOrder procedure executed successfully for OrderID = $orderID";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}

if ($orderID <= 0) {
    $message = "Invalid OrderID.";
} else {
    $sql = "CALL ProcessOrder($orderID)";
    if (mysqli_query($conn, $sql)) {
        $message = "ProcessOrder executed successfully for OrderID = $orderID";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Process Order Procedure</title>
</head>
<body>

<h2>Process Order</h2>

<p>
This page runs the <b>ProcessOrder</b> stored procedure for a given OrderID.
</p>

<form method="post">
    Order ID:
    <input type="number" name="orderID" required>
    <br><br>
    <input type="submit" name="run" value="Run Procedure">
</form>

<p><b><?php echo $message; ?></b></p>

<a href="index.php">Back to Home</a>

</body>
</html>
