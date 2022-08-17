$(function () {
	$('.js-soglasovanie-otcheta-la').on('submit', function (e) {
		e.preventDefault();
		e.stopPropagation();

		var _this = $(this);
		_this.find('>.alert').hide();

		if (_this.get(0).checkValidity() !== false) {
			_this.find('button[type=submit]').prop('disabled', true);

			$.ajax({
				url: _this.attr('action'),
				data: new FormData(_this.get(0)),
				processData: false,
				contentType: false,
				dataType: 'json',
				type: 'POST',
				success: function (resp) {
					if(resp.ajaxid) {
						$("#wait_comp_" + resp.ajaxid).remove();
					}

					if (resp.code != "OK") {
						_this.find('>.alert').attr('class', 'alert alert-danger d-block').html(resp.message)
					} else {
						_this.find('#js--form-action-content').addClass('d-none');
						_this.find('>.alert').attr('class', 'alert alert-success d-block').html(resp.message);
						_this.get(0).reset();
					}
					return;
				}
			}).fail(function () {
				_this.find('>.alert').attr('class', 'alert alert-danger').html("Произошла ошибка, попробуйте позже").show();
			}).always(function () {
				_this.find('button[type=submit]').prop('disabled', false);
				$('html, body').scrollTop(0);
			})
		} else {
			$("[id^=wait_comp_]").remove();
		}
		_this.addClass('was-validated');
		return false;
	});

	$("#add-mc").on('click', function() {
		let tplMC = $(this).closest('.form-group.row').find('[data-mc=1]').eq(0);
		let edomClone = tplMC.clone();
		tplMC.parent().append(edomClone);
		counterInf($(this));
	});

	var counterInf = function(base) {
		let count = 0;
		base.closest('.form-group.row').find('[data-mc=1]').each(function() {
			let len = $(this).find('input[name="MC_NAIMENOVANIE_MC[]"]').val().length;

			if(len > 0) {
				count++;
			}
		});

		$('[name="MC_KOLICHESTVO_MC"]').val(count);

		return count;
	}

	$(document).on('keyup', 'input[name="MC_NAIMENOVANIE_MC[]"]', function() {
		counterInf($(this));
	});
});