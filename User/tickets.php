<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . "/vendor/autoload.php"; // Path doğru

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

$current_user = $_SESSION['username'];
$message_status = ""; 

try {
    // ⚠️ ŞİFRENİ YAZ
    $uri = "mongodb+srv://cs306user:cs306proj123@cluster0.h4jv2qv.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0";
    $client = new Client($uri, [], ['driver' => ['tlsAllowInvalidCertificates' => true]]);
    $collection = $client->cs306->tickets;

    // YENİ BİLET OLUŞTURMA
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_ticket'])) {
        $subject = $_POST['subject'];
        $message = $_POST['message'];
        
        if (!empty($subject) && !empty($message)) {
            $collection->insertOne([
                'username' => $current_user,
                'subject'  => $subject,
                'message'  => $message,
                'status'   => 'Open', 
                'created_at' => new UTCDateTime(),
                'comments' => [] // Yorumlar için boş dizi başlatıyoruz
            ]);
            $message_status = "<div class='alert alert-success'>✅ Ticket created successfully!</div>";
        }
    }

} catch (Exception $e) {
    $message_status = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="index.php">CS306 Support</a>
    <div class="d-flex text-white align-items-center">
        <span class="me-3">User: <strong><?php echo htmlspecialchars($current_user); ?></strong></span>
        <a href="../logout.php" class="btn btn-sm btn-light text-primary">Logout</a>
    </div>
  </div>
</nav>

<div class="container">
    <?php echo $message_status; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">Create Ticket</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label>Subject</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Message</label>
                            <textarea name="message" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" name="create_ticket" class="btn btn-primary w-100">Submit</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">My Active Tickets</div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (isset($collection)) {
                                $filter = ['username' => $current_user, 'status' => ['$ne' => 'Resolved']];
                                $cursor = $collection->find($filter, ['sort' => ['created_at' => -1]]);
                                
                                foreach ($cursor as $ticket) {
                                    $id = (string)$ticket['_id'];
                                    $subj = htmlspecialchars($ticket['subject']);
                                    $status = $ticket['status'];
                                    $badge = ($status == 'Open') ? 'bg-danger' : 'bg-success';

                                    echo "<tr>";
                                    echo "<td>$subj</td>";
                                    echo "<td><span class='badge $badge'>$status</span></td>";
                                    // DETAY BUTONU
                                    echo "<td><a href='ticket_detail.php?id=$id' class='btn btn-sm btn-outline-primary'>View & Reply</a></td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>