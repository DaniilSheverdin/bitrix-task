$(function() {
	let $base = $('[data-id="add-mc"]');

	if($base.length > 0) {
		$base.on('click', function() {
			let tplMC = $(this).closest('.form-group.row').find('[data-mc=1]').eq(0);
			let edomClone = tplMC.clone();
			edomClone.find('input[type="text"]').val('');
			tplMC.parent().append(edomClone);
			if($(this).attr('data-field')) {
				counterInf($(this));
			}
		});

		var counterInf = function(base) {
			let count = 0;
			base.closest('.form-group.row').find('[data-mc=1]').each(function() {
				let len = $(this).find('input[name="' + base.attr('data-field') + '"]').val().length;
				if(len > 0) {
					count++;
				}
			});

			$('[name="' + base.attr('data-count') + '"]').val(count);
			return count;
		}

		let arrFields = [];
		$base.each(function() {
			arrFields.push('input[name="' + $(this).attr('data-field') + '"]');
		});

		$(document).on('keyup', arrFields.join(', '), function () {
			counterInf($(this).closest('.form-group.row').find('[data-id="add-mc"]'));
		});
	}
});