$(document).ready(function() {
    var currentNewId = 0;
    $('body').on('click', '.js-kpi-row-add', function (e) {
        let row = $('.kpi-row-template').clone();

        row.data('id', 0);
        row.attr('class', 'kpi-row');
        row.find('input, textarea').each(function(){
            $(this)
                .attr('name', 'ADD[n' + currentNewId + '][' + $(this).data('id') + ']')
                .attr('required', $(this).hasClass('js-required'));
        });
        row.insertAfter($('.kpi-row').last());
        currentNewId++;
        e.preventDefault();
        return false;
    });

    $('body').on('click', '.js-kpi-row-remove', function (e) {
        let $this = $(this),
            row = $this.closest('tr'),
            id = parseInt(row.data('id')) || 0;
        if (confirm('Вы уверены, что хотите удалить строку?')) {
            if (id <= 0) {
                row.remove();
            } else {
                row.addClass('kpi-row-blured');
                row.find('input, textarea').attr('disabled', true);
                row.find('.js-kpi-row-restore').removeClass('d-none');
                $this.addClass('d-none');
                $('<input/>', {
                    type: 'hidden',
                    class: 'remove-row',
                    name: 'DELETE[]',
                    value: id
                }).appendTo($this.closest('form'));
            }
        }
        e.preventDefault();
        return false;
    });

    $('body').on('click', '.js-kpi-row-restore', function (e) {
        let $this = $(this),
            row = $this.closest('tr'),
            id = parseInt(row.data('id')) || 0;

        row.removeClass('kpi-row-blured');
        row.find('input, textarea').attr('disabled', false);
        row.find('.js-kpi-row-remove').removeClass('d-none');
        $this.addClass('d-none');
        $('input[value=' + id + '].remove-row').remove();

        e.preventDefault();
        return false;
    });


    $('body').on('keypress', '.js-kpi-current-value, .js-kpi-target-value', function (event) {
        event = event || window.event;
        if (event.charCode && event.charCode != 0 && event.charCode != 46 && (event.charCode < 48 || event.charCode > 57) )
            return false;
    });

    $('body').on('keyup paste', '.js-kpi-current-value, .js-kpi-target-value', function (event) {
        var $this = $(this),
            row = $this.closest('tr');
        setTimeout(function() {
            let state = prepareIndicatorData(row.find('.js-kpi-current-value')),
                target = prepareIndicatorData(row.find('.js-kpi-target-value')),
                percent = 0;

            if (!isNaN(state) && !isNaN(target)) {
                percent = (state / target) * 100;
            }

            row.find('.kpi-row-percent').html(percent.toFixed(2).replace('.', ',') + '%');
        }, 0);
    });
});

function prepareIndicatorData(el) {
    let value = el.val();
    value = value.replace(/\s/g, '');
    value = value.replace(',', '.');
    value = parseFloat(value);

    return value;
}
