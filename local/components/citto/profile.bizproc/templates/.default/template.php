<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

use Bitrix\Main\Page;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;

$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'no-all-paddings no-background');
\Bitrix\Main\UI\Extension::load(array("ui.buttons", "ui.alerts", "ui.tooltip", "ui.hint"));
\CJSCore::Init("loader");

$this->SetViewTarget('inside_pagetitle');
$arPopupOptions = [
    'width' => 950,
    'allowChangeHistory' => false,
    'cacheable' => false,
    'requestMethod' => 'post'
];
?>
<span
    onclick='BX.SidePanel.Instance.open("<?=$APPLICATION->GetCurPageParam('create=1', ['create']);?>", <?=Json::encode($arPopupOptions)?>);'
    class="ui-btn ui-btn-light-border ui-btn-themes"
>
    Новый бизнес-процесс
</span>
<?
$this->EndViewTarget();
$selectedTab = 'processes';
if (isset($_REQUEST['type'])) {
    $selectedTab = $_REQUEST['type'];
}

?>
<div class="my-process-container">
	<div>
        <?
        $gridOptions = new CGridOptions('bizproc_task_list');
        $gridOptions_options = $gridOptions->GetOptions();
        if (
            empty($gridOptions_options['filter_rows'])
            || $gridOptions_options['filter_rows'] != "WORKFLOW_TEMPLATE_ID,USER_STATUS,NAME,DESCRIPTION,MODIFIED"
            || empty($gridOptions_options['views']['default']['columns'])
            || $gridOptions_options['views']['default']['columns'] != "WORKFLOW_STARTED,MODIFIED,WORKFLOW_TEMPLATE_NAME,DESCRIPTION,COMMENTS,WORKFLOW_PROGRESS,NAME"
        ) {
            $gridOptions->SetFilterRows("WORKFLOW_TEMPLATE_ID,USER_STATUS,NAME,DESCRIPTION,MODIFIED", "");
            $gridOptions->SetFilterSwitch("Y");
            $gridOptions->SetColumns("WORKFLOW_STARTED,MODIFIED,WORKFLOW_TEMPLATE_NAME,DESCRIPTION,COMMENTS,WORKFLOW_PROGRESS,NAME");
            $gridOptions->Save();
        }
        $APPLICATION->IncludeComponent(
            "bitrix:bizproc.task.list",
            "",
            array(
                'USER_ID' => $arParams['USER_ID'],
                'SET_NAV_CHAIN'=>"N",
                'SET_TITLE'=>"N",
            ),
            $component,
            array("HIDE_ICONS" => "Y")
        );
        ?>
    </div>
    <div class="my_processes <?=($selectedTab=='my_processes'?'trigger-click':'');?>" style="display:none">
        <?
        $f_options = new \Bitrix\Main\UI\Filter\Options("lists_processes", [], "");
        $default_filter = $f_options->getFilterSettings("default_filter");
        if (empty($default_filter['filter_rows']) || $default_filter['filter_rows'] != "NAME,TIMESTAMP_X,DATE_CREATE") {
            $f_options->setFilterSettings('default_filter', [
                'rows'=>"NAME,TIMESTAMP_X,DATE_CREATE"
            ]);
            $f_options->save();
        }
        $gridOptions = new CGridOptions('lists_processes');
        $gridOptions_options = $gridOptions->GetOptions();
        if (
            empty($gridOptions_options['views']['default']['columns'])
            || $gridOptions_options['views']['default']['columns'] != "DOCUMENT_NAME,COMMENTS,WORKFLOW_PROGRESS,WORKFLOW_STATE"
        ) {
            $gridOptions->SetColumns("DOCUMENT_NAME,COMMENTS,WORKFLOW_PROGRESS,WORKFLOW_STATE");
            $gridOptions->Save();
        }
        
        $APPLICATION->IncludeComponent(
            "bitrix:lists.user.processes",
            "",
            array(
                'USER_ID' => $arParams['USER_ID'],
                'TASK_EDIT_URL' => '/company/personal/bizproc/#ID#/',
                'PATH_TO_PROCESSES' => '/company/personal/processes/',
                'PATH_TO_LIST_ELEMENT' => null,
                'SET_TITLE' => 'Y',
            ),
            $component,
            array("HIDE_ICONS" => "Y")
        );
        ?>
    </div>
</div>