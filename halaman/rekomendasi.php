<?php
// Memasukkan komponen navbar
include '../komponen/navbar.php';
// Memasukkan file koneksi
include '../koneksi/koneksi.php';

// Mengambil data brand dari database
$query = "SELECT DISTINCT brand FROM spek_hp";
$result = $conn->query($query);

if (!$result) {
    die("Query gagal: " . $conn->error);
}

$brands = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $brands[] = $row['brand'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekomendasi - EzPhone-Guide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="../asset/css/style.css">
    <link rel="stylesheet" href="../asset/css/rekomendasi.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mt-5">Rekomendasi Smartphone</h1>

        <form id="rekomendasiForm" action="hasil.php" method="POST">
            <div class="form-group">
                <label for="kebutuhan">Pilih Kebutuhan</label>
                <select class="form-control" id="kebutuhan" name="kebutuhan">
                    <option value="gaming">Gaming</option>
                    <option value="fotografi">Fotografi</option>
                    <option value="konten_kreator">Konten Kreator</option>
                    <option value="sehari_hari">Sehari-hari</option>
                </select>
            </div>

            <div class="form-group">
                <label>Pilih Brand</label>
                <?php foreach ($brands as $brand): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="brand[]" value="<?php echo htmlspecialchars($brand); ?>" id="brand-<?php echo htmlspecialchars($brand); ?>">
                        <label class="form-check-label" for="brand-<?php echo htmlspecialchars($brand); ?>">
                            <?php echo htmlspecialchars($brand); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-group">
                <label for="hargaRange">Rentang Harga</label>
                <div id="hargaRange"></div>
                <div id="hargaRangeValue">Rentang Harga: Rp 1.000.000 - Lebih dari Rp 15.000.000</div>
                <input type="hidden" id="hargaMin" name="hargaMin" value="1000000">
                <input type="hidden" id="hargaMax" name="hargaMax" value="999999999">
            </div>

            <div class="form-group">
                <label>Pilih Fitur</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="fitur[]" value="nfc" id="fitur-nfc">
                    <label class="form-check-label" for="fitur-nfc">
                        NFC
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="fitur[]" value="waterproof" id="fitur-waterproof">
                    <label class="form-check-label" for="fitur-waterproof">
                        Waterproof
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="fitur[]" value="jack_3_5_mm" id="fitur-jack_3_5_mm">
                    <label class="form-check-label" for="fitur-jack_3_5_mm">
                        Jack 3.5 mm
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-dark btn-lg btn-block btn-animated">Dapatkan Rekomendasi</button>
        </form>
    </div>
<br><br>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../asset/js/script.js"></script>
    <script src="../asset/js/rekomendasi.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
</body>
</html>
