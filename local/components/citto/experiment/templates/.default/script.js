$(document).ready(function(){
    $('.js-calendar').on('click', function() {
        BX.calendar({
            node: this,
            field: this,
            bTime: false
        });
    });

    $('.js-show-users th').on('click', function() {
        let id = $(this).closest('tr').attr('data-id');
        $('.dep-' + id).toggleClass('d-none hidden');
    });

    $('.js-show-users [type=checkbox]').on('click', function() {
        let id = $(this).closest('tr').attr('data-id'),
            isChecked = $(this).is(':checked');
        $('.dep-' + id)
            .find('[type=checkbox]:not(:disabled)')
            .prop('checked', isChecked)
            .trigger('change');
    });

    $('.js-user-checker').on('change', function(){
        let cnt = $('.js-user-checker:checked').length,
            isVisible = (cnt <= 0);

        $('.js-buttons').toggleClass('d-none hidden', isVisible);
        $('#users-count').html('(' + cnt + ')');
    });

    $('.js-step-1').off('click').on('click', function(){
        var users = [];
        $('.experiment .table [type=checkbox]:checked').each(function(){
            let uId = parseInt($(this).val()),
                uName = $('.js-user-name[data-id=' + uId + ']').text().trim();
            if (uId > 0) {
                users.push({'id': uId, 'name': uName});
            }
        });

        if (users.length > 0) {
            let modalId = '#modalStep1',
                modal = $(modalId),
                table = modal.find('.users');

            modal.find('form').trigger('reset').off('submit').on('submit', function(e) {
                let wait = BX.showWait('modalStep1'),
                    formData = $(this).serialize(),
                    request = BX.ajax.runComponentAction('citto:experiment', 'step1', {
                        mode: 'ajax',
                        data: {
                            action: 'step1',
                            request: formData
                        }
                    });

                request.then(function (ret) {
                    alert(ret.data);
                    modal.modal('hide');
                    window.location.reload();
                    BX.closeWait('modalStep1', wait);
                }, function (ret) {
                    alert(ret.errors[0].message);
                    BX.closeWait('modalStep1', wait);
                });

                e.preventDefault();
                return false;
            });

            table.html('');
            for (var i in users) {
                let user = users[ i ],
                    div = $('<div />', {
                        class: 'form-check'
                    }).appendTo(table);

                $('<input />', {
                    class: 'form-check-input',
                    type: 'checkbox',
                    name: 'USER[' + user.id + ']',
                    id: 'user-cb-' + user.id,
                    value: user.id,
                    checked: true
                }).appendTo(div);

                $('<label />', {
                    class: 'form-check-label',
                    for: 'user-cb-' + user.id,
                    text: user.name
                }).appendTo(div);
            }
            modal.modal();
        }
    });

    $('.js-remove-file').off('click').on('click', function(){
        let userId = $(this).attr('data-user'),
            fileId = $(this).attr('data-file'),
            request = BX.ajax.runComponentAction('citto:experiment', 'removefile', {
                mode: 'ajax',
                data: {
                    action: 'removefile',
                    user: userId,
                    file: fileId
                }
            });

        request.then(function (ret) {
            alert(ret.data);
            window.location.reload();
        }, function (ret) {
            alert(ret.errors[0].message);
        });
    });

    $('.js-hide-user').off('click').on('click', function(){
        let $this = $(this),
            userId = $this.attr('data-user'),
            request = BX.ajax.runComponentAction('citto:experiment', 'hideuser', {
                mode: 'ajax',
                data: {
                    action: 'hideuser',
                    user: userId
                }
            });

        request.then(function (ret) {
            alert(ret.data);
            $this.closest('tr').remove();
        }, function (ret) {
            alert(ret.errors[0].message);
        });
    });

    $('.js-show-user').off('click').on('click', function(){
        let $this = $(this),
            userId = $this.attr('data-user'),
            request = BX.ajax.runComponentAction('citto:experiment', 'showuser', {
                mode: 'ajax',
                data: {
                    action: 'showuser',
                    user: userId
                }
            });

        request.then(function (ret) {
            alert(ret.data);
            $this.closest('tr').remove();
        }, function (ret) {
            alert(ret.errors[0].message);
        });
    });
})