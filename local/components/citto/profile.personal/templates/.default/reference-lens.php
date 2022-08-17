<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

$userId = $GLOBALS['USER']->GetID();
if (isset($arResult['User']['ID'])) {
    $userId = $arResult['User']['ID'];
}
?>
<script type="text/javascript">
BX.ready(function(){
    $('.js-reference-lens').on('click', function() {
        let $this = $(this);
        $this.addClass('ui-btn-wait');
        request = BX.ajax.runComponentAction(
            'citto:profile.personal',
            'getReferenceLensData',
            {
                mode: 'ajax',
                json: {
                    action: 'getReferenceLensData',
                    userId: <?=$userId?>
                }
            }
        );
        request.then(function(ret) {
            if (ret.status == 'error') {
                BX.UI.Dialogs.MessageBox.alert(ret.errors[1].message);
            } else {
                window.open(ret.data);
            }
            $this.removeClass('ui-btn-wait');
        }, function (ret) {
            BX.UI.Dialogs.MessageBox.alert(ret.errors[1].message);
            $this.removeClass('ui-btn-wait');
        });
        return false;
    });
});
</script>