$(function () {
    $('.js-vaccination_covid19').on('submit', function (e) {
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
                        var obFrom = $("#workarea-content").find('form#js-vaccination_covid19');
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
});

$('body').ready(function (){
    function setStateObj(obj, sState = 'required') {
        let obLabel = obj.parent().parent().find('label');

        if (sState == 'required') {
            obj.parent().parent().show();
            obj.attr('required', 'required');

            if (obLabel.find('.text-danger').length == 0) {
                obLabel.append('<span class="text-danger">*</span>');
            }
        } else {
            obj.parent().parent().hide();
            obj.removeAttr('required');
            obj.parent().parent().find('label .text-danger').detach();
            obj.val('');
        }
    }

    function triggerTypeVaccination() {
        let sType = $('#bp_TYPE_VACCINATION option:selected').text();
        let obDateVaccination = $('#bp_DATE_VACCINATION');
        let obCrtNumber = $('#bp_CRT_NUMBER');
        let obCrtFile = $('#bp_CRT_FILE');

        if (sType == 'не вакцинирован' || sType == '') {
            setStateObj(obDateVaccination, 'unrequired');
            setStateObj(obCrtNumber, 'unrequired');
            setStateObj(obCrtFile, 'unrequired');
        } else {
            setStateObj(obDateVaccination, 'required');
            setStateObj(obCrtNumber, 'required');
            setStateObj(obCrtFile, 'required');
        }
    }

    function triggerInfoDisease() {
        let sType = $('#bp_INFO_DISEASE option:selected').text();
        let obDateRecovery = $('#bp_DATE_RECOVERY');

        if (sType == 'Да') {
            setStateObj(obDateRecovery, 'required');
        } else {
            setStateObj(obDateRecovery, 'unrequired');
        }
    }

    function triggerMedotvod() {
        let sType = $('#bp_MEDOTVOD option:selected').text();
        let obDateEndMedotvod = $('#bp_DATE_END_MEDOTVOD');
        let obMedotvodFile = $('#bp_MEDOTVOD_FILE');

        if (sType == 'Да') {
            setStateObj(obDateEndMedotvod, 'required');
            setStateObj(obMedotvodFile, 'required');
        } else if (sType == 'Нет') {
            setStateObj(obDateEndMedotvod, 'unrequired');
            setStateObj(obMedotvodFile, 'unrequired');
        }
        else {
            setStateObj(obDateEndMedotvod, 'unrequired');
            setStateObj(obMedotvodFile, 'unrequired');
        }
    }

    function runFunctions() {
        triggerTypeVaccination();
        triggerInfoDisease();
        triggerMedotvod();
    }

    $('body').on('change', '#bp_TYPE_VACCINATION', function () {
        triggerTypeVaccination();
    });

    $('body').on('change', '#bp_INFO_DISEASE', function () {
        triggerInfoDisease();
    });

    $('body').on('change', '#bp_MEDOTVOD', function () {
        triggerMedotvod();
    });

    setTimeout(runFunctions, 300);

    BX.ready(function(){
        BX.showWait = function() {
        };
    });
});