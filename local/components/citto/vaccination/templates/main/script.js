var siteDir = BX.message('SITE_DIR');
$(function () {
    Inputmask.extendDefaults({
        autoUnmask: true,
        showMaskOnHover: false,
        showMaskOnFocus: false,
        placeholder: "_",
        removeMaskOnSubmit: true,
        onUnMask: function onUnMask(masked, unmasked) {
            var lastTypedChar = unmasked[unmasked.length - 1];
            var lastIndex = masked.lastIndexOf(lastTypedChar) + 1;
            var finalMasked = masked.substring(0, lastIndex);
            return finalMasked;
        }
    });

    if ($('.grid_filter').length) {
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
                        var data = {
                            name: $(this).text(),
                            code: $(this).attr('data-name')
                        };
                        columnsName.push(data);
                    }
                });
            });
            columnsName = removeDuplicates(columnsName);
            BX.addCustomEvent('BX.Main.Grid:paramsUpdated', BX.delegate(function () {
                columnsName = [];
                $xlsButton.text('скачать все записи');
                $xlsButton.css({
                    backgroundColor: 'green'
                });
                columns = userOptions.options.views.default.columns.split(',');
                $('.main-grid-head-title').each(function (i) {
                    $('.main-grid-cell-head').each(function (k) {
                        if (i === k && $(this).text() !== '') {
                            var data = {
                                name: $(this).text(),
                                code: $(this).attr('data-name')
                            };
                            columnsName.push(data);
                        }
                    });
                });
                columnsName = removeDuplicates(columnsName);
                console.log('event if !length', columnsName);
            }));
        } else {
            $('.main-grid-cell-head').each(function () {
                var idx = columns.indexOf($(this).attr('data-name'));

                if (idx !== -1) {
                    var data = {
                        name: $(this).find('.main-grid-head-title').text(),
                        code: columns[idx]
                    };
                    columnsName.push(data);
                }
            });
            columnsName = removeDuplicates(columnsName);
            BX.addCustomEvent('BX.Main.Grid:paramsUpdated', BX.delegate(function () {
                columnsName = [];
                $xlsButton.text('скачать все записи');
                $xlsButton.css({
                    backgroundColor: 'green'
                });
                columns = userOptions.options.views.default.columns.split(',');
                $('.main-grid-cell-head').each(function () {
                    var idx = columns.indexOf($(this).attr('data-name'));

                    if (idx !== -1) {
                        var data = {
                            name: $(this).find('.main-grid-head-title').text(),
                            code: columns[idx]
                        };
                        columnsName.push(data);
                    }
                });
                columnsName = removeDuplicates(columnsName);
                console.log('event if length', columnsName);
            }));
        }

        var containerButton = document.getElementById("js-button");
        var splitButton = new BX.UI.SplitButton({
            id: "split-button",
            text: "Скачать все записи",
            className: "download-xls",
            size: BX.UI.Button.Size.MEDIUM,
            color: BX.UI.Button.Color.PRIMARY,
            icon: 'ui-btn-icon-list',
            menu: {
                items: [{
                    text: "Скачать записи с учетом фильтра",
                    onclick: function onclick() {
                        getTable(columnsName, filter);
                    }
                }],
                offsetTop: 5
            },
            mainButton: {
                tag: BX.UI.Button.Tag.BUTTON,
                onclick: function onclick(button) {
                    button.setActive(!button.isActive());
                    getTable(columnsName, null);
                }
            },
            menuButton: {
                onclick: function onclick(button, event) {
                    button.setActive(!button.isActive());
                },
                props: {}
            },
            props: {},
            onclick: function onclick(btn, event) {
            }
        });
        splitButton.renderTo(containerButton);
    }

    var errors = {};
    var isValidForm = true;
    $("#snils").inputmask("999-999-999 99");
    $("#phone").inputmask("+7 999 999-99-99");
    $("#passport_sn").inputmask("9999 999999");
    $("#passport_code").inputmask("999-999");
    $('.ui-ctl-element input').each(function () {
        if ($(this).is(':disabled')) {
            $(this).css({
                color: '#a9adb2'
            });
            $(this).parent().css({
                backgroundColor: 'whitesmoke'
            });
        }
    });
    var dateFields = ['vac_date', 'birthday_date', 'passport_issued_date'];
    var textFields = ['fio', 'phone', 'snils', 'passport_sn', 'passport_issued_by', 'passport_code', 'passport_address', 'oms_number', 'oms_service'];
    var timeFields = ['time'];
    var agreeFields = ['agree'];
    var allFields = [].concat(dateFields, textFields, timeFields, agreeFields);
    allFields.forEach(function (el) {
        errors[el] = true;
    });
    errors.fio = false;

    function switchError(self, action) {
        var val = self.val();
        var name = self.attr('name');

        if (agreeFields.indexOf(name) >= 0) {
            if (self.is(':checked')) {
                val = 'on';
            } else val = '';
        }

        if (name === 'snils' && val.length !== 14) val = '';
        if (name === 'phone' && val.length !== 16) val = '';
        if (name === 'passport_sn' && val.length !== 11) val = '';
        if (name === 'passport_code' && val.length !== 7) val = '';

        if (val.length) {
            if (dateFields.indexOf(name) >= 0) {
                self.parent().parent().removeClass('ui-ctl-danger');
                errors[name] = false;
            }

            if (textFields.indexOf(name) >= 0 || timeFields.indexOf(name) >= 0) {
                self.parent().removeClass('ui-ctl-danger');
                errors[name] = false;
            }

            if (agreeFields.indexOf(name) >= 0) {
                self.next().removeClass('error');
                errors[name] = false;
            }
        } else {
            if (dateFields.indexOf(name) >= 0) {
                self.parent().parent().addClass('ui-ctl-danger');
                errors[name] = true;
            }

            if (textFields.indexOf(name) >= 0 || timeFields.indexOf(name) >= 0) {
                self.parent().addClass('ui-ctl-danger');
                errors[name] = true;
            }

            if (agreeFields.indexOf(name) >= 0) {
                self.next().addClass('error');
                errors[name] = true;
            }
        }

        isValidForm = true;

        for (var field in errors) {
            if (errors.hasOwnProperty(field)) {
                if (errors[field]) {
                    isValidForm = false;
                }
            }
        }

        if (action === 'submit') {
            $('.ui-ctl-danger:first').find('.required').focus();
        }

        if (isValidForm) {
            console.log('isValidForm', isValidForm);
            $('.vaccination_send').addClass('ui-btn-success ui-btn-icon-done');
            return true;
        } else {
            console.log('isValidForm', isValidForm);
            $('.vaccination_send').removeClass('ui-btn-success ui-btn-icon-done');
            return false;
        }
    }

    $('.form_vaccination').submit(function (e) {
        e.preventDefault();
        var data = $(this).serializeArray();
        var noErrors = false;
        $('.input_group .required').each(function () {
            var $self = $(this);
            noErrors = switchError($self, 'submit');
        });
        $('.input_group .required-change').each(function () {
            var $self = $(this);
            noErrors = switchError($self, 'submit');
        });

        if (noErrors && isValidForm) {
            requestRows('writeData', data).then(function (response) {
                if (response.error === "Элемент с таким символьным кодом уже существует.<br>") {
                    BX.UI.Notification.Center.notify({
                        content: 'Данный человек уже записан на вакцинацию.<br><br> Дата и время: ' + response.isset_date
                    });
                }
                if (response.message) {
                    BX.UI.Notification.Center.notify({
                        content: response.message
                    });
                }

                if (response.id > 0) {
                    window.location = siteDir + 'vaccination/';
                }
            });
        }
    });
    $('.input_group .required').on('keypress, keyup', function () {
        var $self = $(this);
        switchError($self, 'change');
    });
    $('.input_group .required-change').on('change', function () {
        var $self = $(this);
        switchError($self, 'change');
    });
    $('.vaccination_delete').on('click', function () {
        var id = $(this).attr('data-id');

        if (confirm('Вы уверены что хотите отменить запись?')) {
            request('deleteWrite', [{
                name: 'id',
                value: id
            }]).then(function (response) {
                BX.UI.Notification.Center.notify({
                    content: response.message
                });

                if (response.success) {
                    setTimeout(function () {
                        window.location = siteDir + 'vaccination/';
                    }, 2000);
                }
            });
        }
    });
    $('#vac_date').on('change', function () {
        var val = $(this).val();
        let sTypeVaccination = '';
        if ($('[name="type_vaccination"]').length) {
            sTypeVaccination = $('[name="type_vaccination"] option:checked').attr('js-xml-id').toLowerCase();
        }

        let data = {
            'date' : val,
            'type_vaccination' : sTypeVaccination
        };

        var $htmlOptions = '<option value="">...</option>';
        request('getFreeTimes', [{
            name: 'data',
            value: JSON.stringify(data)
        }]).then(function (response) {
            response.forEach(function (el) {
                $htmlOptions += "<option value=\"".concat(el, "\">").concat(el, "</option>");
            });
            $('#time').html($htmlOptions);
        });
    });
    $('.required-change').on('keypress', function () {
        return false;
    });
    $('body').on('click', '#next_step', function (e) {
        e.preventDefault();
        let sTypeVaccination = $('[name="type_vaccination"] option:checked').attr('js-xml-id').toLowerCase();
        let sTypeVaccinationTitle = $('[name="type_vaccination"] option:checked').text();
        if (sTypeVaccination != 'covid-19') {
            $('.hidden_form').slideDown();
        } else {
            request('newRecordCovid', []).then(function (response) {
                if (response == false) {
                    window.location = siteDir + 'vaccination/';
                }
            });
            $('.hidden_form').slideDown();
        }

        $('.type_vaccination_title').text(sTypeVaccinationTitle);
    });

    $('body').on('change', '[name="type_vaccination"]', function (e) {
        $('.hidden_form').slideUp();
    });
});