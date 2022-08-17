function hideElements(arIDs = []) {
    arIDs.forEach(function (v) {
        if ($('input[name="bpriact_' + v + '"]').length > 0) {
            $('input[name="bpriact_' + v + '"]').parent().parent().hide();
        } else if ($('[name="bpriact_' + v + '\[\]"]').length > 0) {
            $('#id_bpriact_' + v).parent().parent().parent().hide();
        } else {
            $('#id_bpriact_' + v).parent().parent().hide();
        }
    })
}

function showElements(arIDs = []) {
    arIDs.forEach(function (v) {
        if ($('input[name="bpriact_' + v + '"]').length > 0) {
            $('input[name="bpriact_' + v + '"]').parent().parent().show();
        } else if ($('[name="bpriact_' + v + '\[\]"]').length > 0) {
            $('[name="bpriact_' + v + '\[\]"]').parent().parent().parent().show();
        } else {
            $('#id_bpriact_' + v).parent().parent().show();
        }
    })
}

function emptyForField(arIDs = []) {
    arIDs.forEach(function (v) {
        if ($('#id_bpriact_' + v + ', [name="bpriact_' + v + '"]').hasClass('bizproc-type-control-required')) {
            if ($('#id_bpriact_' + v).is('select')) {
                $('#id_bpriact_' + v + ' option').prop('selected', false);
            }
            else if ($('input[name="bpriact_' + v + '"]').length > 0) {
                $('input[name="bpriact_' + v + '"]').val('');
            }
            else {
                $('#id_bpriact_' + v).val('')
            }
        }
    })
}

function filledForField(arIDs = []) {
    arIDs.forEach(function (v) {
        if ($('#id_bpriact_' + v + ', [name="bpriact_' + v + '"]').hasClass('bizproc-type-control-required')) {
            if ($('#id_bpriact_' + v).is('select')) {
                $('#id_bpriact_' + v + ' option:last').prop('selected', true);
            }
            else if ($('input[name="bpriact_' + v + '"]').hasClass('bizproc-type-control-date')) {
                $('input[name="bpriact_' + v + '"]').val('01.01.1970');
            }
            else if ($('input[name="bpriact_' + v + '"]').length > 0) {
                $('input[name="bpriact_' + v + '"]').val('0');
            }
            else {
                $('#id_bpriact_' + v).val('0')
            }
        }
    })
}

function stateType(__this) {
    if (__this.val() == 'основной') {
        hideElements(['DATA_NACH_DOP_OTP', 'DAYS_DOP']);
        showElements(['DATA_NACH_OSN_OTP', 'DAYS_VSEGO']);
        filledForField(['DAYS_DOP']);
    } else if (__this.val() == 'дополнительный') {
        hideElements(['DATA_NACH_OSN_OTP', 'DAYS_VSEGO']);
        showElements(['DATA_NACH_DOP_OTP', 'DAYS_DOP']);
        filledForField(['DAYS_VSEGO']);
    } else if (__this.val() == 'совмещенный') {
        showElements(['DATA_NACH_DOP_OTP', 'DAYS_DOP', 'DATA_NACH_OSN_OTP', 'DAYS_VSEGO']);
    } else {
        hideElements(['DATA_NACH_DOP_OTP', 'DAYS_DOP', 'DATA_NACH_OSN_OTP', 'DAYS_VSEGO']);
    }
}



$('body').ready(function () {
    stateType($('#id_bpriact_TYPE'));

    $('#id_bpriact_TYPE').change(function () {
        stateType($(this))
    });

    // Не даём возможности отклонить подписание отпуска сотрудником
    let sHideButton = $('button[name="nonapprove"]').text().trim();
    if (sHideButton == 'HIDE_BUTTON') {
        $('button[name="nonapprove"]').hide();
    }
})
