<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../config/db.php';

// Cek apakah parameter 'id' ada di URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM meetings WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "<h3>✅ Jadwal Meeting berhasil dihapus!</h3>";
            echo "<a href='list_meetings.php'>⬅️ Kembali ke Daftar Meeting</a>";
        } else {
            echo "❌ Gagal menghapus jadwal: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "⚠️ ID tidak valid.";
    }
} else {
    echo "⚠️ ID tidak ditemukan.";
}

$conn->close();
?>
