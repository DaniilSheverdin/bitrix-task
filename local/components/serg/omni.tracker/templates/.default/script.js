$(function () {

    function parseHTML(html) {
        var t = document.createElement('template');
        t.innerHTML = html;
        return t.content.cloneNode(true);
    }

    function resultPercent (create, complete) {
        var percent = 100 / create * complete;
        return percent.toFixed(2)
    }

    var $message = $('#js-message'),
        $omni = $('.js-omni'),
        $omniForm = $('#js-omni_form'),
        $tableBody = $('#js-omni_table_body'),
        $namesSelect = $("#js-names_select"),
        $departmentsSelect = $("#js-departments_select"),
        $createInput = $('#js-create'),
        $completeInput = $('#js-complete'),
        $percentInput = $('#js-percent_exec'),
        valueCreate = 0,
        valueComplete = 0,
        newHtmlRow = '',
        messages = {
            add: '<div class="success">Добавлено</div>',
            error: '<div class="error">Заполните все поля</div>'
        };

    $namesSelect.select2({width: 300});
    $departmentsSelect.select2({width: 300});

    $createInput.on('change, keyup', function (e) {
        valueCreate = e.target.value;
        $percentInput.val(resultPercent(valueCreate, valueComplete))
    });

    $completeInput.on('change, keyup', function (e) {
        valueComplete = e.target.value;
        $percentInput.val(resultPercent(valueCreate, valueComplete))
    });

    $namesSelect.on('select2:select', function (e) {
        var data = e.params.data;
        $departmentsSelect.show();

        var queryControl = {
                c: 'serg:omni.tracker',
                action: 'getDepartmentsByUserID',
                mode: 'ajax'
            },
            requestControl = $.ajax({
                url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
                type: 'POST',
                data: 'id='+ data.id
            });
        requestControl.done(function (result) {

            var departments = result.data.map(function (el) {

                return {
                    id: el.id,
                    text: parseHTML(el.text).textContent
                }
            });

            $departmentsSelect.html('').select2({data: {id:null, text: null}});
            $departmentsSelect.select2({
                width: 300,
                data: departments
            });

        });

        requestControl.fail(function () {
            console.error('Can not save omni tracker statistic data');
        });

    });

    $omni.on('click', '#js-add', function () {

        $('#js-add_user').show();
        $(this).hide();

    });


    $omni.on('click', '#js-save', function(e){

        e.preventDefault();

        var formData = $omniForm.serialize();

        var queryControl = {
                c: 'serg:omni.tracker',
                action: 'addStatistic',
                mode: 'ajax'
            },
            requestControl = $.ajax({
                url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
                type: 'POST',
                data: formData
            });
        requestControl.done(function (result) {

            if (result.status === 'success') {
                if (result.data.error === true) {
                    $message.html(messages.error)
                } else {
                    valueCreate = 0;
                    valueComplete = 0;
                    $omniForm.trigger("reset");
                    $departmentsSelect.html('').select2({data: {id:null, text: null}});
                    $departmentsSelect.select2({
                        width: 300,
                    });
                    $namesSelect.select2({width: 300}).val(null);
                    $message.html(messages.add);

                    newHtmlRow += '<div class="row">';
                    newHtmlRow += '<div class="department"><p>'+ result.data.newData.department +'</p></div>';
                    newHtmlRow += '<div class="name"><p>'+ result.data.newData.user +'</p></div>';
                    newHtmlRow += '<div class="create"><p>'+ result.data.newData.created +'</p></div>';
                    newHtmlRow += '<div class="complete"><p>'+ result.data.newData.completed +'</p></div>';
                    newHtmlRow += '<div class="wrong"><p>'+ result.data.newData.defection +'</p></div>';
                    newHtmlRow += '<div class="percent_exec"><p>'+ result.data.newData.percent +'</p></div>';
                    newHtmlRow += '</div>';

                    $tableBody.append(newHtmlRow);
                }
            }
        });

        requestControl.fail(function () {
            console.error('Can not save omni tracker statistic data');
        });

        return false;
    });


});
