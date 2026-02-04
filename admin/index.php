<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$db = getDB();
$current_month = date('n');
$current_year = date('Y');

// Hitung statistik
$stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'mahasiswa'");
$total_mahasiswa = $stmt->fetchColumn();

$stmt = $db->query("SELECT SUM(jumlah) FROM pengeluaran");
$total_pengeluaran = $stmt->fetchColumn() ?: 0;

$total_setoran = $total_mahasiswa * 4 * 5000; // Estimasi
$saldo = $total_setoran - $total_pengeluaran;

// Ambil data terbaru
$stmt = $db->query("SELECT * FROM pengeluaran ORDER BY id DESC LIMIT 5");
$pengeluaran_terbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("
    SELECT u.nama, b.nama_bulan 
    FROM pembayaran_kas pk 
    JOIN users u ON pk.user_id = u.id 
    JOIN bulan_kas b ON pk.bulan_kas_id = b.id 
    WHERE pk.minggu_1 = 1 OR pk.minggu_2 = 1 OR pk.minggu_3 = 1 OR pk.minggu_4 = 1 
    ORDER BY pk.id DESC LIMIT 5
");
$pembayaran_terbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Bendahara - Kas Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #4361ee 0%, #3a0ca3 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 0;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
        }
        .stat-card {
            border-radius: 10px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3">
                    <h4 class="text-white mb-4">
                        <i class="bi bi-cash-stack"></i> Kas Kelas
                    </h4>
                    <div class="mb-4">
                        <div class="text-white small">Bendahara</div>
                        <div class="text-white fw-bold"><?php echo $_SESSION['nama']; ?></div>
                    </div>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link active" href="index.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="kas.php">
                        <i class="bi bi-calendar-plus"></i> Kelola Kas
                    </a>
                    <a class="nav-link" href="pengeluaran.php">
                        <i class="bi bi-cash-coin"></i> Pengeluaran
                    </a>
                    <a class="nav-link" href="rekap.php">
                        <i class="bi bi-graph-up"></i> Rekapitulasi
                    </a>
                    <a class="nav-link" href="mahasiswa.php">
                        <i class="bi bi-people"></i> Mahasiswa
                    </a>
                    <a class="nav-link" href="../logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-4 pt-3">
                <h2 class="mb-4">Dashboard Bendahara</h2>
                
                <!-- Statistik -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total Mahasiswa</h6>
                                <h2><?php echo $total_mahasiswa; ?></h2>
                                <small>Orang</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total Setoran</h6>
                                <h2>Rp <?php echo number_format($total_setoran, 0, ',', '.'); ?></h2>
                                <small>Estimasi maksimal</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-danger text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total Pengeluaran</h6>
                                <h2>Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></h2>
                                <small>Total keluar</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">Saldo Kas</h6>
                                <h2>Rp <?php echo number_format($saldo, 0, ',', '.'); ?></h2>
                                <small>Saat ini</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Cepat -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Pengeluaran Terbaru</h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($pengeluaran_terbaru)): ?>
                                    <p class="text-muted">Belum ada pengeluaran</p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($pengeluaran_terbaru as $p): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between">
                                                <span><?php echo htmlspecialchars($p['keterangan']); ?></span>
                                                <span class="text-danger">Rp <?php echo number_format($p['jumlah'], 0, ',', '.'); ?></span>
                                            </div>
                                            <small class="text-muted"><?php echo date('d/m/Y', strtotime($p['tanggal'])); ?></small>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bi bi-check-circle"></i> Pembayaran Terbaru</h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($pembayaran_terbaru)): ?>
                                    <p class="text-muted">Belum ada pembayaran</p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($pembayaran_terbaru as $pb): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between">
                                                <span><?php echo htmlspecialchars($pb['nama']); ?></span>
                                                <span class="text-success">✅</span>
                                            </div>
                                            <small class="text-muted"><?php echo htmlspecialchars($pb['nama_bulan']); ?></small>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aksi Cepat -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-lightning"></i> Aksi Cepat</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="kas.php?action=create" class="btn btn-primary w-100">
                                    <i class="bi bi-plus-circle"></i> Buat Bulan Baru
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="pengeluaran.php" class="btn btn-success w-100">
                                    <i class="bi bi-plus-circle"></i> Tambah Pengeluaran
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="mahasiswa.php" class="btn btn-info w-100">
                                    <i class="bi bi-person-plus"></i> Tambah Mahasiswa
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="rekap.php" class="btn btn-warning w-100">
                                    <i class="bi bi-printer"></i> Cetak Laporan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
