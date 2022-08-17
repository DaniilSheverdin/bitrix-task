$(window).on('load', function() {
	// $('#all_arrFilter_2635_3874519075').closest('label').hide();

	$('.charts_open').on('click',function(){
		$('#chartTableRow'+$(this).data('id')).toggle();
		$('#chartTableRow'+$(this).data('id')+' .table-section__chart-container').toggle();
	});

	// расчет процента при заполненном месячном показателе
	const tableRow = $("tr[id^='table-row-']");

		tableRow.each(function () {
			let percentExec = $(this).children('.js-percent-exec_view');

			const monthlyTarget = $(this).children('.js-monthly-target').text(),
				fact = $(this).children('.js-fact').text(),
				monthlyTargetEmptyVal = '-',
				currentMonth = new Date().getMonth() + 1;

			return monthlyTarget !== monthlyTargetEmptyVal
				? percentExec.text(`${Math.round(fact / (monthlyTarget * currentMonth) * 100)}%`)
				: percentExec.text();
		});
});
