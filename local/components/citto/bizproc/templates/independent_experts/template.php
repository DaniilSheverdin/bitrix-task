<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global $APPLICATION
 */
?>
<form class="needs-validation js-independent_experts" novalidate="" style="" id="js-independent_experts" action="<?=$APPLICATION->GetCurPage()?>" method="POST" autocomplete="Off">
    <input type="hidden" name="independent_experts" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_independent_experts">Протокол оценки кандидата<span class="text-danger">*</span>:</label>
            <div class="col-sm-10 py-2">
       <!--         <input class="form-control" type="file" name="PROTOKOL" value="" id="PROTOKOL" required="required"/>-->
                <?$APPLICATION->IncludeComponent("bitrix:main.file.input", "drag_n_drop",
                      array(
                        "INPUT_NAME"=>"PROTOKOL",
                        "MULTIPLE"=>"N",
                        "MODULE_ID"=>"main",
                        "MAX_FILE_SIZE"=>"",
                        "ALLOW_UPLOAD"=>"A",
                        "ALLOW_UPLOAD_EXT"=>""
                    ),
                    false
                );?>
            </div>
        </div>
		<div class="row">
            <div class="col-12 col-sm-2">
                <div class="text-right mb-3">
                    <button class="btn btn-primary btn-block" type="submit">Далее &rarr;</button>
                </div>
            </div>
        </div>
    </div>
</form>