$(function () {
	$('.js-sc_reestr').on('submit', function (e) {
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
						BX.closeWait();
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

	$(".js-reestr_fio").on('change', function() {
		$.ajax({
			url: $(this).closest('form').attr('action'),
			data: { bxajaxid: $('input[name="bxajaxid"]').val(), REESTR_FIO: $(this).val() },
			processData: true,
			dataType: 'json',
			type: 'POST',
			success: function (resp) {
				let _sel = $(".js-reestr_fio_head");
				_sel.closest('.form-group').show();

				if(resp.glava_id) {
					_sel.find('option').removeAttr('selected');
					let option = _sel.find('option[value="' + parseInt(resp.glava_id) + '"]');
					option.attr('selected', 'selected');
					_sel.next('button').attr('title', option.html()).find('.filter-option-inner-inner').text(option.html());

				} else {
					_sel.find('option').removeAttr('selected');
					_sel.find('option[value="0"]').attr('selected', 'selected');
				}
				return;
			}
		}).fail(function () {
			_this.find('>.alert').attr('class', 'alert alert-danger').html("Произошла ошибка, попробуйте позже").show();
		}).always(function () {
			//_this.find('button[type=submit]').prop('disabled', false);
			//$('html, body').scrollTop(0);
		})
	});
});