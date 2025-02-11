<?php
include '../koneksi/koneksi.php';

// Fungsi untuk mendapatkan spesifikasi lengkap berdasarkan ID
function getFullSpecification($id) {
    global $conn;
    $query = "SELECT * FROM spek_hp WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $specification = getFullSpecification($id);

    if ($specification) {
        $productName = urlencode($specification['Brand'] . ' ' . $specification['Nama Produk']);

        echo '<h2>Spesifikasi Lengkap</h2>';
        echo '<table class="table table-bordered">';

        // Menampilkan gambar jika URL gambar tersedia
        if (!empty($specification['Image URL'])) {
            echo '<tr>';
            echo '<td colspan="2" class="text-center"><img src="' . htmlspecialchars($specification['Image URL']) . '" class="img-fluid" alt="Gambar Smartphone"></td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td colspan="2" class="text-center">';
            echo "<a href='https://www.tokopedia.com/search?st=product&q=" . $productName . "' class='btn btn-success mx-1' target='_blank'>Beli di Tokopedia</a>";
            echo "<a href='https://shopee.co.id/search?keyword=" . $productName . "' class='btn btn-warning mx-1' target='_blank'>Beli di Shopee</a>";
            echo '</td>';
            echo '</tr>';
        }

        foreach ($specification as $key => $value) {
            if ($key != 'id' && $key != 'Image URL') {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($key) . '</td>';
                echo '<td>' . htmlspecialchars($value) . '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
    } else {
        echo '<p>Tidak ada data spesifikasi yang ditemukan.</p>';
    }
} else {
    echo '<p>ID tidak valid.</p>';
}
?>
