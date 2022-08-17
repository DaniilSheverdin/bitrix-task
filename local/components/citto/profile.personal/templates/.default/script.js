BX.ready(function(){
    var oPopup = new BX.PopupWindow(
        'raschetnyy-listok-window',
        null,
        {
            content: BX('raschetnyy-listok'),
            overlay: {
                backgroundColor: 'black',
                opacity: '50'
            },
            closeIcon: {
                right: "20px",
                top: "10px"
            },
            closeByEsc : true,
            titleBar: {
                content: BX.create("span", {
                    html: "Расчётный листок",
                    'props': {
                        'style': 'line-height:50px'
                    }
                })
            },
            buttons: [
                new BX.PopupWindowButton({
                    text: "Загрузить" ,
                    className: "popup-window-button-accept" ,
                    events: {
                        click: function() {
                            $('#raschetnyy-listok-window form').submit();
                        }
                    }
                }),
                new BX.PopupWindowButton({
                    text: "Закрыть" ,
                    className: "webform-button-link-cancel" ,
                    events: {
                        click: function(){
                            this.popupWindow.close();
                        }
                    }
              })
           ]
        }
    );
    $('.js-raschetnyy-listok').on('click', function(){
        oPopup.show();
    });
});