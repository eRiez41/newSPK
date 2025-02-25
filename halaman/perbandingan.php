<?php
// Include file koneksi.php
include '../koneksi/koneksi.php';

// Include file navbar.php
include '../komponen/navbar.php';

// Fungsi untuk mendapatkan daftar brand
function getBrands($conn) {
    $brands = [];
    $query = "SELECT DISTINCT `Brand` FROM `spek_hp`";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $brands[] = $row['Brand'];
        }
    } else {
        die("Error in SQL query: " . $conn->error);
    }
    return $brands;
}

// Fungsi untuk mendapatkan daftar smartphone berdasarkan brand
function getSmartphonesByBrand($conn, $brand) {
    $smartphones = [];
    $query = "SELECT `Nama Produk`, `RAM (GB)`, `Memori Internal (GB)` FROM `spek_hp` WHERE `Brand` = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $brand);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $smartphones[] = $row;
            }
        }
    } else {
        die("Error in SQL query: " . $conn->error);
    }
    return $smartphones;
}

// Fungsi untuk mendapatkan spesifikasi smartphone berdasarkan nama produk
function getSmartphoneSpecs($conn, $namaProduk) {
    $specs = [];
    $query = "SELECT * FROM `spek_hp` WHERE `Nama Produk` = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $namaProduk);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $specs = $result->fetch_assoc();
        }
    } else {
        die("Error in SQL query: " . $conn->error);
    }
    return $specs;
}

// Fungsi untuk mendapatkan skor Antutu berdasarkan nama produk
function getAntutuScore($conn, $namaProduk) {
    $antutuScore = null;
    $query = "SELECT s.antutu_10
              FROM spek_hp sp
              LEFT JOIN soc s ON REPLACE(REPLACE(REPLACE(LOWER(sp.Prosesor), ',', ''), 'deca', ''), 'octa', '')
                  LIKE CONCAT('%', LOWER(s.processor), '%')
              WHERE sp.`Nama Produk` = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $namaProduk);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $antutuScore = $row['antutu_10'];
        }
    }
    return $antutuScore;
}

// Mendapatkan daftar brand
$brands = getBrands($conn);

// Mendapatkan daftar smartphone jika brand dipilih
$selectedBrand1 = isset($_POST['brand1']) ? $_POST['brand1'] : '';
$selectedBrand2 = isset($_POST['brand2']) ? $_POST['brand2'] : '';
$smartphones1 = [];
$smartphones2 = [];
if ($selectedBrand1) {
    $smartphones1 = getSmartphonesByBrand($conn, $selectedBrand1);
}
if ($selectedBrand2) {
    $smartphones2 = getSmartphonesByBrand($conn, $selectedBrand2);
}

