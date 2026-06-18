<?php
// Mengaktifkan session untuk login admin
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "ar_personal";

// Koneksi ke database menggunakan PDO agar lebih aman dan modern
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Base URL (Sesuaikan dengan nama folder di htdocs Anda)
define('BASE_URL', 'http://localhost/UAS_AR/');
?>