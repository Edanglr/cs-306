<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CS306 Support System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
        .btn-lg { border-radius: 50px; }
    </style>
</head>
<body>
    <div class="container text-center">
        <div class="card p-5 mx-auto" style="max-width: 500px;">
            <h1 class="mb-4 text-dark fw-bold">Welcome</h1>
            <p class="text-muted mb-4">Please select your login type</p>
            
            <div class="d-grid gap-3">
                <a href="login.php" class="btn btn-primary btn-lg py-3 me-2">
                    👤 User Login / Sign Up
                </a>

                <a href="admin_login.php" class="btn btn-dark btn-lg py-3">
                    🔒 Admin Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>