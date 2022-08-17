function init() {
    let sAction = document.querySelector('#set-type .main-dropdown').getAttribute('data-value');

    let arTR = document.querySelectorAll('#grid_instruction_table .main-grid-row');
    let arElementsID = [];
    arTR.forEach(function (item) {
        let input = item.querySelector('input[type="checkbox"]');
        if (input.checked === true) {
            let id = item.getAttribute('data-id');
            arElementsID.push(id);
        }
    });

    if (sAction == 'export') {
        let request = BX.ajax.runComponentAction('citto:register_heads', 'getExport', {
            mode: 'ajax',
            data: {
                'arElementsID': arElementsID
            }
        });
        request.then(function (data) {
            var $a = $("<a>");
            $a.attr("href", data.data);
            $("body").append($a);
            $a.attr("download", "Реестр руководителей.xls");
            $a[0].click();
            $a.remove();
        });
    } else if (sAction == 'delete') {
        let request = BX.ajax.runComponentAction('citto:register_heads', 'delete', {
            mode: 'ajax',
            data: {
                'arElementsID': arElementsID
            }
        });
        request.then(function (data) {
            if (data.data.result === true) {
                arElementsID.forEach(function (iElementID) {
                    $(`[data-id="${iElementID}"]`).detach();
                })
            }
        });
    }
}

$('body').ready(function () {
    $('body').on('click', '#export_all', function () {
        let request = BX.ajax.runComponentAction('citto:register_heads', 'getExport', {
            mode: 'ajax',
            data: {}
        });
        request.then(function (data) {
            var $a = $("<a>");
            $a.attr("href", data.data);
            $("body").append($a);
            $a.attr("download", "Реестр руководителей.xls");
            $a[0].click();
            $a.remove();
        });
    });
});
