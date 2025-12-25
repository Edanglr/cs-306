<?php
session_start();
if (!isset($_SESSION['loggedin'])) { header("Location: ../login.php"); exit; }

require_once __DIR__ . "/vendor/autoload.php";

use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

if (!isset($_GET['id'])) { die("Invalid ID"); }

$ticket_id = $_GET['id'];
$current_user = $_SESSION['username'];
$message_status = "";

try {
    // ⚠️ ŞİFRENİ YAZ
    $uri = "mongodb+srv://cs306user:cs306proj123@cluster0.h4jv2qv.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0";
    $client = new Client($uri, [], ['driver' => ['tlsAllowInvalidCertificates' => true]]);
    $collection = $client->cs306->tickets;

    // --- YORUM EKLEME İŞLEMİ ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_comment'])) {
        $comment_text = $_POST['comment_text'];
        
        if (!empty($comment_text)) {
            // MongoDB'de diziye eleman eklemek için $push kullanılır
            $collection->updateOne(
                ['_id' => new ObjectId($ticket_id)],
                ['$push' => [
                    'comments' => [
                        'user' => $current_user,
                        'text' => $comment_text,
                        'date' => new UTCDateTime()
                    ]
                ]]
            );
            $message_status = "<div class='alert alert-success'>Comment added!</div>";
        }
    }

    // Bileti Getir (Sadece bu kullanıcıya aitse)
    $ticket = $collection->findOne([
        '_id' => new ObjectId($ticket_id),
        'username' => $current_user
    ]);

    if (!$ticket) { die("Ticket not found or access denied."); }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
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
    <a href="tickets.php" class="btn btn-outline-secondary mb-3">← Back to List</a>
    <?php echo $message_status; ?>

    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between">
            <h5 class="mb-0">Subject: <?php echo htmlspecialchars($ticket['subject']); ?></h5>
            <span class="badge bg-light text-primary"><?php echo $ticket['status']; ?></span>
        </div>
        <div class="card-body">
            
            <div class="alert alert-secondary">
                <strong>Description:</strong><br>
                <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
            </div>

            <?php if (isset($ticket['admin_reply']) && !empty($ticket['admin_reply'])): ?>
                <div class="alert alert-success">
                    <strong>Admin Reply:</strong><br>
                    <?php echo nl2br(htmlspecialchars($ticket['admin_reply'])); ?>
                </div>
            <?php endif; ?>

            <hr>
            
            <h6>Conversation History:</h6>
            <?php 
            if (isset($ticket['comments']) && is_array($ticket['comments'])) {
                foreach ($ticket['comments'] as $comment) {
                    echo "<div class='card mb-2 bg-light border-0'>";
                    echo "<div class='card-body p-2'>";
                    echo "<strong>" . htmlspecialchars($comment['user']) . ":</strong> ";
                    echo htmlspecialchars($comment['text']);
                    echo "</div></div>";
                }
            } else {
                echo "<p class='text-muted small'>No comments yet.</p>";
            }
            ?>

            <hr>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Add a Comment / Reply:</label>
                    <textarea name="comment_text" class="form-control" rows="2" placeholder="Write your reply here..." required></textarea>
                </div>
                <button type="submit" name="add_comment" class="btn btn-primary">Post Comment</button>
            </form>

        </div>
    </div>
</div>

</body>
</html>