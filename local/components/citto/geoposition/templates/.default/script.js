$(window).on('load', function() {
	if($('.js-adress-form').val()=='active'){
		GetCurrentPosition();
	}
	$('body').on('click', '.js-geoposition-search', function() {
		GetCurrentPosition();
		return false;
	});
	function GetCurrentPosition(){
		if (navigator.geolocation) {
			var location_timeout = setTimeout("geolocFail()", 10000);

			navigator.geolocation.getCurrentPosition(AddPosition, function (err)
{
  var mess;
  switch (err.code) {
    case err.PERMISSION_DENIED:
      mess = "Посетитель не дал доступ к сведениям "
        + "о местоположении";
      break;
    case err.POSITION_UNAVAILABLE:
      mess = "Невозможно получить сведения о местоположении";
      break;
    case err.TIMEOUT:
      mess = "Истёк таймаут, в течение которого должны быть " +
        "получены данные о местоположении";
      break;
    default:
      mess = "Возникла ошибка '" + err.message + "' с кодом " + err.code;
  	}
  	alert(mess);
  },
			{
			    maximumAge:Infinity,
			    timeout:Infinity,
			    enableHighAccuracy:false
			});
		} else {
			geolocFail();
		}
	}
	function geolocFail(){
    alert("Ваш браузер не поддерживает гео-локацию");
}
	function AddPosition(position) {
		$('.latitude-input').val(position.coords.latitude);
		$('.longitude-input').val(position.coords.longitude);
		request = BX.ajax.runComponentAction('citto:geoposition', 'GetAdress', {
                    mode: 'class',
                    data: {
                        action: 'GetAdress',
                        lon: position.coords.longitude,
                        lat: position.coords.latitude,
                    }
                });
       	request.then(function (data) {
       		if(data.status=='success'){
       			$('.adress-input').val(data.data.Adress.suggestions[0].value);
       		}else{
       			alert('Не удалось определить местоположение!');
       		}
        		
        });
		
	}
});