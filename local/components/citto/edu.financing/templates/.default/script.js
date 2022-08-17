window.cadesplugin_skip_extension_install = true

var NEED_SIGN = false;
var CAN_SIGN = false;

SEND_TO_SIGN = {

    aInternal: false,
    set a(val) {
        this.aInternal = val;
        this.aListener(val);
    },
    get a() {
        return this.aInternal;
    },

    aListener: function(val) {},
    registerListener: function(listener) {
        this.aListener = listener;
    }
}

SEND_TO_SIGN.a = false


FILE_SIGNED = {

    aInternal: false,
    set a(val) {
        this.aInternal = val;
        this.aListener(val);
    },
    get a() {
        return this.aInternal;
    },

    aListener: function(val) {},
    registerListener: function(listener) {
        this.aListener = listener;
    }
}

FILE_SIGNED.a = false

var CADESCOM_CADES_BES = 1;
var CAPICOM_CURRENT_USER_STORE = 2;
var CAPICOM_MY_STORE = "My";
var CAPICOM_STORE_OPEN_MAXIMUM_ALLOWED = 2;
var CAPICOM_CERTIFICATE_FIND_SUBJECT_NAME = 1;
var CADESCOM_BASE64_TO_BINARY = 1;
var CADESCOM_CADES_X_LONG_TYPE_1 = 1;
var CADES_DEFAULT = 0
var docsignInit = function(){
    setTimeout(function(){


        var connectionStatus = function(message,type){
            alert(message || "Ошибка подключения");
        };

        function showCerts(){
            window.FillCertList_NPAPI().then(
                function(certList) {
                    var docsogn_certlist = $('.docsign-cryptoplugin__certs select');
                    docsogn_certlist.empty();

                    certList.forEach(function(cert) {
                        let datetocert = cert.till.split(' ').map((v)=>{
                            return v.split('.').reverse().join('.');
                        }).join(' ');

                        if(cert.algorithm.indexOf('ГОСТ Р 34') != -1 && (+new Date()) < (+new Date(datetocert))) {
                            NEED_SIGN = true
                        }
                    });
                },
                function(error) {
                    console.log(error);
                    connectionStatus(error.message);
                }
            );
        }

        function init(){
            window.cadesplugin.then(function(){
                initialized = true;
                showCerts();
                $('.docsign-actions').show();
                $('.docsign-cryptoplugin__certs button').click(showCerts);

                $('#docsign__sign-files').click(function(){
                    var certSubject = $('.docsign-cryptoplugin__certs select').val();
                    let sCertInfo = ($('.docsign-cryptoplugin__certs select option:selected').data('certinfo'))
                    if(!certSubject){
                        connectionStatus("Выберите сертификат");
                        return;
                    }

                    sCertInfoString = `Издатель: ${sCertInfo.issuerName};;Субъект: ${sCertInfo.subjectName};;Провайдер: ${sCertInfo.provname};;Действителен: с ${sCertInfo.from} по ${sCertInfo.till}`;

                    let request = BX.ajax.runComponentAction('citto:holiday.list', 'excel', {
                        mode: 'ajax',
                        data: {
                            action: 'excel',
                            year: year,
                            sCertInfo: sCertInfoString,
                            introduction: false,
                            departmentID : departmentID,
                            departmentList : departmentList,
                            myWorkers : myWorkers,
                            recursive : recursive

                        }
                    });
                    request.then(function (data) {
                        let fileBase64 = data.data;
                        SignCreate(certSubject, fileBase64).then(function(data){
                            let request = BX.ajax.runComponentAction('citto:holiday.list', 'signature', {
                                mode: 'ajax',
                                data: {
                                    action: 'signature',
                                    sign: data,
                                    file: fileBase64,
                                    year: year
                                }
                            });
                            request.then(function (data) {
                                let $a = $("<a>");
                                $a.attr("href", data.data);
                                $("body").append($a);
                                $a.attr("download", 'Sign.zip');
                                $a[0].click();
                                $a.remove();
                                alert('Файл подписан');
                            });
                        });
                    });
                });

            },function(err){
                connectionStatus("Произошла ошибка, попробуйте позже."+(typeof err == "object"?err.message:err));
                console.log(err);
            });
            setInterval(function(){
                var docsign_files_input = $('.docsign-files input');
                if(docsign_files_input.length != 0 && docsign_files_input.filter(':not(.signed)').length == 0){
                    $('button[name="docsign-submit"]').show();
                }
            },500);
        }
        try{
            init();
        }catch(e){
            connectionStatus("Ошибка инициализации. Проверьте плагин: "+e.message);
        }
    },1000);
};



