$(function () {
    $('.js-uvolnenie_pravitelstvo').on('submit', function (e) {
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
                    if (resp.ajaxid) {
                        $("#wait_comp_" + resp.ajaxid).remove();
                    }

                    if (resp.code == "ReadySign") {
                        var obFrom = $("#workarea-content").find('form#js-uvolnenie_pravitelstvo');
                        obFrom.prepend('<input type="hidden" name="documentid" value="' + resp.documentid + '" />');
                        obFrom.find('input[name="uved_inaya_rabota"]').val('signed');

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

            $('.js-uved_inaya_rabota').trigger('submit');

        } else if (data == "filesigner_hiden") {
            $('#popup-iframe2').hide();
        }
    });

    $('body').on('change', '[name="POLZOVATEL"]', function () {
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

                if (resp.EMPLOYEE.HELPERS.length > 0) {
                    $('#bp_HELPER_OIV option').prop('selected', false);
                    $.each(resp.EMPLOYEE.HELPERS,function(index,value){
                        $(`#bp_HELPER_OIV option[value=${value}]`).prop('selected', true);
                    });
                    $('#bp_HELPER_OIV').selectpicker('refresh');
                }
            }
        });
    });
});
