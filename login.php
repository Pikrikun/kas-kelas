<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = $_POST['nim'];
    $password = $_POST['password'];
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE nim = ?");
    $stmt->execute([$nim]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nim'] = $user['nim'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] == 'bendahara') {
            redirect('admin/index.php');
        } else {
            redirect('mahasiswa/index.php');
        }
    } else {
        redirect('index.php?error=Login gagal. Periksa NIM dan password.');
    }
} else {
    redirect('index.php');
}
?>