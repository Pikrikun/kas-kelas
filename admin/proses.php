<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$db = getDB();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'delete_bulan':
        $id = $_GET['id'];
        $db->prepare("DELETE FROM bulan_kas WHERE id = ?")->execute([$id]);
        header('Location: kas.php');
        break;
        
    case 'update_pembayaran':
        parse_str(file_get_contents("php://input"), $data);
        $user_id = $data['user_id'];
        $bulan_id = $data['bulan_id'];
        $minggu = $data['minggu'];
        $status = $data['status'];
        
        // Cek apakah sudah ada data
        $stmt = $db->prepare("SELECT id FROM pembayaran_kas WHERE user_id = ? AND bulan_kas_id = ?");
        $stmt->execute([$user_id, $bulan_id]);
        
        if ($stmt->fetch()) {
            // Update
            $sql = "UPDATE pembayaran_kas SET $minggu = ? WHERE user_id = ? AND bulan_kas_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$status, $user_id, $bulan_id]);
        } else {
            // Insert baru
            $sql = "INSERT INTO pembayaran_kas (user_id, bulan_kas_id, $minggu) VALUES (?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user_id, $bulan_id, $status]);
        }
        
        echo json_encode(['success' => true]);
        break;
        
    case 'add_pengeluaran':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $tanggal = $_POST['tanggal'];
            $keterangan = $_POST['keterangan'];
            $jumlah = $_POST['jumlah'];
            
            $stmt = $db->prepare("INSERT INTO pengeluaran (tanggal, keterangan, jumlah) VALUES (?, ?, ?)");
            $stmt->execute([$tanggal, $keterangan, $jumlah]);
            
            header('Location: pengeluaran.php?success=1');
        }
        break;
        
    case 'delete_pengeluaran':
        $id = $_GET['id'];
        $db->prepare("DELETE FROM pengeluaran WHERE id = ?")->execute([$id]);
        header('Location: pengeluaran.php');
        break;
        
    case 'add_mahasiswa':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nim = $_POST['nim'];
            $nama = $_POST['nama'];
            $password = password_hash('mhs123', PASSWORD_DEFAULT);
            
            try {
                $stmt = $db->prepare("INSERT INTO users (nim, nama, password) VALUES (?, ?, ?)");
                $stmt->execute([$nim, $nama, $password]);
                header('Location: mahasiswa.php?success=1');
            } catch(PDOException $e) {
                header('Location: mahasiswa.php?error=NIM sudah ada');
            }
        }
        break;
        
    case 'delete_mahasiswa':
        $id = $_GET['id'];
        $db->prepare("DELETE FROM users WHERE id = ? AND role = 'mahasiswa'")->execute([$id]);
        header('Location: mahasiswa.php');
        break;
}
?>