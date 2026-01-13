<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../config/db.php';
require '../config/google-config.php';
$auth_url = $client->createAuthUrl();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login dengan Google</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="text-center p-5">
    <h2>Login dengan Google untuk sinkronisasi kalender</h2>
    <a href="<?= htmlspecialchars($auth_url) ?>" class="btn btn-danger mt-3">🔗 Login dengan Google</a>
</body>
</html>
