
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
			button.addEventListener('click', () => {
				console.log(content.classList);
				content.classList.toggle('content-active');
				content.classList.toggle('content');
				console.log(content.classList);
			});
		});
	});
}

navSlide();
contentSlide();