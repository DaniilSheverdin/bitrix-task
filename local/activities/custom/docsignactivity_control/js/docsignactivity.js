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
			$('.docsign-form__status span').attr('class',"text-"+(type || "danger")).text(message || "Ошибка подключения");
        };
		
		
		function signInput(inputs,certSubject){
			if(inputs.length == 0){
				connectionStatus("Указанные документы подписаны",'success');
				return;
			}
			var input = inputs.pop();

			if(input.className == "signed"){
				signInput(inputs,certSubject);
				return;
			}

			input.className = "signing";
			SignCreate(certSubject, input.value, input.getAttribute('data-type') == "p7s", input.getAttribute('data-source')).then(function(data){
				$.post("/local/activities/custom/docsignactivity/ajax.php",{
					id:input.getAttribute('data-id'),
					file:data,
				},function(resp){
					if(resp.code != "OK"){
						connectionStatus("Произошла ошибка, попробуйте позже."+resp.message);
						return;
					}
					input.className = "signed";
					signInput(inputs,certSubject);
				},'json').fail(function(){

				});
			},function(error){
				connectionStatus("Произошла ошибка, попробуйте позже."+error.message);
			});
		}

		function showCerts(){
			window.FillCertList_NPAPI().then(
				function(certList) {
					var docsogn_certlist = $('.docsign-cryptoplugin__certs select');
					docsogn_certlist.empty();
					certList.forEach(function(cert) {
						$('<option>').val(cert.value).text(cert.text).appendTo(docsogn_certlist);
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
					if(!certSubject){
						connectionStatus("Выберите сертификат");
						return;
					}
					var inputs = $('.docsign-files input:not(.signed)').toArray();
					if(inputs.length == 0){
						connectionStatus("Нет неподписанных файлов");
						return;
					}
					
					signInput(inputs,certSubject);
				});
			}, function(err){
				connectionStatus("Произошла ошибка, попробуйте позже."+err.message);
			});

			setInterval(function() {
				var docsign_files_input = $('.docsign-files input');
				if(docsign_files_input.length != 0 && docsign_files_input.filter(':not(.signed)').length == 0){
					$('button[name="docsign-submit"]')
						.show()
						.off('click')
						.on('click', function() {
							$(this).addClass('ui-btn ui-btn-success ui-btn-wait');
						});
				}
			}, 500);
		}
        try{
			init();
        }catch(e){
            connectionStatus("Ошибка инициализации. Проверьте плагин: "+e.message);
        }
    }, 1000);
};