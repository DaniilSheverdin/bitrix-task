$(function () {
	$('.js-vnutrenne_peremeshenie').on('submit', function (e) {
		e.preventDefault();
		e.stopPropagation();

		let arObjects = $('.js-property');
		let sObjects = [];
		$.each(arObjects, function () {
			let sObjName = $(this).find('.js-objname').val();
			let sObjNumber = $(this).find('.js-objnumber').val();
			if (sObjName && sObjNumber) {
				sObjects.push([{'name': sObjName, 'number': sObjNumber}]);
			}
		});
		console.log(JSON.stringify(sObjects))
		$('input[name="DVIZHIMOE_HTML"]').val(JSON.stringify(sObjects));

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

	$('body').on('click', '#js-property-add', function() {
		let sContent = `<div class="row my-2 js-property">
                <div class="col-md-5"><input class="form-control js-objname" type="text" placeholder="Объект" required="required"></div>
                <div class="col-md-5"><input class="form-control js-objnumber" type="number" placeholder="Инв. номер" required="required"></div>
                <a class="js-property-del col-md-2" href="javascript: void(0);" style="line-height: 35px;">удалить</a>
            </div>`;
		$('#js-property-add').before(sContent);
	});

	$('body').on('click', '.js-property-del', function() {
		$(this).parent().detach();
	});
});