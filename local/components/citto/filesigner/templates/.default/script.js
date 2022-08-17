<<<<<<< HEAD
var filesignerInit = function(){
    var STATE_INIT      = "st1";
    var STATE_CERTLIST  = "st2";
    var STATE_SIGN      = "st3";
    var STATE_FINISH    = "st4";
    var STATE_PLUG_NO   = "err1";
    var STATE_CERTS_NO  = "err2";
    var STATE_CONN_NO   = "err3";
    var STATE_BRO_NO    = "err4";
    var pp              = $('#docsign-pp');
    var pp_b            = pp.find('.docsign-pp__form__bsign');
    var pp_f            = pp.find('.docsign-pp__signfile');
    var certs__select   = pp.find('.docsign-cryptoplugin__certs__select');
    var countSign = 0;

    var setState = function(state, message){
        pp.attr('class','docsign-pp').addClass(state);
        pp.find('.docsign-pp__form__errmess').text(message || "");
    }

    pp_f.empty();
    setState(STATE_INIT);
    if(!window.Promise){
        setState(STATE_BRO_NO);
        return;
    }

    if(!pp_b.data('initialized')){
        pp_b.click(function(){
            var certSubjectName = certs__select.val();
            var certInfo = certs__select.find('option:selected').data('certinfo');
            var sign = function(){
                return new Promise(function(resolve, reject){
                    var files_wos = filesigner_files.filter(function(element){ return !element.signed; })
                    if(filesigner_double_sign) {
                        var files_wos = filesigner_files.filter(function(element, index){ return (index == countSign); })
                        countSign++;
                    }
                    if(!files_wos.length){
                        resolve();
                        return;
                    }

                    pp_f.text(files_wos[0].name);
                    SignCreate(certInfo.oCertificate, $(files_wos[0].content).val(), false, null)
                        .then(function(file_sign){
                            var formData = new FormData();
                            formData.append('sessid',       BX.message('bitrix_sessid'));
                            formData.append('pos',          filesigner_pos);
                            formData.append('double_sign',  filesigner_double_sign);
                            formData.append('check_sign',   filesigner_check_sign);
                            formData.append('clearf',       JSON.stringify(filesigner_clearf));
                            formData.append('fileid',       files_wos[0].id);
                            formData.append("sign",         new Blob([file_sign], {type: 'text/plain'}), files_wos[0].name+".sig");

                            BX.ajax.runComponentAction('citto:filesigner',
                                'signSave',
                                {
                                    mode: 'class',
                                    data: formData,
                                }
                            ).then(function(response) {
                                    if(response.status !== 'success') {
                                        console.warn(response);
                                        reject(response.errors.reduce(function(acc, val){ return (acc?(acc+"."):"")+val.message}, ""));
                                        return;
                                    }

                                    if(filesigner_double_sign) {
                                        files_wos[0].signed = false;
                                        sign().then(resolve, reject)
                                    }
                                    else {
                                        files_wos[0].signed = true;
                                        sign().then(resolve, reject)
                                    }

                                }, function(error){
                                    console.warn(error);
                                    reject("Не удалось подписать файл: "+files_wos[0].name+". "+error.errors.reduce(function(acc, val){ return (acc?(acc+"."):"")+val.message}, ""));
                                }
                            );
                        }, function(error){
                            reject(error.message);
                        });
                });
            };
            if(!pp.hasClass(STATE_CERTLIST)) return;

            if(!certSubjectName){
                setState(STATE_CERTS_NO);
                return;
            }

            setState(STATE_SIGN);
            sign().then(function(){
                setState(STATE_FINISH);
            }, function(message){
                setState(STATE_CONN_NO, message);
            });
        }).data('initialized' ,true);
    }

    ccadesPluginInit().then(function(){
        window.FillCertList_NPAPI().then(function(certList) {
                var certs__option = [];

                if(!certList.length){
                    setState(STATE_CERTS_NO);
                    return;
                }

                certList.forEach(function(cert) {
                    certs__option.push($('<option>').val(cert.value).data('certinfo', cert).text(cert.text).prop('selected', !certs__option.length));
                });

                certs__select.empty().append(certs__option).trigger('change');

                setState(STATE_CERTLIST);
            }, function(error) {
                setState(STATE_CERTS_NO, error.message);
            }
        );
    }, function(message){
        console.warn(message);
        setState(STATE_PLUG_NO, message);
    });
};
var filesignerHide = function(){
    $('#docsign-pp')
        .attr('class','docsign-pp')
        .find('.docsign-cryptoplugin__certs__select').empty();

    $(document).trigger('filesigner_hiden');
};
var filesignerSigned  = function(){
    $('#docsign-pp')
        .attr('class','docsign-pp')
        .find('.docsign-cryptoplugin__certs__select').empty();

    $(document).trigger('filesigner_signed');
=======
var filesignerInit = function(){
    var STATE_INIT      = "st1";
    var STATE_CERTLIST  = "st2";
    var STATE_SIGN      = "st3";
    var STATE_FINISH    = "st4";
    var STATE_PLUG_NO   = "err1";
    var STATE_CERTS_NO  = "err2";
    var STATE_CONN_NO   = "err3";
    var STATE_BRO_NO    = "err4";
    var pp              = $('#docsign-pp');
    var pp_b            = pp.find('.docsign-pp__form__bsign');
    var pp_f            = pp.find('.docsign-pp__signfile');
    var certs__select   = pp.find('.docsign-cryptoplugin__certs__select');
    var countSign = 0;

    var setState = function(state, message){
        pp.attr('class','docsign-pp').addClass(state);
        pp.find('.docsign-pp__form__errmess').text(message || "");
    }

    pp_f.empty();
    setState(STATE_INIT);
    if(!window.Promise){
        setState(STATE_BRO_NO);
        return;
    }

    if(!pp_b.data('initialized')){
        pp_b.click(function(){
            var certSubjectName = certs__select.val();
            var certInfo = certs__select.find('option:selected').data('certinfo');
            var sign = function(){
                return new Promise(function(resolve, reject){
                    var files_wos = filesigner_files.filter(function(element){ return !element.signed; })
                    if(filesigner_double_sign) {
                        var files_wos = filesigner_files.filter(function(element, index){ return (index == countSign); })
                        countSign++;
                    }
                    if(!files_wos.length){
                        resolve();
                        return;
                    }

                    pp_f.text(files_wos[0].name);
                    SignCreate(certInfo.oCertificate, $(files_wos[0].content).val(), false, null)
                        .then(function(file_sign){
                            var formData = new FormData();
                            formData.append('sessid',       BX.message('bitrix_sessid'));
                            formData.append('pos',          filesigner_pos);
                            formData.append('double_sign',  filesigner_double_sign);
                            formData.append('check_sign',   filesigner_check_sign);
                            formData.append('clearf',       JSON.stringify(filesigner_clearf));
                            formData.append('fileid',       files_wos[0].id);
                            formData.append("sign",         new Blob([file_sign], {type: 'text/plain'}), files_wos[0].name+".sig");

                            BX.ajax.runComponentAction('citto:filesigner',
                                'signSave',
                                {
                                    mode: 'class',
                                    data: formData,
                                }
                            ).then(function(response) {
                                    if(response.status !== 'success') {
                                        console.warn(response);
                                        reject(response.errors.reduce(function(acc, val){ return (acc?(acc+"."):"")+val.message}, ""));
                                        return;
                                    }

                                    if(filesigner_double_sign) {
                                        files_wos[0].signed = false;
                                        sign().then(resolve, reject)
                                    }
                                    else {
                                        files_wos[0].signed = true;
                                        sign().then(resolve, reject)
                                    }

                                }, function(error){
                                    console.warn(error);
                                    reject("Не удалось подписать файл: "+files_wos[0].name+". "+error.errors.reduce(function(acc, val){ return (acc?(acc+"."):"")+val.message}, ""));
                                }
                            );
                        }, function(error){
                            reject(error.message);
                        });
                });
            };
            if(!pp.hasClass(STATE_CERTLIST)) return;

            if(!certSubjectName){
                setState(STATE_CERTS_NO);
                return;
            }

            setState(STATE_SIGN);
            sign().then(function(){
                setState(STATE_FINISH);
            }, function(message){
                setState(STATE_CONN_NO, message);
            });
        }).data('initialized' ,true);
    }

    ccadesPluginInit().then(function(){
        window.FillCertList_NPAPI().then(function(certList) {
                var certs__option = [];

                if(!certList.length){
                    setState(STATE_CERTS_NO);
                    return;
                }

                certList.forEach(function(cert) {
                    certs__option.push($('<option>').val(cert.value).data('certinfo', cert).text(cert.text).prop('selected', !certs__option.length));
                });

                certs__select.empty().append(certs__option).trigger('change');

                setState(STATE_CERTLIST);
            }, function(error) {
                setState(STATE_CERTS_NO, error.message);
            }
        );
    }, function(message){
        console.warn(message);
        setState(STATE_PLUG_NO, message);
    });
};
var filesignerHide = function(){
    $('#docsign-pp')
        .attr('class','docsign-pp')
        .find('.docsign-cryptoplugin__certs__select').empty();

    $(document).trigger('filesigner_hiden');
};
var filesignerSigned  = function(){
    $('#docsign-pp')
        .attr('class','docsign-pp')
        .find('.docsign-cryptoplugin__certs__select').empty();

    $(document).trigger('filesigner_signed');
>>>>>>> e0a0eba79 (init)
};