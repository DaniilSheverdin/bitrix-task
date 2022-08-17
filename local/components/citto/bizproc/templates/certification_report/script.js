$(function () {
    $('.js-certification_report').on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();

        let sListReview = $('#select-reiew option:selected').text();
        let sTextReview = $('[name="REVIEW"]').val();

        if (sListReview) {
            sListReview = sListReview.slice(0, -1).replaceAll(';', '; ');
            $('[name="REVIEW"]').val(sListReview + '; ' + sTextReview + '.');
        } else if (sTextReview) {
            $('[name="REVIEW"]').val(sTextReview + '.');
        }

        let sPenalties = $('#select-penalties option:selected').text();
        let sOrderPenalties = $('[name="PENALTIES"]').val();
        let sDatePenalties = $('#penalties_date').val();
        if (sPenalties == 'Не имеет') {
            $('[name="PENALTIES"]').val(sPenalties);
        } else {
            $('[name="PENALTIES"]').val(sPenalties + ' (приказ от ' + sDatePenalties  + ' №' + sOrderPenalties + ')');
        }

        /* Рекомендуемая оценка */
        let arRanks = []
        $('.rank').each(function(){
            arRanks.push({
                status : $(this).is(':checked'),
                name : $(this).parent().find('label').text().trim()
            });
        });
        let sJsonRanks = JSON.stringify(arRanks);
        $('#bp_RECOMMENDED_RATING').val(sJsonRanks);

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

                    if (resp.code == "ReadySign") {
                        var obFrom = $("#workarea-content").find('form#js-certification_report');
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
                        _this.find('>.alert').attr('class', 'alert alert-danger d-block').html(resp.message);
                    } else {
                        _this.find('#js--form-action-content').addClass('d-none');
                        _this.find('>.alert').attr('class', 'alert alert-info d-block').html(resp.message);
                        _this.get(0).reset();
                    }

                    console.log(resp.ajaxid);
                    if (resp.ajaxid) {
                        $("#wait_comp_" + resp.ajaxid).remove();
                    }
                    console.log(resp.REQUEST.bxajaxid);
                    if(resp.REQUEST.bxajaxid) {
                        $("#wait_comp_" + resp.REQUEST.bxajaxid).remove();
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

    $('body').on('click', '.js-add', function (e) {
        e.preventDefault();

        let sSelectYears = '';
        let iYear = new Date().getFullYear();
        for (x = iYear; x >= iYear - 5; x--) {
            sSelectYears += `<option value="${x}">${x}</option>`;
        }
        sSelectYears = `<select class="form-control" name="YEARS[]">${sSelectYears}</select>`;
        let sNewString = `
                 <div class="row mt-2">
                    <div class="col-md-5">
                        ${sSelectYears}
                    </div>
                    <div class="col-md-5">
                        <input class="form-control" type="file" name="DOCUMENTS[]">
                    </div>
                    <div class="col-md-2">
                        <a class="btn btn-danger btn-block js-del" href="#">Удалить</a>
                    </div>
                </div>`;

        $('.js-documets').append(sNewString);
    });

    $('body').on('click', '.js-del', function (e) {
        e.preventDefault();
        $(this).parent().parent().detach();
    });

    $('#bp_REVIEW').tooltip(
        {
            title: "Указываются уровень знаний, навыков и умений, степень участия в решении поставленных задач, сложность выполняемой работы, ее эффективность, исполнение гражданским служащим должностного регламента"
        }
    );

    $('#bp_TEXT_QUESTIONS').tooltip(
        {
            title: "Укажите перечень решённых вопросов/мероприятий или разработанных актов. Если актов много, укажите их количество"
        }
    );

    $('body').on('change', '[name="EMPLOYEE"]', function() {
        let iUserID = $(this).val();

        $.ajax({
            url: $('#js-certification_report').attr('action'),
            data: { bxajaxid: $('input[name="bxajaxid"]').val(), action: 'birthday', 'userid' : iUserID },
            processData: true,
            dataType: 'json',
            type: 'POST',
            success: function (resp) {
                sBirtDay = resp.message
                if (sBirtDay.length > 0) {
                    $('[name="BIRTHDAY"]').val(sBirtDay);
                }
            }
        });
    });
});
