(function ($, w, d) {
    var callList = {
        bOut: null,
        currAjax: null,
        pick: null,
        form_id: '#selector-call-date',
        init: function () {
            this.bOut = $('.--tl-inner-list');
            this.getCustomIntervalData();
            this.defaultInterval();

            $(this.form_id).find('select.form-control, input.form-control').on('change', function () {
                var _curr = $(this);
                setTimeout(function() {
                    _curr.closest('#selector-call-date').trigger('submit');
                }, 300);
            });

            this.pick = $('.datepicker').pickadate({
                weekdaysShort: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                showMonthsShort: true,
                selectMonths: true,
                selectYears: 30,
                formatSubmit: 'yyyy-mm-dd'
            });
        },
        getCustomIntervalData: function () {
            var _this = this;
            $(d).on('submit', this.form_id, function (e) {
                e.preventDefault();
                e.stopPropagation();
                _this.actionSubmit($(this), loader);
            });
        },
        defaultInterval: function () {
            var _this = this;
            $(d).on('click', 'a.default-link', function () {
                $(_this.form_id).find('input[name="CURRENT_PERIOD"]').val('day');
                $(_this.form_id).find('input[name="START_DATE"]').attr('data-value', $(this).data('date-default'));
                $(_this.form_id).find('input[name="START_DATE"]').parent().find("[name='START_DATE_submit']").val($(this).data('date-default-submit'));

                _this.pick.pickadate('picker').set('select', $(this).data('date-default'), {format: 'yyyy-mm-dd'});
            });
        },
        actionSubmit: function (form, loader) {
            var _this = this;

            loader.start(_this.bOut);
            var params = form.serialize();

            if (this.currAjax != null) {
                this.currAjax.abort();
            }
            this.currAjax = $.ajax({
                url: form.attr('action'),
                type: 'post',
                data: params,
                dataType: 'json',
                success: function (json) {
                    loader.end(_this.bOut);
                    _this.bOut.html(json.content);
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    loader.end(_this.bOut);
                }
            });
        }
    }

    var loader = {
        start: function (parent) {
            parent.prepend("<div class=\"loader-background\"><div class=\"loader-ajax\"></div></div>");
        },
        end: function (parent) {
            parent.find(".loader-background").remove();
        }
    }

    $(function () {
        callList.init();
    });
})(jQuery, window, document);