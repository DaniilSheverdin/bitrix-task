<<<<<<< HEAD

<?
if(!$arResult['CAN_EDIT_USER']) return;
?>
<div class="user-profile-block-wrap-cont user-profile-block-wrap-cont-pr" style="clear: left;">
    <div>
        <?
        $gridOptions = new CGridOptions('bizproc_task_list');
        $gridOptions_options = $gridOptions->GetOptions();
        if(
                empty($gridOptions_options['filter_rows'])
                || $gridOptions_options['filter_rows'] != "WORKFLOW_TEMPLATE_ID,USER_STATUS,NAME,DESCRIPTION,MODIFIED"
                || empty($gridOptions_options['views']['default']['columns'])
                || $gridOptions_options['views']['default']['columns'] != "WORKFLOW_STARTED,MODIFIED,WORKFLOW_TEMPLATE_NAME,DESCRIPTION,COMMENTS,WORKFLOW_PROGRESS,NAME"
            ){
            $gridOptions->SetFilterRows("WORKFLOW_TEMPLATE_ID,USER_STATUS,NAME,DESCRIPTION,MODIFIED", "");
            $gridOptions->SetFilterSwitch("Y");
            $gridOptions->SetColumns("WORKFLOW_STARTED,MODIFIED,WORKFLOW_TEMPLATE_NAME,DESCRIPTION,COMMENTS,WORKFLOW_PROGRESS,NAME");
            $gridOptions->Save();
        }
        $APPLICATION->IncludeComponent(
            "bitrix:bizproc.task.list",
            "lk",
            Array(
                'USER_ID' => $arParams['ID'],
                'SET_NAV_CHAIN'=>"N",
                'SET_TITLE'=>"N",
            ),
            $component,
            array("HIDE_ICONS" => "Y")
        );
        ?>
    
    </div>
    <div class="my_processes" style="display:none">
        <?
        $f_options = new \Bitrix\Main\UI\Filter\Options("lists_processes",[],"");
        $default_filter = $f_options->getFilterSettings("default_filter");
        if(empty($default_filter['filter_rows']) || $default_filter['filter_rows'] != "NAME,TIMESTAMP_X,DATE_CREATE"){
            $f_options->setFilterSettings('default_filter', [
                'rows'=>"NAME,TIMESTAMP_X,DATE_CREATE"
            ]);
            $f_options->save();
        }
        $gridOptions = new CGridOptions('lists_processes');
        $gridOptions_options = $gridOptions->GetOptions();
        if(
                empty($gridOptions_options['views']['default']['columns'])
                || $gridOptions_options['views']['default']['columns'] != "DOCUMENT_NAME,COMMENTS,WORKFLOW_PROGRESS,WORKFLOW_STATE"
            ){
            $gridOptions->SetColumns("DOCUMENT_NAME,COMMENTS,WORKFLOW_PROGRESS,WORKFLOW_STATE");
            $gridOptions->Save();
        }
        
        $APPLICATION->IncludeComponent(
            "bitrix:lists.user.processes",
            "lk",
            Array(
                'USER_ID' => $arParams['ID'],
                'TASK_EDIT_URL' => '/company/personal/bizproc/#ID#/',
                'PATH_TO_PROCESSES' => '/company/personal/processes/',
                'PATH_TO_LIST_ELEMENT' => NULL,
                'SET_TITLE' => 'Y',
            ),
            $component, array("HIDE_ICONS" => "Y")
        );
        ?>
    </div>
=======

<?
if(!$arResult['CAN_EDIT_USER']) return;
?>
<div class="user-profile-block-wrap-cont user-profile-block-wrap-cont-pr" style="clear: left;">
    <div>
        <?
        $gridOptions = new CGridOptions('bizproc_task_list');
        $gridOptions_options = $gridOptions->GetOptions();
        if(
                empty($gridOptions_options['filter_rows'])
                || $gridOptions_options['filter_rows'] != "WORKFLOW_TEMPLATE_ID,USER_STATUS,NAME,DESCRIPTION,MODIFIED"
                || empty($gridOptions_options['views']['default']['columns'])
                || $gridOptions_options['views']['default']['columns'] != "WORKFLOW_STARTED,MODIFIED,WORKFLOW_TEMPLATE_NAME,DESCRIPTION,COMMENTS,WORKFLOW_PROGRESS,NAME"
            ){
            $gridOptions->SetFilterRows("WORKFLOW_TEMPLATE_ID,USER_STATUS,NAME,DESCRIPTION,MODIFIED", "");
            $gridOptions->SetFilterSwitch("Y");
            $gridOptions->SetColumns("WORKFLOW_STARTED,MODIFIED,WORKFLOW_TEMPLATE_NAME,DESCRIPTION,COMMENTS,WORKFLOW_PROGRESS,NAME");
            $gridOptions->Save();
        }
        $APPLICATION->IncludeComponent(
            "bitrix:bizproc.task.list",
            "lk",
            Array(
                'USER_ID' => $arParams['ID'],
                'SET_NAV_CHAIN'=>"N",
                'SET_TITLE'=>"N",
            ),
            $component,
            array("HIDE_ICONS" => "Y")
        );
        ?>
    
    </div>
    <div class="my_processes" style="display:none">
        <?
        $f_options = new \Bitrix\Main\UI\Filter\Options("lists_processes",[],"");
        $default_filter = $f_options->getFilterSettings("default_filter");
        if(empty($default_filter['filter_rows']) || $default_filter['filter_rows'] != "NAME,TIMESTAMP_X,DATE_CREATE"){
            $f_options->setFilterSettings('default_filter', [
                'rows'=>"NAME,TIMESTAMP_X,DATE_CREATE"
            ]);
            $f_options->save();
        }
        $gridOptions = new CGridOptions('lists_processes');
        $gridOptions_options = $gridOptions->GetOptions();
        if(
                empty($gridOptions_options['views']['default']['columns'])
                || $gridOptions_options['views']['default']['columns'] != "DOCUMENT_NAME,COMMENTS,WORKFLOW_PROGRESS,WORKFLOW_STATE"
            ){
            $gridOptions->SetColumns("DOCUMENT_NAME,COMMENTS,WORKFLOW_PROGRESS,WORKFLOW_STATE");
            $gridOptions->Save();
        }
        
        $APPLICATION->IncludeComponent(
            "bitrix:lists.user.processes",
            "lk",
            Array(
                'USER_ID' => $arParams['ID'],
                'TASK_EDIT_URL' => '/company/personal/bizproc/#ID#/',
                'PATH_TO_PROCESSES' => '/company/personal/processes/',
                'PATH_TO_LIST_ELEMENT' => NULL,
                'SET_TITLE' => 'Y',
            ),
            $component, array("HIDE_ICONS" => "Y")
        );
        ?>
    </div>
>>>>>>> e0a0eba79 (init)
</div>