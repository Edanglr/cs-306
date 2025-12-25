<?php
// Hataları gör
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Admin girişi kontrolü
if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: ../admin_login.php");
    exit;
}

require_once __DIR__ . "/../User/vendor/autoload.php"; // Path doğru

use MongoDB\Client;

try {
    // ⚠️ ŞİFRENİ YAZ
    $uri = "mongodb+srv://cs306user:cs306proj123@cluster0.h4jv2qv.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0";
    $client = new Client($uri, [], ['driver' => ['tlsAllowInvalidCertificates' => true]]);
    $collection = $client->cs306->tickets;

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Active Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="index.php">🔧 Admin Panel</a>
    <a href="../logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
  </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Active Ticket List</h2>
    </div>
    <p class="text-muted">Only active (unresolved) tickets are shown here.</p>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-secondary">
                    <tr>
                        <th>User</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // FİLTRE: Sadece 'Resolved' OLMAYANLARI getir ($ne = Not Equal)
                    // Böylece çözülenler listeden kaybolur.
                    $filter = ['status' => ['$ne' => 'Resolved']];
                    $options = ['sort' => ['created_at' => -1]];
                    
                    $cursor = $collection->find($filter, $options);
                    
                    $count = 0;
                    foreach ($cursor as $ticket) {
                        $count++;
                        $id = (string)$ticket['_id'];
                        $user = htmlspecialchars($ticket['username'] ?? 'Anonymous');
                        $subj = htmlspecialchars($ticket['subject'] ?? 'No Subject');
                        $status = $ticket['status'] ?? 'Open';
                        
                        // Durum Rengi
                        $badge = ($status == 'Open') ? 'bg-danger' : 'bg-warning text-dark';

                        echo "<tr>";
                        echo "<td>$user</td>";
                        echo "<td>$subj</td>";
                        echo "<td><span class='badge $badge'>$status</span></td>";
                        echo "<td>
                                <a href='admin_ticket_detail.php?id=$id' class='btn btn-primary btn-sm'>
                                    Manage Ticket
                                </a>
                              </td>";
                        echo "</tr>";
                    }

                    if ($count == 0) {
                        echo "<tr><td colspan='4' class='text-center py-4'>No active tickets found! Good job. 🎉</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>