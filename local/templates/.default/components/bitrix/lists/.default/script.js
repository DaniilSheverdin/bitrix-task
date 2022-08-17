$(
	function () {
		var input_name = $('#tab_el_edit_table input[name="NAME"]');
		if (input_name.length && !input_name.val()) {
			input_name.val($('#pagetitle').text() || "Без названия").closest('tr').hide();
		}
	}
);