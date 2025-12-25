<?php
// PATH AYARI: User klasöründeki vendor'a ulaşıyoruz
require_once __DIR__ . "/User/vendor/autoload.php";

use MongoDB\Client;

try {
    // ⚠️ ŞİFRENİ BURAYA YAZ
    $uri = "mongodb+srv://cs306user:cs306proj123@cluster0.h4jv2qv.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0";
    $client = new Client($uri, [], ['driver' => ['tlsAllowInvalidCertificates' => true]]);
    
    // 'admins' adında yeni bir koleksiyon (tablo) oluşturuyoruz
    $collection = $client->cs306->admins;

    // Admin verisini ekle
    $insertOneResult = $collection->insertOne([
        'username' => 'Super Admin',
        'email'    => 'admin@gmail.com',
        'password' => '12345', // Gerçek hayatta şifrelenmeli (hash), ama proje için böyle kalsın
        'role'     => 'admin'
    ]);

    echo "<h1>✅ Admin Created Successfully!</h1>";
    echo "<p>Email: admin@gmail.com</p>";
    echo "<p>Password: 12345</p>";
    echo "<br><a href='admin_login.php'>Go to Login</a>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>