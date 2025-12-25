<?php require 'mongodb.php'; ?>

<h2>Create New Ticket</h2>
<form method="POST">
    Username:<br>
    <input type="text" name="username" required><br><br>
    Message:<br>
    <textarea name="message" required></textarea><br><br>
    <button type="submit">Submit Ticket</button>
</form>
<br>
<a href="tickets.php">⬅ Back to Tickets</a>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $message = $_POST["message"];

    // Veritabanına ekleme işlemi
    $insertOneResult = $collection->insertOne([
        'username' => $username,
        'message' => $message,
        'comments' => [], // Yorumlar için boş bir dizi başlatıyoruz
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);

    echo "<hr>";
    echo "<b>Ticket Created!</b><br>";
    echo "MongoDB ID: " . $insertOneResult->getInsertedId() . "<br>";
}
?>