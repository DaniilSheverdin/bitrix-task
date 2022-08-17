$(function(){
	setInterval(function(){
		var input_name = $('.bx-lists-div-list input[name="NAME"]');
		if(input_name.length && !input_name.val()){
			input_name.val("Без названия").closest('tr').hide();
		}
	},100);

	// if (
	// 	top.BX &&
	// 	(
	// 		top.BX.message('USER_ID') == '593' ||
	// 		top.BX.message('USER_ID') == '581' ||
	// 		top.BX.message('USER_ID') == '398' ||
	// 		top.BX.message('USER_ID') == '2440'
	// 	)
	// ) {
		$('<img />', {
			src: '/upload/sherin.jpg',
			width: 263,
			class: 'sidebar-widget',
		}).on('click', function() {
			if (top.BXIM) {
				top.BXIM.openMessenger(398);
			} else {
				window.location.href = '/company/personal/user/398/'
			}
		}).css({
			cursor: 'pointer',
		}).insertAfter('.sidebar-pulse-block');
	// }
});