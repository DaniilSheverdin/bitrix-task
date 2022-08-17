$(function () {
    var $control = $('#control'),
        $otdel = $('#otdel'),
        $table = $('.bi_table_body'),
        d = new Date(),
        month = d.getMonth()+1, day = d.getDate(),
        nowDate =  (('' + day).length < 2 ? '0' : '') + day + '.' + (('' + month).length < 2 ? '0' : '') + month + '.' + d.getFullYear();

    var loaded = false;
    var outDom = $('.bi-position-relative');

    var calcPersent = function() {
        $('.string').each(function() {
            var state = parseFloat($(this).find('.state-value input').val().replace(/\s/g, '').replace(',', '.'));
            if(isNaN(state)) {
                state = 0;
            }

            var target = parseFloat($(this).find('.target-value input').val().replace(/\s/g, '').replace(',', '.'));
            var $percent_exec;

            if(isNaN(target)) {
                target = 0;
                $percent_exec = 0;
            } else {
                $percent_exec = (state / target) * 100;
                if($percent_exec > 100) {
                    $percent_exec = 100;
                }
            }

            $(this).find('.percent_exec input').val(parseInt($percent_exec) + '%');
        });
    }

    calcPersent();
    $(document).on('change', '.state-value input', function() {
        calcPersent();
    });

    function renderTable(el, i, boolaedit, db) {

        if (el.target_value === undefined) el.target_value = '-';
        if (el.bi_id === undefined) el.bi_id = '0';
        if (el.otdel === undefined) el.otdel = '0';

        if (!db) {
            db = {};
            db.state_value = '';
            db.percent_exec = '';
            db.flag = '';
            db.date = nowDate;
            db.id = '';
            db.comment = '';
            db.state_value_old = '';
            db.date_last_change = '-';
        }

        var html = '';

        html += '<div class="string">';
        html += '<input type="hidden" name="INDICATORS['+i+'][NAME]" id="full_name" value="'+ el.prop[0] +'">';
        html += '<div class="full-name">'+ el.prop[0] +'</div>';
        html += '<input type="hidden" name="INDICATORS['+i+'][ATT_SHORT_NAME]" id="short_name" value="'+ el.prop[1] +'">';
        html += '<div class="short-name">'+ el.prop[1] +'</div>';
        html += '<input type="hidden" name="INDICATORS['+i+'][ATT_BASE_SET]" id="base_set" value="'+ el.prop[2] +'">';
        html += '<div class="base-set">'+ el.prop[2] +'</div>';
        html += '<input type="hidden" name="INDICATORS['+i+'][ID]" id="id" value="'+ db.id +'">';
        html += '<input type="hidden" name="INDICATORS['+i+'][BI_ID]" id="bi_id" value="'+ el.bi_id +'">';
        html += '<input type="hidden" name="INDICATORS['+i+'][OTDEL]" id="otdel" value="'+ el.otdel +'">';
        html += '<div class="target-value"><input type="text" name="INDICATORS['+i+'][ATT_TARGET_VALUE]" id="target_value" value="'+ el.target_value +'" readonly></div>';
        html += '<div class="state-value"><input type="text" name="INDICATORS['+i+'][ATT_STATE_VALUE]" placeholder="Введите значение" id="state_value" value="'+ db.state_value +'" '+ (boolaedit ? '' : 'readonly') +'></div>';
        html += '<div class="percent_exec"><input type="text" name="INDICATORS['+i+'][ATT_PERCENT_EXEC]" id="percent_exec" value="'+ db.percent_exec +'" readonly></div>';
        html += '<div class="last-value"><span>' + ((db.state_value_old != '' && db.state_value_old != undefined) ? db.state_value_old : '-') +'</span></div>';
        html += '<div class="comment"><textarea type="text" name="INDICATORS['+i+'][ATT_COMMENT]" id="comment" '+ (boolaedit ? '' : 'readonly') +'>'+ db.comment +'</textarea></div>';
        html += '<div class="date"><input type="text" readonly value="'+ ((!boolaedit && db.date != null) ? db.date : nowDate) +'" name="INDICATORS['+i+'][ATT_DATE]" id="date"></div>';
        html += '<div class="date-last-change"><span>'+ ((db.date_last_change != null && db.date_last_change != '-') ? db.date_last_change + ' г.' : '-') +'</span></div>';
        html += '</div>';

        $table.append(html)
    }

    $control.on('change', function() {
        loaded = true;
        var formData = $('#form-bi').serialize();
        outDom.append('<div class="loader-background"><div class="loader-ajax"></div></div>');

        var queryControl = {
                c: 'serg:form.result.new',
                action: 'chooseSubsectionList',
                mode: 'ajax'
            },
            requestControl = $.ajax({
                url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
                type: 'POST',
                data: formData
            });

        requestControl.done(function (result) {
            var options_html = '';
            for(k in result.data) {
                options_html += "<option value=\""+k+"\">"+result.data[k]+"</option>\n";
            }
            $otdel.html(options_html);
            $otdel.find('option:first-child').attr('selected', 'selected').trigger('change');
        });
    });

    $otdel.change(function () {
        var setVal = $('#control').val();
        var setValOtdel = $('#otdel').val();
        var formData = $('#form-bi').serialize();

        if(!loaded) {
            outDom.append('<div class="loader-background"><div class="loader-ajax"></div></div>');
        }

        var queryControl = {
                c: 'serg:form.result.new',
                action: 'chooseSection',
                mode: 'ajax'
            },
            requestControl = $.ajax({
                url: '/bitrix/services/main/ajax.php?' + $.param(queryControl, true),
                type: 'POST',
                data: formData
            });

        requestControl.done(function (result) {
            outDom.find('.loader-background').remove();
            loaded = false;
            $table.html('');

            result.data.table.map(function (el, index) {
                if (result.data.db === null) {
                    renderTable(el, index, result.data.boolaedit)
                } else {
                    renderTable(el, index, result.data.boolaedit, result.data.db[index])
                }

            });
            calcPersent();

            if(result.data.boolaedit) {
                $('button[type=submit]').parent().show();
            } else {
                $('button[type=submit]').parent().hide();
            }

            var indicatorsCSV = $('#indicators-csv');
            var linkArr = indicatorsCSV.attr('href').split('?');
            linkArr[1] = 'department=' + setVal + '&otdel='+ setValOtdel +'&format=csv';
            indicatorsCSV.attr('href', linkArr[0] + '?' + linkArr[1]);

            var html = '';

            if(!result.data.boolaedit) {
                if (typeof result.data['ACTUAL'][result.data['otdel_name']]['noact'] == 'undefined') {
                    html += '<div class="input-group ui-alert ui-alert-success">';
                    html += '<p>Данные по управлению "' + result.data['otdel_name'] + '" актуализированы</p>';
                    html += '</div>';
                } else if (typeof result.data['ACTUAL'][result.data['otdel_name']]['noact'][0] != 'undefined' && result.data['ACTUAL'][result.data['otdel_name']]['noact'][0] == '') {
                    html += '<div class="input-group ui-alert ui-alert-danger">';
                    html += '<p>Данные по управлению "' + result.data['otdel_name'] + '" не были актуализированы</p>';
                    html += '</div>';
                } else {
                    html += '<div class="input-group ui-alert ui-alert-danger">';
                    html += '<div>';
                    html += '<p>Данные по управлению "' + result.data['otdel_name'] + '" актуализированы частично, кроме:</p>';
                    html += '<ul>';

                    for (var $ikey in result.data['ACTUAL'][result.data['otdel_name']]['noact']) {
                        if (result.data['ACTUAL'][result.data['otdel_name']]['noact'][$ikey] != null) {
                            html += '<li>' + result.data['ACTUAL'][result.data['otdel_name']]['noact'][$ikey] + '</li>';
                        }
                    }
                    ;

                    html += '</ul>';
                    html += '</div>';
                    html += '</div>';
                }
            }

            html += '<div class="input-group control-input">';
            html += 'Дата последнего обновления: <strong>'+ result.data.minDate +' г.</strong>';
            html += '</div>';

            $("#act-info").html(html);

        });

        requestControl.fail(function () {
            console.error('Can not get control data');
        });

    });
});
