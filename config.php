<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi database SQLite
define('DB_PATH', dirname(__FILE__) . '/kas.db');

// Inisialisasi database jika belum ada
if (!file_exists(DB_PATH)) {
    require_once 'init_db.php';
}

// Fungsi koneksi database
function getDB() {
    try {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch(PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

// Fungsi redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'bendahara';
}

// Bulan dalam Bahasa Indonesia
$bulan_indonesia = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>