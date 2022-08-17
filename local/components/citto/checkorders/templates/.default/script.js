BX.addCustomEvent('LHE_ConstructorInited', BX.delegate(function(obj) {
    BX.addCustomEvent(obj, 'OnSaveContent', BX.delegate(function(content) {
        if (/<\w[^>]*(( class="?MsoNormal"?)|( class="?western"?)|(="mso-)|(Astra)|(astra)|(Segoe)|(<font))/gi.test(content)) {
            content = obj.CleanWordText(content);
            content = content.replace(/<font[^>]*>([\s\S]*?)<\/font>/gi, '<p>$1</p>');
            content = content.replace(/<font[^>]*>([\s\S]*?)<\/font>/gi, '$1');
            content = content.replace(/<style[^>]*>([\s\S]*?)<\/style>/gi, '$1');
            content = content.replace(/<p align="[^>]*">([\s\S]*?)<\/p>/gi, '<p>$1</p>');
            content = content.replace(/<p><\/p>/gi, '');
            setTimeout(function(){
                obj.SetEditorContent(content);
            }, 90);
        }
    }, this));
}, this));

$(window).on('load', function() {
    let img = $('<img/>', {
            'src': '/local/images/preloader/white.svg',
            'width': 16,
            'height': 16,
        }),
        getCounters = false;
    $('.main-buttons-item-text-title').each(function() {
        if (
            $(this).text().indexOf(' (0)') <= 0 &&
            $(this).text().indexOf(' (') > 0
        ) {
            $(this)
                .closest('.main-buttons-item-link')
                .find('.main-buttons-item-counter')
                .append(img.clone())
                .addClass('p-0');
            getCounters = true;
        }
    })

    if (getCounters) {
        request = BX.ajax.runComponentAction(
            'citto:checkorders',
            'getCounters',
            {
                mode: 'ajax',
                json: {
                    action: 'getCounters'
                }
            }
        );
        request.then(function(data) {
            $('.main-buttons-item-counter').text('');
            for (key in data.data) {
                let div = $('div[data-counter-id="control_orders_' + key+ '"]'),
                    count = data.data[ key ];
                div.attr('data-counter', count);
                if (count > 0) {
                    let countReal = count;
                    if (count > 99) {
                        count = '99+';
                    }
                    div.find('.main-buttons-item-counter')
                        .text(count)
                        .attr('title', countReal)
                        .removeClass('p-0');
                }
            }
        });
    }

    var hash = window.location.hash;
    if (hash !== '' && hash.indexOf('scroll')) {
        $(window).scrollTop(hash.slice(7));
    }

    $('.js-popup-task-add').on('click', function() {
       $('.popup-task-add').toggle();
       return false;
    });

    $('.js-show-delegate-history').on('click', function() {
       $('.delegate-history').removeClass('d-none');
       $(this).closest('li').addClass('d-none');
       return false;
    });

    $('.js-controler-resh').on('change', function() {
        if ($(this).val() == '1277') {
            $('.dop_control').show();
            $('.new_date input').attr('disabled', false);
        } else {
            $('.dop_control').hide();
            $('.new_date input').attr('disabled', true);
        }

        if ($(this).val() == '1276') {
            $('.snytie').show();
            $('.snytie').find('input').attr('disabled', false);
        } else {
            $('.snytie').find('input').attr('disabled', true);
            $('.snytie').hide();
        }
    });

    $('.js-new-date-check').on('change', function() {
        if ($(this).is(':checked')) {
            $('.dop_fields_change_srok input').removeAttr('disabled');
            $('.dop_fields_change_srok').show();
        } else {
            $('.dop_fields_change_srok').hide();
            $('.dop_fields_change_srok input').attr('disabled', 'disabled');
        }
    });

    $('.js-dop-fields-select').on('change', function() {
        let value = $(this).val();
        $('.dop_fields').hide();
        $('.dop_fields input').attr('disabled', 'disabled');
        $('.dop_fields select').attr('disabled', 'disabled');
        $('.dop_fields textarea').attr('disabled', 'disabled');

        if (value != '') {
            $('.dop_fields.' + value + ' input').removeAttr('disabled');
            $('.dop_fields.' + value + ' select').removeAttr('disabled');
            $('.dop_fields.' + value + ' textarea').removeAttr('disabled');
            if (value == 'to_position') {
                $('.dop_fields.' + value + ' textarea').attr('required', 'required');
            }
            $('.dop_fields.' + value).show();
        }
    });

    $('.js-show-vote-data').on('click', function() {
        if ($('.js-vote-data input').attr('disabled') != 'disabled') {
            $('.js-vote-data input').attr('disabled', 'disabled');
            $('.js-vote-data select').removeAttr('disabled');
            $('.js-vote-data').hide();
        } else {
            $('.js-vote-data input').removeAttr('disabled');
            $('.js-vote-data select').removeAttr('disabled');
            $('.js-vote-data').show();
        }
        return false;
    });

    $('.js-doc-position-generate').on('click', function() {
        request = BX.ajax.runComponentAction(
            'citto:checkorders',
            'getDocsForPoruch',
            {
                mode: 'class',
                data: {
                    action: 'getDocsForPoruch',
                    ids: $(this).data('ids')
                }
            }
        );
        request.then(function(data) {
           window.open(data.data);
        });
        return false;
    });


    function getReportControllOrders(arrayOfIds){
        if (arrayOfIds.length == 0) {
            arrayOfIds = $('#control-orders-list_table .main-grid-row').map(function() {
                return $(this).data('id');
            }).get();
        }

        request = BX.ajax.runComponentAction(
            'citto:checkorders',
            'listPdfGenerate',
            {
                mode: 'ajax',
                json: {
                    action: 'listPdfGenerate',
                    data: arrayOfIds
                }
            }
        );
        request.then(function(data) {
            window.open('/upload/checkorders/' + data.data.filename);
        });
    }

    $('.js-save-to-pdf').on('click', function() {
        let arrayOfIds = getSelectedIds();
        getReportControllOrders(arrayOfIds);
        return false;
    });

    $('.js-save-to-pdf-single').on('click', function(event) {
        
        getReportControllOrders( [event.currentTarget.dataset.id] );
        
        return false;
    });

    $('.js-show-comments').on('click', function() {
        if ($('#comments-block').is(':hidden')) {
           $('.js-show-comments').html('Скрыть старые');
        } else {
           $('.js-show-comments').html('Показать старые');
        }
        $('#comments-block').toggle();
        return false;
    });

    $('body').on('click','.js-delete-ispolnitel', function() {
        $(this).closest('div.ispolnitel').remove();
        return false;
    });

    $('.js-add-ispolnitel').on('click', function() {
        $('div.ispolnitel')
            .last()
            .clone()
            .insertAfter($('div.ispolnitel').last());

        $('div.ispolnitel')
            .last()
            .find('.js-delete-ispolnitel')
            .removeClass('hide')
            .closest('.d-none').removeClass('d-none');

        let label = 'DisableDateIspoln-' + BX.util.getRandomString();

        $('div.ispolnitel')
            .last()
            .find('.col-12')
            .removeClass('col-12')
            .addClass('col-11');

        $('div.ispolnitel')
            .last()
            .find('.js-date-ispoln')
            .find('input[type=checkbox]')
            .attr('id', label)
            .on('change', function(e) {
                toggleDateIspoln(this);
                e.preventDefault();
                return false;
            });

        $('div.ispolnitel')
            .last()
            .find('[type=hidden]')
            .attr('class', label);

        $('div.ispolnitel')
            .last()
            .find('.js-date-ispoln')
            .find('label')
            .attr('for', label);

        renderUsersSelect();
        return false;
    });

    $('body').on('click','.js-delete-subexecutor', function() {
        $(this).closest('div.subexecutor').remove();
        renderUsersSelect();
        return false;
    });

    $('.js-add-subexecutor').on('click', function() {
        if ($('div.subexecutor select').hasClass("select2-hidden-accessible")) {
            $('div.subexecutor select').select2('destroy');
        }
        let id = $('div.subexecutor').last().data('id')+1,
            newField = $('div.subexecutor').last().clone(),
            fieldName = $(this).data('field') || false;

        newField
            .attr('data-id', id);

        newField
            .find('.col-1')
            .removeClass('d-none');

        newField
            .find('.col-12')
            .addClass('col-11')
            .removeClass('col-12');

        let name = 'PROP[SUBEXECUTOR][' + id + ']';
        if (fieldName !== false) {
            name = fieldName;
        }
        newField
            .find('select')
            .attr('title', '')
            .attr('name', name)
            .val('');

        newField
            .find('[type=radio]')
            .attr('name', 'PROP[REQUIRED_VISA][' + id + ']');

        newField
            .find('[type=radio][value=N]')
            .attr('checked', true);

        newField.insertAfter($('div.subexecutor').last());

        renderUsersSelect();
        return false;
    });

    $('.js-classifcator-cat-select').on('change', function() {
        var data_id = $(this).val();
        $('.themes-selects').attr('disabled', 'disabled');
        if (data_id != '') {
            $('#classificator_id_' + data_id).removeAttr('disabled');
        }
    });

    $('.js-legend-show').on('click', function() {
        // BX.UI.Dialogs.MessageBox.alert($('.legend-detail').html());
        $('.legend-detail').toggle();
        return false;
    });

    $('.js-changelog').on('click', function() {
        BX.SidePanel.Instance.open(
            '/control-orders/?page=changelog',
            {
                width: 1100,
                cacheable: false,
                allowChangeHistory: false,
                allowChangeTitle: false,
                label: {
                    text: 'История изменений',
                    color: '#FFFFFF',
                    bgColor: '#2FC6F6',
                    opacity: 80
                }
            }
        );
        return false;
    });

    $('body').on('click', '.js-show-events', function() {
        var day = $(this).data('date');
        $('.days-info .day-info').hide();
        $('.days-info #' + day).slideDown();
        return false;
    });

    $('body').on('click', '.js-hide-event-block', function() {
        $('.days-info .day-info').hide();
        return false;
    });

    $('body').on('click', '.js-calendar-prev', function() {
        let $this = $(this).closest('.bx-calendar'),
            prev = $this.prev('.bx-calendar');
        if (prev.length > 0) {
            $this.addClass('d-none');
            prev.removeClass('d-none');
        }
    });

    $('body').on('click', '.js-calendar-next', function() {
        let $this = $(this).closest('.bx-calendar'),
            next = $this.next('.bx-calendar');
        if (next.length > 0) {
            $this.addClass('d-none');
            next.removeClass('d-none');
        }
    });

    $('.js-sign-file').click(function(event) {
        var popupSignFile = new BX.PopupWindow('popup-iframe', null, {
            closeIcon: {right: '12px', top: '10px'},
            width: '100%',
            height: '100%'
        });

        $(window).on('message onmessage', function(e) {
            var data = e.originalEvent.data;
            if (data == 'filesigner_hiden') {
                popupSignFile.close();
                $('.js-signed-data-id').val('');
            }
            if (data == 'filesigner_signed') {
                popupSignFile.close();
                $('.js-signed-true-button').removeClass('ui-btn-disabled');
                $('.js-signed-true-button').prop('disabled', false);
            }
            if (data == 'filesigner_error') {
                popupSignFile.close();
                $('.js-signed-data-id').val('');
                alert('Ошибка. Попробуйте позже');
            }
        });

        button = $(this);
        request = BX.ajax.runComponentAction(
            'citto:checkorders',
            'pdfGenerate',
            {
                mode: 'ajax',
                json: {
                    action: 'pdfGenerate',
                    data: $('[name=DETAIL_TEXT]').val(),
                    action_head: button.data('actionhead'),
                    id: button.data('id'),
                    visa: button.data('visa')
                }
            }
        );
        request.then(function (dataToSign) {
            if (dataToSign.status == 'success') {
                popupSignFile.setContent('');
                $('<iframe>', {
                    src: '/podpis-fayla/?FILES[]=' + dataToSign.data.file_id + '&CHECK_SIGN=Y&sessid=' + dataToSign.data.sessid,
                    id: 'popup-iframe',
                    frameborder: 0,
                    scrolling: 'no'
                }).appendTo('#popup-iframe');
                popupSignFile.show();

                $('.js-signed-data-id').val(dataToSign.data.file_id);
                $('.js-signed-true-button').removeClass('ui-btn-disabled');
                $('.js-signed-true-button').prop('disabled', false);

                button.addClass('ui-btn-disabled');
                button.prop('disabled', true);

                event.preventDefault();
            } else {
                alert('Произошла ошибка, попробуйте позже.' + dataToSign.errors);
            }
        });
        return false;
    });

    $('.js-sign-file-simple').click(function(event) {
        var popupSignFileSimple = new BX.PopupWindow('popup-iframe', null, {
            closeIcon: {right: '12px', top: '10px'},
            width: '100%',
            height: '100%'
        });

        $(window).on('message onmessage', function(e) {
            var data = e.originalEvent.data;
            if (data == 'filesigner_hiden') {
                popupSignFileSimple.close();
                $('.js-signed-data-id').val('');
            }
            if (data == 'filesigner_signed') {
                popupSignFileSimple.close();
                $('.js-signed-true-button').removeClass('ui-btn-disabled');
                $('.js-signed-true-button').prop('disabled', false);
            }
            if (data == 'filesigner_error') {
                popupSignFileSimple.close();
                $('.js-signed-data-id').val('');
                alert('Ошибка. Попробуйте позже');
            }
        });

        let $this = $(this),
            form = $this.closest('form'),
            request = BX.ajax.runComponentAction(
                'citto:checkorders',
                'pdfGenerate',
                {
                    mode: 'ajax',
                    json: {
                        action: 'pdfGenerate',
                        data: $('[name=DETAIL_TEXT]').val(),
                        action_head: $this.data('actionhead'),
                        id: $this.data('id'),
                        visa: $this.data('visa')
                    }
                }
            );
        request.then(function (dataToSign) {
            if (dataToSign.status == 'success') {
                popupSignFileSimple.setContent('');
                $('<iframe>', {
                    src: '/podpis-fayla/?FILES[]=' + dataToSign.data.file_id + '&CHECK_SIGN=Y&sessid=' + dataToSign.data.sessid,
                    id: 'popup-iframe',
                    frameborder: 0,
                    scrolling: 'no'
                }).appendTo('#popup-iframe');
                popupSignFileSimple.show();

                $('.js-signed-data-id').val(dataToSign.data.file_id);

                window.addEventListener('message', function(msg) {
                    if (msg.data === 'filesigner_signed') {
                        form.submit();
                    }
                });

                event.preventDefault();
            } else {
                alert('Произошла ошибка, попробуйте позже.' + dataToSign.errors);
            }
        });
        return false;
    });

    $('.js-sign-file-exist').click(function(event) {
        $('.popup-window').remove();
        let popupId = 'popup-iframe-' + BX.util.getRandomString(),
            $this = $(this),
            popupSignFileExist = new BX.PopupWindow(popupId, null, {
                closeIcon: {right: '12px', top: '10px'},
                width: '100%',
                height: '100%'
            });

        $(window).on('message onmessage', function(e) {
            var data = e.originalEvent.data;
            if (data == 'filesigner_hiden') {
                popupSignFileExist.close();
                $('.js-signed-data-id').val('');
            }
            if (data == 'filesigner_signed') {
                popupSignFileExist.close();
                $this.closest('form').submit();
            }
            if (data == 'filesigner_error') {
                popupSignFileExist.close();
                $('.js-signed-data-id').val('');
                alert('Ошибка. Попробуйте позже');
            }
        });

        let request = BX.ajax.runComponentAction(
            'citto:checkorders',
            'pdfGenerate',
            {
                mode: 'ajax',
                json: {
                    action: 'pdfGenerate',
                    file_id: $this.attr('data-file')
                }
            }
        );
        request.then(function (dataToSign) {
            if (dataToSign.status == 'success') {
                popupSignFileExist.setContent('');
                $('<iframe>', {
                    src: '/podpis-fayla/?FILES[]=' + dataToSign.data.file_id + '&CHECK_SIGN=Y&sessid=' + dataToSign.data.sessid,
                    id: 'popup-iframe',
                    frameborder: 0,
                    scrolling: 'no'
                }).appendTo('#' + popupId);
                popupSignFileExist.show();

                $('.js-signed-data-id').val(dataToSign.data.file_id);

                window.addEventListener('message', function(msg) {
                    if (msg.data === 'filesigner_signed') {
                        form.submit();
                    }
                });

                event.preventDefault();
            } else {
                alert('Произошла ошибка, попробуйте позже.' + dataToSign.errors);
            }
        });
        return false;
    });

    if ($('.select2').length > 0) {
        $('.select2').select2({
            tags: true,
            tokenSeparators: [','],
            templateSelection: function(state) {
                if (!state.id) {
                    return state.text;
                }

                return $('<span><span class="label-' + state.element.value.replace(' ', '') + '">' + state.text+ '</span></span>');
            }
        });
    }

    function renderUsersSelect() {
        $('.select2-users').select2({
            escapeMarkup: function(m) { return m; },
            templateResult: function(state) {
                if (!state.id) {
                    return state.text;
                }
                return '<span bx-tooltip-user-id="' + state.id + '">' + state.text + '</span>';
            }
        });
    }

    if ($('.select2-users').length > 0) {
        renderUsersSelect()
    }

    $('body').on('click', '.js-order-tag-remove', function() {
        $(this).closest('.order-tag').remove();
    });

    if (window.DETAIL_TEXT) {
        $('.js-add-comment-ispolnitel').on('submit', function(e) {
            let text = $(this).find('[name=DETAIL_TEXT]'),
                content = window.DETAIL_TEXT.CleanWordText(text.val()),
                visa = [];

            content = content.replace(/<br[^>]*>/, '').trim();
            if (content.length < 3) {
                BX.UI.Dialogs.MessageBox.alert('Отчет не заполнен');
                e.preventDefault();
                return false;
            }
        });

        $('.js-add-comment-accomplience').on('submit', function(e) {
            let visa = [];
            $(this).find('[name="VISA[]"]').each(function(){
                let val = $(this).val();
                if (val != '') {
                    visa.push(val);
                }
            });
            if (visa.length < 1) {
                BX.UI.Dialogs.MessageBox.alert('Согласующие не выбраны');
                e.preventDefault();
                return false;
            }
        });
    }

    $('.js-set-visa').on('click', function(e) {
        let data = {
            id : $(this).data('comment'),
            user : $(this).data('user'),
            value : $(this).data('value'),
            comment : $('textarea[name=VISA_COMMENT]').val()
        }

        if (data.value === 'N' && data.comment == '') {
            BX.UI.Dialogs.MessageBox.alert('Введите комментарий');
            e.preventDefault();
            return false;
        }

        request = BX.ajax.runComponentAction(
            'citto:checkorders',
            'setVisa',
            {
                mode: 'class',
                json: {
                    data: data
                }
            }
        );
        request.then(function(ret) {
            window.location.reload();
        }, function (ret) {
            BX.UI.Dialogs.MessageBox.alert(ret.errors[1].message);
        });
    });

    $('body').on('change', '.js-set-visa-away', function(e) {
        let data = {
            id : $(this).data('comment'),
            user : $(this).data('user'),
            value : $(this).val(),
            comment : ''
        }

        request = BX.ajax.runComponentAction(
            'citto:checkorders',
            'setVisa',
            {
                mode: 'class',
                json: {
                    data: data
                }
            }
        );
        request.then(function(ret) {
            window.location.reload();
        }, function (ret) {
            BX.UI.Dialogs.MessageBox.alert(ret.errors[1].message);
        });
    });

    $('.js-return').on('click', function(e) {
        if ($('.js-return-form').data('new') !== true) {
            $(this).addClass('d-none');
            $('.js-return-form').removeClass('d-none');
            $('[name=RETURN_COMMENT]').attr('required', true);
        } else {
            BX.UI.Dialogs.MessageBox.show({
                title: 'Отклонение отчёта',
                message: $('.js-return-form').html(),
                modal: true,
                okCaption: 'Вернуть на доработку',
                buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
                onOk: function(messageBox) {
                    let popup = $('#' + messageBox.popupWindow.uniquePopupId),
                        orderId = parseInt(popup.find('[name=ORDER_ID]').val()),
                        reportId = parseInt(popup.find('[name=RETURN_ID]').val()),
                        comment = popup.find('[name=RETURN_COMMENT]').val();
                    if (orderId > 0 && reportId > 0 && comment.length > 3) {
                        let request = BX.ajax.runComponentAction(
                                'citto:checkorders',
                                'returnReport',
                                {
                                    mode: 'ajax',
                                    json: {
                                        action: 'returnReport',
                                        orderId: orderId,
                                        reportId: reportId,
                                        comment: comment
                                    }
                                }
                            );
                        request.then(function (data) {
                            messageBox.close();
                            window.location.reload();
                        });
                    } else {
                        popup.find('[name=RETURN_COMMENT]').trigger('focus');
                        return false;
                    }
                },
                onCancel: function(messageBox) {
                    messageBox.close();
                },
            });
        }
        e.preventDefault();
        return false;
    });

    $('.js-return-cancel').on('click', function(e) {
        $('.js-return').removeClass('d-none');
        $('.js-return-form').addClass('d-none');
        $('[name=RETURN_COMMENT]').attr('required', false);
        e.preventDefault();
        return false;
    });

    $('.js-delegate-add').off('click').on('click', function(e) {
        let $this = $(this),
            container = $this.data('container') || '#SELECT_USER',
            message = $(container).clone(),
            selected = $this.data('user'),
            single = $this.data('single');

        if (selected > 0) {
            message.find('option[value=' + selected + ']').attr('selected', true);
        }

        BX.UI.Dialogs.MessageBox.show({
            title: 'Выбор сотрудника',
            message: message.html(),
            modal: true,
            buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
            onOk: function(messageBox) {
                let popup = $('#' + messageBox.popupWindow.uniquePopupId),
                    userId = popup.find('select').val(),
                    userName = popup.find('select :selected').text();

                setExecutor($this, userId, userName, single);

                messageBox.close();
            },
            onCancel: function(messageBox) {
                messageBox.close();
            },
        });

        setTimeout(function() {
            $('.popup-window select').select2({
                escapeMarkup: function(m) { return m; },
                templateResult: function(state) {
                    if (!state.id) {
                        return state.text;
                    }
                    return '<span bx-tooltip-user-id="' + state.id + '">' + state.text + '</span>';
                }
            });
        }, 50);

        e.preventDefault();
        return false;
    });

    function setExecutor(container, userId, userName, single) {
        if (single) {
            container.closest('.js-user-selector').find('.order-tag').each(function() {
                if ($(this).find('input').val() !== '') {
                    $(this).remove();
                }
            });
        }

        let lastTag = container.closest('.js-user-selector').find('.order-tag').last();
        let newTag = lastTag
            .clone()
            .removeClass('d-none');

        if (lastTag.data('li') == true) {
            let list = container.closest('li').find('> ul');
            newTag
                .appendTo($('<li/>').appendTo(list));
        } else {
            newTag
                .insertBefore(container);
        }

        newTag.find('span')
                .text(userName)
                .attr('bx-tooltip-user-id', userId);

        userId = ''+userId;
        if (userId.substr(0, 3) === 'DEP') {
            newTag.find('span').removeAttr('bx-tooltip-user-id');
        }
        newTag.find('input')
                .val(userId)
                .removeClass('dummy')
                .attr('disabled', false);

        container.attr('data-user', userId);
    }

    $('body').on('click', '.js-delegate-remove', function(e) {
        let $this = $(this).closest('.order-tag');
        $this.find('input').val('-' + $this.find('input').val());
        $this.toggleClass('removed');
        e.preventDefault();
        return false;
    });

    $('input[name*=DISABLE_DATE_ISPOLN], input[name*=DISABLE_SUBEXECUTOR_DATE], input[name*=DISABLE_NEW_SUBEXECUTOR_DATE]').on('change', function(e) {
        toggleDateIspoln(this);
        e.preventDefault();
        return false;
    });

    $('.js-accept-ispolnitel').on('click', function(e) {
        let field = $(this).attr('data-field') || 'DELEGATE_USER',
            className = $(this).attr('data-field-class') || '',
            selector = '[name="' + field + '"]:not(.dummy)',
            curUser = $(this).attr('data-user') || 0,
            curSrok = $(this).attr('data-srok') || '',
            newSrok = $('input[name=DELEGATE_SROK]').val()
        if (className != '') {
            selector = '.' + className + ':not(.dummy)';
        }
        let uId = $(selector).val();

        if (typeof uId === 'undefined' || uId == '' || uId == 0) {
            BX.UI.Dialogs.MessageBox.alert('Не выбран исполнитель');
            e.preventDefault();
            return false;
        }

        if (curUser != uId && curSrok != '' && newSrok != '') {
            let datetimeRegex = /(\d\d)\.(\d\d)\.(\d\d\d\d)/;

            let curSrokArray = datetimeRegex.exec(curSrok);
            curSrokDate = new Date(curSrokArray[3], curSrokArray[2], curSrokArray[1]);

            let newSrokArray = datetimeRegex.exec(newSrok);
            newSrokDate = new Date(newSrokArray[3], newSrokArray[2], newSrokArray[1]);

            if (curSrokDate.getTime() < newSrokDate.getTime()) {
                BX.UI.Dialogs.MessageBox.alert('Новый срок не может быть больше ' + curSrok);
                e.preventDefault();
                return false;
            }
        }
    });

    $('.js-clear-date').on('click', function(e) {
        let form = $(this).closest('form');
        form.find('input[name=FROM]').val('');
        form.find('input[name=TO]').val('');
        form.submit();
    });

    $('.js-add-object').on('click', function(e) {
        let modalId = '#modalObject',
            modalEdit = $(modalId);
        request = BX.ajax.runComponentAction(
            'citto:checkorders',
            'getObject',
            {
                mode: 'ajax',
                json: {}
            }
        );
        let table = $('.js-object-table');
        request.then(function(ret) {
            for (let i in ret.data) {
                let tr = $('<tr/>').appendTo(table);
                $('<td/>', {
                    html: ret.data[ i ].name
                }).appendTo(tr);
                $('<td/>', {
                    html: ret.data[ i ].address
                }).appendTo(tr);

                let td = $('<td/>').appendTo(tr);
                $('<a/>', {
                    href: '#',
                    class: 'ui-btn ui-btn-primary ui-btn-icon-add js-object-add-table',
                    'data-html': ret.data[ i ].html
                }).appendTo(td);
            }

            $('.js-object-add-table').on('click', function(e) {
                $('.js-objects').append($(this).attr('data-html'));
                $('#modalObject').modal('hide');
                e.preventDefault();
                return false;
            });

            $('.js-find-table').keyup(function () {
                var value = this.value.toLowerCase().trim();

                $(this).closest('.tab-pane').find('table tr').each(function (index) {
                    if (!index) return;
                    $(this).find('td').each(function () {
                        var id = $(this).text().toLowerCase().trim();
                        var not_found = (id.indexOf(value) == -1);
                        $(this).closest('tr').toggle(!not_found);
                        return not_found;
                    });
                });
            });
        }, function (ret) {
            // BX.UI.Dialogs.MessageBox.alert(ret.errors[0].message);
            alert(ret.errors[0].message);
        });

        modalEdit.modal();
        modalEdit.on('hide.bs.modal', function (e) {
            table.empty();
        });

        $('.js-find-address').suggestions({
            token: '697e4e53b055f8cbb596f79570f2cbfd118a4a68',
            type: 'ADDRESS',
            onSelect: function(suggestion) {
                $(this).val(suggestion.value);
                $('.js-fias-simple').val(JSON.stringify(suggestion));
            },
            onSelectNothing: function(suggestion) {
                $(this).val('');
                $('.js-fias-simple').val('');
            }
        });

        $('body').on('click', '.js-open-map', function(e) {
            $('#map-container').show();
        });

        e.preventDefault();
        return false;
    });

    $('.js-object-add-simple').on('submit', function(e) {
        let wait = BX.showWait('modalObject'),
            fias = $(this).find('input[name=FIAS]').val(),
            id = parseInt($(this).find('input[name=ID]').val()),
            reload = $(this).find('input[name=RELOAD]').val() == 'Y' || false,
            orders = [];

        if (fias.length <= 0) {
            e.preventDefault();
            return false;
        }

        $(this).find('input.js-item-orders').each(function(){
            orders.push($(this).val());
        })

        let data = {
            id: id,
            name: $(this).find('input[name=NAME]').val(),
            fias: JSON.parse(fias),
            orders: orders,
        };

        if (data.name.length <= 0) {
            data.name = data.fias.value;
        }

        request = BX.ajax.runComponentAction(
            'citto:checkorders',
            'addObject',
            {
                mode: 'ajax',
                json: {
                    data: data
                }
            }
        );
        request.then(function(ret) {
            $('.js-objects').append(ret.data.html);
            $('#modalObject').modal('hide');
            $('.js-object-add-simple').trigger('reset');
            BX.closeWait('modalObject', wait);
            if (reload) {
                window.location.reload();
            }
        }, function (ret) {
            // BX.UI.Dialogs.MessageBox.alert(ret.errors[0].message);
            alert(ret.errors[1].message);
        });

        e.preventDefault();
        return false;
    });

    $('.js-delegate-poruch').on('change', function() {
        let val = $(this).val(),
            btn = $('.js-accept-ispolnitel[value=accept_ispolnitel]'),
            text = btn.attr('data-text'),
            sub = $('select[name*=DELEGATE_SUBEXECUTOR]');
        if (val > 0) {
            text = 'Делегировать';
            sub.each(function(){
                $(this).find('option').each(function(){
                    if ($(this).val().substr(0, 4) == 'user') {
                        $(this).attr('disabled', true);
                    }
                })
            });
        } else {
            sub.each(function(){
                $(this).find('option').each(function(){
                    $(this).attr('disabled', false);
                })
            });
        }

        btn.text(text);
    });

    $('.js-delete-order').on('click', function(e){
        if (confirm('Вы уверены, что хотите удалить поручение?')) {
            window.location.href = $(this).attr('data-href');
        }
        e.preventDefault();
        return false;
    });

    $('.js-delete-object').on('click', function(e){
        let $this = $(this),
            id = $this.data('id');
        if ($this.hasClass('ui-btn-disabled')) {
            e.preventDefault();
            return false;
        }
        if (confirm('Вы уверены, что хотите удалить объект?')) {
            request = BX.ajax.runComponentAction(
                'citto:checkorders',
                'deleteObject',
                {
                    mode: 'ajax',
                    json: {
                        action: 'deleteObject',
                        id: id,
                    }
                }
            );
            request.then(function(data) {
                $this.closest('tr').addClass('d-none');
                $('tr.row-' + id).addClass('d-none');
            });
        }
        e.preventDefault();
        return false;
    });

    function resortVisas() {
        let type = parseInt($('select[name=VISA_TYPE] option:selected').val());
        if (type == 1781) {
            $('.js-end-table').each(function(){
                let $this = $(this),
                    user = $this.data('user');

                $('.visa-row[data-user=' + user + ']').not($this).remove();
                
                $this.clone().insertAfter($('.visa-row').last());
                $this.remove();
            });
        }
    }

    BX.addCustomEvent(
        'BX.Main.User.SelectorController:select',
        BX.delegate(function(data) {
            let id = data.selectorId,
                type = id.replace('VISA_FAKE_', ''),
                salt = '';

            if (type == 'ISPOLNITEL1') {
                salt = '1';
            } else if (type == 'ISPOLNITEL2') {
                salt = '2';
            }
            $('.ui-tile-selector-item').remove();
            $('#' + id).val('');
            if (BX.util.in_array(data.item.id, selectedVisa)) {
                return;
            }
            selectedVisa.push(data.item.id);

            let newVisa = $('.VISA-' + type + ' .visa-row.' + id)
                            .clone()
                            .insertAfter($('.VISA-' + type + ' .visa-row').last());
            newVisa.removeClass('d-none ' + id);
            newVisa.find('[type=hidden]')
                    .attr('disabled', false)
                    .attr('name', 'VISA' + salt + '[]')
                    .val(data.item.id)
                    .after('<span bx-tooltip-user-id="' + data.item.id.replace('U', '') + '">' + data.item.name.replace(' ', '&nbsp;') + '</span>');

            newVisa.find('.js-delete-visa')
                    .attr('data-user', data.item.id);

            resortVisas();
            $('.docsign-form').removeClass('d-inline-block').addClass('d-none');
            $('.js-send-nodraft').text($('.js-send-nodraft').data('visa'));
        })
    );

    $('.visa-container tbody').sortable({
        items: 'tr:not(.disabled)',
        handle: '.sorter',
        helper: function(e, ui) {
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        },
        stop: function() {
            resortVisas();
        }
    });

    $('body').on('change', 'select[name=VISA_TYPE]', function(e) {
        resortVisas();

        $('.js-double-visa').toggleClass('d-none', ($(this).val() == 1780));

        e.preventDefault();
        return false;
    });

    $('body').on('click','.js-delete-visa', function(e) {
        let text = $(this).data('text') || 'Вы уверены, что хотите удалить визирующего из списка?';
        if (confirm(text)) {
            $(this).closest('.visa-row').remove();
            if ($('.visa-row:not(.visa-fake)').length <= 0) {
                $('.docsign-form').removeClass('d-none').addClass('d-inline-block');
                $('.js-send-nodraft').text($('.js-send-nodraft').data('sign'));
            } else {
                $('.js-send-nodraft').text($('.js-send-nodraft').data('visa'));
            }
            for (let i in selectedVisa) {
                if (selectedVisa[ i ] == $(this).data('user')) {
                    delete selectedVisa[ i ];
                }
            }
            selectedVisa = selectedVisa.filter(function(e){return e}); 
        }
        e.preventDefault();
        return false;
    });

    $('body').on('click','.js-set-signer', function(e) {
        let $this = $(this),
            message = $('#SELECT_SIGNER').clone(),
            selected = parseInt($this.data('user'));

        if (selected > 0) {
            message.find('option[value=' + selected + ']').attr('selected', true);
        }

        BX.UI.Dialogs.MessageBox.show({
            title: 'Выбор подписанта',
            message: message.html(),
            modal: true,
            buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
            onOk: function(messageBox) {
                let popup = $('#' + messageBox.popupWindow.uniquePopupId),
                    userId = parseInt(popup.find('select').val()),
                    userName = popup.find('select :selected').text();

                $('.js-signer-name')
                    .text(userName)
                    .attr('bx-tooltip-user-id', userId);
                $('.js-signer-id').val(userId);

                $this.data('user', userId);
                messageBox.close();
            },
            onCancel: function(messageBox) {
                messageBox.close();
            },
        });
        setTimeout(function() {
            function formatState (state) {
                if (!state.id) {
                    return state.text;
                }
                return '<span bx-tooltip-user-id="' + state.id + '">' + state.text + '</span>';
            };
            $('.popup-window select').select2({
                escapeMarkup: function(m) { return m; },
                templateResult: formatState
            });
        }, 50);

        e.preventDefault();
        return false;
    });

    $('body').on('change', 'select.form-control', function() {
        let value = $(this).find(':selected').text();
        $(this).attr('title', $.trim(value));
    });

    $(document).on('focus click', '.main-ui-multi-select[data-name="ISPOLNITEL"], .main-ui-multi-select[data-name="SUBEXECUTOR"]', function() {
        $('.popup-window-content .main-ui-select-inner-item').each(function(e) {
            let data = $.parseJSON($(this).attr('data-item'));
            if (
                data &&
                typeof data.VALUE == 'string' &&
                data.VALUE != '' &&
                data.VALUE.indexOf('-') < 0
            ) {
                $(this).addClass('pl-3');
            }
        });
    });

    BX.addCustomEvent(
        'BX.Main.Filter:show',
        BX.delegate(function(content) {
            $('.main-ui-select[data-name="DATE_CREATE_datesel"], .main-ui-select[data-name="DATE_ISPOLN_datesel"]').each(function() {
                let data = $.parseJSON($(this).attr('data-items')),
                    current = $.parseJSON($(this).attr('data-value')),
                    $this = this;
                if (current.VALUE == 'NONE') {
                    for (let i in data) {
                        if (data[i].VALUE == 'RANGE') {
                            // $(this).attr('data-value', JSON.stringify(data[i]));
                            // $(this).find('.main-ui-select-name').text(data[i].NAME);
                            var result = {
                                node: $this,
                                instance: new BX.Main.ui.select($this)
                            };
                            BX.onCustomEvent(window, 'UI::Select::Change', [result.instance, data[i]]);
                        }
                    }
                }
            });
        })
    );

    BX.addCustomEvent(
        'BX.Main.Filter:beforeApply',
        BX.delegate(function(content) {
            $('.main-ui-select[data-name="DATE_CREATE_datesel"], .main-ui-select[data-name="DATE_ISPOLN_datesel"]').each(function() {
                let data = $.parseJSON($(this).attr('data-items')),
                    current = $.parseJSON($(this).attr('data-value')),
                    $this = this,
                    name = $(this).data('name'),
                    from = '',
                    to = '';
                if (name == 'DATE_CREATE_datesel') {
                    from = $('input[name=DATE_CREATE_from]').val();
                    to = $('input[name=DATE_CREATE_to]').val();
                } else if (name == 'DATE_ISPOLN_datesel') {
                    from = $('input[name=DATE_ISPOLN_from]').val();
                    to = $('input[name=DATE_ISPOLN_to]').val();
                }
                if (from == '' && to == '' && current.VALUE == 'RANGE') {
                    for (let i in data) {
                        if (data[i].VALUE == 'NONE') {
                            var result = {
                                node: $this,
                                instance: new BX.Main.ui.select($this)
                            };
                            BX.onCustomEvent(window, 'UI::Select::Change', [result.instance, data[i]]);
                        }
                    }
                }
            });
        })
    );

    $('body').on('click', '.js-text-toggler', function(e) {
        $(this).removeClass('js-text-toggler').removeAttr('title');
    });

    $('body').on('click', '.js-hide-edit', function(e) {
        $(this).remove();
        $('.js-hide-edit').toggleClass('d-none js-hide-edit');
        return false;
    });

    $('body').on('click', '.js-undraft', function(e) {
        let reportId = $(this).data('report-id'),
            orderId = $(this).data('order-id'),
            text = $(this).text().toLowerCase();
        if (confirm('Вы уверны, что хотите ' + text + '?')) {
            request = BX.ajax.runComponentAction(
                'citto:checkorders',
                'sendToSign',
                {
                    mode: 'ajax',
                    json: {
                        action: 'sendToSign',
                        reportId: reportId,
                        orderId: orderId
                    }
                }
            );
            request.then(function(data) {
                window.location.reload();
            });
        }
        return false;
    });

    $('body').on('click', '.js-send-nodraft', function(e) {
        let text = $(this).text().toLowerCase();
        if (confirm('Вы уверны, что хотите ' + text + '?')) {
            $('[name=send_report_to_sign]').val(1);
            $(this).closest('form').submit();
        } else {
            $('[name=send_report_to_sign]').val(0);
        }
        return false;
    });

    $('body').on('click', '.js-send-to-control', function(e) {
        let popupId = 'popup-iframe-' + BX.util.getRandomString(),
            $this = $(this),
            reportId = $(this).data('report-id') || 0,
            orderId = $(this).data('order-id'),
            isExternal = $(this).data('external') || false,
            fileId = 0;
        if (isExternal) {
            $('[name=send_report_to_control]').val(0);
            if (reportId <= 0) {
                $('[name=send_report_to_control]').val(1);
                $(this).closest('form').submit();
                return false;
            }
        }

        if (
            reportId <= 0 ||
            orderId <= 0 ||
            $this.hasClass('ui-btn-clock')
        ) {
            return false;
        }

        $this.addClass('ui-btn-clock');
        
        if (isExternal) {
            let requestControl = BX.ajax.runComponentAction(
                'citto:checkorders',
                'sendToControl',
                {
                    mode: 'ajax',
                    json: {
                        action: 'sendToControl',
                        orderId: orderId,
                        reportId: reportId
                    }
                }
            );
            requestControl.then(function (data) {
                $this.removeClass('ui-btn-clock');
                window.location.reload();
            });
        } else {
            var popupSignFileNew = new BX.PopupWindow(popupId, null, {
                closeIcon: {right: '12px', top: '10px'},
                width: '100%',
                height: '100%'
            });

            $(window).on('message onmessage', function(e) {
                var data = e.originalEvent.data;
                if (data == 'filesigner_hiden') {
                    popupSignFileNew.close();
                    $this.removeClass('ui-btn-clock');
                    $('#' + popupId).remove();
                }
                if (data == 'filesigner_error') {
                    popupSignFileNew.close();
                    $this.removeClass('ui-btn-clock');
                    $('#' + popupId).remove();
                    alert('Ошибка. Попробуйте позже');
                }
            });

            let request = BX.ajax.runComponentAction(
                'citto:checkorders',
                'pdfGenerate',
                {
                    mode: 'ajax',
                    json: {
                        action: 'pdfGenerate',
                        id: orderId,
                        report_id: reportId
                    }
                }
            );
            request.then(function (dataToSign) {
                if (dataToSign.status == 'success') {
                    fileId = dataToSign.data.file_id;
                    popupSignFileNew.setContent('');
                    let iframe = $('<iframe>', {
                        src: '/podpis-fayla/?FILES[]=' + fileId + '&CHECK_SIGN=Y&sessid=' + dataToSign.data.sessid,
                        id: 'popup-iframe-send-to-control',
                        frameborder: 0,
                        scrolling: 'no'
                    }).appendTo('#' + popupId);
                    popupSignFileNew.show();

                    window.addEventListener('message', function(msg) {
                        if (msg.data === 'filesigner_signed') {
                            let requestControl = BX.ajax.runComponentAction(
                                    'citto:checkorders',
                                    'sendToControl',
                                    {
                                        mode: 'ajax',
                                        json: {
                                            action: 'sendToControl',
                                            orderId: orderId,
                                            reportId: reportId,
                                            fileId: fileId,
                                        }
                                    }
                                );
                            requestControl.then(function (data) {
                                $this.removeClass('ui-btn-clock');
                                popupSignFileNew.close();
                                $('#' + popupId).remove();
                                window.location.reload();
                            });
                        }
                    });
                } else {
                    alert('Произошла ошибка, попробуйте позже.' + dataToSign.errors);
                }
            });
        }
        return false;
    });

    $('body').on('click', '.js-accept-new-ispolnitel', function(e) {
        let $this = $(this),
            id = $(this).data('id'),
            value = $(this).data('value') || 'Y';

        $this.addClass('ui-btn-clock');

        if (value == 'N') {
            BX.UI.Dialogs.MessageBox.show({
                title: 'Отклонение проекта резолюции',
                message: '<textarea name="COMMENT" placeholder="Причина отклонения" class="form-control" required></textarea>',
                modal: true,
                okCaption: 'Отклонение проекта резолюции',
                buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
                onOk: function(messageBox) {
                    let popup = $('#' + messageBox.popupWindow.uniquePopupId),
                        comment = popup.find('[name=COMMENT]').val();
                    if (comment.length > 3) {
                        let request = BX.ajax.runComponentAction(
                                'citto:checkorders',
                                'acceptIspolnitel',
                                {
                                    mode: 'ajax',
                                    json: {
                                        action: 'acceptIspolnitel',
                                        id: id,
                                        value: value,
                                        comment: comment
                                    }
                                }
                            );
                        request.then(function (data) {
                            $this.removeClass('ui-btn-clock');
                            window.location.reload();
                        });
                    } else {
                        popup.find('[name=COMMENT]').trigger('focus');
                        return false;
                    }
                },
                onCancel: function(messageBox) {
                    $this.removeClass('ui-btn-clock');
                    messageBox.close();
                },
            });
        } else {
            let request = BX.ajax.runComponentAction(
                    'citto:checkorders',
                    'acceptIspolnitel',
                    {
                        mode: 'ajax',
                        json: {
                            action: 'acceptIspolnitel',
                            id: id,
                            value: value
                        }
                    }
                );
            request.then(function (data) {
                $this.removeClass('ui-btn-clock');
                window.location.reload();
            });
        }
        return false;
    });

    $('body').on('click', '.js-edit-new-ispolnitel', function(e) {
        let $this = $(this),
            form = $('.js-delegate-form'),
            id = $(this).data('id'),
            ispolnitel = $(this).data('ispolnitel'),
            srok = $(this).data('srok'),
            subexecutor = JSON.parse(decodeURIComponent($(this).data('subexecutor'))),
            comment = $(this).data('comment');

        form.removeClass('d-none');

        form.find('[name="edit-resolution"]').remove();

        $('<input/>', {
            type: 'hidden',
            name: 'edit-resolution',
            value: id
        }).appendTo(form);

        if (ispolnitel > 0) {
            if ($('select[name="DELEGATE_PORUCH"]').length > 0) {
                if (ispolnitel > 0) {
                    form.find('select[name="DELEGATE_PORUCH"]').val('user_' + ispolnitel).change();
                } else {
                    form.find('select[name="DELEGATE_PORUCH"]').val(ispolnitel).change();
                }
            } else {
                setExecutor($('.js-delegate-user'), ispolnitel, userList[ ispolnitel ], true);
            }
        }

        if (srok != '') {
            form.find('input[name=DELEGATE_SROK]').val(srok).change();
        }

        if (subexecutor.length > 0) {
            $('.js-delegate-subexecutor').closest('.js-user-selector').find('.order-tag').each(function() {
                if ($(this).find('input').val() !== '') {
                    $(this).remove();
                }
            });
            for (let i in subexecutor) {
                let uId = subexecutor[ i ];
                if ($('select[name="DELEGATE_SUBEXECUTOR[]"]').length > 0) {
                    if (uId > 0) {
                        form.find('select[name="DELEGATE_SUBEXECUTOR[]"]').last().val('user_' + uId).change();
                    } else {
                        form.find('select[name="DELEGATE_SUBEXECUTOR[]"]').last().val(uId).change();
                    }
                    $('.js-add-subexecutor').trigger('click');
                } else {
                    setExecutor($('.js-delegate-subexecutor'), uId, userList[ uId ], false);
                }
            }
        }

        if (comment != '') {
            form.find('textarea[name=DELEGATE_COMMENT]').val(comment).change();
        }

        renderUsersSelect();

        $('.js-new-ispolnitel-form').addClass('d-none');
        $('.js-new-ispolnitel-form-cancel').removeClass('d-none');

        return false;
    });

    $('body').on('click', '.js-edit-new-ispolnitel-cancel', function(e) {
        $('.js-new-ispolnitel-form').removeClass('d-none');
        $('.js-new-ispolnitel-form-cancel').addClass('d-none');

        $('.js-delegate-form').addClass('d-none');

        return false;
    });

    $('body').on('click', '.js-kurator-zamechanie', function(e) {
        let $this = $(this),
            form = $this.closest('form');
        BX.UI.Dialogs.MessageBox.show({
            title: 'Замечание',
            message: $('.js-kurator-zamechanie-form').html(),
            modal: true,
            okCaption: 'Добавить замечание',
            buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
            onOk: function(messageBox) {
                let popup = $('#' + messageBox.popupWindow.uniquePopupId),
                    comment = popup.find('[name=RETURN_COMMENT]').val();
                if (comment.length > 3) {
                    $('<textarea/>', {
                        name: 'DETAIL_TEXT',
                        class: 'd-none'
                    }).val(comment).appendTo(form);
                    $('input[name="subaction"]').val('zamechanie');
                    form.submit();
                    messageBox.close();
                } else {
                    popup.find('[name=RETURN_COMMENT]').trigger('focus');
                    return false;
                }
            },
            onCancel: function(messageBox) {
                messageBox.close();
            },
        });
        return false;
    });

    $('body').on('click', '.js-subaction-add-comment', function(e) {
        e.preventDefault();
        popupDefault(
            'При сохранении нового текста отчета уже поставленные визы и подпись будут удалены. Вы действительно хотите изменить текст отчета?',
            () => {
                $('[name="subaction"]').click();
            },
            () => {
                return;
            },
        );
    });
});

function toggleDateIspoln(el) {
    let isChecked = $(el).is(':checked'),
        parent = $(el).closest('.js-date-ispoln'),
        isRequired = parent.attr('data-required')!='false',
        dateInput = parent.find('input[type=text][name*="DATE"]'),
        id = $(el).attr('id');

    dateInput
        .attr('required', !isChecked&&isRequired)
        .toggleClass('d-none', isChecked);

    $('[type=hidden].' + id).attr('disabled', isChecked);
}

function add_action_svyzai(Grid) {
    let arrayOfIds = getSelectedIds();
    if (arrayOfIds.length == 0) {
        alert('Не выбрано поручений');
    } else {
        $('#form_add').html('');
        arrayOfIds.forEach(function(item, i, arr) {
           $('<input>').attr('type', 'hidden').attr('name', 'add[]').attr('value', item).appendTo('#form_add');
        });
        $('#form_add').submit();
    }
}

function add_action_position(Grid) {
    let arrayOfIds = getSelectedIds();
    if (arrayOfIds.length == 0) {
        alert('Не выбрано поручений');
    } else {
        $('#form_add').html('');
        $('#form_add').attr('action','?edit=0&action=add_position');
        arrayOfIds.forEach(function(item, i, arr) {
           $('<input>').attr('type', 'hidden').attr('name', 'add[]').attr('value', item).appendTo('#form_add');
        });
        $('#form_add').submit();
    }
}

function edit_action_disacept(Grid) {
    edit_action(Grid, 'disaept');
}

function edit_action(Grid, selected) {
    let arrayOfIds = getSelectedIds();
    if (selected === '' || typeof selected === 'undefined') {
        selected = $('#set-type_control').attr('data-value');
    }

    if (selected == 'disaept') {
        var popup_edit_kurator = BX.PopupWindowManager.create('popup-message', BX('element'), {
            content: '',
            width: 500,
            height: 300,
            zIndex: 100,
            closeIcon: {
                opacity: 1
            },
            titleBar: 'Комментарий для отклонения',
            closeByEsc: true,
            darkMode: false,
            autoHide: true,
            draggable: true,
            resizable: false,
            min_height: 250,
            min_width: 250,
            lightShadow: true,
            angle: false,
            overlay: {
                backgroundColor: 'black',
                opacity: 500
            },
            buttons: [
                new BX.PopupWindowButton({
                    text: 'Отправить на доп контроль',
                    id: 'save-btn',
                    className: 'ui-btn ui-btn-success',
                    events: {
                        click: function() {
                            popup_edit_kurator.close();
                            Grid.editSelected();
                            Grid.editSelectedSave();
                        }
                   }
                }),
                new BX.PopupWindowButton({
                    text: 'Отменить',
                    id: 'copy-btn',
                    className: 'ui-btn ui-btn-primary',
                    events: {
                        click: function() {
                            popup_edit_kurator.close();
                        }
                    }
                })
            ],
            events: {
                onPopupShow: function() {
                    popup_edit_kurator.setContent($('#text-kurator').html())
                }
            }
        });
        popup_edit_kurator.show();
    } else if (selected == 'reject') {
       KuratorAction('reject');
    } else if (selected == 'accept') {
       KuratorAction('accept');
    } else if (selected == 'move_to_work') {
        let data = {
            ids: arrayOfIds,
            prop: {
                'ACTION': 'NEW'
            }
        };
        updateOrders(data);
    } else if (selected == 'change_post' || selected == 'change_controler') {
        if (selected == 'change_post') {
            var type = 'post',
                name = 'Выберите куратора';
        } else {
            var type = 'controler',
                name = 'Выберите контролера';
        }

        popupUserSelect(type, name, function(userId) {
            let data = {
                ids: arrayOfIds,
                prop: {}
            };
            data.prop[ type.toUpperCase() ] = userId;

            updateOrders(data);
        });
    }
}

function popupUserSelect(id, name, callback) {
    let message = $('.js-select-' + id).clone();

    BX.UI.Dialogs.MessageBox.show({
        title: name,
        message: message.html(),
        modal: true,
        buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
        onOk: function(messageBox) {
            let popup = $('#' + messageBox.popupWindow.uniquePopupId),
                userId = parseInt(popup.find('select').val()),
                userName = popup.find('select :selected').text();

            callback(userId);
            messageBox.close();
        },
        onCancel: function(messageBox) {
            messageBox.close();
        },
    });
}

function popupDefault(message, callback_ok, callback_cancel) {
    BX.UI.Dialogs.MessageBox.show({
        title: name,
        message: message,
        modal: true,
        buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
        onOk: function(messageBox) {
            callback_ok();
            messageBox.close();
        },
        onCancel: function(messageBox) {
            callback_cancel();
            messageBox.close();
        },
    });
}

function AcceptKuratorAction() {
    if ($('#popup-iframe2').length <= 0) {
        var popup = new BX.PopupWindow('popup-iframe2', null, {
            closeIcon: {right: '12px', top: '10px'},
            width: '100%',
            height: '100%'
        });
    }

    $(window).on('message onmessage', function(e) {
        var data = e.originalEvent.data;

        if (data == 'filesigner_hiden') {
            $('#popup-iframe2').hide();
        }
        if (data == 'filesigner_signed') {
            request = BX.ajax.runComponentAction(
                'citto:checkorders',
                'ActionFromList',
                {
                    mode: 'class',
                    data: {
                        action: 'ActionFromList',
                        arrayOfIds: arrayOfIds,
                        arrayOfFilesIds: arrayOfFilesIds,
                        sAction: 'accept_kurator'
                    }
                }
            );
            request.then(function (data) {
                $('#popup-iframe2').hide();
                location.reload(true);
            });
        }
        if (data == 'filesigner_error') {
            $('#popup-iframe2').hide();
            alert('Ошибка. Попробуйте позже');
        }
    });

    var arrayOfIds = getSelectedIds();

    var arrayOfFilesIds = [];
    var sessid = '';
    $.each(arrayOfIds, function(index, value) {
        request = BX.ajax.runComponentAction(
            'citto:checkorders',
            'pdfGenerate',
            {
                mode: 'ajax',
                json: {
                    action: 'pdfGenerate',
                    action_head: 'kurator',
                    id: value
                }
            }
        );
        request.then(function (dataToSign) {
            if (dataToSign.status == 'success'){
                sessid = dataToSign.data.sessid;
                arrayOfFilesIds[ index ] = dataToSign.data.file_id;
            } else {
                alert('Произошла ошибка, попробуйте позже.' + dataToSign.errors);
            }

            if (index == (arrayOfIds.length-1)){
                setTimeout(function(){
                    $('#popup-iframe2').html('');
                    $('<iframe>', {
                        src: '/podpis-fayla/?FILES[]='+arrayOfFilesIds.join('&FILES[]=')+'&CHECK_SIGN=Y&sessid='+dataToSign.data.sessid,
                        id: 'popup-iframe',
                        frameborder: 0,
                        scrolling: 'no'
                    }).appendTo('#popup-iframe2');
                    $('#popup-iframe2').show();
                }, 500)
            }
        });
    });
}

function KuratorAction(type) {
    if (type !== 'reject') {
        type = 'accept'
    }
    if ($('#popup-iframe2').length <= 0) {
        var popup = new BX.PopupWindow('popup-iframe2', null, {
            closeIcon: {right: '12px', top: '10px'},
            width: '100%',
            height: '100%'
        });
    }

    $(window).on('message onmessage', function(e) {
        var data = e.originalEvent.data;

        if (data == 'filesigner_hiden') {
            $('#popup-iframe2').hide();
        }
        if (data == 'filesigner_signed')
        {
            request = BX.ajax.runComponentAction(
                'citto:checkorders',
                'ActionFromList',
                {
                    mode: 'class',
                    data: {
                        action: 'ActionFromList',
                        sAction: type + '_kurator',
                        arrayOfIds: arrayOfIds,
                        arrayOfFilesIds: arrayOfFilesIds,
                    }
                }
            );
            request.then(function (data) {
                $('#popup-iframe2').hide();
                location.reload(true);
            });
        }
        if (data == 'filesigner_error') {
            $('#popup-iframe2').hide();
            alert('Ошибка. Попробуйте позже');
        }
    });

    var arrayOfIds = getSelectedIds();
    var arrayOfFilesIds = [];
    var sessid = '';
    $.each(arrayOfIds, function(index, value) {
        request = BX.ajax.runComponentAction(
            'citto:checkorders',
            'pdfGenerate',
            {
                mode: 'ajax',
                json: {
                    action: 'pdfGenerate',
                    action_head: 'kurator',
                    id: value
                }
            }
        );
        request.then(function (dataToSign) {
            if (dataToSign.status == 'success'){
                sessid = dataToSign.data.sessid;
                arrayOfFilesIds[ index ] = dataToSign.data.file_id;
            } else {
                alert('Произошла ошибка, попробуйте позже.' + dataToSign.errors);
            }

            if (index == (arrayOfIds.length-1)) {
                setTimeout(function() {
                    $('#popup-iframe2').html('');
                    $('<iframe>', {
                        src: '/podpis-fayla/?FILES[]='+arrayOfFilesIds.join('&FILES[]=')+'&CHECK_SIGN=Y&sessid='+dataToSign.data.sessid,
                        id: 'popup-iframe',
                        frameborder: 0,
                        scrolling: 'no'
                    }).appendTo('#popup-iframe2');
                    $('#popup-iframe2').show();
                }, 500)
            }
        });
    });
}

function onAddTaskSelect(e) {
    $('.js-task-add-form input[name=add]').val(e.id);
    $('.js-task-add-form').submit();
}

function getSelectedIds() {
    return $('#control-orders-list_table .main-grid-row.main-grid-row-checked').map(function() {
        if ($(this).data('id') != 'template_0') {
            return $(this).data('id');
        }
    }).get();
}

function updateOrders(data) {
    request = BX.ajax.runComponentAction(
        'citto:checkorders',
        'updateOrders',
        {
            mode: 'class',
            json: {
                data: data
            }
        }
    );
    request.then(function(ret) {
        window.location.reload();
    }, function (ret) {
        alert(ret.errors[1].message);
    });
}

function openDetail(type, id, params, backUrl) {
    backUrl = decodeURIComponent(backUrl);
    backUrl = backUrl.replace('&', '|');
    backUrl = backUrl + '#scroll' + $(window).scrollTop();
    let url = '/control-orders/?' + type + '=' + id + '&' + params + '&back_url=' + escape(backUrl);
    window.location.href = url;
}

function changeCalendar(maxDate) {
    let el = $('[id ^= "calendar_popup_"]');
    let links = el.find(".bx-calendar-cell");
    $('.bx-calendar-left-arrow').attr({'onclick': 'changeCalendar();',});
    $('.bx-calendar-right-arrow').attr({'onclick': 'changeCalendar();',});
    $('.bx-calendar-top-month').attr({'onclick': 'changeMonth();',});
    $('.bx-calendar-top-year').attr({'onclick': 'changeYear();',});
    let date = new Date();
    for (var i = 0; i < links.length; i++)
    {
        let atrDate = links[i].attributes['data-date'].value;
        let d = date.valueOf();
        let g = links[i].innerHTML;
        if (date - atrDate > 24*60*60*1000) {
            $('[data-date="' + atrDate +'"]').addClass("bx-calendar-date-hidden disabled");
        }
        if (typeof maxDate !== 'undefined') {
            if (atrDate > maxDate) {
                $('[data-date="' + atrDate +'"]').addClass("bx-calendar-date-hidden disabled");
            }
        }
    }
}

function changeMonth() {
    let el = $('[id ^= "calendar_popup_month_"]');
    let links = el.find(".bx-calendar-month");
    for (var i =0; i < links.length; i++) {
        let func = links[i].attributes['onclick'].value;
        $('[onclick="' + func +'"]').attr({'onclick': func + '; changeCalendar();',});
    }
}

function changeYear() {
    let el = $('[id ^= "calendar_popup_year_"]');
    let link = el.find(".bx-calendar-year-input");
    let func2 = link[0].attributes['onkeyup'].value;
    $('[onkeyup="' + func2 +'"]').attr({'onkeyup': func2 + '; changeCalendar();',});
    let links = el.find(".bx-calendar-year-number");
    for (var i =0; i < links.length; i++) {
        let func = links[i].attributes['onclick'].value;
        $('[onclick="' + func +'"]').attr({'onclick': func + '; changeCalendar();',});
    }
}

