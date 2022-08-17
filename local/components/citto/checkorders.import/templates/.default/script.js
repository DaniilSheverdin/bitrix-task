$(document).ready(function(){
	$('.js-input').on('keyup change', function(){
		let disabled = true,
			number = $('.js-input-number').val(),
			date = $('.js-input-date').val();
		if (number !== '' && date !== '') {
			disabled = false;
		}
        $('.js-submit')
            .toggleClass('ui-btn-disabled', disabled)
            .toggleClass('js-disabled', disabled)
            .toggleClass('ui-btn-primary', !disabled)
            .attr('disabled', disabled);
	});
});

/**
 * Изменить адрес формы на 3 шаг
 */
function step3(grid) {
	let form = $('form[name=form_checkorders_import_step2]'),
		action = form.attr('action');
	form.attr('action', action.replace('step=2', 'step=3'));
	form.submit();
}

/**
 * Обновить страницу после отправки изменённой формы
 */
BX.addCustomEvent("Grid::updated", BX.delegate(function(data){
    window.location.reload();
}, this));