$(document).ready(function() {
    $('.toggle-table').click(function() {
        $(this).closest('.card').find('.table-container').toggleClass('hidden');
    });

    // Fungsi untuk menampilkan modal spesifikasi
    window.showSpecification = function(id) {
        $.ajax({
            url: 'get_specification.php', // Buat file PHP baru untuk mengambil spesifikasi
            method: 'POST',
            data: { id: id },
            success: function(response) {
                $('#specificationContent').html(response);
                $('#specificationModal').show();
            },
            error: function() {
                alert('Terjadi kesalahan saat mengambil data spesifikasi.');
            }
        });
    };

    // Menutup modal saat tombol close diklik
    $('.close').click(function() {
        $('#specificationModal').fadeOut(500, function() {
            $(this).css('display', 'none');
        });
        $('#specificationContent').css('animation', 'slideOut 0.5s');
    });

    // Menutup modal saat area di luar modal diklik
    $(window).click(function(event) {
        if (event.target == document.getElementById('specificationModal')) {
            $('#specificationModal').fadeOut(500, function() {
                $(this).css('display', 'none');
            });
            $('#specificationContent').css('animation', 'slideOut 0.5s');
        }
    });
});