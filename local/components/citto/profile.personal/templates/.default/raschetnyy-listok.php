<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}
?>
<div id="raschetnyy-listok" style="display:none">
    <form method="GET" target="_blank" action="<?=SITE_DIR?>raschetnyy-listok/">
        <div style="margin-bottom:10px">
            <strong>Укажите период:</strong>
        </div>
        <span class="main-ui-control-field-label">Начало</span>
        <div class="ui-ctl ui-ctl-after-icon ui-ctl-date ui-ctl-w100">
            <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
            <input
                name="DateS"
                class="ui-ctl-element"
                value="<?=date('d.m.Y', strtotime('first day of previous month'))?>"
                onclick="BX.calendar({node: this, field: this, bTime: false});"
                required />
        </div>
        <span class="main-ui-control-field-label">Конец</span>
        <div class="ui-ctl ui-ctl-after-icon ui-ctl-date ui-ctl-w100">
            <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
            <input
                name="DateE"
                class="ui-ctl-element"
                value="<?=date('d.m.Y', strtotime('last day of previous month'))?>"
                onclick="BX.calendar({node: this, field: this, bTime: false});"
                required />
        </div>
        <?=bitrix_sessid_post()?>
    </form>
</div>
<script type="text/javascript">
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
</script>