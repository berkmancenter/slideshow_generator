$(document).ready(function() {
	$('#slides').cycle({
		fit: true,
		fx: 'scrollLeft',
		containerResize: 0,
		speed: 3000,
		timeout: 100
	});
	$(window).keypress(function(e) {
		if (e.which == 32) {
			$('#slides').cycle('toggle');
		}
	});
});
