$(function() {
	if (!finished) {
		var date = new Date();
		date.setDate(date.getDate() + countdown.days);
		date.setHours(date.getHours() + countdown.hours);
		date.setMinutes(date.getMinutes() + countdown.minutes);
		// adding one second on user's countdowns to prevent client inaccurate timing
		date.setSeconds(date.getSeconds() + countdown.seconds + (defaultPage ? 0 : 1));

		$("#countdown").countdown(date)
			.on('update.countdown finish.countdown', function (event) {
				var countdown = $(this);
				countdown.find('.days').text(event.strftime('%D'));
				countdown.find('.hours').text(event.strftime('%H'));
				countdown.find('.minutes').text(event.strftime('%M'));
				countdown.find('.seconds').text(event.strftime('%S'));
			})
			.on('finish.countdown', function (event) {
				if (defaultPage) {
					$(this).hide();
					$('#story').show();
				} else {
					window.location.reload();
				}
			});
	}
});