$(document).ready(function() {

    var popup = new BX.PopupWindow("popup-iframe2", null, {
        closeIcon: {right: "12px", top: "10px"},
        width: "100%",
        height: "100%"
    });

    $(window).on("message onmessage", function (e) {
        var data = e.originalEvent.data;
        // const date = new Date()
        // console.log(data)
        // console.log(date.getMinutes(), date.getSeconds(), date.getMilliseconds())

        if ((typeof data.data?.retval?.value) === 'string') {
            if (data.data.retval.value.indexOf('ГОСТ Р 34') >= 0) {
                SEND_TO_SIGN.a = true
            }
        }
        if (data === 'cadesplugin_loaded') {
            CAN_SIGN = true
        }
        if (data === 'filesigner_signed') {
            $('#popup-iframe2').hide();
            FILE_SIGNED.a = true
        }
        if (data === "filesigner_hiden") {
            $('#popup-iframe2').hide();
        }
    });






    if( $('.js-send-repeat').length ) {
        $('.js-send-repeat').attr('disabled', true)
    }

    $('body').on('change', '[name=PROGRAM]', function(){
        $('.event').addClass('d-none').attr('disabled', true);
        $('.event-' + $(this).val()).removeClass('d-none').attr('disabled', false);
    });
    $('[name=PROGRAM]').trigger('change');

    $('body').on('change', '[name=MUNICIPALITY]', function(){
        $('.organ').addClass('d-none').attr('disabled', true);
        $('.organ-' + $(this).val()).removeClass('d-none').attr('disabled', false);
    });
    $('[name=MUNICIPALITY]').trigger('change');


    //Отправка новой заявки на согласования
    $('body').on('click', '.js-send-new', function(){
        let $this = $(this),
            id = $this.attr('data-id');

        request = BX.ajax.runComponentAction(
            'citto:edu.financing',
            'sendToNew',
            {
                mode: 'ajax',
                json: {
                    id: id
                }
            }
        );
        request.then(function(ret) {
            window.location.reload();
        }, function (ret) {
            BX.UI.Dialogs.MessageBox.alert(ret.errors[1].message);
        });
    });



    // Отказ согласования
    $('body').on('click', '.js-send-reject', async function(){

        NEED_SIGN = false
        window.cadesplugin_skip_extension_install = true
        let returnUrl = ''

        if (CAN_SIGN) {
            if (confirm('Подписать отказ цифровой подпиисью?')) {
                window.cadesplugin_skip_extension_install = false
                NEED_SIGN = true
                docsignInit();
            }
        }

        let $this = $(this),
            id = $this.attr('data-id');

        const request = BX.ajax.runComponentAction(
            'citto:edu.financing',
            'sendToReject',
            {
                mode: 'ajax',
                json: { id }
            }
        );
        const response =  await request.then(function(ret) {
            return ret
        }, function (ret) {
            BX.UI.Dialogs.MessageBox.alert(ret.errors[1].message);
        });

        if (response.data.success === 'ok') {

            returnUrl = response.data.returnUrl

            if (NEED_SIGN) {

                const dataPDF = await makePDF(id, 'Отказ')
                const src = dataPDF.data.location

                SEND_TO_SIGN.registerListener(function(val) {

                    if (val) {

                        $('#popup-iframe2').css({'width': '100%', 'height': '100%'}).html('');
                        $('<iframe>', {
                            src,
                            id: 'popup-iframe',
                            frameborder: 0,
                            scrolling: 'no',
                            width: '100%',
                            height: '100%'
                        }).appendTo('#popup-iframe2');
                        $('#popup-iframe2').show();
                    }

                });

                FILE_SIGNED.registerListener(function(val) {
                    if (val) {
                        window.location.href = returnUrl
                    }
                });

            } else {
                window.location.href = returnUrl
            }
        }


    });

    $('.js-checkbox-repeat').on('change', function () {
        if ($(this).is(':checked')) {
            $('.js-send-repeat').attr('disabled', false)
        }
    })


    $('body').on('click', '.js-send-repeat', function(){

        const id = $(this).attr('data-id');
        let kurator = 'N'
        let tech = 'N'

        $('.js-checkbox-repeat').each(function () {
            if ($(this).is(':checked')) {
                if ($(this).attr('name') === 'kurator') kurator = 'Y'
                if ($(this).attr('name') === 'tech') tech = 'Y'
            }
        })

        console.log(kurator, tech)


        request = BX.ajax.runComponentAction(
          'citto:edu.financing',
          'sendToRepeat',
          {
              mode: 'ajax',
              json: { id, kurator, tech }
          }
        );
        request.then(function(ret) {
            console.log('sendToRepeat', ret)
            window.location.href = ret.data;
        }, function (ret) {
            BX.UI.Dialogs.MessageBox.alert(ret.errors[1].message);
        });
    });







    $('body').on('click', '.js-send-success', async function(){
        NEED_SIGN = false
        window.cadesplugin_skip_extension_install = true
        let returnUrl = ''

        if (CAN_SIGN) {
            if (confirm('Подписать согласование цифровой подпиисью?')) {
                window.cadesplugin_skip_extension_install = false
                NEED_SIGN = true
                docsignInit();
            }
        }

        let $this = $(this),
            id = $this.attr('data-id');

        const request = BX.ajax.runComponentAction(
            'citto:edu.financing',
            'sendToSuccess',
            {
                mode: 'ajax',
                json: { id }
            }
        );

        const response =  await request.then(function(ret) {
            return ret
        }, function (ret) {
            BX.UI.Dialogs.MessageBox.alert(ret.errors[1].message);
        });

        if (response.data.success === 'ok') {

            returnUrl = response.data.returnUrl

            if (NEED_SIGN) {

                const dataPDF = await makePDF(id, 'Согласовано')
                const src = dataPDF.data.location

                SEND_TO_SIGN.registerListener(function(val) {

                    if (val) {

                        $('#popup-iframe2').css({'width': '100%', 'height': '100%'}).html('');
                        $('<iframe>', {
                            src,
                            id: 'popup-iframe',
                            frameborder: 0,
                            scrolling: 'no',
                            width: '100%',
                            height: '100%'
                        }).appendTo('#popup-iframe2');
                        $('#popup-iframe2').show();
                    }

                });

                FILE_SIGNED.registerListener(function(val) {
                    if (val) {
                        window.location.href = returnUrl
                    }
                });

            } else {
                window.location.href = returnUrl
            }
        }
    });


    //TEST PDF

    async function makePDF(id, type) {

        const $htmlNode = $('.js-pdf-content')
        const $copyHtmlNode = $htmlNode.clone();
        $copyHtmlNode.find('button').remove();

        const head = `<!doctype html>
                        <html lang="ru">
                        <head>
                          <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                          <title>Document</title>

                          <style>
                          
                          .col-3, .col-9, .col-12 {
                                position: relative;
                                width: 100%;
                                padding-right: 15px;
                                padding-left: 15px;
                          }
                          
                            .col-9 {
                                -ms-flex: 0 0 75%;
                                flex: 0 0 75%;
                                max-width: 75%;
                                
                            }
                            
                            .col-3 {
                                -ms-flex: 0 0 25%;
                                flex: 0 0 25%;
                                max-width: 25%;
                                position: absolute;
                                top: 0;
                                right: 0;
                            }
                            .col-12 {
                                -ms-flex: 0 0 100%;
                                flex: 0 0 100%;
                                max-width: 100%;
                            }
                            
                            .box {
                                position: relative;
                                border-radius: 3px;
                                background: #ffffff;
                                border-top: 3px solid #d2d6de;
                                margin-bottom: 20px;
                                width: 100%;
                                box-shadow: 0 1px 1px rgb(0 0 0 / 10%);
                            }
                            
                            .box.box-primary {
                                border-top-color: #3c8dbc;
                            }
                            
                            .box-body {
                                border-top-left-radius: 0;
                                border-top-right-radius: 0;
                                border-bottom-right-radius: 3px;
                                border-bottom-left-radius: 3px;
                                padding: 10px;
                            }
                            
                            .box-title {
                                display: inline-block;
                                font-size: 18px;
                                margin: 0;
                                line-height: 1;
                            }

                            .box-header {
                                color: #444;
                                display: block;
                                padding: 10px;
                                position: relative;
                            }
                            
                            .box-header.with-border {
                                border-bottom: 1px solid #f4f4f4;
                            }

                          
                            .row {
                                
                                margin-right: -15px;
                                margin-left: -15px;
                            }
                          
                          
                            body { font-family: DejaVu Sans; font-size: 12px }
                            
                            .required {
                              color: red;
                              margin-right: 5px;
                            }
                            
                            .results {
                              border-collapse: separate;
                              border-spacing: 0 23px;
                            }
                            .results__row {
                              padding-bottom: 20px;
                            }
                            .results__row td {
                              padding: 15px;
                            }
                            .results__row td.colored {
                              border-radius: 4px;
                            }
                            .results__row td.colored.red {
                              background-color: #f1361a;
                              color: white;
                            }
                            .results__row td.colored.grey {
                              background-color: #868d95;
                              color: white;
                            }
                            .results__row td.colored.green {
                              background-color: #bbed21;
                              color: #535c69;
                            }
                            .results__checkbox {
                              width: 30px;
                            }
                            
                            .box-title span {
                              margin-left: 141px;
                            }
                            
                           </style>
                        </head>
                        <body>`

        const footer = `</body>
                        </html>`





        const content = $copyHtmlNode.html()


        const html = head + content + footer


        const request = BX.ajax.runComponentAction(
            'citto:edu.financing',
            'makePDF',
            {
                mode: 'ajax',
                json: {id, html, type}
            }
        );

        const response = await request.then(function (ret) {
            return ret
        }, function (ret) {
            BX.UI.Dialogs.MessageBox.alert(ret.errors[1].message);
        });

        return response.data

    }





});
