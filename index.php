<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kas Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .login-card { max-width: 400px; margin: 100px auto; }
        .card { border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-primary { background-color: #4361ee; border-color: #4361ee; }
        .status-paid { color: #28a745; }
        .status-unpaid { color: #dc3545; }
    </style>
</head>
<body>
    <?php if (!isLoggedIn()): ?>
    <!-- Halaman Login -->
    <div class="container">
        <div class="card login-card">
            <div class="card-header text-center bg-primary text-white">
                <h4><i class="bi bi-cash-stack"></i> KAS KELAS</h4>
                <p class="mb-0">Sistem Transparansi Kas</p>
            </div>
            <div class="card-body p-4">
                <h5 class="card-title text-center mb-4">Login</h5>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="nim" class="form-label">NIM</label>
                        <input type="text" class="form-control" id="nim" name="nim" required 
                               placeholder="Masukkan NIM">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required
                               placeholder="Masukkan password">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </button>
                    </div>
                </form>
                <hr>
                <div class="text-center">
                    <p class="mb-2">Login sebagai mahasiswa:</p>
                    <p class="text-muted small">NIM: 21101101 | Password: mhs123</p>
                    <p class="text-muted small">NIM: ADMIN001 | Password: admin123 (Bendahara)</p>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
        <?php 
        if (isAdmin()) {
            header('Location: admin/index.php');
        } else {
            header('Location: mahasiswa/index.php');
        }
        exit();
        ?>
    <?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>