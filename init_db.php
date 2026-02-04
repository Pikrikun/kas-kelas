<?php
// File untuk inisialisasi database dan data awal
try {
    $db = new PDO('sqlite:kas.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buat tabel users
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nim TEXT UNIQUE NOT NULL,
        nama TEXT NOT NULL,
        password TEXT NOT NULL,
        role TEXT DEFAULT 'mahasiswa',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Buat tabel bulan_kas
    $db->exec("CREATE TABLE IF NOT EXISTS bulan_kas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tahun INTEGER NOT NULL,
        bulan INTEGER NOT NULL,
        nama_bulan TEXT,
        UNIQUE(tahun, bulan)
    )");
    
    // Buat tabel pembayaran_kas
    $db->exec("CREATE TABLE IF NOT EXISTS pembayaran_kas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        bulan_kas_id INTEGER NOT NULL,
        minggu_1 INTEGER DEFAULT 0,
        minggu_2 INTEGER DEFAULT 0,
        minggu_3 INTEGER DEFAULT 0,
        minggu_4 INTEGER DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (bulan_kas_id) REFERENCES bulan_kas(id),
        UNIQUE(user_id, bulan_kas_id)
    )");
    
    // Buat tabel pengeluaran
    $db->exec("CREATE TABLE IF NOT EXISTS pengeluaran (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tanggal DATE NOT NULL,
        keterangan TEXT NOT NULL,
        jumlah REAL NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Tambah admin default (bendahara)
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $db->exec("INSERT OR IGNORE INTO users (nim, nama, password, role) 
               VALUES ('ADMIN001', 'Bendahara Kelas', '$password_hash', 'bendahara')");
    
    // Tambah 5 mahasiswa contoh (bisa ditambah nanti sampai 28)
    $mahasiswa_contoh = [
        ['21101101', 'Ahmad Santoso'],
        ['21101102', 'Budi Wijaya'],
        ['21101103', 'Citra Dewi'],
        ['21101104', 'Dian Pratama'],
        ['21101105', 'Eka Putri']
    ];
    
    $stmt = $db->prepare("INSERT OR IGNORE INTO users (nim, nama, password, role) 
                         VALUES (?, ?, ?, 'mahasiswa')");
    
    foreach ($mahasiswa_contoh as $mhs) {
        $pass = password_hash('mhs123', PASSWORD_DEFAULT);
        $stmt->execute([$mhs[0], $mhs[1], $pass]);
    }
    
    echo "Database berhasil diinisialisasi!";
    
} catch(PDOException $e) {
    die("Error initializing database: " . $e->getMessage());
}
?>