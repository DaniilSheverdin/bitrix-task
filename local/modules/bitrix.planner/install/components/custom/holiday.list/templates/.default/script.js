var day_f = 0;
var day_t = 0;
var day_from = 0;
var day_to = 0;

function timestampToDate(ts) {
    var d = new Date();
    d.setTime(ts * 1000);
    return ('0' + d.getDate()).slice(-2) + '.' + ('0' + (d.getMonth() + 1)).slice(-2) + '.' + d.getFullYear();
}

function GetDay(ob) {
    return ob.id.replace(/^day_/, '');
}

function StartSelect(ob) {
    day_from = GetDay(ob);
    Mark(ob);
}

function EndSelect(ob) {
    ShowEditForm(BITRIX_PLANNER_DOBAVLENIE_ZAPISI);
    document.forms.add_form.action.value = 'add';
    v = Math.min(day_from, day_to);
    if (v < 10)
        v = '0' + v;

    document.forms.add_form.day_from.value = v + '.' + month + '.' + year;
    v = Math.max(day_from, day_to);
    if (v < 10)
        v = '0' + v;
    document.forms.add_form.day_to.value = v + '.' + month + '.' + year;
    SetType(type);
    day_from = 0;
}

function EditVacation(id, from, to, type, PREVIEW_TEXT, action) {
    if (action) {
        ShowEditForm(BITRIX_PLANNER_DOBAVLENIE_ZAPISI);
        document.forms.add_form.action.value = 'add';
    } else {
        ShowEditForm(BITRIX_PLANNER_IZMENENIE_ZAPISI);
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
    let date_edit_form = document.getElementById('date_edit_form');
    frm = BX('date_edit_form');
    t = (document.documentElement.scrollTop || document.body.scrollTop) + (window.innerHeight - 400) / 2;
    frm.style.top = (t < 0 ? 0 : t) + 'px';
    l = (window.innerWidth - 600) / 2;
    frm.style.left = (l < 0 ? 0 : l) + 'px';
    frm.style.display = '';
    BX('date_edit_title').innerHTML = '<b>' + text + '</b>';
    date_edit_form.style.display = "block";
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
            ob.title = i == day_to ? BITRIX_PLANNER_PRODOLJITELQNOSTQ + cnt + BITRIX_PLANNER_DN : '';
        }
    }
}

function DeleteVacation(id) {
    if (confirm(BITRIX_PLANNER_UDALITQ_ZAPISQ)) {
        id = JSON.stringify(id);
        document.location = BASE_URL + '&action=delete&id=' + id;
    }
}

function ApproveVacation(id) {
    if (confirm(BITRIX_PLANNER_PODTVERDITQ_ZAPISQ)) {
        id = JSON.stringify(id);
        document.location = BASE_URL + '&action=approve&id=' + id;
    }
}

function UnApproveVacation(id) {
    if (confirm(BITRIX_PLANNER_VERNUTQ_STATUS_NEPOD))
        id = JSON.stringify(id);
    document.location = BASE_URL + '&action=unapprove&id=' + id;
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
                document.location = BASE_URL + '&set_user_id=' + id + '&add_days=' + encodeURIComponent(inp.value);
            }
        }
}

function RefreshList(department) {
    document.location = BASE_URL + '&department=' + department;
}

function to_cadrs(e) {
    document.location = BASE_URL + '&to_cadrs=yes';
}

