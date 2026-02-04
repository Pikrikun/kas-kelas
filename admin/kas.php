<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$db = getDB();
$action = $_GET['action'] ?? 'view';
$bulan_kas_id = $_GET['id'] ?? null;
$message = '';

// Proses form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($action == 'create') {
        $tahun = $_POST['tahun'];
        $bulan = $_POST['bulan'];
        $nama_bulan = $bulan_indonesia[$bulan] . ' ' . $tahun;
        
        try {
            $stmt = $db->prepare("INSERT INTO bulan_kas (tahun, bulan, nama_bulan) VALUES (?, ?, ?)");
            $stmt->execute([$tahun, $bulan, $nama_bulan]);
            $message = '<div class="alert alert-success">Bulan kas berhasil ditambahkan!</div>';
        } catch(PDOException $e) {
            $message = '<div class="alert alert-danger">Bulan kas sudah ada!</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kas - Bendahara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .table-hover tbody tr:hover { background-color: rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="col-md-9 col-lg-10 ms-sm-auto px-4 pt-3">
        <h2 class="mb-4">
            <i class="bi bi-calendar-plus"></i> Kelola Kas
        </h2>
        
        <?php echo $message; ?>
        
        <?php if ($action == 'create'): ?>
        <!-- Form Tambah Bulan Kas -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tambah Bulan Kas Baru</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select class="form-select" id="tahun" name="tahun" required>
                                <?php for ($i = date('Y')-1; $i <= date('Y')+1; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $i == date('Y') ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="bulan" class="form-label">Bulan</label>
                            <select class="form-select" id="bulan" name="bulan" required>
                                <?php foreach ($bulan_indonesia as $num => $nama): ?>
                                <option value="<?php echo $num; ?>" <?php echo $num == date('n') ? 'selected' : ''; ?>>
                                    <?php echo $nama; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Simpan
                    </button>
                    <a href="kas.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Daftar Bulan Kas -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Bulan Kas</h5>
                <a href="kas.php?action=create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Tambah Bulan
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th class="text-center">Total Mahasiswa</th>
                                <th class="text-center">Total Setoran</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $db->query("SELECT * FROM bulan_kas ORDER BY tahun DESC, bulan DESC");
                            $bulan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (empty($bulan_list)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data bulan kas</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bulan_list as $bulan): ?>
                                <?php
                                // Hitung statistik
                                $stmt = $db->prepare("
                                    SELECT COUNT(*) as total_mhs,
                                           SUM(minggu_1 + minggu_2 + minggu_3 + minggu_4) as total_setoran
                                    FROM pembayaran_kas 
                                    WHERE bulan_kas_id = ?
                                ");
                                $stmt->execute([$bulan['id']]);
                                $stats = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                $total_setoran = ($stats['total_setoran'] ?: 0) * 5000;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($bulan['nama_bulan']); ?></td>
                                    <td class="text-center"><?php echo $stats['total_mhs'] ?: 0; ?></td>
                                    <td class="text-center">Rp <?php echo number_format($total_setoran, 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <a href="kelola_pembayaran.php?bulan_id=<?php echo $bulan['id']; ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="bi bi-pencil"></i> Kelola
                                        </a>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="if(confirm('Hapus bulan kas ini?')) 
                                                        location.href='proses.php?action=delete_bulan&id=<?php echo $bulan['id']; ?>'">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>