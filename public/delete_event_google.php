<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../config/db.php';
require '../config/google-config.php';

if (!isset($_SESSION['access_token'])) {
    die("⚠️ Harap login dengan Google terlebih dahulu. <a href='login_google.php'>Login di sini</a>");
}

$client->setAccessToken($_SESSION['access_token']);
$service = new Google_Service_Calendar($client);

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Ambil google_event_id dari database
    $result = $conn->query("SELECT google_event_id, title FROM meetings WHERE id = $id");
    $row = $result->fetch_assoc();

    if ($row && !empty($row['google_event_id'])) {
        $google_event_id = $row['google_event_id'];
        $calendarId = 'primary';

        try {
            // Hapus event dari Google Calendar + kirim notifikasi ke peserta
            $service->events->delete($calendarId, $google_event_id, ['sendUpdates' => 'all']);
            echo "✅ Event '{$row['title']}' berhasil dihapus dari Google Calendar dan notifikasi dikirim ke peserta.<br>";
        } catch (Exception $e) {
            echo "⚠️ Gagal hapus di Google Calendar: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ Meeting tidak ditemukan atau belum disinkron ke Google Calendar.<br>";
    }

    // Hapus juga dari database
    $conn->query("DELETE FROM meetings WHERE id = $id");
    echo "🗑️ Meeting berhasil dihapus dari database.";
} else {
    echo "❌ ID tidak ditemukan.";
}

$conn->close();
?>
