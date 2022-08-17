<?php

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Система электронных заявок");

if (SITE_TEMPLATE_ID == "bitrix24") {
    $html = '<div class="sidebar-buttons"><a href="/gusc/services/requests/my.php" class="sidebar-button">
            <span class="sidebar-button-top"><span class="corner left"></span><span class="corner right"></span></span>
            <span class="sidebar-button-content"><span class="sidebar-button-content-inner"><i class="sidebar-button-create"></i><b>Мои заявки</b></span></span>
            <span class="sidebar-button-bottom"><span class="corner left"></span><span class="corner right"></span></span></a></div>';
    $APPLICATION->AddViewContent("sidebar", $html);
}

CModule::IncludeModule('intranet');

$mayCreate = $GLOBALS['USER']->IsAdmin();
$curUserId = $GLOBALS['USER']->GetID();
$mayCreateIB = $GLOBALS['USER']->IsAdmin();

/*
 * Руководители отделений МФЦ
 */
$arSkip = [58, 2698];
$arSpecialDeps = CIntranetUtils::GetDeparmentsTree(2698, true);
$arSkip = array_merge($arSkip, $arSpecialDeps);
$arDeps = array_diff(\Citto\gusc::getDepartmentList(), $arSkip);
$arManagers = array_keys(CIntranetUtils::GetDepartmentManager($arDeps));
if (in_array($curUserId, $arManagers)) {
    $mayCreate = true;
} else {
    if (CSite::InGroup([1, 123, 124, 125, 126, 132, 133])) {
        $mayCreate = true;
    } else {
    	$mayCreateIB = false;
        $arguscDepartments = Citto\gusc::getDepartmentList();
        $orm = Bitrix\Main\UserTable::getList([
            'select'    => ['ID', 'WORK_POSITION', 'UF_DEPARTMENT'],
            'filter'    => ['ACTIVE' => 'Y']
        ]);
        while ($arUser = $orm->fetch()) {
            $arDiff = array_intersect($arUser['UF_DEPARTMENT'], $arguscDepartments);
            if (!empty($arDiff)) {
                if ($curUserId == $arUser['ID']) {
                    $arDiff2 = array_intersect($arUser['UF_DEPARTMENT'], $arSpecialDeps);
                    if (!empty($arDiff2)) {
                        /*
                         * Все сотрудники Уполномоченного МФЦ.
                         */
                        $mayCreate = true;
                        break;
                    } else {
                        if (false !== mb_stripos($arUser['WORK_POSITION'], 'ведущий')) {
                            /*
                             * Ведущие специалисты отделений МФЦ.
                             */
                            $mayCreate = true;
                            break;
                        }
                    }
                }
            }
        }
    }
}

?><p>Для оформления заявки на услугу выберите вид заявки, а затем заполните специальную форму.</p>

