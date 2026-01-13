<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require '../config/db.php';
require '../config/google-config.php';
require '../vendor/autoload.php';

use Google\Service\Calendar;

date_default_timezone_set('Asia/Jakarta'); // zona waktu Indonesia

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Update Meeting - E-Meeting</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">

<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<div class="alert alert-danger shadow-sm">❌ Akses tidak valid.</div>';
    exit;
}

// Ambil data dari form
$id = intval($_POST['id']);
$title = $_POST['title'];
$description = $_POST['description'];
$date_time = $_POST['date_time'];
$location = $_POST['location'];
$participant_email = $_POST['participant_email'];

// 🔹 Update ke database
$stmt = $conn->prepare("UPDATE meetings SET title=?, description=?, date_time=?, location=?, participant_email=? WHERE id=?");
$stmt->bind_param("sssssi", $title, $description, $date_time, $location, $participant_email, $id);

if ($stmt->execute()) {
    echo "<div class='alert alert-success shadow-sm'>
            ✅ <b>Data meeting berhasil diperbarui di database!</b>
          </div>";

    // 🔹 Cek apakah user sudah login Google
    if (isset($_SESSION['access_token'])) {
        $client->setAccessToken($_SESSION['access_token']);
        $service = new Calendar($client);
        $calendarId = 'primary';

        // Ambil google_event_id dari database
        $result = $conn->query("SELECT google_event_id FROM meetings WHERE id = $id");
        $row = $result->fetch_assoc();
        $google_event_id = $row['google_event_id'] ?? '';

        try {
            if (!empty($google_event_id)) {
                // 🌀 Jika sudah tersinkron, update event yang ada
                $event = $service->events->get($calendarId, $google_event_id);

                $event->setSummary($title);
                $event->setDescription($description);
                $event->setLocation($location);
                $event->setStart(new Calendar\EventDateTime([
                    'dateTime' => date('c', strtotime($date_time)),
                    'timeZone' => 'Asia/Jakarta',
                ]));
                $event->setEnd(new Calendar\EventDateTime([
                    'dateTime' => date('c', strtotime($date_time . ' +1 hour')),
                    'timeZone' => 'Asia/Jakarta',
                ]));
                $event->setAttendees([
                    ['email' => $participant_email]
                ]);

                $service->events->update($calendarId, $event->getId(), $event);
                echo "<div class='alert alert-primary shadow-sm'>
                        ☁️ Event di <b>Google Calendar</b> berhasil diperbarui!
                      </div>";
            } else {
                // 🆕 Jika belum pernah disinkron, buat event baru
                $newEvent = new Calendar\Event([
                    'summary' => $title,
                    'description' => $description,
                    'location' => $location,
                    'start' => [
                        'dateTime' => date('c', strtotime($date_time)),
                        'timeZone' => 'Asia/Jakarta',
                    ],
                    'end' => [
                        'dateTime' => date('c', strtotime($date_time . ' +1 hour')),
                        'timeZone' => 'Asia/Jakarta',
                    ],
                    'attendees' => [
                        ['email' => $participant_email]
                    ],
                ]);

                $createdEvent = $service->events->insert($calendarId, $newEvent, ['sendUpdates' => 'all']);
                $new_google_event_id = $createdEvent->getId();

                // Simpan google_event_id baru ke database
                $conn->query("UPDATE meetings SET google_event_id = '$new_google_event_id' WHERE id = $id");

                echo "<div class='alert alert-success shadow-sm'>
                        📅 Event baru berhasil dibuat di 
                        <a href='{$createdEvent->htmlLink}' target='_blank' class='text-decoration-none'>Google Calendar</a>.
                      </div>";
            }
        } catch (Exception $e) {
            echo "<div class='alert alert-warning shadow-sm'>
                    ⚠️ <b>Gagal update ke Google Calendar:</b> {$e->getMessage()}
                  </div>";
        }
    } else {
        echo "<div class='alert alert-info shadow-sm'>
                ⚠️ Anda belum login ke Google.
                <a href='login_google.php' class='btn btn-sm btn-outline-primary ms-2'>Login Sekarang</a>
              </div>";
    }
} else {
    echo "<div class='alert alert-danger shadow-sm'>
            ❌ Gagal memperbarui data meeting: {$stmt->error}
          </div>";
}

$stmt->close();
$conn->close();
?>

  <div class="mt-4 text-center">
    <a href="list_meetings.php" class="btn btn-secondary">⬅️ Kembali ke Daftar Meeting</a>
  </div>
</div>
</body>
</html>
