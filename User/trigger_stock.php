<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

$message = "";

if (isset($_POST["insert"])) {

    // Fixed product + values (must exist in Product)
    $pid = 101;
    $quantity = 1;
    $price = 1200.99;

    // Use a fixed existing customer (must exist in Customer)
    $cid = 1;

    // 1) Ensure the customer exists (safe no-op if already exists)
    $sqlCustomer = "INSERT INTO Customer (Cid, Cname, Address, Email)
                    VALUES (?, 'Test User', 'Test Address', 'test@test.com')
                    ON DUPLICATE KEY UPDATE Cname = Cname";
    $stmt = mysqli_prepare($conn, $sqlCustomer);
    mysqli_stmt_bind_param($stmt, "i", $cid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // 2) Pick a NEW OrderID each click to avoid duplicate (OrderID,Pid) primary key
    $res = mysqli_query($conn, "SELECT IFNULL(MAX(OrderID), 0) + 1 AS next_id FROM Orders");
    if (!$res) {
        $message = "Error getting next OrderID: " . mysqli_error($conn);
    } else {
        $row = mysqli_fetch_assoc($res);
        mysqli_free_result($res);
        $orderID = (int)$row["next_id"];

        // 3) Create the parent order row (required for FK)
        $sqlOrder = "INSERT INTO Orders (OrderID, Cid, OrderDate, Total, Status)
                     VALUES (?, ?, NOW(), 0, 'Pending')";
        $stmt = mysqli_prepare($conn, $sqlOrder);
        mysqli_stmt_bind_param($stmt, "ii", $orderID, $cid);

        if (!mysqli_stmt_execute($stmt)) {
            $message = "Error inserting into Orders: " . mysqli_error($conn);
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);

            // 4) Insert order detail (this fires AFTER INSERT trigger to update stock)
            $sqlDetail = "INSERT INTO OrderDetails (OrderID, Pid, Quantity, Price)
                          VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sqlDetail);
            mysqli_stmt_bind_param($stmt, "iiid", $orderID, $pid, $quantity, $price);

            if (mysqli_stmt_execute($stmt)) {
                $message = "OrderDetails inserted successfully (OrderID=$orderID, Pid=$pid). Stock trigger executed.";
            } else {
                $message = "Error inserting into OrderDetails: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../assets/style.css">

    <title>Stock Update Trigger</title>
</head>

<body>
    <div class="container">
        <div class="card">
            <h2>Stock Update Trigger</h2>
            <p class="subtitle">
                Inserts into <b>OrderDetails</b>. AFTER INSERT trigger updates product stock automatically.
            </p>

            <form method="post">
                <input class="btn" type="submit" name="insert" value="Insert Order Detail">
            </form>

            <?php if (!empty($message)) : ?>
                <div class="notice <?php echo (stripos($message,'error') !== false) ? 'error' : 'success'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <br>
            <a class="btn secondary" href="index.php">Back to Home</a>
        </div>
    </div>
</body>


</html>
