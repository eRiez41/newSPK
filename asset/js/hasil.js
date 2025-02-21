$(document).ready(function() {
    $('.card').click(function() {
        var id = $(this).find('.btn-blue').attr('onclick').match(/\d+/)[0];
        showSpecification(id);
    });

    $('.toggle-table').click(function(event) {
        event.stopPropagation();
        $(this).closest('.card').find('.table-container').toggleClass('hidden');
    });

    $('.btn-blue').click(function(event) {
        event.stopPropagation();
    });

    // Fungsi untuk menampilkan SweetAlert2
    function showAlert() {
        Swal.fire({
            title: 'Bagaimana Sistemnya?',
            text: 'Bisa tolong klik tombol penilaian dibawah ini ga?',
            icon: 'question',
            confirmButtonText: 'OK',
            showCloseButton: true, // Menambahkan tombol close
            allowOutsideClick: true, // Mengaktifkan penutupan dengan klik di luar
            showCancelButton: false, // Menghilangkan tombol cancel
        }).then((result) => {
            if (result.isConfirmed) {
                window.open('https://forms.gle/ik7qbmuzU4ELgLdz5', '_blank');
            }
        });
    }
    // Menampilkan pop-up setiap 30 detik
    setInterval(showAlert, 45000);
});

function showSpecification(id) {
    $.ajax({
        url: 'get_specification.php',
        method: 'POST',
        data: { id: id },
        success: function(response) {
            $('#specificationContent').html(response);
            $('#specificationModal').show();
        }
    });
}

$('.close').click(function() {
    $('#specificationModal').hide();
});

window.onclick = function(event) {
    var modal = document.getElementById('specificationModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}