$(document).ready(function () {
    // $('#example, #violations').DataTable({
    //     paging: false,
    //     searching: false,
    //     info: false
    // });
    // Ширина первого столбца таблицы
    let width = 0;
    $('.table-vacation tbody th:first-child').each(function (i, v) {
        if (width < $(this).outerWidth())
            width = $(this).outerWidth();
    });
    if (width > 285) {
        $('.table-vacation tr th:first-child').css('width', width - 1);
        $('.inner').css('margin-left', width - 1);
    }

    $('body').on('input', '#DAYS_B', function () {
        let value = $(this).val();
        if (value > 0) {
            value = value * 86400;
            df = $('input[name="day_from"]').val().split('.');
            dt = $('input[name="day_to"]').val().split('.');
            day_f = (+new Date(df[2], df[1] - 1, df[0])) / 1000;
            day_t = (+new Date(dt[2], dt[1] - 1, dt[0])) / 1000;
            day_t = day_f + value - 86400;

            let holidays = JSON.parse($('#date_edit_form input[name="holidays"]').val());
            let workdays = (value) / 86400;
            let holidays_count = 0;

            for (key in holidays) {
                let day = holidays[key];
                if (day <= day_t && day >= day_f) {
                    holidays_count++;
                }
            }
            wh = workdays - holidays_count;

            callee = (function (day = +day_t + 86400) {
                if ($.inArray((day), holidays) >= 0) {
                    holidays_count++;
                    return callee(day + 86400);
                } else if (workdays != wh) {
                    wh++;
                    return callee(day + 86400);
                } else return day;
            });
            callee();
            day_t = +day_f + workdays * 86400 + holidays_count * 86400;

            $('#HOLIDAYS_B').val(holidays_count);
            $('input[name="day_to"]').val(timestampToDate(day_t - 86400));
        } else $('#DAYS_B').val(1);
    });

    $('body').on('change keyup', 'input[name="day_from"], input[name="day_to"]', function () {
        df = $('input[name="day_from"]').val().split('.');
        dt = $('input[name="day_to"]').val().split('.');
        day_f = (+new Date(df[2], df[1] - 1, df[0])) / 1000;
        day_t = (+new Date(dt[2], dt[1] - 1, dt[0])) / 1000;
        let holidays = JSON.parse($('#date_edit_form input[name="holidays"]').val());
        var holidays_count = 0;

        callee = (function (day) {
            if ($.inArray((day), holidays) >= 0) {
                holidays_count++;
                return callee(day + 86400);
            } else if (day < day_t) return callee(day + 86400);
            else return day;
        });
        day_t = callee(day_f);
        $('input[name="day_to"]').val(timestampToDate(day_t));
        $('#HOLIDAYS_B').val(holidays_count);
        $('#DAYS_B').val((day_t - day_f + 86400) / 86400 - holidays_count);
    });

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
        if ($(this).index() == 0) return false;

        if (day_f > 0) {
            day_f = $('.select-week').first().attr('data-date');
            day_t = $('.select-week').last().attr('data-date');
        }

        let holidays = JSON.parse($('#date_edit_form input[name="holidays"]').val());

        let workdays = (day_t - day_f) / 86400 + 1;
        let holidays_count = 0;

        for (key in holidays) {
            let day = holidays[key];
            if (day <= day_t && day >= day_f) {
                holidays_count++;
            }
        }
        wh = workdays - holidays_count;
        callee = (function (day = +day_t + 86400) {
            if ($.inArray((day), holidays) >= 0) {
                holidays_count++;
                return callee(day + 86400);
            } else if (workdays != wh) {
                wh++;
                return callee(day + 86400);
            } else {
                return day;
            }
        });
        callee();
        day_t = +day_f + workdays * 86400 + holidays_count * 86400;
        $('#DAYS_B').val(workdays);
        $('#HOLIDAYS_B').val(holidays_count);
        ShowEditForm(BITRIX_PLANNER_DOBAVLENIE_ZAPISI);
        document.forms.add_form.action.value = 'add';
        document.forms.add_form.day_from.value = timestampToDate(day_f);
        document.forms.add_form.day_to.value = timestampToDate(day_t - 86400);
        day_f = day_t = 0;
        $('.current-user').removeClass('select-week');
    });

    $('body').on('click', '.showorhide a', function () {
        let id = $(this).parent().attr('data-edit');
        $('#' + id).slideToggle();
        return false;
    })

    $('body').on('click', '.selectall, .unselectall', function () {
        let checkboxes = $('.select_emp');
        if ($(this).hasClass('selectall')) {
            $(checkboxes).each(function (i, v) {
                $(this).prop('checked', true)
            })
        } else {
            $(checkboxes).each(function (i, v) {
                $(this).prop('checked', false)
            })
        }
        $('select option[value="description"]').prop('selected', true);
        return false;
    });

    $('body').on('change', 'select[name="mass_action"]', function () {
        let cur_value = $(this).val();
        if (cur_value != 'description') {
            let checkboxes = $('.select_emp');
            let checkboxes_arr = [];
            $(checkboxes).each(function (i, v) {
                if ($(this).is(':checked'))
                    checkboxes_arr.push($(this).attr('data-id'));
            });
            if (cur_value == 'add') ApproveVacation(checkboxes_arr);
            if (cur_value == 'unadd') UnApproveVacation(checkboxes_arr);
            if (cur_value == 'del') DeleteVacation(checkboxes_arr);
        }
    });

    $('body').on('click', '.link-approve, .link-disapprove, .link-remove', function () {
        let id = $(this).attr('data-id');
        if ($(this).hasClass('link-approve')) ApproveVacation([id]);
        if ($(this).hasClass('link-disapprove')) UnApproveVacation([id]);
        if ($(this).hasClass('link-remove')) DeleteVacation([id]);
        return false;
    });

    $('body').on('change', '#selectDelegation', function () {
        if ($(this).val() != 'none') {
            $('form[name="selectDelegationForm"]').submit();
        }
    });

    $('body').on('click', '.hideUser', function (e) {
        e.preventDefault();
        let idUser = $(this).parent().attr('id').split('_')[2];
        $(this).parent().parent().hide();
        $('.system_person_' + idUser).parent().hide();
        let hideUsers = localStorage.getItem("hideUsers");

        if (hideUsers == null) {
            localStorage.setItem("hideUsers", JSON.stringify([idUser]));
            $('.showhideusers a span').text(1);
        } else {
            hideUsers = JSON.parse(hideUsers);
            hideUsers.push(idUser);
            $('.showhideusers a span').text(hideUsers.length);
            localStorage.setItem("hideUsers", JSON.stringify(hideUsers));
        }

        return false;
    });

    if ((hideUsers = localStorage.getItem("hideUsers")) != null) {
        hideUsers = JSON.parse(hideUsers);
        $('.showhideusers a span').text(hideUsers.length);
        $.each(hideUsers, function (i, v) {
            $('#system_person_' + v).parent().hide();
            $('.system_person_' + v).parent().hide();
        });
    }

    $('body').on('click', '.showhideusers a', function (e) {
        e.preventDefault();
        if ((hideUsers = localStorage.getItem("hideUsers")) != null) {
            hideUsers = JSON.parse(hideUsers);
            $.each(hideUsers, function (i, v) {
                $('#system_person_' + v).parent().show();
                $('.system_person_' + v).parent().show();
            });
            localStorage.removeItem("hideUsers");
            $('.showhideusers a span').text(0);
        }
        return false;
    });

    $('body').on('click', '[data-rec-vacation]', function (e) {
        let parent = $(this).parent().find('th [data-wstart]').length;

        if (parent > 0) {
            let recId = $(this).attr('data-rec-vacation');
            let hrefs = $('.statusUser').find(`[data-id = ${recId}]`);

            $('#popupMenu .actions').empty();
            $.each(hrefs, function (i, v) {
                let clonHrefs = v.cloneNode(true);
                $('#popupMenu .actions').append(clonHrefs);
            });

            $('#popupMenu')
                .slideDown()
                .css({top: e.pageY, left: e.pageX})
        }
    });

    $('body').on('click', '#closeMenu', function (e) {
        $(this).parent().slideUp();
        e.preventDefault;
        return false;
    });

    if (usercadrs == 1) {
        let holidays = JSON.parse($('#date_edit_form input[name="holidays"]').val());
        var violations = [];
        $.each(jsonUsers, function (i, v) {

            let periods = v.WORKPERIODS.PERIODS;
            let cUserCells = $('#system_person_' + i).parent().find('td');

            $.each(cUserCells, (index, name) => {
                if (index + 1 <= periods.p1) $(name).addClass('period_1');
                else if (periods.p2) $(name).addClass('period_2');
            });

            if ('VACATION' in v) {
                let prevPeriod = 0;
                let periodBetween = false; // Период между отпусками
                let multiplicity = 0; // Кратность 7-ми, за исключением
                let more = false; // Период более 14-ми дней
                let less = false; // Период менее 7-ми дней

                $.each(v.VACATION, function (i2, v2) {
                    let from = +i2;
                    let to = +i2 + +v2.PERIOD - 86400;

                    let d = new Date(from * 1000);
                    if (d.getFullYear() != year) return false;

                    if ((from - prevPeriod) / 86400 < 30) periodBetween = true;

                    callee = (function (from) {
                        if ($.inArray((from), holidays) >= 0) {
                            v2.PERIOD -= 86400;
                            return callee(from + 86400);
                        } else if (from < to) return callee(from + 86400);
                        else return v2.PERIOD;
                    });
                    v2.PERIOD = callee(from);

                    if ((v2.PERIOD / 86400 % 7) != 0 && (v2.PERIOD > 7)) {
                        if (multiplicity >= 1 && $('#system_person_' + i + ' .left').text() == 0) multiplicity = 2;
                        else multiplicity = 1;
                    }

                    if ((v2.PERIOD / 86400) > 14) more = true;

                    if ((v2.PERIOD / 86400) < 7) less = true;

                    prevPeriod = to;
                });

                violations[i] = [];
                if (periodBetween) violations[i].push("- Период между отпусками менее 30 дней");
                if (multiplicity == 2) violations[i].push("- Период отпуска не кратен 7");
                if (more) violations[i].push("- Период отпуска превышает 14 дней");
                if (less) violations[i].push("- Период отпуска менее 7 дней");
                if (v.DETAIL.length > 0) violations[i].push("- " + v.DETAIL);

                if (violations[i].length > 0) {
                    $('#system_person_' + i).addClass('errorUser');
                    $('#system_person_' + i).attr('tooltip', 'Нарушения:\n\r' + violations[i].join('\n\r'));
                }
            }
        });
    }

    // Автокомплит
    let valheads = $('#valheads').val();
    $("#tags").autocomplete({
        source: function (req, response) {
            let request = BX.ajax.runComponentAction('citto:holiday.list', 'excel', {
                mode: 'ajax',
                data: {
                    action: 'getusers',
                    word: req.term,
                }
            });

            request.then(function (data) {
                response($.map(data.data, function (key, item) {
                    return {
                        id: item,
                        label: key.fio,
                    };
                }));
            });
        },

        select: function (event, ui) {
            let user = `
                <div class="alert alert-info" role="alert" data-head = "${ui.item.id}">
                    <a href="#" class="close">✕</a>
                    ${ui.item.value}
                </div>`;

            let add = true;
            $.each($("[data-head]"), function (i, v) {
                if ($(this).attr('data-head') == ui.item.id) add = false
            });

            if (add) $('.heads').append(user);
        },

        delay: 1500,
        minLength: 3
    });

    $('body').on('click', '.heads .close', function (e) {
        $(this).parent().detach();
        e.preventDefault();
        return false;
    });


    function oldHeadsFunc() {
        let valheads = $('.heads [data-head]');
        let arrHeads = [];
        $.each(valheads, function (i, v) {
            arrHeads.push($(this).attr('data-head'));
        });
        return arrHeads.join('|');
    };
    let oldHeads = oldHeadsFunc();

    $('body').on('click', '#save_heads', function (e) {
        let valheads = $('.heads [data-head]');
        let arrHeads = [];
        $.each(valheads, function (i, v) {
            arrHeads.push($(this).attr('data-head'));
        });


        $('#selectHead .count').text(arrHeads.length);

        let request = BX.ajax.runComponentAction('citto:holiday.list', 'setheads', {
            mode: 'ajax',
            data: {
                action: 'setheads',
                heads: arrHeads.join('|'),
                oldHeads: oldHeads,
                userId: userId
            }
        });

        request.then(function (data) {
            window.location.reload();

            $('#save_heads')
                .addClass('btn-success')
                .removeClass('btn-info');
            setTimeout(() => {
                $('#save_heads')
                    .addClass('btn-info')
                    .removeClass('btn-success');
            }, 1500);
            oldHeads = oldHeadsFunc();
        });
        e.preventDefault;
        return false;
    });

    $('body').on('click', '#violations a', function (e) {
        let arViolations = [];
        $.each($('#calendar tbody th'), function (i, v) {
            let violations = ($(this).attr('tooltip').length > 0) ? $(this).attr('tooltip') : 'Нарушения отсутствуют';
            let fio = $(this).find('.fiouser').html().trim();
            arViolations.push({
                fio: fio,
                violations: violations
            });
        });

        let request = BX.ajax.runComponentAction('citto:holiday.list', 'excel', {
            mode: 'ajax',
            data: {
                action: 'getviolations',
                arViolations: arViolations
            }
        });

        request.then(function (data) {
            var $a = $("<a>");
            $a.attr("href", data.data);
            $("body").append($a);
            $a.attr("download", "Violations.xls");
            $a[0].click();
            $a.remove();
        });

        e.preventDefault;
        return false;
    });


    // ЭЦП--
    $("body").on('click', '#excel', function () {
        let cryptopro = new window.RusCryptoJS.CryptoPro;
        cryptopro.init();

        let curCerts = $('.docsign-cryptoplugin__certs option');
        let arrCurCerts = [];
        if (curCerts.length != 0) {
            curCerts.map(function () {
                arrCurCerts.push($(this).val());
            });
        }

        cryptopro.listCertificates().then(function (info) {
            $.each(info, function (i, v) {
                if (arrCurCerts.indexOf(v.id) == -1) {
                    $('.docsign-cryptoplugin__certs select').append(
                        `<option value="${v.id}">${v.name}</option>`
                    );
                }
            });
        });
    });

    $('body').on('click', '#aboutExcel', function (e, q) {
        e.preventDefault();
        let request = BX.ajax.runComponentAction('citto:holiday.list', 'excel', {
            mode: 'ajax',
            data: {
                action: 'excel',
                data: jsonExcel,
                users: jsonUsers,
                year: year,
                introduction: true
            }
        });

        request.then(function (data) {
            var $a = $("<a>");
            $a.attr("href", data.data);
            $("body").append($a);
            $a.attr("download", "Report.xls");
            $a[0].click();
            $a.remove();
        });
        return false;
    });

    $('body').on('click', '#docsign__sign-files', function (e) {
        e.preventDefault();
        let cert = $('.docsign-cryptoplugin__certs select');
        if (cert.val() != null) {
            let cryptopro = new window.RusCryptoJS.CryptoPro;
            cert = cert.val();
            cryptopro.init().then(() => {
            }).then(()=>{
                let certInfo = cryptopro.certificateInfo(cert);
                let sCertInfo;
                certInfo.then((infoCert) => {
                    sCertInfo = `Издатель: ${infoCert.IssuerName};;Субъект: ${infoCert.SubjectName};;Серийный №: ${infoCert.SerialNumber};;Действителен: с ${(new Date(infoCert.ValidFromDate)).toLocaleString("ru", {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    })} по ${(new Date(infoCert.ValidToDate)).toLocaleString("ru", {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    })}`;
                }).then(() => {
                    let request = BX.ajax.runComponentAction('citto:holiday.list', 'excel', {
                        mode: 'ajax',
                        data: {
                            action: 'excel',
                            data: jsonExcel,
                            users: jsonUsers,
                            year: year,
                            sCertInfo: sCertInfo,
                            introduction: false
                        }
                    });
                    request.then(function (data) {
                        let fileBase64 = data.data;
                        cryptopro.init().then(info => {
                            return cryptopro.signData(fileBase64, cert);
                        }).then(sign => {
                            cryptopro.verifySign(fileBase64, sign).then((res)=>{
                                if(res) {
                                    let request = BX.ajax.runComponentAction('citto:holiday.list', 'signature', {
                                        mode: 'ajax',
                                        data: {
                                            action: 'signature',
                                            sign: sign,
                                            file: fileBase64,
                                            year: year
                                        }
                                    });
                                    request.then(function (data) {
                                        let $a = $("<a>");
                                        $a.attr("href", data.data);
                                        $("body").append($a);
                                        $a.attr("download", 'Sign.zip');
                                        $a[0].click();
                                        $a.remove();
                                        alert('Файл подписан');
                                    });
                                }
                                else alert('Подпись не прошла прошла проверку');
                            }).catch(() => {
                                alert('Подпись не прошла проверку');
                            });
                        }).catch(e => {
                            alert('Failed! ' + e);
                        });
                    });
                });
            });
        } else alert('Прикрепите сертификат');

        e.preventDefault();
        return false;
    });
    // --ЭЦП
});
