
function navSlide() {
	const burger = document.querySelector('.burger');
	const nav = document.querySelector('.nav-links');

	burger.addEventListener('click', () => {
		nav.classList.toggle('nav-active');
		// burger animation
		burger.classList.toggle('toggle');
	});
}

function contentSlide() {
	const buttons = document.querySelectorAll('button');
	buttons.forEach(function(button) {
		var contents = button.parentElement.getElementsByClassName('content');
		Array.prototype.slice.call(contents).forEach(function(content) {
			console.log(content);
			button.addEventListener('mouseenter', () => {
				content.classList.add('content-active');
				content.classList.remove('content');
			});
			button.addEventListener('mouseleave', () => {
				content.classList.remove('content-active');
				content.classList.add('content');
			});
		});
	});
}


navSlide();
contentSlide();