<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . "/vendor/autoload.php";

use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

// PHP warning’leri user ekranında gizle
error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', 0);

// ID kontrolü
if (!isset($_GET['id'])) {
    die("Invalid ticket ID");
}

$ticket_id = $_GET['id'];
$current_user = $_SESSION['username'];

try {
    // MongoDB bağlantısı
    $uri = "mongodb+srv://cs306user:cs306proj123@cluster0.h4jv2qv.mongodb.net/cs306?retryWrites=true&w=majority&tls=true";
    $client = new Client($uri);
    $collection = $client->cs306->tickets;

    // =========================
    // USER COMMENT EKLE
    // =========================
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_comment'])) {
        $comment_text = trim($_POST['comment_text']);

        if (!empty($comment_text)) {
            $collection->updateOne(
                ['_id' => new ObjectId($ticket_id)],
                ['$push' => [
                    'comments' => [
                        'author'   => 'user',
                        'username' => $current_user,
                        'text'     => $comment_text,
                        'date'     => new UTCDateTime()
                    ]
                ]]
            );

            // POST → REDIRECT → GET
            header("Location: ticket_detail.php?id=" . $ticket_id);
            exit;
        }
    }

   
    $ticket = $collection->findOne(
        ['_id' => new ObjectId($ticket_id)],
        ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']]
    );

    if (!$ticket) {
        die("Ticket not found.");
    }

    // Yetki kontrolü
    if (($ticket['username'] ?? '') !== $current_user) {
        die("Access denied.");
    }

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

    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between">
            <h5 class="mb-0">Subject: <?= htmlspecialchars($ticket['subject'] ?? '') ?></h5>
            <span class="badge bg-light text-primary"><?= htmlspecialchars($ticket['status'] ?? '') ?></span>
        </div>

        <div class="card-body">

            <!-- Ticket Description -->
            <div class="alert alert-secondary">
                <strong>Description:</strong><br>
                <?= nl2br(htmlspecialchars($ticket['message'] ?? '')) ?>
            </div>

            <hr>

            <!-- Conversation -->
            <h6>Conversation History:</h6>

            <?php if (!empty($ticket['comments']) && is_array($ticket['comments'])): ?>
                <?php foreach ($ticket['comments'] as $c): ?>
                    <?php
                        $author   = $c['author']   ?? 'user';
                        $username = $c['username'] ?? ($c['user'] ?? 'User');
                        $text     = $c['text']     ?? '';
                        $date     = $c['date']     ?? null;
                    ?>

                    <div class="card mb-2 <?= $author === 'admin' ? 'border-success' : 'border-primary' ?>">
                        <div class="card-body p-2">
                            <strong>
                                <?= $author === 'admin' ? 'Admin' : htmlspecialchars($username) ?>:
                            </strong>

                            <?= htmlspecialchars($text) ?>

                            <?php if ($date instanceof MongoDB\BSON\UTCDateTime): ?>
                                <div class="text-muted small">
                                    <?= date('d.m.Y H:i', $date->toDateTime()->getTimestamp()) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No messages yet.</p>
            <?php endif; ?>

            <hr>

            <!-- User Reply -->
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Add a Comment / Reply:</label>
                    <textarea name="comment_text"
                              class="form-control"
                              rows="2"
                              placeholder="Write your reply here..."
                              required></textarea>
                </div>
                <button type="submit" name="add_comment" class="btn btn-primary">
                    Post Comment
                </button>
            </form>

        </div>
    </div>
</div>

</body>
</html>
