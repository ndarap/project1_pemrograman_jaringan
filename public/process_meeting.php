<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
date_default_timezone_set('Asia/Jakarta');

require '../config/db.php';
require '../config/google-config.php';
require '../vendor/autoload.php';

use Google\Service\Calendar;

// Ambil data dari form
$title = $_POST['title'];
$description = $_POST['description'];
$date_time = $_POST['date_time'];
$location = $_POST['location'];
$participant_email = $_POST['participant_email'];

// Simpan ke database
$stmt = $conn->prepare("INSERT INTO meetings (title, description, date_time, location, participant_email) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $title, $description, $date_time, $location, $participant_email);

if ($stmt->execute()) {
    echo "<h3>✅ Meeting berhasil disimpan ke database!</h3>";
    $meeting_id = $stmt->insert_id;

    // --- Integrasi Google Calendar ---
    if (isset($_SESSION['access_token'])) {
        $client->setAccessToken($_SESSION['access_token']);
        $service = new Calendar($client);

        // 🧠 Ubah daftar email jadi array
        $emails = array_map('trim', explode(',', $participant_email));
        $attendees = [];

        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $attendees[] = ['email' => $email];
            }
        }

        // Buat event dengan attendees
        $event = new Calendar\Event([
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
            'attendees' => $attendees, // Tambahkan semua peserta
        ]);

        try {
            $calendarId = 'primary';
            $createdEvent = $service->events->insert($calendarId, $event, ['sendUpdates' => 'all']);

            // Simpan Google Event ID ke database
            $google_event_id = $createdEvent->id;
            $update = $conn->prepare("UPDATE meetings SET google_event_id = ? WHERE id = ?");
            $update->bind_param("si", $google_event_id, $meeting_id);
            $update->execute();

            echo "<p>📅 Event berhasil dibuat dan undangan dikirim ke: <b>" . htmlspecialchars($participant_email) . "</b></p>";
            echo "<p><a href='" . htmlspecialchars($createdEvent->htmlLink) . "' target='_blank'>Lihat di Google Calendar</a></p>";
        } catch (Exception $e) {
            echo "<p>⚠️ Gagal menambahkan ke Google Calendar: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p>⚠️ Belum login ke Google. <a href='login_google.php'>Login di sini</a> untuk sinkronisasi.</p>";
    }
} else {
    echo "<p>❌ Gagal menyimpan meeting: " . htmlspecialchars($stmt->error) . "</p>";
}

$stmt->close();
$conn->close();

echo "<p><a href='list_meetings.php'>⬅️ Kembali ke Daftar Meeting</a></p>";
?>
