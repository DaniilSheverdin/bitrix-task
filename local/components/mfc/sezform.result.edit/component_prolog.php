<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?><?

if (!empty($_REQUEST['WEB_FORM_ID'])) {
    $obForm = CForm::GetByID($_REQUEST['WEB_FORM_ID']);
    if ($obForm->SelectedRowsCount() > 0) {
        $arFormData = $obForm->GetNext();
    }
}

$arForm = $arQuestions = $arAnswers = $arDropdown = $arMultiselect = [];
CForm::GetDataByID($arFormData['ID'], $arForm, $arQuestions, $arAnswers, $arDropdown, $arMultiselect, 'Y');

if (!empty($arQuestions)
    && in_array('ADMIN_NOTE', array_keys($arQuestions))
) {
    $charRights = (CForm::GetPermission($_REQUEST['WEB_FORM_ID']) >= 20) ? 'Y' : 'N';
    $arParams['EDIT_ADDITIONAL'] = $charRights;
    $arParams['EDIT_STATUS'] = $charRights;
}