function removeDuplicates(arr) {

    const result = [];
    const duplicatesIndices = [];

    // Перебираем каждый элемент в исходном массиве
    arr.forEach((current, index) => {

        if (duplicatesIndices.includes(index)) return;

        result.push(current);

        // Сравниваем каждый элемент в массиве после текущего
        for (let comparisonIndex = index + 1; comparisonIndex < arr.length; comparisonIndex++) {

            const comparison = arr[comparisonIndex];
            const currentKeys = Object.keys(current);
            const comparisonKeys = Object.keys(comparison);

            // Проверяем длину массивов
            if (currentKeys.length !== comparisonKeys.length) continue;

            // Проверяем значение ключей
            const currentKeysString = currentKeys.sort().join("").toLowerCase();
            const comparisonKeysString = comparisonKeys.sort().join("").toLowerCase();
            if (currentKeysString !== comparisonKeysString) continue;

            // Проверяем индексы ключей
            let valuesEqual = true;
            for (let i = 0; i < currentKeys.length; i++) {
                const key = currentKeys[i];
                if ( current[key] !== comparison[key] ) {
                    valuesEqual = false;
                    break;
                }
            }
            if (valuesEqual) duplicatesIndices.push(comparisonIndex);

        } // Конец цикла
    });
    return result;
}

function getFile(data) {
    console.log('get file start');

    var queryControl = {
            c: 'serg:super.component',
            action: 'createFile',
            mode: 'ajax'
        },
        requestControl = $.ajax({
            url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
            type: 'POST',
            data: data
        });
    requestControl.done(function (result) {

        location = result.data;

    });

    requestControl.fail(function () {
        console.error('Can not get data');
    });
}


function getFileComing(data) {
    console.log('get file coming start');

    var queryControl = {
            c: 'serg:super.component',
            action: 'createFileComing',
            mode: 'ajax'
        },
        requestControl = $.ajax({
            url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
            type: 'POST',
            data: data
        });
    requestControl.done(function (result) {

        location = result.data;

    });

    requestControl.fail(function () {
        console.error('Can not get data');
    });
}


function validSelectedSave(grid) {
    
    var notifyPosition = "top-right"
    
    var $dateContactInputs = $('input[name=ATT_DATE_PERESECHENIYA]')
    var $notifyWayInputs = $('div[name=ATT_NOTIFY_WAY_ENUM] span')
    
    var issetEmptyDate = false
    var issetEmptyNotifyWay = false
    $dateContactInputs.each(function (count, input) {
        if (count > 0) {
            if (!input.value.length) {
                issetEmptyDate = true
                BX.UI.Notification.Center.notify({
                    content: "Заполните дату контакта в строке: " + count,
                    position: notifyPosition
                });
            }
            
        }
    })
    $notifyWayInputs.each(function (count) {
        
        if ($(this).text() === '(Не выбрано)') {
            issetEmptyNotifyWay = true
            BX.UI.Notification.Center.notify({
                content: "Заполните способ оповещения в строке: " + (count + 1),
                position: notifyPosition
            });
        }
        
    })
    
    if (!issetEmptyDate && !issetEmptyNotifyWay) grid.editSelectedSave()
    
}



function getFileMZH(data) {
    console.log('get file start MZH');

    var queryControl = {
            c: 'serg:super.component',
            action: 'createFileMZH',
            mode: 'ajax'
        },
        requestControl = $.ajax({
            url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
            type: 'POST',
            data: data
        });
    requestControl.done(function (result) {

        location = result.data;

    });

    requestControl.fail(function () {
        console.error('Can not get data');
    });
}

function getFileMP(data) {
    console.log('get file start MP');

    var queryControl = {
            c: 'serg:super.component',
            action: 'createFileMZH',
            mode: 'ajax'
        },
        requestControl = $.ajax({
            url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
            type: 'POST',
            data: data
        });
    requestControl.done(function (result) {
        location = result.data;

    });

    requestControl.fail(function () {
        console.error('Can not get data');
    });
}

