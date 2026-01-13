<?php
date_default_timezone_set('Asia/Jakarta');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../config/db.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ambil semua meeting dalam 1 jam ke depan
$now = date('Y-m-d H:i:s');
$now = date('Y-m-d H:i:s');
$oneHourLater = date('Y-m-d H:i:s', strtotime('+1 hour'));
$sql = "SELECT * FROM meetings WHERE date_time BETWEEN '$now' AND '$oneHourLater'";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "✅ Tidak ada meeting dalam 1 jam ke depan.";
    exit;
}

while ($row = $result->fetch_assoc()) {
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi server Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'danielndarap@gmail.com'; // ganti dengan email kamu
        $mail->Password = 'wobk ofsv qllh dzfa'; // gunakan App Password Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Pengirim & penerima
        $mail->setFrom('danielndarap@gmail.com', 'E-Meeting Reminder');
        
        if (!empty($row['participant_email']) && filter_var($row['participant_email'], FILTER_VALIDATE_EMAIL)) {
    $mail->addAddress($row['participant_email']);
} else {
    echo "⚠️ Alamat email tidak valid untuk meeting ID {$row['id']}.<br>";
    continue;
}


        // Konten email
        $mail->isHTML(true);
        $mail->Subject = '📅 Reminder: ' . $row['title'];
        $mail->Body = "
            <h3>Hai, ini pengingat meeting kamu!</h3>
            <p><strong>Judul:</strong> {$row['title']}</p>
            <p><strong>Deskripsi:</strong> {$row['description']}</p>
            <p><strong>Waktu:</strong> {$row['date_time']}</p>
            <p><strong>Lokasi:</strong> {$row['location']}</p>
            <br>
            <p>Terima kasih,<br><b>Tim E-Meeting</b></p>
        ";

        $mail->send();
        echo "✅ Email reminder terkirim ke {$row['participant_email']}<br>";
    } catch (Exception $e) {
        echo "❌ Gagal kirim ke {$row['participant_email']}. Error: {$mail->ErrorInfo}<br>";
    }
}
?>
