
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Jadwal Meeting</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Jadwalkan Meeting</h2>
        <form action="process_meeting.php" method="POST">
            <label for="title">Judul Meeting:</label>
            <input type="text" id="title" name="title" required>

            <label for="description">Deskripsi:</label>
            <textarea id="description" name="description"></textarea>

            <label for="date_time">Waktu Meeting:</label>
            <input type="datetime-local" id="date_time" name="date_time" required>

            <label for="location">Lokasi:</label>
            <input type="text" id="location" name="location">
            <label for="participant_email" class="form-label">Email Peserta</label>
            
            <textarea class="form-control" id="participant_email" name="participant_email"
                 placeholder="contoh1@gmail.com, contoh2@gmail.com, contoh3@gmail.com" rows="2" required></textarea>
            


            

            <button type="submit">Simpan Jadwal</button>
        </form>
    </div>
</body>
</html>