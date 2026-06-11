const gambar = document.querySelector("#gambar");
const preview = document.querySelector("#preview");

gambar.addEventListener("change", function(){

    const file = this.files[0];

    if(file){

        const reader = new FileReader();

        reader.onload = function(e){
            preview.src = e.target.result;
        }

        reader.readAsDataURL(file);
    }
});

// === MODAL KONFIRMASI HAPUS ===
function bukaModal(url) {
    document.getElementById('btn-konfirmasi-hapus').href = url;
    document.getElementById('modalHapus').classList.add('active');
}

function tutupModal() {
    document.getElementById('modalHapus').classList.remove('active');
}

// Tutup modal saat klik di luar box
const modalHapus = document.getElementById('modalHapus');
if (modalHapus) {
    modalHapus.addEventListener('click', function(e) {
        if (e.target === this) tutupModal();
    });
}

// === PREVIEW GAMBAR TAMBAH MENU ===
const inputGambar = document.getElementById('gambar');
if (inputGambar) {
    inputGambar.addEventListener('change', function() {
        const file = this.files[0];
        const fileNameText = document.getElementById('file-name-text');
        const preview = document.getElementById('preview');
        const placeholder = document.getElementById('preview-placeholder');

        if (file) {
            fileNameText.textContent = file.name;

            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            }
            reader.readAsDataURL(file);
        }
    });
}