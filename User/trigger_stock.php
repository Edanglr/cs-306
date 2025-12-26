<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

$message = "";
$products = [];

// 0) Ürünleri çek (dropdown için)
$resP = mysqli_query($conn, "SELECT Pid, Pname, Price, Stock FROM Product ORDER BY Pid ASC");
if ($resP) {
    while ($p = mysqli_fetch_assoc($resP)) {
        $products[] = $p;
    }
    mysqli_free_result($resP);
} else {
    $message = "Error fetching products: " . mysqli_error($conn);
}

// Formdan varsayılan değerler (sayfa ilk açıldığında)
$selected_pid = isset($_POST['pid']) ? (int)$_POST['pid'] : (isset($products[0]['Pid']) ? (int)$products[0]['Pid'] : 0);
$quantity_in  = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if (isset($_POST["insert"])) {

    // 1) Seçilen ürün var mı kontrol + fiyat/stock al
    $stmt = mysqli_prepare($conn, "SELECT Pid, Price, Stock FROM Product WHERE Pid = ?");
    mysqli_stmt_bind_param($stmt, "i", $selected_pid);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $prodRow = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);

    if (!$prodRow) {
        $message = "Error: Selected product not found.";
    } elseif ($quantity_in <= 0) {
        $message = "Error: Quantity must be >= 1.";
    } else {
        $pid = (int)$prodRow['Pid'];
        $price = (float)$prodRow['Price'];
        $stock = (int)$prodRow['Stock'];

        // (İsteğe bağlı) stok yetmezse insert yapma
        if ($stock < $quantity_in) {
            $message = "Error: Not enough stock. Current stock = $stock";
        } else {

            // 2) Sabit müşteri (demo)
            $cid = 1;

            $sqlCustomer = "INSERT INTO Customer (Cid, Cname, Address, Email)
                            VALUES (?, 'Test User', 'Test Address', 'test@test.com')
                            ON DUPLICATE KEY UPDATE Cname = Cname";
            $stmt = mysqli_prepare($conn, $sqlCustomer);
            mysqli_stmt_bind_param($stmt, "i", $cid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // 3) Yeni OrderID üret
            $res = mysqli_query($conn, "SELECT IFNULL(MAX(OrderID), 0) + 1 AS next_id FROM Orders");
            if (!$res) {
                $message = "Error getting next OrderID: " . mysqli_error($conn);
            } else {
                $row = mysqli_fetch_assoc($res);
                mysqli_free_result($res);
                $orderID = (int)$row["next_id"];

                // 4) Orders insert (FK için)
                $sqlOrder = "INSERT INTO Orders (OrderID, Cid, OrderDate, Total, Status)
                             VALUES (?, ?, NOW(), 0, 'Pending')";
                $stmt = mysqli_prepare($conn, $sqlOrder);
                mysqli_stmt_bind_param($stmt, "ii", $orderID, $cid);

                if (!mysqli_stmt_execute($stmt)) {
                    $message = "Error inserting into Orders: " . mysqli_error($conn);
                    mysqli_stmt_close($stmt);
                } else {
                    mysqli_stmt_close($stmt);

                    // 5) OrderDetails insert (trigger burada çalışır)
                    $sqlDetail = "INSERT INTO OrderDetails (OrderID, Pid, Quantity, Price)
                                  VALUES (?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $sqlDetail);
                    mysqli_stmt_bind_param($stmt, "iiid", $orderID, $pid, $quantity_in, $price);

                    if (mysqli_stmt_execute($stmt)) {
                        $message = "✅ Insert OK (OrderID=$orderID, Pid=$pid, Qty=$quantity_in). Stock trigger executed.";
                    } else {
                        $message = "Error inserting into OrderDetails: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($stmt);
                }
            }
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
            <label style="display:block; margin-bottom:6px; font-weight:600;">Select Product:</label>
            <select name="pid" class="input" style="width:100%; padding:10px; border-radius:10px; margin-bottom:12px;">
                <?php foreach ($products as $p): ?>
                    <?php
                        $pidOpt = (int)$p['Pid'];
                        $pname  = $p['Pname'] ?? '';
                        $pstock = (int)($p['Stock'] ?? 0);
                        $pprice = (float)($p['Price'] ?? 0);
                        $sel = ($pidOpt === $selected_pid) ? 'selected' : '';
                    ?>
                    <option value="<?= $pidOpt ?>" <?= $sel ?>>
                        <?= htmlspecialchars($pidOpt . " - " . $pname . " (Stock: $pstock, Price: $pprice)") ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label style="display:block; margin-bottom:6px; font-weight:600;">Quantity:</label>
            <input
                type="number"
                name="quantity"
                min="1"
                value="<?= htmlspecialchars((string)$quantity_in) ?>"
                class="input"
                style="width:100%; padding:10px; border-radius:10px; margin-bottom:12px;"
                required
            >

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
