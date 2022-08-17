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
                $('input[name="bpriact_' + v + '"]').val('empty');
            }
            else {
                $('#id_bpriact_' + v).val('empty')
            }
        }
    })
}

// ОИВ

function stateOiv(__this) {
    if (__this.val() == 'Да') {
        hideElements(['RAB_MESTO']);
        showElements(['SDANI_NOSITELI', 'INVENTARIZATSIYA', 'NOSITELI', 'GERB_TO', 'PECHATI_SHTAMPI', 'RES_INVENTAR', 'NUM_ACT', 'REASON', 'PERIOD', 'PERED_DATE', 'UTV_DATE']);
        emptyForField(['SDANI_NOSITELI', 'RAB_MESTO', 'INVENTARIZATSIYA', 'NOSITELI', 'GERB_TO', 'PECHATI_SHTAMPI', 'RES_INVENTAR', 'NUM_ACT', 'REASON', 'PERIOD', 'PERED_DATE', 'UTV_DATE']);
    } else if (__this.val() == 'Нет' || __this.val() == 'Курирует \"Управление делами\"') {
        hideElements(['INVENTARIZATSIYA', 'RES_INVENTAR']);
        showElements(['SDANI_NOSITELI', 'RAB_MESTO', 'NOSITELI', 'GERB_TO', 'PECHATI_SHTAMPI', 'NUM_ACT', 'REASON', 'PERIOD', 'PERED_DATE', 'UTV_DATE']);
        emptyForField(['SDANI_NOSITELI', 'RAB_MESTO', 'INVENTARIZATSIYA', 'NOSITELI', 'GERB_TO', 'PECHATI_SHTAMPI', 'RES_INVENTAR', 'NUM_ACT', 'REASON', 'PERIOD', 'PERED_DATE', 'UTV_DATE']);
    } else {
        hideElements(['RAB_MESTO', 'INVENTARIZATSIYA', 'NOSITELI', 'GERB_TO', 'PECHATI_SHTAMPI', 'RES_INVENTAR', 'NUM_ACT', 'REASON', 'PERIOD', 'PERED_DATE', 'UTV_DATE']);
        filledForField(['RAB_MESTO', 'INVENTARIZATSIYA', 'NOSITELI', 'GERB_TO', 'PECHATI_SHTAMPI', 'RES_INVENTAR', 'NUM_ACT', 'REASON', 'PERIOD', 'PERED_DATE', 'UTV_DATE']);
    }

    hideElements(['MASSIV_ACT']);
}

function stateSource(__this) {
    if (['Да', 'Нет', 'Курирует \"Управление делами\"'].indexOf($('#id_bpriact_OIV_MAT').val()) != -1) {
        if (__this.val() == 'Сданы') {
            hideElements(['PRICHINA_NOSITELI']);
            showElements(['NOSITELI_LIST']);
            emptyForField(['NOSITELI_LIST']);
            filledForField(['PRICHINA_NOSITELI']);
        } else if (__this.val() == 'Не сданы') {
            showElements(['NOSITELI_LIST', 'PRICHINA_NOSITELI']);
            emptyForField(['NOSITELI_LIST', 'PRICHINA_NOSITELI']);
        } else {
            hideElements(['NOSITELI_LIST', 'PRICHINA_NOSITELI']);
            filledForField(['NOSITELI_LIST', 'PRICHINA_NOSITELI']);
        }
    }
    else {
        hideElements(['NOSITELI_LIST', 'PRICHINA_NOSITELI', 'SDANI_NOSITELI']);
        filledForField(['NOSITELI_LIST', 'PRICHINA_NOSITELI', 'SDANI_NOSITELI']);
    }
}

function stateAsed(__this) {
    if (['Да', 'Нет', 'Курирует \"Управление делами\"'].indexOf($('#id_bpriact_OIV_MAT').val()) != -1) {
        showElements(['PEREDANI_ASED']);

        if (__this.val() == 'Y') {
            showElements(['DOC_ASED']);
            emptyForField(['DOC_ASED']);
        } else if (__this.val() == 'N') {
            hideElements(['DOC_ASED']);
            filledForField(['DOC_ASED']);
        }
    }
    else {
        hideElements(['DOC_ASED', 'PEREDANI_ASED']);
        filledForField(['DOC_ASED']);
    }
}

