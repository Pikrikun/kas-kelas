<?php
require_once '../config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('../index.php');
}

$db = getDB();
$current_month = date('n');
$current_year = date('Y');

// Ambil data bulan kas aktif
$stmt = $db->prepare("SELECT * FROM bulan_kas WHERE tahun = ? AND bulan = ?");
$stmt->execute([$current_year, $current_month]);
$bulan_kas = $stmt->fetch(PDO::FETCH_ASSOC);

// Hitung total kas
$stmt = $db->query("SELECT SUM(jumlah) as total_keluar FROM pengeluaran");
$total_keluar = $stmt->fetchColumn() ?: 0;

// Hitung total kas masuk (dari semua pembayaran)
$stmt = $db->query("
    SELECT COUNT(*) as total_mahasiswa FROM users WHERE role = 'mahasiswa'
");
$total_mahasiswa = $stmt->fetchColumn();

// Setiap mahasiswa bayar 20.000 per bulan (4 minggu x 5.000)
$total_masuk = $total_mahasiswa * 4 * 5000; // Estimasi maksimal
$saldo = $total_masuk - $total_keluar;

// Ambil data pengeluaran terbaru
$stmt = $db->query("SELECT * FROM pengeluaran ORDER BY tanggal DESC LIMIT 10");
$pengeluaran = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data pembayaran mahasiswa ini
$pembayaran = null;
if ($bulan_kas) {
    $stmt = $db->prepare("SELECT * FROM pembayaran_kas WHERE user_id = ? AND bulan_kas_id = ?");
    $stmt->execute([$_SESSION['user_id'], $bulan_kas['id']]);
    $pembayaran = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - Kas Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-cash-stack"></i> Kas Kelas
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link"><?php echo $_SESSION['nama']; ?></span>
                <a class="nav-item nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <!-- Kartu Saldo -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-wallet2"></i> Saldo Kas</h5>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-success">Rp <?php echo number_format($saldo, 0, ',', '.'); ?></h2>
                        <p class="text-muted">Total kas saat ini</p>
                    </div>
                </div>
            </div>

            <!-- Status Pembayaran -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Status Pembayaran <?php echo date('F Y'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-3">
                                <h6>Minggu 1</h6>
                                <h3><?php echo ($pembayaran && $pembayaran['minggu_1']) ? '✅' : '❌'; ?></h3>
                                <small>Rp 5.000</small>
                            </div>
                            <div class="col-3">
                                <h6>Minggu 2</h6>
                                <h3><?php echo ($pembayaran && $pembayaran['minggu_2']) ? '✅' : '❌'; ?></h3>
                                <small>Rp 5.000</small>
                            </div>
                            <div class="col-3">
                                <h6>Minggu 3</h6>
                                <h3><?php echo ($pembayaran && $pembayaran['minggu_3']) ? '✅' : '❌'; ?></h3>
                                <small>Rp 5.000</small>
                            </div>
                            <div class="col-3">
                                <h6>Minggu 4</h6>
                                <h3><?php echo ($pembayaran && $pembayaran['minggu_4']) ? '✅' : '❌'; ?></h3>
                                <small>Rp 5.000</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Pengeluaran -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-receipt"></i> Riwayat Pengeluaran Terakhir</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pengeluaran)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">Belum ada pengeluaran</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pengeluaran as $p): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($p['tanggal'])); ?></td>
                                    <td><?php echo htmlspecialchars($p['keterangan']); ?></td>
                                    <td class="text-end text-danger">Rp <?php echo number_format($p['jumlah'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Info -->
        <div class="alert alert-info">
            <h5><i class="bi bi-info-circle"></i> Informasi</h5>
            <ul>
                <li>Iuran kas: Rp 5.000 per minggu</li>
                <li>Total per bulan: Rp 20.000</li>
                <li>Pembayaran diverifikasi oleh bendahara</li>
                <li>Data diperbarui secara real-time</li>
            </ul>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
