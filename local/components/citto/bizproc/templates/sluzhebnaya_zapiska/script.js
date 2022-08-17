$(function () {
	$('.js-zayavka-na-mc').on('submit', function (e) {
		e.preventDefault();
		e.stopPropagation();

		var _this = $(this);
		_this.find('>.alert').hide();


		var arMesta = [];
		$('.mesta input').each(function(){
			if ($(this).val().length > 0 )
				arMesta.push($(this).val())
		});

		if (arMesta.length > 0) {
			$('#bp_MESTO_KOMANDIROVANIYA').val(JSON.stringify(arMesta));
		}

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

	$('body').on('click', '#add_mesta', function(e){
		$(this).before('<input type="text" class="form-control">');
		e.preventDefault()
	});

	$('body').on('change', '#bp_FIO_SOTRUDNIKOV', function(){
		let sUsers = '';
		$.each($(this).val(), function (i, v) {
			sUsers += '<p>- '+$('#bp_FIO_SOTRUDNIKOV option[value="'+v+'"]').text()+'</p>';
		});

		$('#list_users').empty();
		$('#list_users').append(sUsers);
	});

});