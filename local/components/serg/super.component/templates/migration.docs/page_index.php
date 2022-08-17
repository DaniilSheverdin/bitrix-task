<?if ($arResult['ACCESS']) :?>
<div class="button_container">
    <div class="button_item">
        <a class="ui-btn ui-btn-primary"
           href="<?echo $APPLICATION->GetCurPageParam("PAGE=RF_TABLE", array('PAGE', 'TYPE', 'q'));?>">
            <?=GetMessage('BUTTON_RF_TABLE')?>
        </a>
    </div>
    <div class="button_item">
        <a class="ui-btn ui-btn-primary"
           href="<?echo $APPLICATION->GetCurPageParam("PAGE=MZH_TABLE", array('PAGE', 'TYPE', 'q'));?>">
            <?=GetMessage('BUTTON_MZH_TABLE')?>
        </a>
    </div>
    <div class="button_item">
        <a class="ui-btn ui-btn-primary"
           href="<?echo $APPLICATION->GetCurPageParam("PAGE=MP_TABLE", array('PAGE', 'TYPE', 'q'));?>">
            <?=GetMessage('BUTTON_MP_TABLE')?>
        </a>
    </div>
    <div class="button_item">
        <a class="ui-btn ui-btn-primary"
           href="<?echo $APPLICATION->GetCurPageParam("PAGE=CONT_TABLE", array('PAGE', 'TYPE', 'q'));?>">
            <?=GetMessage('BUTTON_CONT_TABLE')?>
        </a>
    </div>
    <div class="button_item">
        <a class="ui-btn ui-btn-primary"
           href="<?echo $APPLICATION->GetCurPageParam("PAGE=COMING_TABLE", array('PAGE', 'TYPE', 'q'));?>">
            <?=GetMessage('BUTTON_COMING_TABLE')?>
        </a>
    </div>
    <div class="button_item">
        <a class="ui-btn ui-btn-primary"
           href="<?echo $APPLICATION->GetCurPageParam("PAGE=VIOLATORS_TABLE", array('PAGE', 'TYPE', 'q'));?>">
            <?=GetMessage('BUTTON_VIOLATORS_TABLE')?>
        </a>
    </div>
    <div class="button_item">
        <a class="ui-btn ui-btn-primary"
           href="<?echo $APPLICATION->GetCurPageParam("PAGE=VIOLATORS_CCMIS_TABLE", array('PAGE', 'TYPE', 'q'));?>">
            <?=GetMessage('BUTTON_VIOLATORS_CCMIS_TABLE')?>
        </a>
    </div>
		<div class="button_item">
				<a class="ui-btn ui-btn-primary"
				   href="<?echo $APPLICATION->GetCurPageParam("PAGE=ARRIVED_TABLE", array('PAGE', 'TYPE', 'q'));?>">
						<?=GetMessage('BUTTON_ARRIVED_TABLE')?>
				</a>
		</div>

</div>
<div class="statistic">
        <div class="page_title">
            <span>Сводная статистика</span>
        </div>

        <p>Всего постановлений: <b><?=$arResult['FULL']['ALL']?></b></p>
        <p>Всего приезжих: <b><?=count($arResult['FULL']['SECTIONS'][SECTION_ID_MIGRATION_DOCS_MZH])+count($arResult['FULL']['SECTIONS'][SECTION_ID_MIGRATION_DOCS_MP])?></b></p>
        <p>Всего контактирующих: <b><?=count($arResult['FULL']['SECTIONS'][SECTION_ID_MIGRATION_CONT])?></b></p>
        <p>Выдано постановлений: <b><?=$arResult['FULL']['RESOLUT']?></b></p>
        <p>Постановлений в работе: <b><?=$arResult['FULL']['ALL']-$arResult['FULL']['RESOLUT']?></b></p>
        <p>На карантине: <b><?=$arResult['FULL']['QUARANTINE']?></b></p>
        <p>Вышло из карантина: <b><?=$arResult['FULL']['FREE']?></b></p>
    </div>

        <div class="statistic">
        <div class="page_title">
            <span>Статистика по городам ТО:</span>
        </div>
        <table width="100%" class="table table-striped">
        <thead>
            <th>Город</th>
            <th>Поостановлений</th>
            <th>Приезжих</th>
            <th>Контактирующих</th>
            <th>Выдано</th>
            <th>В работе</th>
            <th>На карантине</th>
            <th>Вышло</th>
        </thead>
        <tbody>
            <?
            foreach ($arResult['STATS'] as $sKey => $arCity) {?>
                <tr>
                    <td><?=explode(',', $arResult['CITIES'][$sKey])[0]?></td>
                    <td><?=$arCity['FULL_CNT']?></td>
                    <td><?=$arCity['SECTIONS'][SECTION_ID_MIGRATION_DOCS_MZH]+$arCity['SECTIONS'][SECTION_ID_MIGRATION_DOCS_MP]?></td>
                    <td><?=intval($arCity['SECTIONS'][SECTION_ID_MIGRATION_CONT])?></td>
                    <td><?=$arCity['RESOLUT']?></td>
                    <td><?=(intval($arCity['FULL_CNT']-$arCity['RESOLUT'])>0)?intval($arCity['FULL_CNT']-$arCity['RESOLUT']):'0';?></td>
                    <td><?=$arCity['QUARANTINE']?></td>
                    <td><?=$arCity['FREE']?></td>
                </tr>

            <?
            }
            ?>
            <tr>
                <td>Другие</td>
                    <td><?=$arResult['OTHERS']['FULL_CNT']?></td>
                    <td><?=$arResult['OTHERS']['SECTIONS'][SECTION_ID_MIGRATION_DOCS_MZH]+$arResult['OTHERS']['SECTIONS'][SECTION_ID_MIGRATION_DOCS_MP]?></td>
                    <td><?=intval($arResult['OTHERS']['SECTIONS'][SECTION_ID_MIGRATION_CONT])?></td>
                    <td><?=$arResult['OTHERS']['RESOLUT']?></td>
                    <td><?=(intval($arResult['OTHERS']['FULL_CNT']-$arResult['OTHERS']['RESOLUT'])>0)?intval($arResult['OTHERS']['FULL_CNT']-$arResult['OTHERS']['RESOLUT']):'0';?></td>
                    <td><?=$arResult['OTHERS']['QUARANTINE']?></td>
                    <td><?=$arResult['OTHERS']['FREE']?></td>
            </tr>
            <tr>
                <td><b>ИТОГО</b></td>
                    <td><?=$arResult['FULL']['ALL']?></td>
                    <td><?=count($arResult['FULL']['SECTIONS'][SECTION_ID_MIGRATION_DOCS_MZH])+count($arResult['FULL']['SECTIONS'][SECTION_ID_MIGRATION_DOCS_MP])?></td>
                    <td><?=intval(count($arResult['FULL']['SECTIONS'][SECTION_ID_MIGRATION_CONT]))?></td>
                    <td><?=$arResult['FULL']['RESOLUT']?></td>
                    <td><?=$arResult['FULL']['ALL']-$arResult['FULL']['RESOLUT']?></td>
                    <td><?=$arResult['FULL']['QUARANTINE']?></td>
                    <td><?=$arResult['FULL']['FREE']?></td>
            </tr>
        </tbody>
        </table>
        </div>
<?else :
    echo GetMessage('ACCESS_ERROR');
endif;