function getFileContact(data) {
    console.log('get file start Contact', data);

    var queryControl = {
            c: 'serg:super.component',
            action: 'createFileContact',
            mode: 'ajax'
        },
        requestControl = $.ajax({
            url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
            type: 'POST',
            data: data
        });
    requestControl.done(function (result) {
        location = result.data;

    });

    requestControl.fail(function () {
        console.error('Can not get data');
    });
}

function makeMaskForEditField (code, mask) {

    console.info('makeMaskForEditField()');
    $("input.main-grid-editor[name = " + code + "]").each(function () {
        $(this).inputmask({"mask": mask});
    })

}


function getAddressForEditField (code) {
    
    
    
    console.info('getAddressForEditField()');
    $("input.main-grid-editor[name = " + code + "]").suggestions({
        token: "697e4e53b055f8cbb596f79570f2cbfd118a4a68",
        type: "ADDRESS",
        onSelect: function(suggestion) {
            console.log(suggestion);
        },
    });
    $("input.main-grid-editor[name = " + code + "]").on('keypress', function () {
            $('body .suggestions-suggestions').addClass('relative')
    })
    
}


$(function () {

    if ($('.main-grid').length) {



        $('#ATT_PHONE_control').inputmask({"mask": "(999) 999-9999"});

        var grid_id = $('.main-grid').attr('id'),
            isFetch = true,
            $xlsButton = $('#xls-button'),
            $title = $('.page_title span').text(),
            gridObject = BX.Main.gridManager.getById(grid_id),
            userOptions = gridObject.instance.getUserOptions(),
            columns = userOptions.options.views.default.columns.split(','),
            columnsName = [];


        if (columns.length <= 1) {
            $('.main-grid-head-title').each(function (i) {
                $('.main-grid-cell-head').each(function (k) {
                    if (i === k && $(this).text() !== '') {
                        var data = {name: $(this).text(), code: $(this).attr('data-name')};
                        columnsName.push(data)

                    }
                })
            });
            columnsName = removeDuplicates(columnsName);

            BX.addCustomEvent('BX.Main.Grid:paramsUpdated', BX.delegate(function () {
                columnsName = [];
                $xlsButton.text('скачать все записи');
                $xlsButton.css({backgroundColor: 'green'});
                columns = userOptions.options.views.default.columns.split(',');

                $('.main-grid-head-title').each(function (i) {
                    $('.main-grid-cell-head').each(function (k) {
                        if (i === k && $(this).text() !== '') {
                            var data = {name: $(this).text(), code: $(this).attr('data-name')};
                            columnsName.push(data)

                        }
                    })
                });
                columnsName = removeDuplicates(columnsName);

                console.log('event if !length', columnsName);

            }));

        } else {
            $('.main-grid-cell-head').each(function () {
                var idx = columns.indexOf($(this).attr('data-name'));
                if (idx !== -1) {
                    var data = {name: $(this).find('.main-grid-head-title').text(), code: columns[idx]};
                    columnsName.push(data);
                }
            });

            columnsName = removeDuplicates(columnsName);

            BX.addCustomEvent('BX.Main.Grid:paramsUpdated', BX.delegate(function () {
                columnsName = [];
                $xlsButton.text('скачать все записи');
                $xlsButton.css({backgroundColor: 'green'});
                columns = userOptions.options.views.default.columns.split(',');

                $('.main-grid-cell-head').each(function () {
                    var idx = columns.indexOf($(this).attr('data-name'));
                    if (idx !== -1) {
                        var data = {name: $(this).find('.main-grid-head-title').text(), code: columns[idx]};
                        columnsName.push(data);
                    }
                });
                columnsName = removeDuplicates(columnsName);

                console.log('event if length', columnsName);

            }));

        }


        function getTable(data, section) {
            console.log('downloading xls...');
            console.log('get Table ', data);

            var queryControl = {
                    c: 'serg:super.component',
                    action: 'getTable',
                    mode: 'ajax'
                },
                requestControl = $.ajax({
                    url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
                    type: 'POST',
                    data: 'columns=' + JSON.stringify(data) + '&section=' + section,
                });

            requestControl.done(function (result) {
                console.log('result ', result);
                $('.lds-dual-ring').remove();
                $xlsButton.text('загрузка завершена');
                $xlsButton.css({backgroundColor: '#41bd16'});


                if (result.data.list.length) {
                    var xlsData = [];
                    result.data.list.forEach(function (row, i) {
                        xlsData[i] = [];
                        xlsData[i] = row;
                    });

                    var xls = new XlsExport(xlsData, $title);
                    isFetch = true;
                    xls.exportToXLS($title + '.xls');
                }
                console.log('xlsData', xlsData);

            });

            requestControl.fail(function () {
                console.error('Can not get table');
            });
        }
    
        function getTableAllContact(data, section) {
            console.log('downloading xls...');
            console.log('get Table ', data);
        
            var queryControl = {
                  c: 'serg:super.component',
                  action: 'getTableAllContact',
                  mode: 'ajax'
              },
              requestControl = $.ajax({
                  url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
                  type: 'POST',
                  data: 'columns=' + JSON.stringify(data),
              });
        
            requestControl.done(function (result) {
                console.log('result ', result);
                $('.lds-dual-ring').remove();
                $xlsButton.text('загрузка завершена');
                $xlsButton.css({backgroundColor: '#41bd16'});


                if (result.data.indexOf('.csv') > 0) {
                    document.location = result.data
                }
            
            });
        
            requestControl.fail(function () {
                console.error('Can not get table');
            });
        }

        function getTableViolation(data, filter, kind) {
            console.log('downloading xls...');
            console.log('get TableViolation ', data);

            var queryControl = {
                    c: 'serg:super.component',
                    action: 'getTableViolation',
                    mode: 'ajax'
                },
                requestControl = $.ajax({
                    url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
                    type: 'POST',
                    data: 'columns=' + JSON.stringify(data)+'&filter='+JSON.stringify(filter)+'&kind='+kind,
                });

            requestControl.done(function (result) {
                console.log('result ', result);
                $('.lds-dual-ring').remove();
                $xlsButton.text('загрузка завершена');
                $xlsButton.css({backgroundColor: '#41bd16'});


                if (result.data.list.length) {
                    var xlsData = [];
                    result.data.list.forEach(function (row, i) {
                        xlsData[i] = [];
                        xlsData[i] = row;
                    });

                    var xls = new XlsExport(xlsData, $title);
                    isFetch = true;
                    xls.exportToXLS($title + '.xls');
                }
                console.log('xlsData', xlsData);

            });

            requestControl.fail(function () {
                console.error('Can not get table');
            });
        }
    
        function getTableContact(data, filter) {
            console.log('downloading xls...');
            console.log('get TableContact ', data);
        
            var queryControl = {
                  c: 'serg:super.component',
                  action: 'getTableContact',
                  mode: 'ajax'
              },
              requestControl = $.ajax({
                  url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
                  type: 'POST',
                  data: 'columns=' + JSON.stringify(data)+'&filter='+JSON.stringify(filter),
              });
        
            requestControl.done(function (result) {
                console.log('result ', result);
                $('.lds-dual-ring').remove();
                $xlsButton.text('загрузка завершена');
                $xlsButton.css({backgroundColor: '#41bd16'});


                if (result.data.list.length) {
                    var xlsData = [];
                    result.data.list.forEach(function (row, i) {
                        xlsData[i] = [];
                        xlsData[i] = row;
                    });

                    var xls = new XlsExport(xlsData, $title);
                    isFetch = true;
                    xls.exportToXLS($title + '.xls');
                }
                console.log('xlsData', xlsData);
            
            });
        
            requestControl.fail(function () {
                console.error('Can not get table');
            });
        }

        $xlsButton.on('click', function () {
            $('#preloader').prepend('<div class="lds-dual-ring"></div>');
            $(this).text('Загрузка...');
            $(this).css({backgroundColor: '#3773bd'});
            getTable(columnsName, grid_id);
        });


    }

        var splitButton = new BX.UI.SplitButton({
        id: "split-button",
        text: "Скачать записи",
        className: "download-xls",
        size: BX.UI.Button.Size.MEDIUM,
        color: BX.UI.Button.Color.PRIMARY,
        icon: 'ui-btn-icon-list',
        menu: {
            items: [
                {text: 'Скачать по причине изоляции', disabled: true},
                {delimiter: true},
                {
                    text: "возвратившийся",
                    onclick: function(event, item) {
                        $('#preloader').prepend('<div class="lds-dual-ring"></div>');
                        getTableViolation(columnsName, filter, 'возвратившийся');
                    }
                },
                {
                    text: "иные основания",
                    onclick: function(event, item) {
                        $('#preloader').prepend('<div class="lds-dual-ring"></div>');
                        getTableViolation(columnsName, filter, 'иные основания');

                    }
                },
            ],
            offsetTop: 5
        },
        // menuTarget: BX.UI.SplitSubButton.Type.MENU,
        mainButton: {
            tag: BX.UI.Button.Tag.BUTTON,

            onclick: function(button, event) {
                button.setActive(!button.isActive());
                $('#preloader').prepend('<div class="lds-dual-ring"></div>');
                getTableViolation(columnsName, filter, 'main');
            },
        },

        menuButton: {
            onclick: function(button, event) {
                button.setActive(!button.isActive());
            },
            props: {},
        },
        //Атрибуты для контейера двойной кнопки
        props: {},
        //События для целой кнопки
        onclick: function(btn, event) {},
    });
    
    
    var splitButtonContact = new BX.UI.SplitButton({
        id: "split-button-contact",
        text: "Скачать все записи",
        className: "download-xls",
        size: BX.UI.Button.Size.MEDIUM,
        color: BX.UI.Button.Color.PRIMARY,
        icon: 'ui-btn-icon-list',
        menu: {
            items: [
                {
                    text: "Скачать записи с учетом фильтра",
                    onclick: function(event, item) {
                        $('#preloader').prepend('<div class="lds-dual-ring"></div>');
                        getTableContact(columnsName, filter);
                    }
                },
            ],
            offsetTop: 5
        },
        // menuTarget: BX.UI.SplitSubButton.Type.MENU,
        mainButton: {
            tag: BX.UI.Button.Tag.BUTTON,
            
            onclick: function(button, event) {
                button.setActive(!button.isActive());
                $('#preloader').prepend('<div class="lds-dual-ring"></div>');
                getTableAllContact(columnsName);
            },
        },
        
        menuButton: {
            onclick: function(button, event) {
                button.setActive(!button.isActive());
            },
            props: {},
        },
        //Атрибуты для контейера двойной кнопки
        props: {},
        //События для целой кнопки
        onclick: function(btn, event) {},
    });

    var container = document.getElementById("js-button");
    var containerContact = document.getElementById("js-button-contact");

    splitButton.renderTo(container);
    splitButtonContact.renderTo(containerContact);

    var $uploadLabel = $('.ui-ctl-label-text'),
        $uploadInput = $('.ui-ctl-element');

    $uploadInput.on('change', function (e) {
        var fileName = e.target.value.split("\\").pop();
        $uploadLabel.text(fileName);
    });


    var $elementButtons = document.getElementById("add_element_buttons");
    var $form = $('.add_element_form');
    
    var newElementName = ''
    var newElementDate = ''

    var addElementButton = new BX.UI.Button({
        id: "add_button",
        text: "Добавить",
        noCaps: true,
        round: false,
        className: "button_add",
        onclick: function(btn, event) {
    
            notifyWayValue = $('#ATT_NOTIFY_WAY_ENUM').val()
            
            btn.setDisabled();
            saveElementButton.setDisabled(true);
            saveElementButton.setText('Сохранить');
            saveElementButton.setIcon(BX.UI.Button.Icon.ADD);
            cancelElementButton.setDisabled(false)
            saveElementButton.renderTo($elementButtons);
            cancelElementButton.renderTo($elementButtons);
            $form.trigger("reset");
            $form.show()
        },
        size: BX.UI.Button.Size.SMALL,
        color: BX.UI.Button.Color.PRIMARY,
        tag: BX.UI.Button.Tag.BUTTON,
        icon: BX.UI.Button.Icon.ADD,
        state: BX.UI.Button.State
    });

    var saveElementButton = new BX.UI.Button({
        id: "save_button",
        text: "Сохранить",
        noCaps: true,
        round: false,
        className: "button_save",
        onclick: function(btn, event) {
            btn.setState(BX.UI.Button.State.WAITING);
            cancelElementButton.setDisabled(true);
            console.log('FORM_DATA', $form.serialize());
            // btn.setDisabled(true);

            var queryControl = {
                    c: 'serg:super.component',
                    action: 'addElementContact',
                    mode: 'ajax'
                },
                requestControl = $.ajax({
                    url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
                    type: 'POST',
                    data: $form.serialize(),
                });

            requestControl.done(function (result) {
                console.log('result ', result);

                if (result.data.status === 'Y') {

                    setTimeout(function () {
                        btn.setDisabled(true);
                        btn.setIcon(BX.UI.Button.Icon.INFO);
                        btn.setText(result.data.message);
                        cancelElementButton.button.remove();
                        $form.hide();
                        setTimeout(function () {
                            saveElementButton.button.remove();
                            addElementButton.setDisabled(false)
                            BX.Main.gridManager.reload(grid_id)
                        }, 1000);
                    }, 1500);

                } else if (result.data.status === 'N') {
                    btn.setIcon(BX.UI.Button.Icon.INFO);
                    btn.setText(result.data.message);
                    btn.setState(BX.UI.Button.State.ACTIVE);
                    addElementButton.setDisabled(true)
                    cancelElementButton.setDisabled(false)
                }



            });

            requestControl.fail(function () {
                console.error('Can not add element');
            });

        },
        size: BX.UI.Button.Size.SMALL,
        color: BX.UI.Button.Color.SUCCESS,
        tag: BX.UI.Button.Tag.BUTTON,
        icon: BX.UI.Button.Icon.ADD,
        state: BX.UI.Button.State
    });

    var cancelElementButton = new BX.UI.Button({
        id: "cancel_button",
        text: "Отменить",
        noCaps: true,
        round: false,
        className: "button_cancel",
        onclick: function(btn, event) {
            addElementButton.setDisabled(false)
            event.target.remove()
            saveElementButton.button.remove()
            $form.trigger("reset");
            $form.hide()
        },

        size: BX.UI.Button.Size.SMALL,
        color: BX.UI.Button.Color.DANGER,
        tag: BX.UI.Button.Tag.BUTTON,
        icon: BX.UI.Button.Icon.STOP,
        state: BX.UI.Button.State
    });

    addElementButton.renderTo($elementButtons);
    
    
    $('#NAME').on('keypress, keydown, keyup', function (e) {
    
        newElementName = e.target.value
        if (newElementName !== '' && newElementDate !== '') saveElementButton.setDisabled(false)
        else saveElementButton.setDisabled(true)
    });
    $('#ATT_DATE_PERESECHENIYA').on('change', function (e) {
        
        newElementDate = e.target.value
        if (newElementDate !== '' && newElementName !== '') saveElementButton.setDisabled(false)
        else saveElementButton.setDisabled(true)
    });
    
    
    $("#ATT_PHONE").inputmask({"mask": '+7 (999) 999 99 99'});
    
    $("#ATT_ADDRESS").suggestions({
        token: "697e4e53b055f8cbb596f79570f2cbfd118a4a68",
        type: "ADDRESS",
        onSelect: function(suggestion) {
            console.log(suggestion);
        }
    });
    
    $('.add_element_form_row input').on('change, keypress', function () {
        saveElementButton.setText('Сохранить')
    })
    
    

    




})

