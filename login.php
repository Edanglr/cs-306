<?php
session_start();
require_once __DIR__ . "/User/vendor/autoload.php";

use MongoDB\Client;

$message = "";

// Eğer kayıt sayfasından gelindiyse başarı mesajı göster
if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $message = "<div class='alert alert-success'>Kayıt başarılı! Şimdi giriş yapabilirsin.</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // ⚠️ ŞİFRENİ YAZ
        $uri = "mongodb+srv://cs306user:cs306proj123@cluster0.h4jv2qv.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0";
        $client = new Client($uri, [], ['driver' => ['tlsAllowInvalidCertificates' => true]]);
        $collection = $client->cs306->users;

        // Kullanıcıyı bul
        $user = $collection->findOne(['email' => $email, 'password' => $password]);

        if ($user) {
            // Giriş Başarılı: Session bilgilerini kaydet
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['email']    = $user['email'];

            // User paneline yönlendir
            header("Location: User/index.php");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Email veya şifre hatalı!</div>";
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
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center" style="height: 100vh;">

    <div class="card shadow p-4" style="width: 400px;">
        <h3 class="text-center mb-3">🔐 User Login</h3>
        <?php echo $message; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Login</button>
        </form>
        <div class="text-center mt-3">
            <a href="signup.php">Don't have an account? Sign Up</a>
            <br>
            <a href="index.php" class="text-muted small">← Back to Home</a>
        </div>
    </div>

</body>
</html>