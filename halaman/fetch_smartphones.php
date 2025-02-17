<?php
// Memasukkan file koneksi
include '../koneksi/koneksi.php';

if (isset($_POST['brand'])) {
    $brand = $_POST['brand'];

    $query = "SELECT id, `Nama Produk`, `RAM (GB)`, `Memori Internal (GB)` FROM spek_hp WHERE Brand = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $brand);
    $stmt->execute();
    $result = $stmt->get_result();

    $smartphones = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $smartphones[] = $row;
        }
    }

    echo json_encode($smartphones);
} else {
    echo json_encode([]);
}
?>
