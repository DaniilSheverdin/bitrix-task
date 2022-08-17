$(function () {
	$('.js-independent_experts').on('submit', function (e) {
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
});