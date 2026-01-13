<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../config/db.php';
require '../config/google-config.php';
require '../vendor/autoload.php';

session_start();

// Pastikan parameter id ada
if (!isset($_GET['id'])) {
    die("❌ ID meeting tidak ditemukan.");
}

$id = intval($_GET['id']);

// Ambil data meeting berdasarkan ID
$result = $conn->query("SELECT * FROM meetings WHERE id = $id");
if ($result->num_rows == 0) {
    die("⚠️ Data meeting tidak ditemukan.");
}

$meeting = $result->fetch_assoc();

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date_time = $_POST['date_time'];
    $location = $_POST['location'];
    $participant_email = $meeting['participant_email']; // ambil dari DB

    // Update ke database
    $update = $conn->prepare("UPDATE meetings SET title=?, description=?, date_time=?, location=? WHERE id=?");
    $update->bind_param("ssssi", $title, $description, $date_time, $location, $id);

    if ($update->execute()) {
        echo "<div class='alert alert-success'>✅ Data meeting berhasil diperbarui!</div>";

        // ====== UPDATE GOOGLE CALENDAR EVENT ======
        if (isset($_SESSION['access_token']) && !empty($meeting['google_event_id'])) {
            try {
                $client->setAccessToken($_SESSION['access_token']);
                $service = new Google\Service\Calendar($client);
                $calendarId = 'primary';
                $google_event_id = $meeting['google_event_id'];

                // Ambil event dari Google Calendar
                $event = $service->events->get($calendarId, $google_event_id);

                // Perbarui data event
                $event->setSummary($title);
                $event->setDescription($description);
                $event->setLocation($location);

                $event->setStart(new Google\Service\Calendar\EventDateTime([
                    'dateTime' => date('c', strtotime($date_time)),
                    'timeZone' => 'Asia/Jakarta',
                ]));

                $event->setEnd(new Google\Service\Calendar\EventDateTime([
                    'dateTime' => date('c', strtotime($date_time . ' +1 hour')),
                    'timeZone' => 'Asia/Jakarta',
                ]));

                // Perbarui peserta (jika ada email)
                if (!empty($participant_email)) {
                    $event->setAttendees([
                        ['email' => $participant_email],
                    ]);
                }

                // Simpan perubahan ke Google Calendar
                $updatedEvent = $service->events->update($calendarId, $event->getId(), $event);
                echo "<div class='alert alert-success'>📅 Event Google Calendar berhasil diperbarui!</div>";
            } catch (Exception $e) {
                echo "<div class='alert alert-warning'>⚠️ Gagal update ke Google Calendar: " . $e->getMessage() . "</div>";
            }
        } else {
            echo "<div class='alert alert-info'>ℹ️ Tidak ada event Google untuk diperbarui atau belum login Google.</div>";
        }

        echo "<script>setTimeout(() => window.location='list_meetings.php', 2000);</script>";
    } else {
        echo "<div class='alert alert-danger'>Gagal memperbarui data: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Meeting - E-Meeting</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2 class="mb-4">✏️ Edit Meeting</h2>
    <form method="POST" action="update_meeting.php" class="card p-4 shadow-sm bg-white">
    <input type="hidden" name="id" value="<?= $meeting['id']; ?>">

      <div class="mb-3">
        <label for="title" class="form-label">Judul Meeting</label>
        <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($meeting['title']); ?>" required>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Deskripsi</label>
        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($meeting['description']); ?></textarea>
      </div>

      <div class="mb-3">
        <label for="date_time" class="form-label">Tanggal & Waktu</label>
        <input type="datetime-local" class="form-control" id="date_time" name="date_time"
               value="<?= date('Y-m-d\TH:i', strtotime($meeting['date_time'])); ?>" required>
      </div>

      <div class="mb-3">
        <label for="location" class="form-label">Lokasi</label>
        <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($meeting['location']); ?>">
      </div>

      <div class="mb-3">
        <label for="participant_email" class="form-label">Email Peserta</label>
        <input type="email" class="form-control" id="participant_email" name="participant_email"
        value="<?= htmlspecialchars($meeting['participant_email'] ?? ''); ?>" placeholder="contoh@gmail.com" required>
      </div>


    

      <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
      <a href="list_meetings.php" class="btn btn-secondary">⬅️ Kembali</a>
    </form>
  </div>
</body>
</html>
