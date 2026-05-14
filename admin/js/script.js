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