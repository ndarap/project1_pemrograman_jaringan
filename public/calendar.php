<?php
require '../config/db.php';
$result = $conn->query("SELECT title, date_time FROM meetings");
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = ['title' => $row['title'], 'start' => $row['date_time']];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kalender Meeting - E-Meeting</title>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2 class="text-center mb-4">📅 Kalender Jadwal Meeting</h2>
    <div id="calendar"></div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        events: <?= json_encode($events) ?>
      });
      calendar.render();
    });
  </script>
</body>
</html>
