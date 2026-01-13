<?php
session_start();
require '../config/db.php';
require '../config/google-config.php';
require '../vendor/autoload.php';

use Google\Service\Calendar;

// Pastikan user sudah login Google
if (!isset($_SESSION['access_token'])) {
    die("<div class='alert alert-warning text-center mt-5'>⚠️ Harap login dengan Google terlebih dahulu. <a href='login_google.php'>Login di sini</a></div>");
}

$client->setAccessToken($_SESSION['access_token']);
$service = new Calendar($client);

// Pastikan ada parameter id
if (!isset($_GET['id'])) {
    die("<div class='alert alert-danger text-center mt-5'>❌ ID meeting tidak ditemukan.</div>");
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT google_event_id, title FROM meetings WHERE id = $id");
$row = $result->fetch_assoc();

if (!$row || empty($row['google_event_id'])) {
    die("<div class='alert alert-warning text-center mt-5'>⚠️ Meeting ini belum terhubung ke Google Calendar.</div>");
}

$google_event_id = $row['google_event_id'];
$calendarId = 'primary';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Status Peserta Meeting - E-Meeting</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .status-badge { font-size: 0.9rem; padding: 6px 10px; border-radius: 8px; }
    .accepted { background-color: #28a745; color: white; }
    .declined { background-color: #dc3545; color: white; }
    .tentative { background-color: #ffc107; color: black; }
    .needsAction { background-color: #6c757d; color: white; }
  </style>
</head>
<body>
  <div class="container mt-5">
    <div class="text-center mb-4">
      <h2>👥 Status Kehadiran Peserta</h2>
      <h5 class="text-muted">Untuk meeting: <b><?= htmlspecialchars($row['title']); ?></b></h5>
    </div>

    <?php
    try {
        // Ambil data event dari Google Calendar
        $event = $service->events->get($calendarId, $google_event_id);

        echo '<div class="card shadow-sm">';
        echo '<div class="card-body">';
        echo '<table class="table table-hover align-middle text-center">';
        echo '<thead class="table-primary"><tr><th>Email Peserta</th><th>Status Kehadiran</th></tr></thead>';
        echo '<tbody>';

        if (isset($event->attendees)) {
            foreach ($event->attendees as $attendee) {
                $email = htmlspecialchars($attendee->email);
                $response = $attendee->responseStatus; // accepted, declined, tentative, needsAction

                $statusText = match ($response) {
                    'accepted' => '<span class="status-badge accepted">✅ Diterima</span>',
                    'declined' => '<span class="status-badge declined">❌ Ditolak</span>',
                    'tentative' => '<span class="status-badge tentative">🤔 Mungkin</span>',
                    default => '<span class="status-badge needsAction">⏳ Belum Merespons</span>',
                };

                echo "<tr><td>$email</td><td>$statusText</td></tr>";
            }
        } else {
            echo "<tr><td colspan='2' class='text-muted'>Belum ada peserta terdaftar.</td></tr>";
        }

        echo '</tbody></table>';
        echo '</div></div>';
    } catch (Exception $e) {
        echo "<div class='alert alert-danger mt-4'>⚠️ Gagal mengambil data event: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    ?>

    <div class="text-center mt-4">
      <a href="list_meetings.php" class="btn btn-secondary">⬅️ Kembali ke Daftar Meeting</a>
    </div>
  </div>
</body>
</html>
<?php
$conn->close();
?>
