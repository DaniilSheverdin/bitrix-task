<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$obItilium = new Citto\Integration\Itilium();
$obTask = new Citto\Integration\Itilium\Task();
$obIncident = new Citto\Integration\Itilium\Incident();

$arTaskStatuses = $obTask->getStatuses();
$arIncidentStatuses = $obIncident->getStatuses();

$arBusinessServices = $obItilium->getBusinessServices();
$arPriorities = $obItilium->getPriorities();
$arTechnicalServices = $obItilium->getTechnicalServices();

function buildSorter(string $key): callable
{
    return static function ($a, $b) use ($key) {
        return strnatcmp($a[ $key ], $b[ $key ]);
    };
}

uasort($arBusinessServices, buildSorter('Name'));
uasort($arTechnicalServices, buildSorter('Name'));

?>
<h1 style="color:red; text-align: center;">Идёт тестирование активити, не запускать!</h1><br/><br/><br/><br/>
<?/*
<tr>
    <td align="right" width="40%" valign="top">Создаём:</td>
    <td width="60%" valign="top">
        <select name="TYPE">
            <option value="TASK"<?= ("TASK" == $arCurrentValues["TYPE"] ? ' selected' : '') ?>>Задачу (наряд)</option>
            <option value="INCIDENT"<?= ("INCIDENT" == $arCurrentValues["TYPE"] ? ' selected' : '') ?>>Инцидент (обращение)</option>
        </select>
    </td>
</tr>
*/?>
<tr>
    <td align="right" width="40%" valign="top">Создавать задачу:</td>
    <td width="60%" valign="top">
        <select name="CREATE_TASK">
            <option value="Y"<?= ("Y" == $arCurrentValues["CREATE_TASK"] ? ' selected' : '') ?>><?= GetMessage("BPCGHLP_YES") ?></option>
            <option value="N"<?= ("N" == $arCurrentValues["CREATE_TASK"] ? ' selected' : '') ?>><?= GetMessage("BPCGHLP_NO") ?></option>
        </select>
    </td>
</tr>
<tr>
    <td align="right" width="40%" valign="top">Останавливать процесс:</td>
    <td width="60%" valign="top">
        <select name="HOLD_TO_CLOSE">
            <option value="Y"<?= ("Y" == $arCurrentValues["HOLD_TO_CLOSE"] ? ' selected' : '') ?>><?= GetMessage("BPCGHLP_YES") ?></option>
            <option value="N"<?= ("N" == $arCurrentValues["HOLD_TO_CLOSE"] ? ' selected' : '') ?>><?= GetMessage("BPCGHLP_NO") ?></option>
        </select>
    </td>
</tr>
<?/*
<tr class="NO-Show-select Show-TASK">
    <td align="right" width="40%" valign="top">
        <span class="adm-required-field">При каких статусах задачи завершать выполнение</span>:
    </td>
    <td width="60%" valign="top">
        <select name="TASK_STATUS[]" multiple required>
            <? foreach ($arTaskStatuses as $status) : ?>
                <option value="<?=$status['UID']?>" <?=(in_array($status['UID'], $arCurrentValues["TASK_STATUS"])?'selected':'')?>>
                    <?=($status['Final'] ? '[Финальный]' : '')?>
                    <?=$status['Name']?>
                </option>
            <? endforeach; ?>
        </select>
    </td>
</tr>
*/?>
<?/*
<tr class="Show-select Show-INCIDENT">
    <td align="right" width="40%" valign="top">
        <span class="adm-required-field">При каких статусах инцидента завершать выполнение</span>:
    </td>
    <td width="60%" valign="top">
        <select name="INCIDENT_STATUS[]" multiple required>
            <? foreach ($arIncidentStatuses as $status) : ?>
                <option value="<?=$status['UID']?>" <?=(in_array($status['UID'], $arCurrentValues["INCIDENT_STATUS"])?'selected':'')?>><?=$status['Name']?></option>
            <? endforeach; ?>
        </select>
    </td>
</tr>
*/?>
<tr class="NO-Show-select Show-INCIDENT">
    <td align="right" width="40%" valign="top">
        <span class="adm-required-field">Бизнес-услуга</span>:
    </td>
    <td width="60%" valign="top">
        <select name="BUSINESS_SERVICE" required>
            <? foreach ($arBusinessServices as $service) : ?>
                <option value="<?=$service['UID']?>" <?=($service['UID']==$arCurrentValues["BUSINESS_SERVICE"]?'selected':'')?>><?=$service['Name']?></option>
            <? endforeach; ?>
        </select><br/>
        <? foreach ($arBusinessServices as $service) : ?>
        <select name="BUSINESS_SERVICE_COMPONENT" class="BusinessComponent-select" id="Component-<?=$service['UID']?>" required>
            <?
            if (!isset($service['ServiceComponents']['ServiceComponent'][0])) {
                $service['ServiceComponents']['ServiceComponent'] = [$service['ServiceComponents']['ServiceComponent']];
            }
            uasort($service['ServiceComponents']['ServiceComponent'], buildSorter('Name'));
            ?>
            <? foreach ($service['ServiceComponents']['ServiceComponent'] as $subservice) : ?>
                <option value="<?=$subservice['UID']?>" <?=($subservice['UID']==$arCurrentValues["BUSINESS_SERVICE_COMPONENT"]?'selected':'')?>><?=$subservice['Name']?></option>
            <? endforeach; ?>
        </select>
        <? endforeach; ?>
    </td>
