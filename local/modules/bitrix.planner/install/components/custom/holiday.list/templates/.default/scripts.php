<script>
    var day_from = 0;
    var day_to = 0;
    var month = '<?=sprintf('%02d', $arResult['MONTH'])?>';
    var year = <?=$arResult['YEAR']?>;
    var last_day = <?=$arResult['LAST_DAY']?>;
    var day_left = <?=intval($arResult['USERS'][$arResult['USER_ID']]['day_left'])?>;

    var day_f = 0;
    var day_t = 0;

    function timestampToDate(ts) {
        var d = new Date();
        d.setTime(ts * 1000);
        console.log(ts)
        return ('0' + d.getDate()).slice(-2) + '.' + ('0' + (d.getMonth() + 1)).slice(-2) + '.' + d.getFullYear();
    }

    $('body').on('mousedown', '.table-vacation .current-user', function () {
        if ($(this).hasClass('from')) day_f = $(this).attr('data-date');
        else day_f = $(this).prevAll(".from").attr('data-date');
        let data_week = $(this).attr('data-week');
        $('[data-week=' + data_week + ']').toggleClass('select-week')
    })

    $('body').on('mouseover', '.table-vacation .current-user', function () {
        if (day_f > 0) {
            let data_week = $(this).attr('data-week');
            $('[data-week=' + data_week + ']').addClass('select-week')
        }
    })

    $('body').on('mouseup', '.table-vacation .current-user', function () {
        if (day_f > 0) {
            day_f = $('.select-week').first().attr('data-date');
            day_t = $('.select-week').last().attr('data-date');
        }

        ShowEditForm('<?=GetMessageJS("BITRIX_PLANNER_DOBAVLENIE_ZAPISI")?>');
        document.forms.add_form.action.value = 'add';
        document.forms.add_form.day_from.value = timestampToDate(day_f);
        document.forms.add_form.day_to.value = timestampToDate(day_t);
        day_f = day_t = 0;
        $('.current-user').removeClass('select-week');
    })

    function GetDay(ob) {
        return ob.id.replace(/^day_/, '');
    }

    function StartSelect(ob) {
        day_from = GetDay(ob);
        Mark(ob);
    }

    function EndSelect(ob) {
        ShowEditForm('<?=GetMessageJS("BITRIX_PLANNER_DOBAVLENIE_ZAPISI")?>');
        document.forms.add_form.action.value = 'add';

        v = Math.min(day_from, day_to);
        if (v < 10)
            v = '0' + v;

        document.forms.add_form.day_from.value = v + '.' + month + '.' + year;
        v = Math.max(day_from, day_to);
        if (v < 10)
            v = '0' + v;
        document.forms.add_form.day_to.value = v + '.' + month + '.' + year;

        type = '<?=!$arResult['COUNT_DAYS'] || $arResult['USERS'][$arResult['USER_ID']]['day_left'] > 0 ? 'VACATION' : ''?>'
        SetType(type);
        day_from = 0;
    }

    function EditVacation(id, from, to, type, PREVIEW_TEXT, action) {
        if (action) {
            ShowEditForm('<?=GetMessageJS("BITRIX_PLANNER_DOBAVLENIE_ZAPISI")?>');
            document.forms.add_form.action.value = 'add';
        } else {
            ShowEditForm('<?=GetMessageJS("BITRIX_PLANNER_IZMENENIE_ZAPISI")?>');
            document.forms.add_form.action.value = 'edit';
        }
        document.forms.add_form.id.value = id;
        document.forms.add_form.day_from.value = from;
        document.forms.add_form.day_to.value = to;
        document.forms.add_form.PREVIEW_TEXT.value = PREVIEW_TEXT;
        SetType(type);
    }

    function SetType(type) {
        var sel = document.forms.add_form.event_type;
        for (i = 0; i < sel.options.length; i++)
            sel.options[i].selected = sel.options[i].value == type;
    }

    function ShowEditForm(text) {
        frm = BX('date_edit_form');
        t = (document.documentElement.scrollTop || document.body.scrollTop) + (window.innerHeight - 400) / 2;
        frm.style.top = (t < 0 ? 0 : t) + 'px';
        l = (window.innerWidth - 600) / 2;
        frm.style.left = (l < 0 ? 0 : l) + 'px';
        frm.style.display = '';
        BX('date_edit_title').innerHTML = '<b>' + text + '</b>';

        document.onkeydown = function (e) {
            e = e || window.event;
            if (e.keyCode == 27) {
                BX('date_edit_form').style.display = 'none';
            }
        }
    }

    function Mark(ob) {
        if (day_from > 0) {
            day_to_tmp = GetDay(ob);
            min = Math.min(day_from, day_to_tmp);
            max = Math.max(day_from, day_to_tmp);
            cnt = max - min + 1;

            day_to = day_to_tmp;
            for (i = 1; i <= last_day; i++) {
                color = i >= min && i <= max ? 'rgb(136, 168, 226)' : '';
                ob = document.getElementById('day_' + i);
                ob.style.background = color;

                ob.title = i == day_to ? '<?=GetMessageJS("BITRIX_PLANNER_PRODOLJITELQNOSTQ")?>' + cnt + ' <?=GetMessageJS("BITRIX_PLANNER_DN")?>' : '';
            }
        }
    }

    function DeleteVacation(id) {
        if (confirm('<?=GetMessageJS("BITRIX_PLANNER_UDALITQ_ZAPISQ")?>'))
            document.location = '<?=$arResult['BASE_URL']?>&action=delete&id=' + id;
    }

    function ApproveVacation(id) {
        if (confirm('<?=GetMessageJS("BITRIX_PLANNER_PODTVERDITQ_ZAPISQ")?>'))
            document.location = '<?=$arResult['BASE_URL']?>&action=approve&id=' + id;
    }

    function UnApproveVacation(id) {
        if (confirm('<?=GetMessageJS("BITRIX_PLANNER_VERNUTQ_STATUS_NEPOD")?>'))
            document.location = '<?=$arResult['BASE_URL']?>&action=unapprove&id=' + id;
    }

    function AddDaysLeft(id) {
        inp = BX('days_left_' + id);
        inp.value = 28;
        inp.style.display = '';
        inp.focus();
        inp.onkeypress =
            function (event) {
                if (event.keyCode == 13) {
                    inp = BX('days_left_' + id);
                    document.location = '<?=$arResult['BASE_URL']?>&set_user_id=' + id + '&add_days=' + encodeURIComponent(inp.value);
                }
            }
    }

    function RefreshList(department) {
        document.location = '<?=$arResult['BASE_URL']?>&department=' + department;
    }

    function to_cadrs(e) {
        document.location = '<?=$arResult['BASE_URL']?>&to_cadrs=yes';
    }
</script>
