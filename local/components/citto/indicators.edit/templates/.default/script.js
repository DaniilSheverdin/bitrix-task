$(window).on('load', function() {
    $('.charts_open').on('click', function() {
        $('#chartTableRow' + $(this).data('id')).toggle();
        $('#chartTableRow' + $(this).data('id') + ' .table-section__chart-container').toggle();
    });

    $('.js-form-department-change select').on('change', function(){
        $(this).closest('form').submit();
    });

    $('.js-state-value').on('keypress', function(event) {
        event = event || window.event;
        if (event.charCode && event.charCode != 0 && event.charCode != 46 && (event.charCode < 48 || event.charCode > 57) )
            return false;
    });

    $('.js-state-value').on('keyup paste', function(event) {
        var $this = $(this),
            inverted = $this.attr('data-inverted') === 'true' || false;
        setTimeout(function() {
            let state = prepareIndicatorData($this.closest('tr').find('.js-state-value'));
            if (isNaN(state)) {
                state = 0;
            }

            let target = prepareIndicatorData($this.closest('tr').find('.js-target-value')),
                target_min = prepareIndicatorData($this.closest('tr').find('.js-target-value-min')),
                target_monthly_tr = $this.closest('tr').find('.js-monthly-target-value'),
                target_monthly = target_monthly_tr.text() === '-' ? 0 : prepareIndicatorData(target_monthly_tr),
                currentMonth = new Date().getMonth() + 1,
                percent_exec = 0,
                interval = !isNaN(target_min);

            $this.closest('tr').removeClass('table__indicator--normal table__indicator--failed table__indicator--success');

            if (isNaN(target)) {
                target = 0;
                percent_exec = 0;
            } else if (interval) {
                if (state >= target_min && state <= target) {
                    percent_exec = 100;
                } else if (state <= target_min) {
                    percent_exec = (state / target_min) * 100;
                } else {
                    percent_exec = target_monthly > 0
                      ? (target_monthly * currentMonth / state) * 100
                      : (target / state) * 100;
                }
            } else if (inverted) {
                percent_exec = target_monthly > 0
                  ? (target_monthly * currentMonth / state) * 100
                  : (target / state) * 100;
            } else {
                percent_exec = target_monthly > 0
                  ? (state / (target_monthly * currentMonth)) * 100
                  : (state / target) * 100;
            }

            var className = 'success';
            if (parseInt(percent_exec) < 30) {
                className = 'failed';
            } else if (parseInt(percent_exec) > 30 && parseInt(percent_exec) < 90) {
                className = 'normal';
            }

            $this.closest('tr').addClass('table__indicator--' + className);
            $this.closest('tr').find('.js-percent-exec_view').html(parseInt(percent_exec) + '%');
            $this.closest('tr').find('.js-percent-exec-value').val(parseInt(percent_exec));
        }, 0);
    });

    function prepareIndicatorData(el) {
        let value = el.val();
        value = value.replace(/\s/g, '');
        value = value.replace(',', '.');
        value = parseFloat(value);

        return value;
    }

    $('.js-state-value').each(function(){
        $(this).trigger('keyup');
    });
});