</tr>

<tr class="NO-Show-select Show-TASK">
    <td align="right" width="40%" valign="top">
        <span class="adm-required-field">Техническая услуга</span>:
    </td>
    <td width="60%" valign="top">
        <select name="TECH_SERVICE" required>
            <? foreach ($arTechnicalServices as $service) : ?>
                <option value="<?=$service['UID']?>" <?=($service['UID']==$arCurrentValues["TECH_SERVICE"]?'selected':'')?>><?=$service['Name']?></option>
            <? endforeach; ?>
        </select><br/>
        <? foreach ($arTechnicalServices as $service) : ?>
        <select name="TECH_SERVICE_COMPONENT" class="TechComponent-select" id="Component-<?=$service['UID']?>" required>
            <?
            if (!isset($service['ServiceComponents']['ServiceComponent'][0])) {
                $service['ServiceComponents']['ServiceComponent'] = [$service['ServiceComponents']['ServiceComponent']];
            }
            uasort($service['ServiceComponents']['ServiceComponent'], buildSorter('Name'));
            ?>
            <? foreach ($service['ServiceComponents']['ServiceComponent'] as $subservice) : ?>
                <option value="<?=$subservice['UID']?>" <?=($subservice['UID']==$arCurrentValues["TECH_SERVICE_COMPONENT"]?'selected':'')?>><?=$subservice['Name']?></option>
            <? endforeach; ?>
        </select>
        <? endforeach; ?>
    </td>
</tr>

<?

