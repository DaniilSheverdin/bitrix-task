/*let request = BX.ajax.runComponentAction('citto:instructions', 'getExport', {
     mode: 'class',
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
});*/

var getFormData = function ($form) {
    var unindexed_array = $form.serializeArray();
    var indexed_array = {};

    $.map(unindexed_array, function (n, i) {
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}

$(function () {
    $('#js--kmoppn').on('submit', function (event) {
        event.preventDefault();
        event.stopPropagation();

        let _this = $(this);
        _this.find('.alert').remove();
        _this.addClass('was-validated');

        let request = BX.ajax.runComponentAction('citto:kmoppn', 'setSubmitData', {
            mode: 'class',
            data: {
                post: getFormData(_this)
            }
        }).then(function (data) {
                if (data.status) {
                    if (data.data.data) {
                        _this.prepend("<div class=\"alert alert-success mb-3\">Данные критериев по вашему подразделению обновлены за текущий месяц</div>");
                        $("html, body").animate({ scrollTop: 0 }, "300");
                    }
                }
            }, function (data) {
                console.error(data.data.ajaxRejectData.data.data);
            }
        );

    });
});
