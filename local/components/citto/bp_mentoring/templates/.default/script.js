$('body').ready(function() {
    $('body').on('click', '.download', function(e) {
        e.preventDefault();
        let iFileID = $(this).attr('data-id');

        let request = BX.ajax.runComponentAction('citto:bp_mentoring', 'getFile', {
            mode: 'ajax',
            data: {
                'iFileID' : iFileID
            }
        });
        request.then(function (data) {
            let arInfo = data.data;
            var $a = $("<a>");
            $a.attr("href", arInfo.CONTENT);
            $("body").append($a);
            $a.attr("download", arInfo.NAME);
            $a[0].click();
            $a.remove();
        });
    });
});
