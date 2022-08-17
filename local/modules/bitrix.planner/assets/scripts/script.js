$(document).ready(function () {
    let url = $('#p_ajax').val();
    $("#STAFF_DEPARTMENT\\[\\]").hide();
    $("#DELEGATION_USERS\\[\\]").hide();
    $("textarea[name='DELEGATION_ARR']").parent().parent().hide();

    function getEmployes() {
        $('.curuser').detach();
        let department = 'key_'+$('#DEPARTMENTS option:selected').val();
        let usersArr = $('textarea[name="DELEGATION_ARR"]').val();
        if(usersArr.length != 0) {
            usersArr = JSON.parse(usersArr);
            usersArr = usersArr[department];
            if(usersArr!=undefined && usersArr.length != 0) {
                $.post({
                    url: url,
                    data: {
                        action: 'get_employes',
                        usersArr: usersArr,
                    },
                    success: function (data) {
                        let jsonData = JSON.parse(data);
                        $.each(jsonData,function(i,v){
                            let append = '<div class="curuser"><div data-user = "' + i + '" style="border: 1px solid #9ea7b1;display: inline-block;border-radius: 3px;padding: 5px;">' + v + '<span class="close" style="cursor: pointer;margin-left: 5px;font-weight: 700;color: red;">x</span></div></div>';
                            $('.ui-widget:eq(1)').append(append);
                        });

                    }
                });
            }
        }
    }
    getEmployes();

    $('#DEPARTMENTS').on('change', function() {
        getEmployes();
    });

    $(".myInput").autocomplete({
        source: function (request, response) {
            $.post({
                url: url,
                data: {
                    action: 'complete',
                    q: request.term
                },
                success: function (data) {
                    let json = JSON.parse(data);
                    response($.map(json, function (value, item) {
                        return {
                            id: item,
                            label: value
                        }
                    }));
                }
            });
        },
        select: function (event, ui) {
            let append = '<div><div data-user = "' + ui.item.id + '" style="border: 1px solid #9ea7b1;display: inline-block;border-radius: 3px;padding: 5px;">' + ui.item.label + '<span class="close" style="cursor: pointer;margin-left: 5px;font-weight: 700;color: red;">x</span></div></div>';

            if($(this).attr('name') === 'STAFF_DEPARTMENT') {
                $('#STAFF_DEPARTMENT\\[\\] option[value=' + ui.item.id + ']').prop('selected', true);
                $('.ui-widget:eq(0)').append(append);
            }
            else if($(this).attr('name') === 'DELEGATION_USERS') {
                let department = 'key_'+$('#DEPARTMENTS option:selected').val();
                let usersArr = $('textarea[name="DELEGATION_ARR"]').val();

                if(usersArr.length == 0) usersArr = {};
                else usersArr = JSON.parse(usersArr);

                if (department in usersArr) {
                    if(usersArr[department].indexOf(ui.item.id) != -1) return;
                    usersArr[department].push(ui.item.id);
                }
                else usersArr[department] = [ui.item.id];

                usersArr = JSON.stringify(usersArr);
                $('textarea[name="DELEGATION_ARR"]').val(usersArr);
                $('.ui-widget:eq(1)').append(append);
            }
        },
        minLength: 2
    });

    $('body').on('click', '.close', function () {
        let userid = $(this).parent().attr('data-user');
        $(this).parent().parent().detach();

        if($(this).parent().parent().attr('class') != 'curuser')
            $('select option[value=' + userid + ']').prop('selected', false);
        else {
            let usersArr = $('textarea[name="DELEGATION_ARR"]').val();
            let department = 'key_'+$('#DEPARTMENTS option:selected').val();
            usersArr = JSON.parse(usersArr);
            console.log(usersArr)
            $.each(usersArr[department], function(i,v){
                if(v == userid) {
                    usersArr[department].splice(i, 1);
                    if(usersArr[department].count == 0)
                        delete usersArr[department];
                }
            });
            usersArr = JSON.stringify(usersArr);
            $('textarea[name="DELEGATION_ARR"]').val(usersArr);
        }
    });

    $('body').on('change', '#TYPE_IBLOCK', function () {
        let type = $('#TYPE_IBLOCK option:selected').val();
        $.post({
            url: url,
            data: {
                action: 'sel_structure',
                type: type
            },
            success: function (data) {
                let json = JSON.parse(data);
                $('#VACATION_RECORDS').empty();
                $.each(json, function (k, v) {
                    $('#VACATION_RECORDS').append("<option value=" + k + ">" + v + "</option>");
                })
            }
        });
    });
});
