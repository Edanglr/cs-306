<?php
require_once __DIR__ . "/vendor/autoload.php";

use MongoDB\Client;

try {
    // ŞİFRE kısmına Atlas şifreni, cs306user kısmına doğru kullanıcı adını yazdığından emin ol.
    $uri = "mongodb+srv://cs306user:cs306proj123@cluster0.h4jv2qv.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0";

    $client = new Client($uri, [], [
        'typeMap' => [
            'root' => 'array', 
            'document' => 'array', 
            'array' => 'array'
        ],
        // TLS hatasını (SSL certificate problem) aşmak için bu satırı ekliyoruz:
        'driver' => ['tlsAllowInvalidCertificates' => true] 
    ]);

    // Veritabanını seçiyoruz (CS-306 projen için 'cs306' db ismi)
    $db = $client->cs306;
    $collection = $db->tickets; // 'tickets' koleksiyonunu kullanacağız

} catch (Exception $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>