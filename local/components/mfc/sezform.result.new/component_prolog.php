<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?><?

if (!empty($_POST['WEB_FORM_ID'])) {
    $obForm = CForm::GetByID($_POST['WEB_FORM_ID']);
    if ($obForm->SelectedRowsCount() > 0) {
        $arFormData = $obForm->Fetch();
    }
}

if (isset($arFormData['SID']) && in_array($arFormData['SID'], ['GETTING_EXPERT_HELP_gi'])) {
    $res = CFormField::GetList($_REQUEST['WEB_FORM_ID'], 'ALL', $by, $order, [], $filt);

    while ($arField = $res->Fetch()) {
        if ($arField['SID'] == 'TYPE') {
            $arFieldID = $arField['ID'];
        }

        if ($arField['SID'] == 'EXPERT_EMAIL') {
            $arFieldEMAIL = $arField['ID'];
        }
    }

    if (!empty($arFieldID)) {
        $objAnswerVals = CFormAnswer::GetList($arFieldID, $by, $order, [], $is_filtered);
        $arAnswers = [];
        while ($arAnswer = $objAnswerVals->Fetch()) {
            $arAnswers[$arAnswer['ID']] = $arAnswer['VALUE'];
        }
    }

    if (!empty($arFieldEMAIL)) {
        $objAnswerEmail = CFormAnswer::GetList($arFieldEMAIL, $by, $order, [], $is_filtered);
        $arAnswerEmail = $objAnswerEmail->Fetch();
        if (!empty($arAnswerEmail)) {
            $strIndexEmail = 'form_'.$arAnswerEmail['FIELD_TYPE'].'_'.$arAnswerEmail['ID'];
        }
    }

    $obUser = new CUser();
    $arUserData = $obUser->GetByID($arAnswers[$_REQUEST['form_dropdown_TYPE']])->Fetch();

    $_REQUEST[$strIndexEmail] = $arUserData['EMAIL'];
}