var CADESCOM_CADES_BES = 1;
var CAPICOM_CURRENT_USER_STORE = 2;
var CAPICOM_MY_STORE = 'My';
var CAPICOM_STORE_OPEN_MAXIMUM_ALLOWED = 2;
var CAPICOM_CERTIFICATE_FIND_SUBJECT_NAME = 1;
var CADESCOM_BASE64_TO_BINARY = 1;
var CADESCOM_CADES_X_LONG_TYPE_1 = 1;
var CADES_DEFAULT = 0
var docsignInit = function(){
    setTimeout(function(){
        var connectionStatus = function(message,type){
			$('.docsign-form__status span').attr('class','text-'+(type || 'danger')).text(message || 'Ошибка подключения');
        };

		function signInput(certSubject,dataInBase64){}

		function showCerts(){
			window.FillCertList_NPAPI().then(
				function(certList) {
					var docsogn_certlist = $('.docsign-cryptoplugin__certs select');
					docsogn_certlist.empty();
					certList.forEach(function(cert) {
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
				
				
			},function(err){
				if(!$('.js-signed-true-button').hasClass('ui-btn-disabled')){
									$('.js-signed-true-button').addClass('ui-btn-disabled');
									$('.js-signed-true-button').prop('disabled',true);
								}
				connectionStatus('Произошла ошибка, попробуйте позже.'+err.message);
			});
		}
        try{
			init();
        }catch(e){
        			if(!$('.js-signed-true-button').hasClass('ui-btn-disabled')){
									$('.js-signed-true-button').addClass('ui-btn-disabled');
									$('.js-signed-true-button').prop('disabled',true);
								}
            connectionStatus('Ошибка инициализации. Проверьте плагин: '+e.message);
        }
    },1000);
};