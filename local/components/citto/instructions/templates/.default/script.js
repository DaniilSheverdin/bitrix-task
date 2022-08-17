function getExport() {
    let sAction = document.querySelector('#set-type .main-dropdown').getAttribute('data-value');

    if (sAction == 'export') {
        let arTR = document.querySelectorAll('#grid_instruction_table .main-grid-row');
        let arElementsID = [];
        arTR.forEach(function(item) {
            let input = item.querySelector('input[type="checkbox"]');
            if (input.checked === true) {
                let id = item.getAttribute('data-id');
                arElementsID.push(id);
            }
        });

        let request = BX.ajax.runComponentAction('citto:instructions', 'getExport', {
            mode: 'ajax',
            data: {
                'arElementsID' : arElementsID
            }
        });
        request.then(function (data) {
            var $a = $("<a>");
            $a.attr("href", data.data);
            $("body").append($a);
            $a.attr("download", "Должностная инструкция.xls");
            $a[0].click();
            $a.remove();
        });
    }
}
