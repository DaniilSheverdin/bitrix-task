<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($_GET['ACTION'] && $_GET['ACTION'] == "bp_stop"){
    \Bitrix\Main\Loader::includeModule("iblock");
    \Bitrix\Main\Loader::includeModule("bizproc");
	$GLOBALS['APPLICATION']->RestartBuffer();
	
    $resp = (object)['code'=>"error",'message'=>""];
    try{
        $DOCUMENT_ID	= isset($_GET['DOCUMENT_ID'])	? intVal($_GET['DOCUMENT_ID'])	:0;
        $WORKFLOW_ID	= isset($_GET['WORKFLOW_ID'])	? $_GET['WORKFLOW_ID']			:0;
        if($DOCUMENT_ID < 1 || empty($WORKFLOW_ID)) throw new Exception("Ошибка удаления");

        $arElem = CIBlockElement::GetByID($DOCUMENT_ID)->Fetch();
        if(empty($arElem)) throw new Exception("Процесс не найден");

        if(!CIBlockElementRights::UserHasRightTo($arElem['IBLOCK_ID'], $DOCUMENT_ID, "element_rights_edit")) throw new Exception("Нет прав на остановку процесса");
		
		try{
			$runtime = CBPRuntime::GetRuntime();
			$workflow = $runtime->GetWorkflow($WORKFLOW_ID, true);
			$workflow_document_id = $workflow->GetDocumentId();
			if($workflow_document_id[2] != $DOCUMENT_ID) throw new Exception("Процесс запущен не для этого документа");
			$workflow->Terminate();
		}catch(Exception $exc){
			if(!$exc->getMessage() == "Бизнес-процесс не найден") throw $exc;
		}
        $el = new CIBlockElement;
        $el->Update($DOCUMENT_ID, ['ACTIVE'=>"N"]);
        unset($el);
		// CIBlockElement::Delete($DOCUMENT_ID);
		
		$resp->code = "OK";
		$resp->message = "Удалено";
    }catch(Exception $exc){
        $resp->message = $exc->getMessage();
    }
    echo json_encode($resp);
    die;
}