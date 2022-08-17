function tableToExcel(table, name) {
    var uri = 'data:application/vnd.ms-excel;base64,',
        template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta charset="utf-8" ></head><body><table>{table}</table></body></html>',
        base64 = function (s) { return window.btoa(unescape(encodeURIComponent(s))); },
        format = function (s, c) { return s.replace(/{(\w+)}/g, function (m, p) { return c[p]; }); };
    if (!table.nodeType)
        table = document.getElementById(table);
    var ctx = {
        worksheet: name || 'Worksheet',
        table: table.innerHTML
    };

    var uri = uri + base64(format(template, ctx));

    var link = document.createElement("a");
    link.download = name + '.xls';
    link.href = uri;

    document.body.appendChild(link);
    link.click();

    document.body.removeChild(link);
    delete link;
}
$(document).ready(function(){
    $('.select2').select2({
        width: '100%'
    });

    function hideDeps(id) {
        $('tr.dep-' + id).each(function(){
            $(this).removeClass('opened');
            $(this).addClass('d-none');
            $(this).find('.toggle-button').removeClass('minus-button').addClass('plus-button');
            hideDeps($(this).data('id'))
        });
    }

    $('.toggle-button').on('click', function(){
        let tr = $(this).closest('tr'),
            id = tr.data('id'),
            parent = tr.data('parent'),
            chain = tr.data('chain'),
            isOpen = tr.hasClass('opened');
        if (!isOpen) {
            tr.addClass('opened');
            $('tr.dep-' + id).removeClass('d-none');
            tr.find('.toggle-button').removeClass('plus-button').addClass('minus-button');
        } else {
            tr.removeClass('opened');
            tr.find('.toggle-button').removeClass('minus-button').addClass('plus-button');
            hideDeps(id);
        }
    });
});