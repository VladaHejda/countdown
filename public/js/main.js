$(function() {
	var date = new Date();
	date.setDate(date.getDate() + countdown.days);
	date.setHours(date.getHours() + countdown.hours);
	date.setMinutes(date.getMinutes() + countdown.minutes);
	date.setSeconds(date.getSeconds() + countdown.seconds);
	$("#countdown").countdown(date)
		.on('update.countdown finish.countdown', function(event) {
			var countdown = $(this);
			countdown.find('.days').text(event.strftime('%D'));
			countdown.find('.hours').text(event.strftime('%H'));
			countdown.find('.minutes').text(event.strftime('%M'));
			countdown.find('.seconds').text(event.strftime('%S'));
		})
		.on('finish.countdown', function(event) {
			window.location.reload();
	});
});