// Mendapatkan spesifikasi smartphone jika smartphone dipilih
$selectedSmartphone1 = isset($_POST['smartphone1']) ? $_POST['smartphone1'] : '';
$selectedSmartphone2 = isset($_POST['smartphone2']) ? $_POST['smartphone2'] : '';
$specs1 = [];
$specs2 = [];
if ($selectedSmartphone1) {
    $specs1 = getSmartphoneSpecs($conn, $selectedSmartphone1);
    $specs1['Antutu Score'] = getAntutuScore($conn, $selectedSmartphone1); // Tambahkan skor Antutu
}
if ($selectedSmartphone2) {
    $specs2 = getSmartphoneSpecs($conn, $selectedSmartphone2);
    $specs2['Antutu Score'] = getAntutuScore($conn, $selectedSmartphone2); // Tambahkan skor Antutu
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perbandingan Smartphone - EzPhone</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <style>
        .card { margin-bottom: 20px; border: none; }
        .card-title { font-size: 1.5rem; font-weight: bold; }
        .spec-item { margin-bottom: 10px; }
        .card-img-top { max-height: 200px; object-fit: contain; }
        .better { color: green; }
        .worse { color: red; }
        .equal { color: blue; }
        .table th, .table td { vertical-align: middle; }
        .image-container { text-align: center; margin-bottom: 20px; }
        .image-container img { max-width: 100%; height: auto; }
        .product-name { font-size: 1.2rem; font-weight: bold; }
        .product-price { font-size: 1rem; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mt-5">Perbandingan Smartphone
        <button type="button" class="btn btn-success btn-sm btn-custom" onclick="window.open('https://forms.gle/ik7qbmuzU4ELgLdz5', '_blank')">
        Penilaian
    </button>
        </h1>
<br>
        <form id="perbandinganForm" method="POST" action="">
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="brand1">Pilih Merek Pertama</label>
                    <select class="form-control" id="brand1" name="brand1" onchange="this.form.submit()">
                        <option value="">Pilih Merek</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo htmlspecialchars($brand); ?>" <?php echo $selectedBrand1 === $brand ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label for="brand2">Pilih Merek Kedua</label>
                    <select class="form-control" id="brand2" name="brand2" onchange="this.form.submit()">
                        <option value="">Pilih Merek</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo htmlspecialchars($brand); ?>" <?php echo $selectedBrand2 === $brand ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if (!empty($smartphones1) && !empty($smartphones2)): ?>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="smartphone1">Pilih Smartphone Pertama</label>
                        <select class="form-control" id="smartphone1" name="smartphone1" onchange="this.form.submit()">
                            <option value="">Pilih Smartphone</option>
                            <?php foreach ($smartphones1 as $smartphone): ?>
                                <option value="<?php echo htmlspecialchars($smartphone['Nama Produk']); ?>" <?php echo $selectedSmartphone1 === $smartphone['Nama Produk'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($smartphone['Nama Produk'] . " " . $smartphone['RAM (GB)'] . "/" . $smartphone['Memori Internal (GB)']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="smartphone2">Pilih Smartphone Kedua</label>
                        <select class="form-control" id="smartphone2" name="smartphone2" onchange="this.form.submit()">
                            <option value="">Pilih Smartphone</option>
                            <?php foreach ($smartphones2 as $smartphone): ?>
                                <option value="<?php echo htmlspecialchars($smartphone['Nama Produk']); ?>" <?php echo $selectedSmartphone2 === $smartphone['Nama Produk'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($smartphone['Nama Produk'] . " " . $smartphone['RAM (GB)'] . "/" . $smartphone['Memori Internal (GB)']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-dark btn-lg btn-block btn-animated">Bandingkan</button>
        </form>

        <?php if (!empty($specs1) && !empty($specs2)): ?>
            <div id="comparisonResult" class="mt-5">

                <!-- Menampilkan Gambar Smartphone -->
                <div class="row image-container">
                    <div class="col-md-6">
                        <?php if (!empty($specs1['Image URL'])): ?>
                            <img src="<?php echo htmlspecialchars($specs1['Image URL']); ?>" alt="<?php echo htmlspecialchars($specs1['Brand'] . ' ' . $selectedSmartphone1); ?>">
                        <?php endif; ?>
                        <div class="product-name"><?php echo htmlspecialchars($specs1['Brand'] . ' ' . $selectedSmartphone1); ?></div>
                        <div class="product-price <?php echo compareSpec('Harga', $specs1['Harga'], $specs2['Harga']); ?>">
                            <?php echo htmlspecialchars($specs1['Harga']); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php if (!empty($specs2['Image URL'])): ?>
                            <img src="<?php echo htmlspecialchars($specs2['Image URL']); ?>" alt="<?php echo htmlspecialchars($specs2['Brand'] . ' ' . $selectedSmartphone2); ?>">
                        <?php endif; ?>
                        <div class="product-name"><?php echo htmlspecialchars($specs2['Brand'] . ' ' . $selectedSmartphone2); ?></div>
                        <div class="product-price <?php echo compareSpec('Harga', $specs2['Harga'], $specs1['Harga']); ?>">
                            <?php echo htmlspecialchars($specs2['Harga']); ?>
                        </div>
                    </div>
                </div>

                <!-- Tabel Perbandingan Spesifikasi -->
                <table class="table table">
                    <tbody>
                        <?php
                        // Urutan spesifikasi yang diinginkan
                        $orderedSpecs = [
                            'Brand', 'Tahun Rilis', 'Jaringan', 'SIM Slots', 'NFC', 'USB', 'Material',
                            'Ukuran Layar', 'Screen Resolution', 'Technology', 'Waterproof', 'Prosesor', 'GPU', 'Antutu Score',
                            'RAM (GB)', 'Memori Internal (GB)', 'OS Version & Version Detail', 'Kapasitas Baterai', 'Daya Fast Charging',
                            'Resolusi Kamera Belakang', 'Resolusi Kamera Utama Lainnya', 'Jumlah Kamera Belakang', 'Resolusi Kamera Depan',
                            'Video', 'Sensor', '3.5mm Jack'
                        ];

                        // Daftar spesifikasi yang tidak perlu ditampilkan
                        $excludedSpecs = ['Nama Produk','Dual Kamera Belakang', 'Dual Kamera Depan', 'Flash', 'Dual Flash'];

                        // Ambil warna untuk Prosesor dan GPU berdasarkan Skor Antutu
                        $antutuColor1 = compareSpec('Antutu Score', $specs1['Antutu Score'], $specs2['Antutu Score']);
                        $antutuColor2 = compareSpec('Antutu Score', $specs2['Antutu Score'], $specs1['Antutu Score']);

                        foreach ($orderedSpecs as $key): ?>
                            <?php if (isset($specs1[$key]) && isset($specs2[$key]) && !in_array($key, $excludedSpecs)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($key); ?></strong></td>
                                    <td class="<?php
                                        if ($key === 'Prosesor' || $key === 'GPU') {
                                            echo $antutuColor1; // Warna berdasarkan Skor Antutu smartphone 1
                                        } else {
                                            echo compareSpec($key, $specs1[$key], $specs2[$key]);
                                        }
                                    ?>">
                                        <?php echo htmlspecialchars($specs1[$key]); ?>
                                    </td>
                                    <td class="<?php
                                        if ($key === 'Prosesor' || $key === 'GPU') {
                                            echo $antutuColor2; // Warna berdasarkan Skor Antutu smartphone 2
                                        } else {
                                            echo compareSpec($key, $specs2[$key], $specs1[$key]);
                                        }
                                    ?>">
                                        <?php echo htmlspecialchars($specs2[$key]); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Fungsi untuk membandingkan spesifikasi dan menentukan warna
function compareSpec($key, $value1, $value2) {
    // Daftar spesifikasi yang akan dibandingkan
    $comparableSpecs = [
        'Tahun Rilis', 'Jaringan', 'Technology', 'Ukuran Layar', 'Screen Resolution',
        'OS Version & Version Detail', 'RAM (GB)', 'Memori Internal (GB)', 'NFC',
        'USB', 'Kapasitas Baterai', 'Daya Fast Charging', 'Waterproof', 'Sensor',
        '3.5mm Jack', 'Resolusi Kamera Belakang', 'Resolusi Kamera Utama Lainnya',
        'Jumlah Kamera Belakang', 'Resolusi Kamera Depan', 'Video', 'Harga', 'Material', 'Antutu Score',
        'SIM Slots'
    ];

    // Jika spesifikasi tidak perlu dibandingkan, kembalikan warna hitam
    if (!in_array($key, $comparableSpecs)) {
        return '';
    }

    // Logika perbandingan khusus untuk Material
    if ($key === 'Material') {
        $count1 = substr_count($value1, ',') + 1; // Hitung jumlah komponen material
        $count2 = substr_count($value2, ',') + 1;
        if ($count1 == $count2) {
            return 'equal';
        } elseif ($count1 > $count2) {
            return 'better';
        } else {
            return 'worse';
        }
    }

    // Logika perbandingan khusus untuk Video
    if ($key === 'Video') {
        // Urutan prioritas resolusi
        $resolutions = ['HD', '720p', 'FHD', '1080p', '2K', '4K', '8K'];
        $value1Res = strtoupper(explode('@', $value1)[0]); // Ambil resolusi dari value1
        $value2Res = strtoupper(explode('@', $value2)[0]); // Ambil resolusi dari value2

        // Cari indeks resolusi dalam array
        $index1 = array_search($value1Res, $resolutions);
        $index2 = array_search($value2Res, $resolutions);

        // Bandingkan resolusi
        if ($index1 === $index2) {
            // Jika resolusi sama, bandingkan kelengkapan fitur
            $count1 = substr_count($value1, ',') + 1; // Hitung jumlah fitur
            $count2 = substr_count($value2, ',') + 1;
            if ($count1 == $count2) {
                return 'equal';
            } elseif ($count1 > $count2) {
                return 'better';
            } else {
                return 'worse';
            }
        } elseif ($index1 > $index2) {
            return 'better';
        } else {
            return 'worse';
        }
    }

    // Logika perbandingan untuk spesifikasi lainnya
    if ($value1 == $value2) {
        return 'equal';
    } elseif (($key === 'Tahun Rilis' && strtotime($value1) > strtotime($value2)) ||
              ($key === 'Jaringan' && substr_count($value1, ',') > substr_count($value2, ',')) ||
              ($key === 'Technology' && stripos($value1, 'AMOLED') !== false && stripos($value2, 'AMOLED') === false) ||
              ($key === 'Ukuran Layar' && $value1 > $value2) ||
              ($key === 'Screen Resolution' && explode('×', $value1)[0] * explode('×', $value1)[1] > explode('×', $value2)[0] * explode('×', $value2)[1]) ||
              ($key === 'OS Version & Version Detail' && version_compare(explode(',', $value1)[0], explode(',', $value2)[0], '>')) ||
              ($key === 'RAM (GB)' && str_replace(' GB', '', $value1) > str_replace(' GB', '', $value2)) ||
              ($key === 'Memori Internal (GB)' && str_replace(' GB', '', $value1) > str_replace(' GB', '', $value2)) ||
              ($key === 'NFC' && $value1 === 'True' && $value2 === 'No') ||
              ($key === 'USB' && stripos($value1, 'Type-C') !== false && stripos($value2, 'Type-C') === false) ||
              ($key === 'Kapasitas Baterai' && $value1 > $value2) ||
              ($key === 'Daya Fast Charging' && str_replace(' W', '', $value1) > str_replace(' W', '', $value2)) ||
              ($key === 'Waterproof' && $value1 === 'True' && $value2 === 'No') ||
              ($key === 'Sensor' && substr_count($value1, ',') > substr_count($value2, ',')) ||
              ($key === '3.5mm Jack' && $value1 === 'True' && $value2 === 'No') ||
              ($key === 'Resolusi Kamera Belakang' && $value1 > $value2) ||
              ($key === 'SIM Slots' && $value1 > $value2) ||
              ($key === 'Resolusi Kamera Utama Lainnya' && count(explode(',', $value1)) > count(explode(',', $value2))) ||
              ($key === 'Jumlah Kamera Belakang' && $value1 > $value2) ||
              ($key === 'Resolusi Kamera Depan' && $value1 > $value2) ||
              ($key === 'Harga' && str_replace(['Rp ', '.'], '', $value1) < str_replace(['Rp ', '.'], '', $value2)) ||
              ($key === 'Antutu Score' && $value1 > $value2)) {
        return 'better';
    } else {
        return 'worse';
    }
}
?>