<?if ($mayCreate) : ?><?endif;?><?if ($mayCreateIB) : ?><?endif;?><?/*
        <tr>
            <td colspan="5">
                <br /><br />
            </td>
        </tr>

        <tr>
            <td colspan="5">
                <b>Для руководителей</b>
                <br /><br />
            </td>
        </tr>

        <tr>
            <td align="center">
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=HR_REQUEST_gi">
                    <img hspace="5" height="70" border="0" width="70" vspace="5" title="Подбор персонала " alt="Подбор персонала " src="/gusc/images/ru/requests/person.jpg" />
                </a>
                <br /><br />
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=HR_REQUEST_gi">
                    Подбор<br />персонала
                </a>
            </td>

            <td colspan="4">
                <br /><br />
            </td>
        </tr>

        <tr>
            <td colspan="5">
                <br /><br />
            </td>
        </tr>

        <tr>
            <td colspan="5">
                <b>Разное</b>
                <br /><br />
            </td>
        </tr>

        <tr>
            <td align="center">
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=MOBIL_GROUP">
                    <img hspace="5" height="70" border="0" width="70" vspace="5" title="Мобильная группа " alt="Мобильная группа " src="/gusc/images/ru/requests/mobile.jpg" />
                </a>
                <br /><br />
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=MOBIL_GROUP">
                    Мобильная<br />группа
                </a>
            </td>

            <td colspan="4">
                <br /><br />
            </td>
        </tr>
        */?><table cellspacing="0" cellpadding="3" width="100%">
    <tbody>
        
        <tr>
            <td colspan="5">
                <b>Запрос материалов и услуг</b>
                <br><br>
            </td>
        </tr>

        <tr>
            <td align="center" width="20%">
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=COURIER_DELIVERY_gi">
                    <img width="70" alt="Курьерская доставка" src="/gusc/images/ru/requests/package.jpg" height="70" hspace="5" border="0" vspace="5" title="Курьерская доставка">
                </a>
                <br><br>
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=COURIER_DELIVERY_gi">
                    Курьерская<br>доставка
                </a>
            </td>

            <td align="center" width="20%">
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=OFFICE_SUPPLIES_gi">
                    <img width="70" alt="Заказ канцелярских товаров" src="/gusc/images/ru/requests/kanstov.jpg" height="70" hspace="5" border="0" vspace="5" title="Заказ канцелярских товаров">
                </a>
                <br><br>
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=OFFICE_SUPPLIES_gi">
                    Канц. товары<br>и расходные материалы
                </a>
            </td>

            <td align="center" width="20%">
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=OFFICE_SUPPLIES_gi_po4d2">
                    <img width="70" alt="Заказ расходных материалов" src="/gusc/images/ru/requests/kanstov.jpg" height="70" hspace="5" border="0" vspace="5" title="Заказ расходных материалов">
                </a>
                <br>
                <br>
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=OFFICE_SUPPLIES_gi_po4d2">
                    Основные<br>средства
                </a>
            </td>

            <td align="center" width="20%">
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=CARTRIDGE_gi">
                    <img width="70" alt="Заказ картриджей для принтеров и МФУ" src="/gusc/images/ru/requests/kanstov.jpg" height="70" hspace="5" border="0" vspace="5" title="Заказ картриджей для принтеров и МФУ">
                </a>
                <br>
                <br>
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=CARTRIDGE_gi">
                    Заказ картриджей<br>для принтеров и МФУ
                </a>
            </td>
            <td align="center" width="20%">
                &nbsp;
            </td>
        </tr>
        

        <tr>
            <td colspan="5">
                <br><br>
                <b>Устранение неполадок</b>
                <br><br>
            </td>
        </tr>

        <tr>
            <td align="center">
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=ADM_TROUBLESHOOTING_gi">
                    <img width="70" alt="Хозяйственная служба" src="/gusc/images/ru/requests/tool.jpg" height="70" hspace="5" border="0" vspace="5" title="Хозяйственная служба">
                </a>
                <br><br>
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=ADM_TROUBLESHOOTING_gi">
                    Хозяйственная<br>служба
                </a>
            </td>

            <td colspan="4">
                <br><br>
            </td>
        </tr>

        <tr>
            <td colspan="5">
                <br><br>
                <b>Организация работы</b>
                <br><br>
            </td>
        </tr>

        <tr>
            <td align="center" width="20%">
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=GETTING_EXPERT_HELP_gi">
                    <img width="70" alt="Получение экспертной помощи" src="/gusc/images/ru/requests/question.jpg" height="70" hspace="5" border="0" vspace="5" title="Получение экспертной помощи">
                </a>
                <br>
                <br>
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=GETTING_EXPERT_HELP_gi">
                    Получение<br> экспертной помощи
                </a>
            </td>
 <td align="center" width="20%">
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=GETTING_INFORMATION_SECUR_Q6D14">
                    <img width="70" alt="Изменение услуг в АИС МФЦ" src="/gusc/images/ru/requests/question.jpg" height="70" hspace="5" border="0" vspace="5" title="Изменение услуг в АИС МФЦ">
                </a>
                <br>
                <br>
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=GETTING_INFORMATION_SECUR_Q6D14">
                    Изменение<br>услуг в АИС МФЦ
                </a>
            </td>
            <td colspan="3"><br><br>
            </td>
        </tr>

        
        <tr>
            <td colspan="5">
                <br><br>
                <b>Служба информационной безопасности</b>
                <br><br>
            </td>
        </tr>

        <tr>
            <td align="center" width="20%">
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=GETTING_INFORMATION_SECURITY_gi">
                    <img width="70" alt="Заявка в службу информационной безопасности" src="/gusc/images/ru/requests/inform_b.jpg" height="70" hspace="5" border="0" vspace="5" title="Заявка в службу информационной безопасности">
                </a>
                <br>
                <br>
                <a href="/gusc/services/requests/form.php?WEB_FORM_ID=GETTING_INFORMATION_SECURITY_gi">
                    Заявка в службу<br> информационной безопасности
                </a>
            </td>

            <td colspan="4">
                <br><br>
            </td>
        </tr>
        

		
    </tbody>
</table><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>