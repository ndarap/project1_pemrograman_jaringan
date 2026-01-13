<?php
session_start();
if (!isset($_SESSION['access_token'])) {
    header("Location: login_google.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - E-Meeting</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #4f46e5, #06b6d4);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Poppins', sans-serif;
    }
    .dashboard-card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 40px;
      text-align: center;
      width: 420px;
      animation: fadeIn 1s ease;
    }
    .dashboard-card h2 {
      color: #333;
      font-weight: 600;
    }
    .dashboard-card p {
      color: #555;
      margin-bottom: 25px;
    }
    .btn-custom {
      background: linear-gradient(45deg, #4f46e5, #06b6d4);
      border: none;
      color: white;
      padding: 10px 20px;
      border-radius: 30px;
      transition: 0.3s;
    }
    .btn-custom:hover {
      background: linear-gradient(45deg, #4338ca, #0891b2);
      transform: scale(1.05);
    }
    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(-20px);}
      to {opacity: 1; transform: translateY(0);}
    }
    .icon {
      font-size: 64px;
      color: #4f46e5;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

  <div class="dashboard-card">
    <i class="bi bi-calendar-check icon"></i>
    <h2>🎉 Selamat Datang di <br><strong>E-Meeting</strong></h2>
    <p>Login Google kamu berhasil! Sekarang kamu bisa mengelola jadwal meeting dengan mudah.</p>

    <div class="d-grid gap-2">
      <a href="list_meetings.php" class="btn btn-custom mb-2">
        <i class="bi bi-list-task"></i> Lihat Daftar Meeting
      </a>
      <a href="add_meeting.php" class="btn btn-outline-primary mb-2">
        <i class="bi bi-plus-circle"></i> Tambah Meeting Baru
      </a>
      <a href="logout_google.php" class="btn btn-danger">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
