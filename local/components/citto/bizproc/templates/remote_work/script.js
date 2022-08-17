$(function () {
    $('.js-remote_work').on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var _this = $(this);
        _this.find('>.alert').hide();

        let dateStart = _this.find('#DATA_NACHALA').val(),
            dateEnd = _this.find('#DATA_OKONCHANIYA').val();

        if (dateStart != '' && dateEnd != '') {
            let datetimeRegex = /(\d\d\d\d)\-(\d\d)\-(\d\d)/;

            let curDate = new Date();
            curDate.setHours(-24 * 2);
            curDate.setMinutes(0);
            curDate.setSeconds(0);

            let dateStartArray = datetimeRegex.exec(dateStart);
            dateStartDate = new Date(parseInt(dateStartArray[1]), parseInt(dateStartArray[2])-1, parseInt(dateStartArray[3]), 0, 0, 1);

            let dateEndArray = datetimeRegex.exec(dateEnd);
            dateEndDate = new Date(parseInt(dateEndArray[1]), parseInt(dateEndArray[2])-1, parseInt(dateEndArray[3]), 0, 0, 1);

            let timeError = false,
                errorName = 'начала';
            if (dateStartDate.getTime() < curDate.getTime()) {
                timeError = true;
            }
            if (dateEndDate.getTime() < curDate.getTime()) {
                timeError = true;
                errorName = 'окончания';
            }

            if (timeError) {
                _this.find('>.alert').attr('class', 'alert alert-danger').html('Дата ' + errorName + ` удаленной работы не может быть раньше ${curDate.getDate()}.${curDate.getMonth()+1}.${curDate.getFullYear()}`).show();
                _this.addClass('was-validated');
                _this.find('button[type=submit]').prop('disabled', false);
                $('html, body').scrollTop(0);
                $("[id^=wait_comp_]").remove();
                e.preventDefault();
                return false;
            }
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
                    if (resp.ajaxid) {
                        $("#wait_comp_" + resp.ajaxid).remove();
                    }

                    if (resp.code == "ReadySign") {
                        var obFrom = $("#workarea-content").find('form#js-remote_work');
                        obFrom.prepend('<input type="hidden" name="documentid" value="' + resp.documentid + '" />');
                        obFrom.find('input[name="uved_remote_work"]').val('signed');

                        var popup = new BX.PopupWindow("popup-iframe2", null, {
                            closeIcon: {right: "12px", top: "10px"},
                            width: "100%",
                            height: "100%"
                        });

                        $('#popup-iframe2').css({'width': '100%', 'height': '100%'}).html('');
                        $('<iframe>', {
                            src: '/podpis-fayla/?FILES[]=' + resp.file_id + '&CHECK_SIGN=N&sessid=' + resp.sessid,
                            id: 'popup-iframe',
                            frameborder: 0,
                            scrolling: 'no',
                            width: '100%',
                            height: '100%'
                        }).appendTo('#popup-iframe2');
                        $('#popup-iframe2').show();
                    } else if (resp.code != "OK") {
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

    $(window).on("message onmessage", function (e) {
        var data = e.originalEvent.data;
        if (data == 'filesigner_signed') {
            $('#popup-iframe2').hide();

            $('.js-uved_remote_work').trigger('submit');

        } else if (data == "filesigner_hiden") {
            $('#popup-iframe2').hide();
        }
    });

    $('body').on('change', '[name="SOTRUDNIK"]', function () {
        let _this = $(this).parents('form');
        let _data = new FormData(_this.get(0));
        _data.set('action', 'get_users');

        $.ajax({
            url: _this.attr('action'),
            data : _data,
            processData: false,
            contentType: false,
            dataType: 'json',
            type: 'POST',
            success: function (resp) {
                if (resp.EMPLOYEE.HEAD) {
                    $('#bp_HEAD_OIV option').prop('selected', false);
                    $(`#bp_HEAD_OIV option[value=${resp.EMPLOYEE.HEAD}]`).prop('selected', true);
                    $('#bp_HEAD_OIV').selectpicker('refresh');
                }
            }
        });
    });

    $('[name="SOTRUDNIK"]').trigger('change');
});
