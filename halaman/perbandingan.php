<?php
// Memasukkan komponen navbar
include '../komponen/navbar.php';
// Memasukkan file koneksi
include '../koneksi/koneksi.php';

// Mengambil data smartphone dari database
$query = "SELECT id, `Nama Produk`, `RAM (GB)`, `Memori Internal (GB)` FROM spek_hp";
$result = $conn->query($query);

if (!$result) {
    die("Query gagal: " . $conn->error);
}

$smartphones = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $smartphones[] = $row;
    }
}

// Mengambil data spesifikasi smartphone jika form telah disubmit
$smartphone1 = null;
$smartphone2 = null;
if (isset($_POST['smartphone1']) && isset($_POST['smartphone2'])) {
    $smartphone1Id = $_POST['smartphone1'];
    $smartphone2Id = $_POST['smartphone2'];

    // Mengambil data spesifikasi smartphone pertama
    $query1 = "SELECT * FROM spek_hp WHERE id = ?";
    $stmt1 = $conn->prepare($query1);
    $stmt1->bind_param("i", $smartphone1Id);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    $smartphone1 = $result1->fetch_assoc();

    // Mengambil data spesifikasi smartphone kedua
    $query2 = "SELECT * FROM spek_hp WHERE id = ?";
    $stmt2 = $conn->prepare($query2);
    $stmt2->bind_param("i", $smartphone2Id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $smartphone2 = $result2->fetch_assoc();

    // Mengambil skor AnTuTu untuk smartphone pertama
    $antutuSql1 = "
        SELECT s.antutu_10
        FROM spek_hp sp
        LEFT JOIN soc s ON REPLACE(REPLACE(REPLACE(LOWER(sp.Prosesor), ',', ''), 'deca', ''), 'octa', '')
            LIKE CONCAT('%', LOWER(s.processor), '%')
        WHERE sp.`Nama Produk` = '" . $conn->real_escape_string($smartphone1['Nama Produk']) . "'
    ";
    $antutuResult1 = $conn->query($antutuSql1);
    $antutuScore1 = $antutuResult1->fetch_assoc()['antutu_10'] ?? 'N/A';

    // Mengambil skor AnTuTu untuk smartphone kedua
    $antutuSql2 = "
        SELECT s.antutu_10
        FROM spek_hp sp
        LEFT JOIN soc s ON REPLACE(REPLACE(REPLACE(LOWER(sp.Prosesor), ',', ''), 'deca', ''), 'octa', '')
            LIKE CONCAT('%', LOWER(s.processor), '%')
        WHERE sp.`Nama Produk` = '" . $conn->real_escape_string($smartphone2['Nama Produk']) . "'
    ";
    $antutuResult2 = $conn->query($antutuSql2);
    $antutuScore2 = $antutuResult2->fetch_assoc()['antutu_10'] ?? 'N/A';
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
    <link rel="stylesheet" href="../asset/css/perbandingan.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mt-5">Perbandingan Smartphone</h1>

        <form id="perbandinganForm" method="POST">
            <div class="form-group">
                <label for="smartphone1">Pilih Smartphone Pertama</label>
                <select class="form-control" id="smartphone1" name="smartphone1">
                    <?php foreach ($smartphones as $smartphone): ?>
                        <option value="<?php echo htmlspecialchars($smartphone['id']); ?>">
                            <?php echo htmlspecialchars($smartphone['Nama Produk']) . " " . htmlspecialchars($smartphone['RAM (GB)']) . "  / " . htmlspecialchars($smartphone['Memori Internal (GB)']) ; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="smartphone2">Pilih Smartphone Kedua</label>
                <select class="form-control" id="smartphone2" name="smartphone2">
                    <?php foreach ($smartphones as $smartphone): ?>
                        <option value="<?php echo htmlspecialchars($smartphone['id']); ?>">
                            <?php echo htmlspecialchars($smartphone['Nama Produk']) . " " . htmlspecialchars($smartphone['RAM (GB)']) . "  / " . htmlspecialchars($smartphone['Memori Internal (GB)']) ; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-dark btn-lg btn-block btn-animated">Bandingkan</button>
        </form>

        <?php if ($smartphone1 && $smartphone2): ?>
            <div id="comparisonResult" class="mt-5"
                 data-smartphone1='<?php echo htmlspecialchars(json_encode($smartphone1)); ?>'
                 data-smartphone2='<?php echo htmlspecialchars(json_encode($smartphone2)); ?>'
                 data-antutu-score1='<?php echo htmlspecialchars($antutuScore1); ?>'
                 data-antutu-score2='<?php echo htmlspecialchars($antutuScore2); ?>'>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../asset/js/perbandingan.js"></script>
</body>
</html>
