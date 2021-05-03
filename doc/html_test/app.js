const navSlide = () => {
    const burger = document.querySelector('.burger');
    const nav = document.querySelector('.nav-links');


    burger.addEventListener('click', () => {
        nav.classList.toggle('nav-active');

        // burger animation
    burger.classList.toggle('toggle');
    });

}

navSlide();

const contentSlide = () => {
    const buttons = document.querySelectorAll('button');
    buttons.forEach(function(button) {
        var contents = buttons.parentElement.getElementsByClassName('content');

        contents.forEach(button => {
            button.addEventListener('click', () => {
                console.log(content.classList);
                content.classList.toggle('content-active');
                content.classList.toggle('content');
                console.log(content.classList);
            });
        });
        /* .forEach(function(content){
            
                
            }); 
        });*/

       
    });
    
}

contentSlide();