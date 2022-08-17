$(document).ready(function () {
    let obResult = $('.js-result-parent');
    let obInputSearch = $('#js-ajax-parent');

    obInputSearch.on('keyup', function () {
        $.ajax({
            url: "/local/components/citto/lnpa/templates/.default/ajax.php",
            type: "GET",
            dataType: "json",
            data: {search: obInputSearch.val()},
        }).done(function (data) {
            obResult.text('');
            obResult.addClass('active');
            var sHtml = "";
            // Скрытие всплывающего окна, если кликнули мимо
            $(document).mouseup(function (e) {
                var div = $(".js-result-parent");
                if (!div.is(e.target)
                    && div.has(e.target).length === 0) {
                    div.removeClass('active');
                }
            });

            for (var i = 0; i < data.length; i++) {
                if (typeof data[i].NAME !== 'undefined') {
                    sHtml += "<div data-parent=" + data[i].ID + " class='item-parent'>" + [data[i].NAME].toString() + "</div>";
                }
            }
            obResult.append(sHtml);
        });
    });
    obResult.on('click', ".item-parent", function () {
        var input_id = $(this).attr("data-parent");
        var input_name = $(this).text();
        obResult.removeClass('active');
        obInputSearch.val(`${input_name} [${input_id}]`);
    });
});

$(document).ready(function () {
    function hideParentDoc(sOption) {
        if (sOption == 'MAIN') {
            $('#UF_PARENT_ELEM').hide();
        }
        else {
            $('#UF_PARENT_ELEM').show();
        }
    }

    let sMain = $('#js-UF_MAIN option:selected').attr('data-xml');
    hideParentDoc(sMain);

    $('#js-UF_MAIN').change(function(){
        let sMain = $(this).find('option:selected').attr('data-xml');
        hideParentDoc(sMain);
    });
});

$(document).ready(function () {
    $('body').on('click', '#sign', function () {
        if ($('#sign-popup').length != 0) {
            $('#sign-popup').detach()
        }

        let sSessionID = $(this).attr('js-session');
        let iFileSignID = $(this).attr('js-file-sign-id');
        let iCardID = $(this).attr('js-card-id');

        let popup = new BX.PopupWindow("sign-popup", null, {
            autoHide: true,
            closeIcon: true,
            closeByEsc: true,
            closeIcon: {right: "20px", top: "10px"},
        });

        $('<iframe>', {
            src: '/podpis-fayla/?FILES[]=' + iFileSignID + '&CHECK_SIGN=N&sessid=' + sSessionID,
            id: 'popup-iframe',
            frameborder: 0,
            scrolling: 'no',
            width: '100%',
            height: '100%'
        }).appendTo('#sign-popup .popup-window-content');

        window.addEventListener('message', function(msg) {
            if (msg.data === 'filesigner_signed') {
                let request = BX.ajax.runComponentAction(
                    'citto:lnpa',
                    'getTest',
                    {
                        mode: 'ajax',
                        data: {
                            iFileSignID: iFileSignID,
                            iCardID: iCardID
                        }
                    }
                );

                request.then(function(data) {
                    window.location.reload();
                });

                $('#sign-popup').detach();
            }
        });

        popup.show()
    });
});

$(document).ready(function () {
    function toggleAlert(bChecked = false) {
        let obDate = $('[name="alert_date"]');

        if (bChecked === true) {
            obDate.prop('disabled', false)
        } else {
            obDate.prop('disabled', true)
        }
    }

    $('body').on('click', '#alert_check', function () {
        bChecked = $(this).is(':checked')
        toggleAlert(bChecked);
    });
});