function stateStampBlank() {
    if (['Да', 'Нет', 'Курирует \"Управление делами\"'].indexOf($('#id_bpriact_OIV_MAT').val()) != -1) {
        showElements(['PECHATI_SHTAMPI']);

        sValBlanki = $('#id_bpriact_GERB_TO').val()
        sValStamp = $('#id_bpriact_PECHATI_SHTAMPI').val()



        if ([sValBlanki, sValStamp].indexOf('Сданы по акту приема - передачи') != -1)  {
            showElements(['KOMU_SHTAMPY']);
        } else {
            hideElements(['KOMU_SHTAMPY']);
        }
    }
    else {
        hideElements(['PECHATI_SHTAMPI', 'GERB_TO', 'KOMU_SHTAMPY']);
    }
}


$('body').ready(function () {
    stateOiv($('#id_bpriact_OIV_MAT'));

    $('#id_bpriact_OIV_MAT').change(function () {
        stateOiv($(this));
        stateSource($('#id_bpriact_SDANI_NOSITELI'));
        stateAsed($('#id_bpriact_PEREDANI_ASED'));
        stateStampBlank();
    });

    // Съёмные носители
    stateSource($('#id_bpriact_SDANI_NOSITELI'));
    $('#id_bpriact_SDANI_NOSITELI').change(function () {
        stateSource($(this));
    });

    // Асед Дело
    stateAsed($('#id_bpriact_PEREDANI_ASED'));
    $('#id_bpriact_PEREDANI_ASED').change(function () {
        stateAsed($(this));
    });

    // Штампы, Бланки
    stateStampBlank();
    $('#id_bpriact_PECHATI_SHTAMPI, #id_bpriact_GERB_TO').change(function () {
        stateStampBlank();
    });

    if ($('#id_bpriact_MASSIV_ACT').length > 0) {
        function formingTableAct() {
            let arActs = [];

            if ($('.tr_act').length > 0) {
                $('.tr_act').each(function () {
                    let arCurr = [];
                    $(this).find('.act').each(function (i) {
                        arCurr.push($(this).text());
                    });
                    arActs.push(arCurr);
                });
            }

            return JSON.stringify(arActs);
        }

        let sTrMassiv = $('#id_bpriact_MASSIV_ACT').parent().parent().parent().parent();

        let sNewContent = `<div class="new_content" id="id_bpriact_TABLE_ACT" style="margin-top: 20px;">
            <span><strong>Таблица актов приёма-передачи документов</strong></span>
            <form action="post">
            <input id="act_index" type="text" placeholder="Индекс дела по номенклатуре дел" />
            <input id="act_name" type="text" placeholder="Наименование документа" />
            <input id="act_number" type="text" placeholder="Номер, дата документа" />
            <input id="act_count" type="text" placeholder="Кол-во документов" />
            <input id="act_prim" type="text" placeholder="Примечание" />
            <a href="#" class="add_content bp-button bp-button bp-button-accept">Добавить</a>
            <table class="table_content" border="1">
                <tr>
                    <td>Индекс дела по номенклатуре дел</td>
                    <td>Наименование документа</td>
                    <td>Номер, дата документа</td>
                    <td>Кол-во документов</td>
                    <td>Примечание</td>
                    <td style="width: 80px"></td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>2</td>
                    <td>3</td>
                    <td>4</td>
                    <td>5</td>
                    <td></td>
                </tr>
            </table>
            </div>`;
        $(sNewContent).insertAfter(sTrMassiv);

        $('body').on('click', '.add_content', function (e) {
            e.preventDefault();
            let sFields = `
                <tr class="tr_act">
                    <td class="act">${$('#act_index').val()}</td>
                    <td class="act">${$('#act_name').val()}</td>
                    <td class="act">${$('#act_number').val()}</td>
                    <td class="act">${$('#act_count').val()}</td>
                    <td class="act">${$('#act_prim').val()}</td>
                    <th><a href="#" class="del_content bp-button bp-button bp-button-accept">Удалить</a></th>
                </tr>          
            `;
            $('.table_content').append(sFields);
            $('.new_content form')[0].reset();
            $('#id_bpriact_MASSIV_ACT').val(formingTableAct());
        });

        $('body').on('click', '.del_content', function (e) {
            e.preventDefault();
            $(this).parent().parent().detach();
            $('#id_bpriact_MASSIV_ACT').val(formingTableAct());
        });
    }
});

