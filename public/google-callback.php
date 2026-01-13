<?php
session_start();
require '../config/google-config.php';

if (!isset($_GET['code'])) {
    die('Kode otorisasi tidak ditemukan.');
}

// Dapatkan access token
$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
$_SESSION['access_token'] = $token;

// Simpan ke file (opsional, supaya tidak hilang setelah restart)
file_put_contents('../config/token.json', json_encode($token));

header("Location: dashboard.php");
exit;
?>


