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
use MongoDB\BSON\UTCDateTime;

// Admin ekranında da warning göstermeyelim
error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', 0);

// ID Kontrolü
if (!isset($_GET['id'])) {
    die("Ticket ID is missing.");
}

$ticket_id = $_GET['id'];

try {
    // MongoDB bağlantısı
    $uri = "mongodb+srv://cs306user:cs306proj123@cluster0.h4jv2qv.mongodb.net/cs306?retryWrites=true&w=majority&tls=true";
    $client = new Client($uri);
    $collection = $client->cs306->tickets;

    // =========================
    // ADMIN REPLY (COMMENT PUSH)
    // =========================
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reply_ticket'])) {
        $admin_response = trim($_POST['admin_response']);

        if (!empty($admin_response)) {
            $collection->updateOne(
                ['_id' => new ObjectId($ticket_id)],
                [
                    '$push' => [
                        'comments' => [
                            'author'   => 'admin',
                            'username' => 'admin',
                            'text'     => $admin_response,
                            'date'     => new UTCDateTime()
                        ]
                    ],
                    '$set' => ['status' => 'Answered']
                ]
            );

            header("Location: admin_ticket_detail.php?id=" . $ticket_id);
            exit;
        }
    }

    // =========================
    // RESOLVE
    // =========================
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['resolve_ticket'])) {
        $collection->updateOne(
            ['_id' => new ObjectId($ticket_id)],
            ['$set' => ['status' => 'Resolved']]
        );
        header("Location: admin_tickets.php");
        exit;
    }


    $ticket = $collection->findOne(
        ['_id' => new ObjectId($ticket_id)],
        ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']]
    );

    if (!$ticket) {
        die("Ticket not found!");
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Ticket Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <a href="admin_tickets.php" class="btn btn-outline-secondary mb-3">← Back to List</a>

    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between">
            <h4 class="mb-0">Ticket Details</h4>
            <span class="badge bg-light text-primary"><?= htmlspecialchars($ticket['status'] ?? '') ?></span>
        </div>

        <div class="card-body">

            <!-- Ticket Info -->
            <div class="mb-4">
                <h5 class="text-primary"><?= htmlspecialchars($ticket['subject'] ?? '') ?></h5>
                <p class="text-muted mb-1">
                    Created by:
                    <strong><?= htmlspecialchars($ticket['username'] ?? 'Anonymous') ?></strong>
                </p>
                <div class="p-3 bg-light border rounded">
                    <?= nl2br(htmlspecialchars($ticket['message'] ?? '')) ?>
                </div>
            </div>

            <hr>

            <!-- Conversation -->
            <h6>Conversation History:</h6>

            <?php if (!empty($ticket['comments']) && is_array($ticket['comments'])): ?>
                <?php foreach ($ticket['comments'] as $c): ?>
                    <?php
                        $author   = $c['author']   ?? (isset($c['user']) ? 'user' : 'admin');
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

            <!-- Admin Reply -->
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Write a Reply:</label>
                    <textarea name="admin_response"
                              class="form-control"
                              rows="3"
                              placeholder="Type your answer here..."
                              required></textarea>
                </div>
                <button type="submit" name="reply_ticket" class="btn btn-primary">
                    Send Reply
                </button>
            </form>

            <hr>

            <!-- Resolve -->
            <form method="POST">
                <button type="submit"
                        name="resolve_ticket"
                        class="btn btn-success w-100"
                        onclick="return confirm('Are you sure you want to close this ticket?');">
                    ✅ Mark as Resolved
                </button>
            </form>

        </div>
    </div>
</div>

</body>
</html>
