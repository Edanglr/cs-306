<?php
session_start();
// Admin girişi kontrolü
if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: ../admin_login.php");
    exit;
}

require_once __DIR__ . "/../User/vendor/autoload.php"; 

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

$message_status = "";
$ticket = null;

// ID Kontrolü
if (!isset($_GET['id'])) {
    die("Ticket ID is missing.");
}

$ticket_id = $_GET['id'];

try {
    // ⚠️ ŞİFRENİ YAZ
    $uri = "mongodb+srv://cs306user:cs306proj123@cluster0.h4jv2qv.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0";
    $client = new Client($uri, [], ['driver' => ['tlsAllowInvalidCertificates' => true]]);
    $collection = $client->cs306->tickets;

    // --- İŞLEM 1: CEVAP VERME ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply_ticket'])) {
        $admin_response = $_POST['admin_response'];
        if (!empty($admin_response)) {
            $collection->updateOne(
                ['_id' => new ObjectId($ticket_id)],
                ['$set' => ['admin_reply' => $admin_response, 'status' => 'Answered']]
            );
            $message_status = "<div class='alert alert-success'>Reply sent successfully!</div>";
        }
    }

    // --- İŞLEM 2: ÇÖZÜLDÜ İŞARETLEME (RESOLVE) ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resolve_ticket'])) {
        // Status 'Resolved' olunca ana listeden kaybolacak
        $collection->updateOne(
            ['_id' => new ObjectId($ticket_id)],
            ['$set' => ['status' => 'Resolved']]
        );
        // Listeye geri yönlendir
        header("Location: admin_tickets.php");
        exit;
    }

    // Bileti Getir
    $ticket = $collection->findOne(['_id' => new ObjectId($ticket_id)]);

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

if (!$ticket) {
    die("Ticket not found!");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <a href="admin_tickets.php" class="btn btn-outline-secondary mb-3">← Back to List</a>
    
    <?php echo $message_status; ?>

    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Ticket Details</h4>
            <span class="badge bg-light text-primary"><?php echo $ticket['status']; ?></span>
        </div>
        <div class="card-body">
            
            <div class="mb-4">
                <h5 class="text-primary"><?php echo htmlspecialchars($ticket['subject']); ?></h5>
                <p class="text-muted mb-1">Created by: <strong><?php echo htmlspecialchars($ticket['username'] ?? 'Anonymous'); ?></strong></p>
                <div class="p-3 bg-light border rounded">
                    <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
                </div>
            </div>

            <?php if (isset($ticket['admin_reply'])): ?>
                <div class="mb-4">
                    <label class="fw-bold text-success">Previous Admin Reply:</label>
                    <div class="alert alert-success">
                        <?php echo nl2br(htmlspecialchars($ticket['admin_reply'])); ?>
                    </div>
                </div>
            <?php endif; ?>

            <hr>

            <div class="row">
                <div class="col-md-8">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Submit a Reply:</label>
                            <textarea name="admin_response" class="form-control" rows="3" placeholder="Type your answer here..." required></textarea>
                        </div>
                        <button type="submit" name="reply_ticket" class="btn btn-primary">Send Reply</button>
                    </form>
                </div>

                <div class="col-md-4 text-end border-start">
                    <div class="p-3">
                        <label class="form-label d-block fw-bold text-danger">Is this issue solved?</label>
                        <p class="small text-muted">Marking as resolved will hide this ticket from the active list.</p>
                        
                        <form method="POST">
                            <button type="submit" name="resolve_ticket" class="btn btn-success w-100 py-2" onclick="return confirm('Are you sure you want to close this ticket?');">
                                ✅ Mark as Resolved
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>