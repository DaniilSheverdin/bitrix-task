$(document).ready(function() {
    var reInitEditors = function() {
        for (let i in JCLightHTMLEditor.items) {
            if ($('#' + JCLightHTMLEditor.items[ i ].iFrame.id).length > 0) {
                JCLightHTMLEditor.items[ i ].ReInit();
            }
        }
    }

    var initCreateModal = function() {
        let modalId = '#modalCreate',
            modal = $(modalId);
        modal.find('form').trigger('reset');

        reInitEditors();
        initAccompliceSelect();
        modal.modal({backdrop: 'static', keyboard: false});
    }

    var ordersTable = function(html) {
        if (html !== '') {
            $('.js-orders-list').html(html);
        }
        initSorter();
        initEditModal();
        initRemoveRow();

        let activeButton = ($('.js-orders-list').find('tbody tr').length > 0),
            disabled = 'ui-btn-disabled',
            enabled = 'ui-btn-primary';
        if (!$('.js-delo-sync').hasClass('js-disabled')) {
            $('.js-delo-sync, .js-download-file')
                .toggleClass(disabled, !activeButton)
                .toggleClass(enabled, activeButton)
                .attr('disabled', !activeButton);
        }
    }

    var initSorter = function() {
        $('.js-orders-list tbody').sortable({
            handle: '.sorter',
            helper : function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            },
            stop : function() {
                let wait = BX.showWait('sort-table');

                let request = BX.ajax.runComponentAction('citto:checkorders.protocol', 'sort', {
                    mode: 'ajax',
                    data: {
                        action: 'sort',
                        id: $('input[name=id]').val(),
                        sorting: $('.js-orders-list tbody input').serialize()
                    }
                });

                request.then(function (ret) {
                    ordersTable(ret.data.ORDERS);
                    $('.js-real-orders').html(ret.data.REAL_ORDERS);
                    BX.closeWait('sort-table', wait);
                });
            }
        });
    }

    var initEditModal = function() {
        $('.js-edit-order').off('click').on('click', function() {
            let wait = BX.showWait('sort-table'),
                protocol = $(this).attr('data-protocol'),
                hash = $(this).attr('data-hash'),
                request = BX.ajax.runComponentAction('citto:checkorders.protocol', 'getOrder', {
                    mode: 'ajax',
                    data: {
                        action: 'getOrder',
                        iProtocolId: protocol,
                        hash: hash
                    }
                });

            request.then(function (ret) {
                BX.closeWait('sort-table', wait);
                let modalId = '#modalEdit',
                    modalEdit = $(modalId);
                modalEdit.find('.modal-body').html(ret.data);
                modalEdit.modal({backdrop: 'static', keyboard: false});
                modalEdit.on('hide.bs.modal', function (e) {
                    reInitEditors();
                    modalEdit.find('.modal-body').html('');
                });

                initAccompliceSelect();

                modalEdit.find('form').off('submit').on('submit', function(e) {
                    let text = $(this).find('[name=DETAIL_TEXT]').val();
                    if (text === '' || text === '<br />') {
                        alert('Заполните поле "Содержание поручения"');
                        e.preventDefault();
                        return false;
                    }
                    let wait = BX.showWait('modalEdit'),
                        formData = $(this).serialize(),
                        request = BX.ajax.runComponentAction('citto:checkorders.protocol', 'updateOrder', {
                            mode: 'ajax',
                            data: {
                                action: 'updateOrder',
                                request: formData
                            }
                        });

                    request.then(function (ret) {
                        ordersTable(ret.data.ORDERS);
                        $('.js-real-orders').html(ret.data.REAL_ORDERS);
                        modalEdit.modal('hide');
                        BX.closeWait('sort-table', wait);
                    });

                    e.preventDefault();
                    return false;
                });
            });
        });
    }

    var initRemoveRow = function() {
        $('.js-remove-order').on('click', function(e) {
            let mess = 'Вы уверены, что хотите удалить эту запись?';
            /**
             * @todo Если будет модуль UI версии выше 19.0.500
             * @todo Или подцепить bootbox\подобное для bootstrap
             */
            // BX.UI.Dialogs.MessageBox.show({});

            if (confirm(mess)) {
                let wait = BX.showWait('sort-table'),
                    protocol = $(this).attr('data-protocol'),
                    hash = $(this).attr('data-hash'),
                    request = BX.ajax.runComponentAction('citto:checkorders.protocol', 'removeOrder', {
                        mode: 'ajax',
                        data: {
                            action: 'removeOrder',
                            iProtocolId: protocol,
                            hash: hash
                        }
                    });

                request.then(function (ret) {
                    ordersTable(ret.data.ORDERS);
                    $('.js-real-orders').html(ret.data.REAL_ORDERS);
                    BX.closeWait('sort-table', wait);
                });
            }

            e.preventDefault();
            return false;
        });
    }

    var initAccompliceSelect = function() {
        $('.js-accomplice-add').off('click').on('click', function(e) {
            let newRow = $('.js-accomplice-row').last().clone(),
                lastId = parseInt(newRow.data('id')),
                label = 'DISABLE_DATE_ISPOLN-' + BX.util.getRandomString(),
                checkBox = newRow.find('.form-check');

            lastId = lastId + 1;

            checkBox
                .find('input')
                .attr('name', 'PROP[DISABLE_DATE_ISPOLN][' + lastId + ']');
            checkBox
                .find('input[type=checkbox]')
                .attr('id', label);
            checkBox.find('label').attr('for', label);

            newRow.attr('data-id', lastId);
            newRow.addClass('mt-2');
            newRow
                .find('select')
                .val('')
                .attr('name', 'PROP[ACCOMPLICE][' + lastId + ']');
            newRow.find('.js-accomplice-add').remove();
            newRow.find('.js-accomplice-remove').removeClass('d-none');
            newRow.insertAfter($('.js-accomplice-row').last());

            initAccompliceSelect();
            e.preventDefault();
            return false;
        });
        $('.js-accomplice-remove').off('click').on('click', function(e) {
            $(this).closest('.js-accomplice-row').remove();
            initAccompliceSelect();
            e.preventDefault();
            return false;
        });
    }

    $(".js-add-order").on("click", initCreateModal);

    $('#modalCreate .js-create-order-form').on('submit', function(e) {
        let text = $(this).find('[name=DETAIL_TEXT]').val();
        if (text === '' || text === '<br />') {
            alert('Заполните поле "Содержание поручения"');
            e.preventDefault();
            return false;
        }
        let wait = BX.showWait('modalCreate'),
            formData = $(this).serialize(),
            request = BX.ajax.runComponentAction('citto:checkorders.protocol', 'addOrder', {
                mode: 'ajax',
                data: {
                    action: 'addOrder',
                    request: formData
                }
            });

        request.then(function (ret) {
            ordersTable(ret.data.ORDERS);
            $('.js-real-orders').html(ret.data.REAL_ORDERS);
            initCreateModal();
            BX.closeWait('modalCreate', wait);
        });

        e.preventDefault();
        return false;
    });

    $('.js-download-file').on('click', function(e) {
        if ($(this).hasClass('ui-btn-disabled')) {
            e.preventDefault();
            return false;
        }
        let id = $(this).attr('data-id'),
            wait = BX.showWait('sort-table');
            request = BX.ajax.runComponentAction('citto:checkorders.protocol', 'document', {
            mode: 'ajax',
            data: {
                action: 'document',
                id: id
            }
        });

        request.then(function (ret) {
            if (ret.status === 'success') {
                let downloadLink = document.createElement("a");
                downloadLink.href = ret.data;
                downloadLink.download = "Перечень поручений.docx";

                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            }
            BX.closeWait('sort-table', wait);
        });

        e.preventDefault();
        return false;
    });

    $('.js-user-select').on('click', function() {
        let field = $(this).attr('data-field'),
            multi = ($(this).attr('data-multi')==='true'),
            modalId = '#modalSelectUser',
            modalSelect = $(modalId),
            currentSelected = [];

        /**
         * Текущие выбранные пользователи
         */
        $('input[name="' + field + (multi ? '[]' : '') + '"]').each(function() {
            currentSelected.push($(this).val());
        });

        /**
         * Быстрее чем redraw
         */
        modalSelect.find('.modal-body').html('<div class="js-select-users"></div>');

        $('.js-select-users').jstree({
            'plugins' : [
                'changed',
                'search',
                multi ? 'checkbox' : null
            ],
            'checkbox' : {
                'keep_selected_style' : false,
                'three_state' : false,
            },
            'search' : {
                'show_only_matches': true
            },
            'core' : {
                'data' : function (obj, cb) {
                    let request = BX.ajax.runComponentAction('citto:checkorders.protocol', 'usersTree', {
                        mode: 'ajax',
                        data: {
                            action: 'usersTree',
                            selected: currentSelected
                        }
                    });

                    request.then(function (ret) {
                        if (ret.status === 'success') {
                            cb.call(this, ret.data);
                        }
                    });
                }
            }
        });

        var to = false;
        $('.js-search-user').val('').keyup(function() {
            let value = $(this).val();
            if (to) {
                clearTimeout(to);
            }
            to = setTimeout(function() {
                $('.js-select-users').jstree(true).search(value);
            }, 500);
        })

        modalSelect.modal({backdrop: 'static', keyboard: false});

        modalSelect.find('.js-change-users').off('click').on('click', function() {
            let checked_ids = [],
                checked_names = [],
                checked_titles = [],
                selectedNodes = $('.js-select-users').jstree("get_selected", true),
                container = $('#' + field);

            $.each(selectedNodes, function() {
                checked_ids.push(this.id);
                checked_names.push(this.text);
                checked_titles.push(this.a_attr.title);
            });

            container.empty();

            let ul = $('<ul>').appendTo(container);
            for (let i in checked_ids) {
                container.append($('<input/>', {
                    type: 'hidden',
                    name: field + (multi ? '[]' : ''),
                    value: checked_ids[ i ]
                }));
                $('<li/>', {
                    html: checked_names[ i ],
                    title: checked_titles[ i ]
                }).appendTo(ul);
            }

            $('.js-delo-sync, .js-download-file')
                .toggleClass('ui-btn-disabled', true)
                .toggleClass('js-disabled', true)
                .toggleClass('ui-btn-primary', false)
                .attr('disabled', true);

            modalSelect.modal('hide');
        });
    });

    $('.js-calendar').on('click', function() {
        BX.calendar({
            node: this,
            field: this,
            bTime: false
        });
    });

    $('.js-delo-sync').on('click', function(e) {
        if ($(this).hasClass('ui-btn-disabled')) {
            e.preventDefault();
            return false;
        }

        let $this = $(this),
            mess = 'Вы уверены, что хотите отправить протокол в АСЭД Дело?';
        /**
         * @todo Если будет модуль UI версии выше 19.0.500
         * @todo Или подцепить bootbox\подобное для bootstrap
         */
        // BX.UI.Dialogs.MessageBox.show({});

        if (confirm(mess)) {
            let wait = BX.showWait('sort-table'),
                protocol = $this.attr('data-id'),
                request = BX.ajax.runComponentAction('citto:checkorders.protocol', 'deloSync', {
                    mode: 'ajax',
                    data: {
                        action: 'deloSync',
                        iProtocolId: protocol
                    }
                });

            request.then(function (ret) {
                $this.remove();
                $('input[type=submit]').remove();
                BX.closeWait('sort-table', wait);
                alert('Успешно отправлено в АСЭД Дело');
                window.location.reload();
            }, function (ret) {
                alert('Ошибка при отправке в АСЭД Дело: ' + ret.errors[0].message);
                BX.closeWait('sort-table', wait);
            });
        }
    });

    $('input, textarea').on('keyup change', function() {
        $('.js-delo-sync, .js-download-file')
            .toggleClass('ui-btn-disabled', true)
            .toggleClass('js-disabled', true)
            .toggleClass('ui-btn-primary', false)
            .attr('disabled', true);
    });

    $(".js-start-edit").on("click", function() {
        if (confirm('Протокол отправлен в Дело. При изменении необходимо сохранить протокол и заново запустить синхронизацию')) {
            let id = $(this).attr('data-id');
            window.location.href = '/control-orders/protocol/?id=' + id + '&mode=edit';
        }
    });

    ordersTable('');
});