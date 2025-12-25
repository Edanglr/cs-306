<?php
session_start();

require_once __DIR__ . "/User/vendor/autoload.php"; // Path düzeltildi

use MongoDB\Client;

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // ⚠️ ŞİFRENİ BURAYA YAZ
        $uri = "mongodb+srv://cs306user:cs306proj123@cluster0.h4jv2qv.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0";
        $client = new Client($uri, [], ['driver' => ['tlsAllowInvalidCertificates' => true]]);
        
        // Admins tablosuna bakıyoruz
        $collection = $client->cs306->admins;

        // Eşleşen kullanıcı var mı?
        $admin = $collection->findOne(['email' => $email, 'password' => $password]);

        if ($admin) {
            // Giriş Başarılı
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name'] = $admin['username'];
            
            header("Location: Admin/index.php"); // Admin paneline yönlendir
            exit;
        } else {
            // Giriş Başarısız
            $error = "Invalid email or password!";
        }

    } catch (Exception $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #212529; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-box { width: 100%; max-width: 400px; padding: 2rem; background: white; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h3 class="text-center mb-4 text-dark">Admin Panel</h3>
        
        <?php if(!empty($error)): ?>
            <div class='alert alert-danger'><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="admin@gmail.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="******" required>
            </div>
            <button type="submit" class="btn btn-dark w-100">Login</button>
            <div class="text-center mt-3">
                <a href="index.php" class="text-decoration-none">← Back to Home</a>
            </div>
        </form>
    </div>
</body>
</html>