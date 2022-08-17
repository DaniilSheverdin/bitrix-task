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
        // $('.dep-' + id).removeClass('d-none hidden');
        $('.dep-' + id)
            .find('[type=checkbox]:not(:disabled)')
            .attr('checked', isChecked)
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
        $('.etk .table [type=checkbox]:checked').each(function(){
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
                    formData = $(this).serialize();

                $.post('?mode=step1', formData, function(data) {
                    let ret = jQuery.parseJSON(data);
                    if (!ret.result) {
                        alert(ret.message);
                    } else {
                        alert(ret.message);
                        modal.modal('hide');
                        window.location.reload();
                    }
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
})