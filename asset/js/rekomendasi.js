document.addEventListener('DOMContentLoaded', function() {
    const hargaRangeValue = document.getElementById('hargaRangeValue');
    const hargaMinInput = document.getElementById('hargaMin');
    const hargaMaxInput = document.getElementById('hargaMax');

    // Inisialisasi slider harga
    $("#hargaRange").slider({
        range: true,
        min: 1000000,
        max: 16000000, // Mengatur maksimum menjadi 16 juta untuk menampilkan "lebih dari 15 juta"
        step: 1000000,
        values: [1000000, 16000000],
        slide: function(event, ui) {
            const minValue = ui.values[0];
            const maxValue = ui.values[1] > 15000000 ? 'lebih dari 15 juta' : ui.values[1].toLocaleString('id-ID');
            hargaMinInput.value = minValue.toString();
            hargaMaxInput.value = maxValue === 'lebih dari 15 juta' ? '999999999' : maxValue.toString().replace(/\./g, '');
            hargaRangeValue.textContent = `Rentang Harga: Rp ${minValue.toLocaleString('id-ID')} - Rp ${maxValue}`;
        },
        create: function(event, ui) {
            const minValue = $(this).slider("values", 0);
            const maxValue = $(this).slider("values", 1) > 15000000 ? 'lebih dari 15 juta' : $(this).slider("values", 1).toLocaleString('id-ID');
            hargaMinInput.value = minValue.toString();
            hargaMaxInput.value = maxValue === 'lebih dari 15 juta' ? '999999999' : maxValue.toString().replace(/\./g, '');
            hargaRangeValue.textContent = `Rentang Harga: Rp ${minValue.toLocaleString('id-ID')} - Rp ${maxValue}`;
        }
    });
});
