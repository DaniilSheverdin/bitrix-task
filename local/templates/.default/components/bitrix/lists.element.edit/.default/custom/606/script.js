$(document).ready(function(){
	let input = $('input[name="PROPERTY_2686[]"]');
	input.closest('tr').addClass('hidden js-user-list');

	let select = $('select[name=PROPERTY_2651]');
	select.on('change', function(){
		let hide = true;
		if (
			$(this).find(':checked').text() === 'Выбранные пользователи'
			|| $(this).val() === 1718
		) {
			hide = false;
		}

		$('.js-user-list').toggleClass('hidden', hide);
	});
});