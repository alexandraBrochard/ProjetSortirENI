/*
/!*!* fonction JS - formulaire Création - grisé champs recherche *!*!/

function desactiverNouveauLieu() {

    var lieuSelect = document.getElementById('slieu');
    var nouveauLieu = document.getElementById('nouveaulieu');

    lieuSelect.addEventListener('change',function() {
        if (lieuSelect.value !=='') {
            nouveauLieu.disabled =true;
        } else {
            nouveauLieu.disabled =false;
        }
    });
}
desactiverNouveauLieu();
*/
