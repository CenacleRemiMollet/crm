var btn = document.querySelector('.toggle-btn');
var nav = document.querySelector('.search-club');

btn.onclick = function(){
    nav.classList.toggle('search-club-open');
}