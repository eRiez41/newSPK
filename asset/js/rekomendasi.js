document.addEventListener('DOMContentLoaded', function() {
    const hargaRangeValue = document.getElementById('hargaRangeValue');
    const hargaMinInput = document.getElementById('hargaMin');
    const hargaMaxInput = document.getElementById('hargaMax');
    const selectAllBrands = document.getElementById('selectAllBrands');
    const brandCheckboxes = document.querySelectorAll('.brand-checkbox');
    const selectAllFeatures = document.getElementById('selectAllFeatures');
    const featureCheckboxes = document.querySelectorAll('.feature-checkbox');

    // Inisialisasi slider harga
    $("#hargaRange").slider({
        range: true,
        min: 1000000,
        max: 16000000,
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

    // Menangani klik pada "Pilih Semua" untuk brand
    selectAllBrands.addEventListener('change', function() {
        brandCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllBrands.checked;
        });
    });

    // Menangani klik pada "Pilih Semua" untuk fitur
    selectAllFeatures.addEventListener('change', function() {
        featureCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllFeatures.checked;
        });
    });
});
