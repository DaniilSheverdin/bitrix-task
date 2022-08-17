(function ($, JSON) {
    $(document).ready(
        function () {
            let main = $('#lists_element_add_form');
            let defaulted = '', intervalGUser = null, intervalUUser = null;
            let dir = baseDirBP;

            main.on(
                'change',
                "[data-id='poluchenie_lichno']",
                function () {
                    if ($(this).find('option:selected').attr('data-id') == 'd87b8a65579274b521fbaa0f98bdc373') {
                        var values = {};
                        main.find('form').find('input').each(
                            function () {
                                values[$(this).attr('name')] = $(this).attr('value');
                            }
                        );

                        //defaulted = JSON.stringify(values);
                        main.find('[data-id="ep_doverennoe_litso"]').next('.userselector').find('a').on(
                            'blur.user',
                            function () {
                                main.find('[data-id="ep_seriya_i_nomer_pasporta_dov_litsa"]').val('');
                                main.find('[data-id="ep_kem_vydan_pasport_dov_litsa"]').val('');
                                main.find('[data-id="ep_kogda_vydan_pasport_dov_litsa"]').val('');
                                main.find('[data-id="ep_adres_fakticheskogo_prozhivaniya_doverennogo_li"]').val('');

                                let inp = $(this).closest('.userselector').prev('[data-id="ep_doverennoe_litso"]');
                                intervalUUser = setInterval(
                                    function () {
                                        if (inp.data('prevval') != inp.val()) {
                                            fetch(
                                                dir + '/dover_lico.php',
                                                {
                                                    method: 'post',
                                                    headers: {
                                                        "Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
                                                    },
                                                    body: 'userid=' + inp.val()
                                                }
                                            ).then(
                                                function (resp) {
                                                    return resp.json();
                                                }
                                            ).then(
                                                function (resp) {
                                                    if(resp.status == 'OK' && resp.data.fields) {
                                                        let fieldsObj = resp.data.fields;
                                                        main.find('[data-id="ep_seriya_i_nomer_pasporta_dov_litsa"]').val(fieldsObj.passport.Series + ' ' + fieldsObj.passport.Number);
                                                        main.find('[data-id="ep_kem_vydan_pasport_dov_litsa"]').val(fieldsObj.passport.IssuedBy);
                                                        main.find('[data-id="ep_kogda_vydan_pasport_dov_litsa"]').val(fieldsObj.passport.DateOfIssue);
                                                        main.find('[data-id="ep_adres_fakticheskogo_prozhivaniya_doverennogo_li"]').val(fieldsObj.placelive.scalar);
                                                    }
                                                }
                                            ).catch(
                                                function (error) {
                                                    console.error(error);
                                                }
                                            );
                                        }
                                        inp.data('prevval', inp.val());
                                    },
                                    1000
                                );
                            }
                        );
                    } else if (intervalUUser != null) {
                        clearInterval(intervalUUser);
                    }
                }
            );

            main.on(
                'change',
                "[data-id='zayavitel_yavlyaetsya_rukovoditelem']",
                function () {
                    if ($(this).find('option:selected').attr('data-id') == '86efece2e108dd43fa4981ba051bad78') {
                        main.find('[data-id="rukovoditel"]').next('.userselector').find('a').on(
                            'blur.ruc',
                            function () {
                                main.find('[data-id="ep_naimenovanie_organizatsii"]').val('');
                                main.find('[data-id="ep_glava_organizatsii"]').val('');
                                main.find('[data-id="ep_dolzhnost_glavy_organizatsii"]').val('');
                                main.find('[data-id="ep_osnovanie_deystviya_organizatsii"]').val('');

                                let inp = $(this).closest('.userselector').prev('[data-id="rukovoditel"]');
                                intervalGUser = setInterval(
                                    function () {
                                        if (inp.data('prevval') != inp.val()) {
                                            fetch(
                                                dir + '/rukovoditel.php',
                                                {
                                                    method: 'post',
                                                    headers: {
                                                        "Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
                                                    },
                                                    body: 'userid=' + inp.val()
                                                }
                                            ).then(
                                                function (resp) {
                                                    return resp.json();
                                                }
                                            ).then(
                                                function (resp) {
                                                    if(resp.status == 'OK' && !$.isEmptyObject(resp.data)) {
                                                        let fieldsObj = resp.data;

                                                        main.find('[data-id="ep_naimenovanie_organizatsii"]').val(fieldsObj.deraptmentname);
                                                        main.find('[data-id="ep_glava_organizatsii"]').val(fieldsObj.glava);
                                                        main.find('[data-id="ep_dolzhnost_glavy_organizatsii"]').val(fieldsObj.dolzhnost_glavy);
                                                        main.find('[data-id="ep_osnovanie_deystviya_organizatsii"]').val(fieldsObj.osnovanie);
                                                        main.find('[data-id="ep_glava_organizatsii_short"]').val(fieldsObj.glava_short);
                                                    }
                                                }
                                            ).catch(
                                                function (error) {
                                                    console.error(error);
                                                }
                                            );
                                        }
                                        inp.data('prevval', inp.val());
                                    },
                                    1000
                                );
                            }
                        );
                    } else {
                        clearInterval(intervalGUser);
                    }
                }
            );
        }
    );
})(jQuery, JSON);