// Управление делами

function stateDela(__this) {
    if (__this.val() == 'Да') {
        hideElements(['RES_TMC']);
        showElements(['RES_INVENT', 'UD_RES_INVENTAR']);
    } else if (__this.val() == 'Нет') {
        hideElements(['RES_INVENT', 'UD_RES_INVENTAR']);
        showElements(['RES_TMC']);
        filledForField(['UD_RES_INVENTAR']);
    } else {
        hideElements(['RES_INVENT', 'RES_TMC', 'UD_RES_INVENTAR']);
        filledForField(['UD_RES_INVENTAR']);
    }
}

function stateKomUslugi(__this) {
    if (__this.val() == 'Предоставлялось') {
        showElements(['KOM_USLUGI']);
    } else {
        hideElements(['KOM_USLUGI']);
    }
}

function stateZadolzh(__this) {
    if ($('#id_bpriact_PRED_SLUZH_ZHILIE').val() == 'Предоставлялось') {
        if (__this.val() == 'Y') {
            showElements(['RAZMER_ZADOLZH', 'RESHENIE']);
            emptyForField(['RAZMER_ZADOLZH', 'RESHENIE']);
        }
        else {
            hideElements(['RAZMER_ZADOLZH', 'RESHENIE']);
            filledForField(['RAZMER_ZADOLZH', 'RESHENIE']);
        }
    } else {
        hideElements(['KOM_USLUGI', 'RAZMER_ZADOLZH', 'RESHENIE']);
        filledForField(['KOM_USLUGI', 'RAZMER_ZADOLZH', 'RESHENIE']);
    }
}

function statePretenzii(__this) {
    if ($('#id_bpriact_PRED_SLUZH_ZHILIE').val() == 'Предоставлялось') {
        showElements(['PRETENZII_SOST_ZHIL']);

        if (__this.val() == 'Y') {
            showElements(['PRETENZII_RESHENIE']);
            emptyForField(['PRETENZII_RESHENIE']);
        }
        else {
            hideElements(['PRETENZII_RESHENIE']);
            filledForField(['PRETENZII_RESHENIE']);
        }
    } else {
        hideElements(['PRETENZII_RESHENIE', 'PRETENZII_SOST_ZHIL']);
        filledForField(['PRETENZII_RESHENIE']);
    }
}

function statePechat(__this) {
    if (__this.val() == 'Не сдана' || __this.val() == 'Не выдавалась') {
        showElements(['PRICHINA_PECHAT']);
        emptyForField(['PRICHINA_PECHAT']);
    } else {
        hideElements(['PRICHINA_PECHAT']);
        filledForField(['PRICHINA_PECHAT']);
    }
}

$('body').ready(function () {
    // hideElements(['RES_INVENT', 'RES_TMC', 'KOM_USLUGI', 'SOST_ZHILIYA', 'UD_RES_INVENTAR']);
    stateDela($('#id_bpriact_DELA_MAT'));
    stateKomUslugi($('#id_bpriact_PRED_SLUZH_ZHILIE'));
    stateZadolzh($('#id_bpriact_PRED_SLUZH_ZHILIE'));
    statePretenzii($('#id_bpriact_PRED_SLUZH_ZHILIE'));
    statePechat($('#id_bpriact_PECHAT_DELA'));

    $('#id_bpriact_DELA_MAT').change(function () {
        stateDela($(this));
    });

    $('#id_bpriact_PRED_SLUZH_ZHILIE').change(function () {
        stateKomUslugi($(this));
        stateZadolzh($(this));
        statePretenzii($('#id_bpriact_PRETENZII_SOST_ZHIL'));
    });

    $('#id_bpriact_KOM_USLUGI').change(function () {
        stateZadolzh($('#id_bpriact_KOM_USLUGI'));
    });

    $('#id_bpriact_PRETENZII_SOST_ZHIL').change(function () {
        statePretenzii($('#id_bpriact_PRETENZII_SOST_ZHIL'));
    });

    $('#id_bpriact_PECHAT_DELA').change(function () {
        statePechat($('#id_bpriact_PECHAT_DELA'));
    });
});
