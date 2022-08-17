$(document).ready(function(){
	let input = $('select[name="PROPERTY_2716[]"]');
	input.closest('tr').addClass('hidden js-user-list');

	let select = $('select[name=PROPERTY_2705]');
	select.on('change', function(){
		let hide = true;
		if (
			$(this).find(':checked').text() === 'Да'
			|| $(this).val() === 1722
		) {
			hide = false;
		}

		$('.js-user-list').toggleClass('hidden', hide);
	});
});