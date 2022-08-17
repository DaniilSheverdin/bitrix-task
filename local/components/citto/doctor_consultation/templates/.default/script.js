function getElementIDs() {
    let arTR = document.querySelectorAll('#grid_instruction_table .main-grid-row');
    let arElementsID = [];
    arTR.forEach(function (item) {
        let input = item.querySelector('input[type="checkbox"]');
        if (input.checked === true) {
            let id = item.getAttribute('data-id');
            arElementsID.push(id);
        }
    });

    return arElementsID;
}

function getExport() {
    let sAction = document.querySelector('#set-type .main-dropdown').getAttribute('data-value');

    if (sAction == 'export') {
        arElementsID = getElementIDs();

        let request = BX.ajax.runComponentAction('citto:doctor_consultation', 'getExport', {
            mode: 'ajax',
            data: {
                'arElementsID': arElementsID
            }
        });
        request.then(function (data) {
            var $a = $("<a>");
            $a.attr("href", data.data);
            $("body").append($a);
            $a.attr("download", "Конусльтация врача.xls");
            $a[0].click();
            $a.remove();
        });
    } else if (['IN_WORK', 'REJECTED', 'OK', 'WITHOUT_CONSULTATION'].indexOf(sAction) != -1) {
        arElementsID = getElementIDs();

        if (['REJECTED', 'OK', 'WITHOUT_CONSULTATION'].indexOf(sAction) != -1) {
            let sMessage = '';

            if (sAction == 'REJECTED') {
                sMessage = `<div class="ui-ctl ui-ctl-textarea"><textarea id="reason_reject" class="ui-ctl-element "></textarea></div>`;
            } else if (sAction == 'OK') {
                sMessage = `<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown" style="width: 100%">
                                <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                                <select class="ui-ctl-element" id="reason_select">
                                    <option>даны рекомендации</option>
                                    <option>направлена бригада</option>
                                    <option>выдан ЛН</option>
                                    <option>A</option>
                                    <option>B</option>
                                    <option>C</option>
                                    <option>иное</option>
                                </select>
                            </div>
                            <div class="ui-ctl ui-ctl-textarea" style="display: none"><textarea id="reason_reject" class="ui-ctl-element"></textarea></div>`;
            } else if (sAction == 'WITHOUT_CONSULTATION') {
                sMessage = `<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown" style="width: 100%">
                                <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                                <select class="ui-ctl-element" id="reason_select">
                                    <option>не дозвонились</option>
                                    <option>госпитализирован</option>
                                    <option>отказ от консультации</option>
                                    <option>иное</option>
                                </select>
                            </div>
                            <div class="ui-ctl ui-ctl-textarea" style="display: none"><textarea id="reason_reject" class="ui-ctl-element hide"></textarea></div>`;
            }

            BX.UI.Dialogs.MessageBox.show({
                title: 'Причина',
                message: sMessage,
                modal: true,
                buttons: [
                    new BX.UI.Button(
                        {
                            color: BX.UI.Button.Color.SUCCESS,
                            text: "OK",
                            events : {
                                click: function() {
                                    if ($('#reason_select').length > 0) {
                                        let sReasonSelect = $('#reason_select').val();
                                        if (sReasonSelect != 'иное') {
                                            $('#reason_reject').val(sReasonSelect)
                                        }
                                    }

                                    let sReason = $('#reason_reject').val();
                                    if (sReason) {
                                        let request = BX.ajax.runComponentAction('citto:doctor_consultation', 'setStatus', {
                                            mode: 'ajax',
                                            data: {
                                                'arElementsID': arElementsID,
                                                'sAction': sAction,
                                                'sReason': sReason
                                            }
                                        });
                                        request.then(function (data) {
                                            if (data.data == 'Y') {
                                                location.reload();
                                            }
                                        });

                                        this.getContext().close();
                                    } else {
                                        alert('Укажите причину');
                                    }
                                }
                            }
                        }
                    ),
                    new BX.UI.CancelButton(
                        {
                            events : {
                                click: function() {
                                    this.getContext().close();
                                }
                            }
                        }
                    )
                ],
            });
        } else {
            let request = BX.ajax.runComponentAction('citto:doctor_consultation', 'setStatus', {
                mode: 'ajax',
                data: {
                    'arElementsID': arElementsID,
                    'sAction': sAction
                }
            });
            request.then(function (data) {
                if (data.data == 'Y') {
                    location.reload();
                }
            });
        }
    }
}

function selectRows() {
    SELECT_DATE_IDS.forEach(function (value) {
        let obRow = $(`[data-id="${value}"]`);

        if (obRow.length > 0) {
            obRow.find('td').css({'background' : '#f3c0c0'});
        }
    });
}

$('body').ready(function () {
    $('body').on('change', '#reason_select', function () {
        let sSelectVal = $(this).val();
        if (sSelectVal == 'иное') {
            $('#reason_reject').parent().show()
        } else {
            $('#reason_reject').parent().hide()
        }
    });

    selectRows();

    $('body').on('click', 'button, .main-ui-square-delete', function () {
        setTimeout(selectRows, 1000);
    });

    $('body').on('keydown keypress keyup', function(e) {
        if (e.keyCode == 13) {
            setTimeout(selectRows, 1000);
        }
    });

    BX.ready(function(){

        BX.showWait = function() {
            alert()
        };
    });
});

