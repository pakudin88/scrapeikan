<?php
require_once 'config.php';
require_once 'db.php';

$message = '';

// Fungsi untuk mengambil detail tempat untuk mendapatkan nomor telepon
function getPlaceDetails($place_id, $api_key) {
    $details_url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$place_id}&fields=name,formatted_phone_number,website&key={$api_key}";
    $details_response = @file_get_contents($details_url);
    if ($details_response === FALSE) {
        return null;
    }
    $details_data = json_decode($details_response, true);
    return $details_data['result'];
}

if (isset($_POST['search'])) {
    $keyword = urlencode($_POST['keyword']);
    $location = urlencode($_POST['location']);

    if (GOOGLE_API_KEY === 'MASUKKAN_API_KEY_ANDA_DISINI' || GOOGLE_API_KEY === '') {
        $message = '<p style="color:red;">Error: Harap masukkan Google API Key Anda di file config.php.</p>';
    } else {
        // 1. Mencari place_id menggunakan Text Search
        $search_url = "https://maps.googleapis.com/maps/api/place/textsearch/json?query={$keyword}+in+{$location}&key=" . GOOGLE_API_KEY;

        $search_response = @file_get_contents($search_url);
        if ($search_response === FALSE) {
            $message = '<p style="color:red;">Error: Gagal menghubungi Google API. Periksa koneksi internet atau API key Anda.</p>';
        } else {
            $search_data = json_decode($search_response, true);
            $new_places_count = 0;

            if ($search_data['status'] === 'OK') {
                foreach ($search_data['results'] as $place) {
                    $place_id = $place['place_id'];

                    // 2. Mengambil detail (termasuk nomor telepon)
                    $place_details = getPlaceDetails($place_id, GOOGLE_API_KEY);

                    if ($place_details) {
                        $name = $conn->real_escape_string($place_details['name']);
                        $phone = isset($place_details['formatted_phone_number']) ? $conn->real_escape_string($place_details['formatted_phone_number']) : 'N/A';
                        $address = isset($place['formatted_address']) ? $conn->real_escape_string($place['formatted_address']) : 'N/A';
                        $website = isset($place_details['website']) ? $conn->real_escape_string($place_details['website']) : 'N/A';

                        // Cek duplikat berdasarkan place_id
                        $check_sql = "SELECT id FROM places WHERE place_id = '$place_id'";
                        $result = $conn->query($check_sql);

                        if ($result->num_rows == 0) {
                            // Simpan ke database jika belum ada
                            $insert_sql = "INSERT INTO places (place_id, name, phone_number, address, website) VALUES ('$place_id', '$name', '$phone', '$address', '$website')";
                            if ($conn->query($insert_sql) === TRUE) {
                                $new_places_count++;
                            }
                        }
                    }
                }
                $message = "<p style='color:green;'>Pencarian selesai. Berhasil menambahkan {$new_places_count} data baru ke database.</p>";
            } else {
                $message = "<p style='color:red;'>Error dari Google API: " . $search_data['status'] . " - " . (isset($search_data['error_message']) ? $search_data['error_message'] : 'Tidak ada detail tambahan.') . "</p>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencari Data Google Maps</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        form { display: flex; flex-direction: column; }
        label { margin-bottom: 5px; }
        input[type="text"] { padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 3px; }
        input[type="submit"] { padding: 10px; background-color: #007BFF; color: white; border: none; border-radius: 3px; cursor: pointer; }
        input[type="submit"]:hover { background-color: #0056b3; }
        #results { margin-top: 20px; }
    </style>
</head>
<body>

    <div class="container">
        <h1>Pencari Data Google Maps</h1>
        <form action="fetch_places.php" method="post">
            <label for="keyword">Kata Kunci:</label>
            <input type="text" id="keyword" name="keyword" placeholder="Contoh: restoran, bengkel mobil" required>

            <label for="location">Lokasi:</label>
            <input type="text" id="location" name="location" placeholder="Contoh: Jakarta, Surabaya" required>

            <input type="submit" name="search" value="Cari & Simpan Data">
        </form>

        <div id="results">
            <?php echo $message; ?>
        </div>
    </div>

</body>
</html>
