<?php
require_once __DIR__ . "/vendor/autoload.php";

use MongoDB\Client;

try {

    $uri = "mongodb+srv://cs306user:cs306proj123@cluster0.h4jv2qv.mongodb.net/cs306
        ?retryWrites=true
        &w=majority
        &tls=true
        &tlsAllowInvalidCertificates=true";

    $client = new Client(
        $uri,
        [],
        [
            'typeMap' => [
                'root' => 'array',
                'document' => 'array',
                'array' => 'array'
            ]
        ]
    );

    $db = $client->cs306;
    $collection = $db->tickets;

} catch (Exception $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>
