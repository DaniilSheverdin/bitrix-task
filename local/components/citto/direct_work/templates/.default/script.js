function getExport() {
    let sAction = document.querySelector('#set-type .main-dropdown').getAttribute('data-value');

    if (sAction == 'export') {
        let arTR = document.querySelectorAll('#grid_instruction_table .main-grid-row');
        let arElementsID = [];
        arTR.forEach(function (item) {
            let input = item.querySelector('input[type="checkbox"]');
            if (input.checked === true) {
                let id = item.getAttribute('data-id');
                arElementsID.push(id);
            }
        });

        console.log(arElementsID)

        let request = BX.ajax.runComponentAction('citto:vaccination_covid19', 'getExport', {
            mode: 'ajax',
            data: {
                'arElementsID': arElementsID
            }
        });
        request.then(function (data) {
            var $a = $("<a>");
            $a.attr("href", data.data);
            $("body").append($a);
            $a.attr("download", "Вакцинация Covid-19.xls");
            $a[0].click();
            $a.remove();
        });
    }
}

$('body').ready(function () {
    $('body').on('click', '#stat_departments', function () {
        let request = BX.ajax.runComponentAction('citto:vaccination_covid19', 'getStatDetartments', {
            mode: 'ajax',
            data: {}
        });
        request.then(function (data) {
            var $a = $("<a>");
            $a.attr("href", data.data);
            $("body").append($a);
            $a.attr("download", "Вакцинация Covid-19 (подразделения).xls");
            $a[0].click();
            $a.remove();
        });
    });

    $('body').on('click', '#stat_cit', function () {
        let request = BX.ajax.runComponentAction('citto:vaccination_covid19', 'getStatCit', {
            mode: 'ajax',
            data: {}
        });
        request.then(function (data) {
            var $a = $("<a>");
            $a.attr("href", data.data);
            $("body").append($a);
            $a.attr("download", "Вакцинация Covid-19 (ЦИТ).xls");
            $a[0].click();
            $a.remove();
        });
    });
});