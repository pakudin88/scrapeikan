<?php
require_once 'db.php';

$contacts = [];
// Ambil kontak yang memiliki nomor telepon yang valid
$sql = "SELECT name, phone_number FROM places WHERE phone_number IS NOT NULL AND phone_number != 'N/A' AND phone_number != ''";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor Bantuan Promosi WA</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: #fff; }
        h1, h2 { text-align: center; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        textarea { width: 100%; box-sizing: border-box; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .send-button { padding: 5px 10px; background-color: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; }
        .send-button:hover { background-color: #218838; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Dasbor Bantuan Promosi WA</h1>

        <div class="warning">
            <strong>Peringatan:</strong> Mengirim pesan promosi massal (blasting) dapat menyebabkan nomor WhatsApp Anda diblokir. Gunakan fitur ini dengan bijak dan kirim pesan secara manual satu per satu untuk mengurangi risiko.
        </div>

        <h2>Tulis Pesan Promosi Anda</h2>
        <textarea id="message-template" rows="6" placeholder="Contoh: Halo! Kami dari [Nama Usaha Anda], ingin menawarkan promo spesial..."></textarea>

        <h2>Daftar Kontak</h2>
        <table>
            <thead>
                <tr>
                    <th>Nama Tempat</th>
                    <th>Nomor Telepon</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contacts)): ?>
                    <tr>
                        <td colspan="3" style="text-align:center;">Tidak ada kontak yang ditemukan di database.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($contacts as $contact): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($contact['name']); ?></td>
                            <td class="phone-number"><?php echo htmlspecialchars($contact['phone_number']); ?></td>
                            <td>
                                <a href="#" class="send-button">Kirim Pesan</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- JavaScript logic will be added here -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const messageTemplate = document.getElementById('message-template');
        const sendButtons = document.querySelectorAll('.send-button');

        sendButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                const message = messageTemplate.value;
                if (!message.trim()) {
                    alert('Harap tulis pesan promosi terlebih dahulu.');
                    return;
                }

                const row = this.closest('tr');
                const phoneNumberTD = row.querySelector('.phone-number');
                let phoneNumber = phoneNumberTD.textContent.trim();

                // Format nomor telepon:
                // 1. Hapus karakter selain angka
                phoneNumber = phoneNumber.replace(/\D/g, '');
                // 2. Jika nomor dimulai dengan 0, ganti dengan 62
                if (phoneNumber.startsWith('0')) {
                    phoneNumber = '62' + phoneNumber.substring(1);
                }
                // 3. Jika tidak dimulai dengan 62, tambahkan 62 (asumsi nomor lokal)
                else if (!phoneNumber.startsWith('62')) {
                    phoneNumber = '62' + phoneNumber;
                }

                const encodedMessage = encodeURIComponent(message);
                const waLink = `https://wa.me/${phoneNumber}?text=${encodedMessage}`;

                // Buka link di tab baru
                window.open(waLink, '_blank');
            });
        });
    });
    </script>
</body>
</html>
