<?php
// User klasöründeki vendor'a ulaşıyoruz
require_once __DIR__ . "/User/vendor/autoload.php";

use MongoDB\Client;

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // ⚠️ ŞİFRENİ YAZ
        $uri = "mongodb+srv://cs306user:cs306proj123@cluster0.h4jv2qv.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0";
        $client = new Client($uri, [], ['driver' => ['tlsAllowInvalidCertificates' => true]]);
        
        // 'users' adında yeni bir koleksiyon kullanıyoruz
        $collection = $client->cs306->users;

        // Aynı email var mı kontrol et
        $existingUser = $collection->findOne(['email' => $email]);

        if ($existingUser) {
            $message = "<div class='alert alert-danger'>Bu email zaten kayıtlı!</div>";
        } else {
            // Yeni kullanıcıyı ekle
            $collection->insertOne([
                'username' => $username,
                'email'    => $email,
                'password' => $password // Proje için düz metin (Normalde hashlenmeli)
            ]);
            
            // Kayıt başarılıysa Login sayfasına yönlendir
            header("Location: login.php?status=success");
            exit;
        }

    } catch (Exception $e) {
        $message = "Hata: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">

    <div class="card shadow p-4" style="width: 400px;">
        <h3 class="text-center mb-3">📝 Sign Up</h3>
        <?php echo $message; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
        <div class="text-center mt-3">
            <a href="login.php">Already have an account? Login</a>
        </div>
    </div>

</body>
</html>