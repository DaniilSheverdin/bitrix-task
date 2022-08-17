(function ($, JSON) {
    $(document).ready(
        function () {
            let main = $('#lists_element_add_form');
            let defaulted = '', intervalGUser = null, intervalUUser = null;

            main.on(
                'change',
                "[data-id='poluchenie_lichno']",
                function () {
                    if ($(this).find('option:selected').attr('data-id') == '3b035cb6f1ce6a7a887ba0a1df87dbf9') {
                        var values = {};
                        main.find('form').find('input').each(function () {
                            values[$(this).attr('name')] = $(this).attr('value');
                        });

                        defaulted = JSON.stringify(values);

                        main.find('[data-id="rukovoditel"]').next('.userselector').find('a').on(
                            'blur.user',
                            function () {
                                let inp = $(this).closest('.userselector').prev('[data-id="rukovoditel"]');
                                intervalGUser = setInterval(
                                    function () {
                                        console.log(inp.data('prevval') == inp.val());
                                        inp.data('prevval', inp.val());
                                        console.log(inp.val());
                                    },
                                    1000
                                );
                            }
                        );
                    } else if (intervalGUser != null) {
                        clearInterval(intervalGUser);
                    }
                }
            );
        }
    );
})(jQuery, JSON);
