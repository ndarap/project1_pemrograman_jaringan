<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Jakarta'); // Waktu Indonesia
require '../config/db.php';

// Ambil parameter pencarian (jika ada)
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
$filter_title = isset($_GET['filter_title']) ? trim($_GET['filter_title']) : '';

// Query dasar
$sql = "SELECT * FROM meetings WHERE 1=1";

// Filter berdasarkan tanggal
if (!empty($filter_date)) {
    $sql .= " AND DATE(date_time) = '" . $conn->real_escape_string($filter_date) . "'";
}

// Filter berdasarkan judul
if (!empty($filter_title)) {
    $sql .= " AND title LIKE '%" . $conn->real_escape_string($filter_title) . "%'";
}

$sql .= " ORDER BY date_time ASC";

// Jalankan query
$result = $conn->query($sql);
if (!$result) {
    die("Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Meeting - E-Meeting</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>📅 Daftar Meeting</h2>
      <a href="add_meeting.php" class="btn btn-success">➕ Tambah Meeting</a>
    </div>

    <!-- 🔍 Form Filter -->
    <form method="GET" class="card p-3 mb-4 shadow-sm bg-white">
      <div class="row g-3 align-items-end">
        <div class="col-md-5">
          <label for="filter_title" class="form-label">Cari Judul Meeting</label>
          <input type="text" name="filter_title" id="filter_title" class="form-control"
                 placeholder="Masukkan judul..." value="<?= htmlspecialchars($filter_title) ?>">
        </div>

        <div class="col-md-4">
          <label for="filter_date" class="form-label">Tanggal Meeting</label>
          <input type="date" name="filter_date" id="filter_date" class="form-control"
                 value="<?= htmlspecialchars($filter_date) ?>">
        </div>

        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary w-50">🔍 Cari</button>
          <a href="list_meetings.php" class="btn btn-secondary w-50">♻️ Reset</a>
        </div>
      </div>
    </form>

    <!-- 📋 Tabel Meeting -->
    <table class="table table-bordered table-striped shadow-sm bg-white">
      <thead class="table-primary text-center">
        <tr>
          <th>No</th>
          <th>Judul</th>
          <th>Waktu</th>
          <th>Lokasi</th>
          <th>Aksi</th>
        </tr>
      </thead>
     <tbody>
  <?php if ($result->num_rows > 0): ?>
    <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td class="text-center"><?= $no++; ?></td>
        <td><?= htmlspecialchars($row['title']); ?></td>
        <td><?= htmlspecialchars($row['date_time']); ?></td>
        <td><?= htmlspecialchars($row['location']); ?></td>
        <td class="text-center">
          <a href="edit_meeting.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
          <a href="meeting_status.php?id=<?= $row['id']; ?>" class="btn btn-info btn-sm">👥 Status</a>
          <a href="sync_google_calendar.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">☁️ Sync</a>
          <a href="delete_event_google.php?id=<?= $row['id']; ?>" 
             class="btn btn-danger btn-sm"
             onclick="return confirm('Yakin ingin menghapus data dan event dari Google Calendar?');">
             🗑️ Hapus
          </a>
        </td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
      <tr>
        <td colspan="5" class="text-center">⚠️ Tidak ada meeting ditemukan.</td>
      </tr>
  <?php endif; ?>
</tbody>

    </table>
  </div>
</body>
</html>

<?php
$conn->close();
?>
