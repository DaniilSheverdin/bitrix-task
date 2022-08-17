$(function () {
	$('.js-zapros_poyasneniy').on('submit', function (e) {
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
						_this.find('>.alert').attr('class', 'alert alert-info d-block').html(resp.message);
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

	function setObjectUID() {
		sYear = $('[name="DATE_FROM"]').find("option:selected").val();
		$.ajax({
			url: $('#js-zapros_poyasneniy').attr('action'),
			data: { bxajaxid: $('input[name="bxajaxid"]').val(), action: 'select', 'year' : sYear, 'userid' : $('[name="SLUZHASHCHIY"] option:selected').val() },
			processData: true,
			dataType: 'json',
			type: 'POST',
			success: function (resp) {
				sUID = resp.message;
				$('[name="UID"]').val(sUID)
				console.log(sUID)
			}
		})
	}

	function hideDataPodachi() {
		let sStatus = $('[name="STATUS"] option:selected').text();
		let obDataPodachi = $('[name="DATA_PODACHI"]').parent().parent();
		let sDate = '';
		if (sStatus == 'Претендент') {
			obDataPodachi.css({'display' : 'flex'});
		} else {
			obDataPodachi.css({'display' : 'none'});
			sDate = '1971-01-01';
		}
		$('[name="DATA_PODACHI"]').val(sDate)
	}

	hideDataPodachi();

	$('body').on('change', '[name="DATE_FROM"], [name="SLUZHASHCHIY"]', function() {
		setObjectUID();
	});

	$('body').on('change', '[name="STATUS"]', function() {
		hideDataPodachi();
	});
});