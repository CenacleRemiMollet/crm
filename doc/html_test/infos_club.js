const cours = document.querySelectorAll(".carousel-item > .case , .carousel-item > .doubleCase > .case");
const adresse = document.querySelectorAll(".carousel-item > .case > .adresse , .carousel-item > .doubleCase > .case > .adresse");
const infos = document.querySelectorAll(".carousel-item > .case > .frontCase , .carousel-item > .doubleCase > .case > .frontCase");

cours.onclick = function(){
    adresse.classList.toggle('visible');
	infos.classList.toggle('hidden');
}