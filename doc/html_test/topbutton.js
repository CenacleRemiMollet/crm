var btn = document.querySelector('.top-button');
btn.addEventListener('click', () => {

    window.scrollTo({
        top: 0,
        left: 0,
        behavior: "smooth"
    })

})

// Button appear

window.addEventListener('scroll', function(){
    
scrollValue = (window.innerHeight + window.scrollY) / (document.body.offsetHeight);

console.log(scrollValue);

if (scrollValue > 0.2){
    btn.style.opacity = "1";
    btn.style.transform = "none";
}

if (scrollValue < 0.2){
    btn.style.opacity = "0";
    btn.style.transform = "translateX(200px)";
}

})