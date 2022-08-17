(function ($, d) {
    var caller = {
        bOut: null,
        form_id: '#form-call-gubernator',
        init: function () {
            this.bOut = $('.form-call-gubernator');
            this.formsender();
            this.reinit();
            this.delete();
            this.inwork();
            this.filter();
        },
        reinit: function () {
            $("#time-call-text").inputmask({"mask": "99:99"});
        },
        formsender: function () {
            var _this = this;
            $(d).on('submit', this.form_id, function (e) {
                e.preventDefault();
                e.stopPropagation();
                loader.start(_this.bOut);

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'post',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function (json) {
                        loader.end(_this.bOut);
                        var parent = $(".form-call-gubernator");
                        parent.html(json.content);
                        $("body, html").animate({
                            scrollTop: (parent.offset().top - 100)
                        }, 1000);
                        setTimeout(function () {
                            parent.find('.alert').remove();
                        }, 5000);
                        _this.reinit();
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        loader.end(_this.bOut);
                    }
                });
            });
        },
        delete: function () {
            var _this = this;
            $(d).on('click', '[data-del-id]', function () {
                var $id = parseInt($(this).attr('data-del-id'));
                var _curr = $(this);
                loader.start(_this.bOut.find('._list-calls'));
                $.post($(this.form_id).attr('action'), {
                    gub: {
                        action: 'delete',
                        confirm: '1',
                        ID: $id
                    }
                }, function (json) {
                    loader.end(_this.bOut.find('._list-calls'));
                    _curr.parents('li.list-group-item').remove();
                    $(".form-call-gubernator").html(json.content);
                });
            });
        },
        filter: function() {
            var _this = this;
            $(d).on('click', '[data-list-action]', function () {
                var act = $(this).attr('data-list-action');
                loader.start(_this.bOut.find('._list-calls'));

                $.post($(this.form_id).attr('action'), {
                    gub: {
                        action: act,
                        confirm: '1'
                    }
                }, function (json) {
                    loader.end(_this.bOut.find('._list-calls'));
                    $(".form-call-gubernator").html(json.content);
                });
            });
        },
        inwork: function() {
            $(d).on('click', '[name="viewed_act[]"]', function () {
                var $params = { gub: {} };
                if($(this).is(":checked")) {
                    $params.gub.inwork = '2';
                } else {
                    $params.gub.inwork = '1';
                }

                $params.gub.action = 'inwork';
                $params.gub.confirm = '1';
                $params.gub.ID = $(this).attr('value');

                $.post($(this.form_id).attr('action'),
                    $params ,
                    function (json) {}
                );

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
        caller.init();
    });
})(jQuery, document);