<?php
session_start();
require '../config/google-config.php';
require '../vendor/autoload.php';

use Google\Service\Calendar;

// Pastikan user sudah login
if (!isset($_SESSION['access_token'])) {
    die("<div style='text-align:center;margin-top:50px;'>
            ⚠️ Harap login dengan Google terlebih dahulu. 
            <br><a href='login_google.php'>Login di sini</a>
         </div>");
}

$client->setAccessToken($_SESSION['access_token']);
$service = new Calendar($client);

// Ambil event dalam seminggu ke depan
$calendarId = 'primary';
$now = date('c');
$nextWeek = date('c', strtotime('+7 days'));

try {
    $events = $service->events->listEvents($calendarId, [
        'timeMin' => $now,
        'timeMax' => $nextWeek,
        'singleEvents' => true,
        'orderBy' => 'startTime'
    ]);

    echo "<!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <title>Daftar Event Google Calendar</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body class='bg-light'>
        <div class='container py-5'>
            <h2 class='mb-4 text-center'>📅 Event dalam 7 Hari ke Depan</h2>
            <table class='table table-bordered table-striped'>
                <thead class='table-primary'>
                    <tr>
                        <th>Judul</th>
                        <th>Waktu</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>";

    if (count($events->getItems()) == 0) {
        echo "<tr><td colspan='4' class='text-center text-muted'>Tidak ada event ditemukan.</td></tr>";
    } else {
        foreach ($events->getItems() as $event) {
            $title = htmlspecialchars($event->getSummary() ?: '(Tanpa Judul)');
            $start = htmlspecialchars($event->getStart()->getDateTime() ?: $event->getStart()->getDate());
            $desc = htmlspecialchars($event->getDescription() ?: '-');
            $id = htmlspecialchars($event->getId());

            echo "<tr>
                    <td>$title</td>
                    <td>$start</td>
                    <td>$desc</td>
                    <td>
                        <a href='delete_google_event.php?event_id=$id' class='btn btn-danger btn-sm'>🗑️ Hapus</a>
                    </td>
                  </tr>";
        }
    }

    echo "</tbody></table>
          <div class='text-center'>
            <a href='list_meetings.php' class='btn btn-secondary'>⬅️ Kembali ke Daftar Meeting</a>
          </div>
        </div>
    </body>
    </html>";
} catch (Exception $e) {
    echo "<div class='alert alert-danger text-center mt-5'>
            ⚠️ Gagal mengambil event: " . htmlspecialchars($e->getMessage()) . "
          </div>";
}
?>
