// Fungsi untuk mengonversi resolusi layar
function convertScreenResolution($resolution) {
    $resolutionParts = explode(' × ', $resolution);
    $width = intval($resolutionParts[0]);
    if ($width >= 720 && $width < 1080) {
        return 'HD';
    } elseif ($width >= 1080 && $width < 1440) {
        return 'FHD';
    } elseif ($width >= 1440 && $width < 2160) {
        return '2K';
    } elseif ($width >= 2160 && $width < 2880) {
        return '2K+';
    } elseif ($width >= 2880 && $width < 3840) {
        return '3K';
    } elseif ($width >= 3840 && $width < 5120) {
        return '3K+';
    } elseif ($width >= 5120 && $width < 7680) {
        return '4K';
    } elseif ($width >= 7680) {
        return '8K';
    } else {
        return 'Unknown Resolution';
    }
}

// Fungsi untuk mendapatkan skor berdasarkan kriteria dan nilai spesifikasi
function getScore($kriteria, $nilai) {
    global $conn;

    // Hapus "GB" dari nilai jika ada
    $nilai = str_replace(' GB', '', $nilai);
    // Hapus "mAh" dari nilai jika ada
    $nilai = str_replace(' mAh', '', $nilai);
    // Hapus "MP" dari nilai jika ada
    $nilai = str_replace(' MP', '', $nilai);
    // Hapus "W" dari nilai jika ada
    $nilai = str_replace(' W', '', $nilai);

    // Query untuk mendapatkan skor dari tabel skoring
    if ($kriteria == 'Screen Resolution' || $kriteria == 'Technology') {
        // Khusus untuk Screen Resolution dan Technology, langsung periksa nilai_string
        $query = "SELECT skor FROM skoring WHERE kategori_kriteria = '$kriteria' AND nilai_string = '$nilai' LIMIT 1";
    } else {
        $query = "SELECT skor FROM skoring WHERE kategori_kriteria = '$kriteria' AND (
            (nilai_min IS NOT NULL AND nilai_max IS NOT NULL AND $nilai BETWEEN nilai_min AND nilai_max) OR
            (nilai_string IS NOT NULL AND nilai_string = '$nilai')
        ) LIMIT 1";
    }

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['skor'];
    }

    return 0; // Return 0 jika tidak ada skor yang cocok
}

// Fungsi untuk menghitung bobot berdasarkan jumlah kriteria
function calculateWeight($totalCriteria) {
    return 1 / $totalCriteria;
}

// Fungsi untuk menghasilkan tabel
function generateTable($tableTitle, $criteria, $row, $antutuScore) {
    $totalCriteria = count($criteria);
    $weight = calculateWeight($totalCriteria);
    $totalScore = 0;
    $detailedCalculation = [];

    $table = '
    <table class="table table-bordered mt-3">
        <thead>
            <tr><th colspan="4" class="text-center">' . htmlspecialchars($tableTitle) . '</th></tr>
            <tr>
                <th>Kriteria</th>
                <th>Spesifikasi</th>
                <th>Skor</th>
                <th>Bobot</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($criteria as $item) {
        $specification = '';
        $score = 0;
        switch ($item) {
            case 'RAM (GB)':
                $specification = htmlspecialchars($row['RAM (GB)']);
                $score = getScore('RAM (GB)', $row['RAM (GB)']);
                break;
            case 'Memori Internal (GB)':
                $specification = htmlspecialchars($row['Memori Internal (GB)']);
                $score = getScore('Memori Internal (GB)', $row['Memori Internal (GB)']);
                break;
            case 'Skor AnTuTu':
                $specification = $antutuScore !== null ? htmlspecialchars($antutuScore) : 'N/A';
                $score = getScore('Skor_AnTuTu', $antutuScore);
                break;
            case 'Kapasitas Baterai':
                $specification = htmlspecialchars($row['Kapasitas Baterai']);
                $score = getScore('Kapasitas Baterai', $row['Kapasitas Baterai']);
                break;
            case 'Technology':
                $specification = htmlspecialchars($row['Technology']);
                $score = getScore('Technology', $row['Technology']);
                break;
            case 'Resolusi Kamera Belakang':
                $specification = htmlspecialchars($row['Resolusi Kamera Belakang']);
                $score = getScore('Resolusi Kamera Belakang', $row['Resolusi Kamera Belakang']);
                break;
            case 'Resolusi Kamera Depan':
                $specification = htmlspecialchars($row['Resolusi Kamera Depan']);
                $score = getScore('Resolusi Kamera Depan', $row['Resolusi Kamera Depan']);
                break;
            case 'Screen Resolution':
                $specification = convertScreenResolution(htmlspecialchars($row['Screen Resolution']));
                $score = getScore('Screen Resolution', $specification);
                break;
            case 'Daya Fast Charging':
                $specification = htmlspecialchars($row['Daya Fast Charging']);
                $score = getScore('Daya Fast Charging', $row['Daya Fast Charging']);
                break;
            case 'Technology (Jenis Layar)':
                $specification = htmlspecialchars($row['Technology']);
                $score = getScore('Technology', $row['Technology']);
                break;
        }

        $weightedScore = $weight * $score;
        $totalScore += $weightedScore;
        $detailedCalculation[] = "($weight × $score) = " . number_format($weightedScore, 1);

        $table .= '
            <tr>
                <td>' . htmlspecialchars($item) . '</td>
                <td>' . $specification . '</td>
                <td>' . ($score ?: '') . '</td>
                <td>' . rtrim(rtrim(number_format($weight, 2), '0'), '.') . '</td>
            </tr>';
    }

    $table .= '
        </tbody>
    </table>
    <p><strong>Bobot kriteria:</strong> Wj = 1/' . $totalCriteria . ' = ' . rtrim(rtrim(number_format($weight, 2), '0'), '.') . '</p>
    <p><strong>Perhitungan Kriteria ' . $tableTitle . ':</strong> ' . implode(' + ', $detailedCalculation) . '</p>
    <p><strong>Skor akhir ' . $tableTitle . ':</strong> Vi = ∑_(j=1)^n (Wj × rij) = ' . number_format($totalScore, 1) . '</p>';

    return [
        'table' => $table,
        'totalScore' => $totalScore
    ];
}

// Fungsi untuk mendapatkan spesifikasi lengkap berdasarkan ID
function getFullSpecification($id) {
    global $conn;
    $query = "SELECT * FROM spek_hp WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}