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
        if (location.protocol !== 'https:'){
            alert("Требуется защищенное соединение");
            return;
        }

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

						if(cert.algorithm.indexOf('ГОСТ Р 34') != -1 && (+new Date()) < (+new Date(datetocert)))
						    $('<option>').val(cert.value).text(cert.text).appendTo(docsogn_certlist).data('certinfo',cert);
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