foreach ($arDocumentFields as $fieldKey => $fieldValue)
{
    if (
        ($fieldValue['UserField']['USER_TYPE']['USER_TYPE_ID'] === 'crm')
        && CModule::IncludeModule('crm')
    )
    {
        ?>
        <tr>
            <td align="right" width="40%" valign="top"><?= GetMessage("TASKS_BP_AUTO_LINK_TO_CRM_ENTITY") ?>:</td>
            <td width="60%" valign="top">
                <select name="AUTO_LINK_TO_CRM_ENTITY">
                    <option value="Y"<?= ("Y" == $arCurrentValues["AUTO_LINK_TO_CRM_ENTITY"] ? ' selected' : '') ?>><?= GetMessage("BPCGHLP_YES") ?></option>
                    <option value="N"<?= ("N" == $arCurrentValues["AUTO_LINK_TO_CRM_ENTITY"] ? ' selected' : '') ?>><?= GetMessage("BPCGHLP_NO") ?></option>
                </select>
            </td>
        </tr>
        <?php
    }
    ?>
    <tr>
        <td align="right" width="40%" valign="top"><?= ($fieldValue["Required"]) ? "<span class=\"adm-required-field\">".htmlspecialcharsbx($fieldValue["Name"])."</span>:" : htmlspecialcharsbx($fieldValue["Name"]) .":" ?></td>
        <td width="60%" id="td_<?= htmlspecialcharsbx($fieldKey) ?>" valign="top">
            <?
            if ($fieldValue["UserField"])
            {
                if ($arCurrentValues[$fieldKey])
                {
                    if ($fieldValue["UserField"]["USER_TYPE_ID"] == "boolean")
                    {
                        $fieldValue["UserField"]["VALUE"] = ($arCurrentValues[$fieldKey] == "Y" ? 1 : 0);
                    }
                    else
                    {
                        $fieldValue["UserField"]["VALUE"] = $arCurrentValues[$fieldKey];
                    }
                    $fieldValue["UserField"]["ENTITY_VALUE_ID"] = 1; //hack to not empty value
                }
                $userFieldTypes = [
                    \Bitrix\Bizproc\FieldType::STRING,
                    \Bitrix\Bizproc\FieldType::DOUBLE,
                    \Bitrix\Bizproc\FieldType::DATETIME,
                    'boolean'
                ];
                if(in_array($fieldValue['UserField']['USER_TYPE_ID'], $userFieldTypes)
                    && mb_substr($fieldValue['UserField']['FIELD_NAME'], 0, mb_strlen('UF_AUTO_')) === 'UF_AUTO_')
                {
                    $fieldType = $fieldValue['UserField']['USER_TYPE_ID'];
                    $fieldMap = [
                        'Name' => $fieldValue['Name'],
                        'FieldName' => $fieldValue['UserField']['FIELD_NAME'],
                        'Type' => $fieldType === 'boolean' ? 'bool' : $fieldType,
                        'Required' => $fieldValue['UserField']['MANDATORY'],
                        'Multiple' => $fieldValue['UserField']['MULTIPLE']
                    ];
                    $field = $dialog->getFieldTypeObject($fieldMap);
                    echo $field->renderControl(array(
                        'Form' => $dialog->getFormName(),
                        'Field' => $fieldMap['FieldName']
                    ), $dialog->getCurrentValue($fieldMap['FieldName']), true, 0);
                }
                else
                {
                    $GLOBALS["APPLICATION"]->IncludeComponent(
                        "bitrix:system.field.edit",
                        $fieldValue["UserField"]["USER_TYPE"]["USER_TYPE_ID"],
                        array(
                            "bVarsFromForm" => false,
                            "arUserField" => $fieldValue["UserField"],
                            "form_name" => $formName,
                            'SITE_ID' => $currentSiteId,
                        ), null, array("HIDE_ICONS" => "Y")
                    );
                    if ($fieldKey === "UF_TASK_WEBDAV_FILES" || $fieldKey === "UF_CRM_TASK")
                    {
                        $fieldMap = [
                            'FieldName' => $fieldValue['UserField']['FIELD_NAME'] . '_text',
                            'Type' => \Bitrix\Bizproc\FieldType::STRING,
                            'Required' => $fieldValue['MANDATORY'] === 'Y',
                            'Multiple' => $fieldValue['MULTIPLE'] === 'Y'
                        ];

                        $fieldType = $dialog->getFieldTypeObject($fieldMap);

                        echo $fieldType->renderControl(array(
                            'Form' => $dialog->getFormName(),
                            'Field' => $fieldMap['FieldName']
                        ), $dialog->getCurrentValue($fieldMap['FieldName']), true, 0);
                    }
                }
            }
            else
            {
                $fieldValueTmp = $arCurrentValues[$fieldKey];

                if($fieldKey == 'PRIORITY')
                {
                    $fieldValueTmp == CTasks::PRIORITY_HIGH ? CTasks::PRIORITY_HIGH : CTasks::PRIORITY_AVERAGE;
                }

                $fieldValueTextTmp = '';
                if (isset($arCurrentValues[$fieldKey . '_text']))
                    $fieldValueTextTmp = $arCurrentValues[$fieldKey . '_text'];

                switch ($fieldValue["Type"])
                {
                    case "S:UserID":
                        echo CBPDocument::ShowParameterField('user', $fieldKey, $fieldValueTmp, Array('rows' => 1));
                        break;
                    case "S:DateTime":
                        echo CBPDocument::ShowParameterField('datetime', $fieldKey, $fieldValueTmp);
                        break;
                    case "L":
                        ?>
                        <select id="id_<?= $fieldKey ?>" name="<?= $fieldKey ?>">
                            <?
                            foreach ($fieldValue["Options"] as $k => $v)
                            {
                                echo '<option value="'.htmlspecialcharsbx($k).'"'.($k."!" === $fieldValueTmp."!" ? ' selected' : '').'>'.htmlspecialcharsbx($v).'</option>';
                                if ($k."!" === $fieldValueTmp."!")
                                    $fieldValueTmp = "";
                            }
                            ?>
                        </select>
                        <?
                        echo CBPDocument::ShowParameterField("string", $fieldKey.'_text', $fieldValueTextTmp, Array('size'=> 30));
                        break;
                    case "B":
                        ?>
                        <select id="id_<?= $fieldKey ?>" name="<?= $fieldKey ?>">
                            <option value="Y"<?= ("Y" == $fieldValueTmp ? ' selected' : '') ?>><?= GetMessage("BPCGHLP_YES") ?></option>
                            <option value="N"<?= ("N" == $fieldValueTmp ? ' selected' : '') ?>><?= GetMessage("BPCGHLP_NO") ?></option>
                        </select>
                        <?
                        if (in_array($fieldValueTmp, array("Y", "N")))
                        {
                            $fieldValueTmp = "";
                        }
                        echo CBPDocument::ShowParameterField("string", $fieldKey.'_text', $fieldValueTextTmp, Array('size'=> 20));
                        break;
                    case "T":
                        echo CBPDocument::ShowParameterField("text", $fieldKey, $fieldValueTmp, ['rows'=> 7, 'cols' => 40]);
                        break;
                    default:
                        echo CBPDocument::ShowParameterField("string", $fieldKey, $fieldValueTmp, Array('size'=> 40));
                        break;
                }
            }
            ?>
        </td>
    </tr>
    <?php
}
?>
<link rel="stylesheet" type="text/css" href="/local/activities/custom/itiliumactivity/style.css?rnd=<?=time()?>" />
<script type="text/javascript" src="/bitrix/js/main/jquery/jquery-2.1.3.min.js"></script>
<script type="text/javascript" src="/local/activities/custom/itiliumactivity/script.js?rnd=<?=time()?>"></script>