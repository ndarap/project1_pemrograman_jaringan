<?php
session_start();
require '../config/db.php';
require '../config/google-config.php';
require '../vendor/autoload.php';

use Google\Service\Calendar;

// Pastikan user login Google
if (!isset($_SESSION['access_token'])) {
    die("<div style='text-align:center;margin-top:50px;'>
            ⚠️ Harap login dengan Google terlebih dahulu. 
            <br><a href='login_google.php'>Login di sini</a>
         </div>");
}

$client->setAccessToken($_SESSION['access_token']);
$service = new Calendar($client);

// Cek apakah ada ID meeting
if (!isset($_GET['id'])) {
    die("<div style='text-align:center;margin-top:50px;'>❌ ID meeting tidak ditemukan.</div>");
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM meetings WHERE id = $id");
$meeting = $result->fetch_assoc();

if (!$meeting) {
    die("<div style='text-align:center;margin-top:50px;'>⚠️ Meeting tidak ditemukan.</div>");
}

// Buat event di Google Calendar
$event = new Calendar\Event([
    'summary' => $meeting['title'],
    'description' => $meeting['description'],
    'location' => $meeting['location'],
    'start' => [
        'dateTime' => date('c', strtotime($meeting['date_time'])),
        'timeZone' => 'Asia/Jakarta',
    ],
    'end' => [
        'dateTime' => date('c', strtotime($meeting['date_time'] . ' +1 hour')),
        'timeZone' => 'Asia/Jakarta',
    ],
    'attendees' => [
        ['email' => $meeting['participant_email']],
    ],
]);

try {
    $calendarId = 'primary';
    $createdEvent = $service->events->insert($calendarId, $event, ['sendUpdates' => 'all']);

    // Simpan ID event Google ke database agar bisa dihapus nanti
    $google_event_id = $createdEvent->getId();
    $conn->query("UPDATE meetings SET google_event_id = '$google_event_id' WHERE id = $id");

    echo "<div style='text-align:center;margin-top:50px;'>
            ✅ Event berhasil disinkronkan ke <a href='{$createdEvent->htmlLink}' target='_blank'>Google Calendar</a>!<br><br>
            <a href='list_meetings.php'>⬅️ Kembali ke Daftar Meeting</a>
          </div>";
} catch (Exception $e) {
    echo "<div style='text-align:center;margin-top:50px;'>⚠️ Gagal sinkronisasi: " . $e->getMessage() . "</div>";
}

$conn->close();
?>
