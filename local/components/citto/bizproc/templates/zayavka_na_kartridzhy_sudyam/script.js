$(function () {
    $('.js-zayavka-na-kartridzhy-sudam').on('submit', function (e) {
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
                $("[id^=wait_comp_]").remove();
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

    let setPrinter = function (sel, selVal, matid) {
        let selMat = sel.closest('[data-mc=1]').find('select[name="WJL_SODERZHANIE_MATERIAL[]"]');
        let ids = matid.split(',');

        selMat.find('option').each(
            function () {
                if ($.inArray($(this).attr('value'), ids) >= 0) {
                    $(this).attr('selected', 'selected');
                    $(this).removeAttr('disabled');
                    $(this).show();
                } else {
                    $(this).removeAttr('selected');
                    $(this).attr('disabled', 'disabled');
                    $(this).hide();
                }
            }
        );
    }

    let setMaterials = function (sel, selVal, printerId) {
        let selPrint = sel.closest('[data-mc=1]').find('select[name="WJL_SODERZHANIE_PRINTER[]"]');
        let ids = printerId.split(',');

        selPrint.find('option').each(
            function () {
                if ($.inArray($(this).attr('value'), ids) >= 0) {
                    $(this).attr('selected', 'selected');
                    $(this).removeAttr('disabled');
                    $(this).show();
                } else {
                    $(this).removeAttr('selected');
                    $(this).attr('disabled', 'disabled');
                    $(this).hide();
                }
            }
        );
    }

    $("#add-mc").on(
        'click',
        function () {
            let tplMC = $(this).closest('.form-group.row').find('[data-mc=1]').eq(0);
            let edomClone = tplMC.clone();
            tplMC.parent().append(edomClone);

            let lastSel = $(this).closest('.form-group.row').find('[data-mc=1]:last-child').find('select[name="WJL_SODERZHANIE_MATERIAL[]"]');
            let lastPrinter = $(this).closest('.form-group.row').find('[data-mc=1]:last-child').find('select[name="WJL_SODERZHANIE_PRINTER[]"]');
            //setPrinter(lastSel, lastSel.val());
            //setMaterials(lastPrinter, lastPrinter.val());

            edomClone.find('select[name="WJL_SODERZHANIE_PRINTER[]"]').val('0');
            edomClone.find('select[name="WJL_SODERZHANIE_MATERIAL[]"]').val('0');
            edomClone.find('option').removeAttr('disabled').show();

        }
    );

    $(document).on('click', '.js-zayavka-na-kartridzhy-sudam .closed', function () {
        let block = $(this).closest('.form-group.row').find('[data-mc=1]');
        if (block.length > 1) {
            $(this).closest('[data-mc=1]').remove();
        } else {
            block.find('option').removeAttr('disabled').show();
        }
    });

    $(document).on(
        'change',
        'select[name="WJL_SODERZHANIE_PRINTER[]"]',
        function () {
            let pid = $(this).find('option[value="' + $(this).val() + '"]').attr('data-mat-id');
            setPrinter($(this), $(this).val(), pid);
        }
    );

    $(document).on(
        'change',
        'select[name="WJL_SODERZHANIE_MATERIAL[]"]',
        function () {
            let pid = $(this).find('option[value="' + $(this).val() + '"]').attr('data-printer-id');
            setMaterials($(this), $(this).val(), pid);
        }
    );

    let sel = $('[data-mc=1]').eq(0).find('select[name="WJL_SODERZHANIE_PRINTER[]"]');
    //setPrinter(sel, sel.val());

});