$('body').ready(function () {
    $('body').on('click', '.delete', function (e) {
        e.preventDefault;
        let id = $(this).attr('id');
        let deleteFiles = $('input[name="deleteFiles"]').val();

        if (deleteFiles.length > 0) deleteFiles = JSON.parse(deleteFiles);
        else deleteFiles = [];
        deleteFiles.push(id);
        $('input[name="deleteFiles"]').val(JSON.stringify(deleteFiles));
        $(this).parent().detach();
        return false;
    });


    if ($('select[name="uf_type"]').text() == 'Другое') $('input[name="uf_type_other"]').parent().parent().show();
    else $('input[name="uf_type_other"]').parent().parent().hide();

    $('body').on('change', 'select[name="uf_type"]', function () {
        if ($('select[name="uf_type"] option:selected').text() == 'Другое') $('input[name="uf_type_other"]').parent().parent().show();
        else $('input[name="uf_type_other"]').parent().parent().hide();
    });

    function showOrhide(aNames) {
        for (let name in aNames) {
            $.each(aNames[name], function (i, v) {
                if ($('select[name="' + name + '"]').val() == 1)
                    $('#' + v).parent().parent().show();
                else
                    $('#' + v).parent().parent().hide();
            });

            $('body').on('change', 'select[name="' + name + '"]', function () {
                if ($('select[name="' + name + '"] option:selected').val() == 1) {
                    $.each(aNames[name], function (i, v) {
                        $('#' + v).parent().parent().toggle();
                    });
                }
                else {
                    $.each(aNames[name], function (i, v) {
                        $('#' + v).parent().parent().toggle();
                    });
                }
            });
        }
    };

    let aNames = {
        uf_stage: ['uf_stage_w', 'uf_stage_h'],
        uf_presidium: ['uf_presidium_w', 'uf_presidium_h'],
        uf_audio: ['uf_audio_char'],
        uf_dinner: ['uf_dinner_info'],
        uf_parking: ['uf_parking_type', 'uf_parking_place'],
        uf_arenda: ['uf_arenda_pay']
    };
    showOrhide(aNames);
});