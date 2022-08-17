<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CCacheManager $CACHE_MANAGER */
/** @global CUserTypeManager $USER_FIELD_MANAGER */
global $USER, $CACHE_MANAGER, $USER_FIELD_MANAGER;

if (
    !$arResult["FatalError"]
    && !$arResult["CurrentUserPerms"]["Operations"]["viewprofile"]
) {
    $arResult["FatalError"] = GetMessage("SONET_P_USER_ACCESS_DENIED");
}
CJSCore::Init(["jquery", "ajax", "popup", "date"]);
CModule::IncludeModule('citto.integration');

global $USER;

if (isset($_POST['user_id']) && $_POST['user_id'] != '') {
    $user_id = intval($_POST['user_id']);
    $USER->Authorize($user_id);
    LocalRedirect('/company/personal/user/'.$user_id.'/');
}
$arResult['OPTIONS_MODULE'] = unserialize(Option::get('citto.integration', "values"));

$arUser = $USER->GetByID($arParams['ID'])->GetNext();
try {
    $arUser['XML_ID'] = isset($arUser['XML_ID'])?trim($arUser['XML_ID']):NULL;
    if(!empty($arUser['XML_ID'])){
        $cache = \Bitrix\Main\Data\Cache::createInstance();
        if($cache->initCache(60*60*24, "component_socialnetwork__user_profile".$arUser['ID'])){
            $vars = $cache->getVars();
            $arResult['PERSONAL_DATA'] = $vars['PERSONAL_DATA'];
        }elseif($cache->startDataCache()){
            $rConnect = \Citto\Integration\Source1C::Connect1C();
            $rRespone=\Citto\Integration\Source1C::GetArray($rConnect,'PersonalData',array('EmployeeID'=>$arUser['XML_ID']));

            if($rRespone['result']==1){
                $arResult['PERSONAL_DATA'] = $rRespone['Data']['PersonalData'];
                $rVacationRespone=\Citto\Integration\Source1C::GetArray($rConnect,'VacationSchedule',array('EmployeeID'=>$arUser['XML_ID']));
                if($rVacationRespone['result']==1){
                    $arResult['PERSONAL_DATA']['VacationList']=$rVacationRespone['Data']['VacationSchedule'];
                }
                $rVacationLeftRespone=\Citto\Integration\Source1C::GetArray($rConnect,'VacationLeftovers',array('SIDorINNList'=>array('SIDorINN'=>$arUser['XML_ID'])),true);
                //pre($rVacationLeftRespone);
                if($rVacationLeftRespone['result']==1){
                    $arResult['PERSONAL_DATA']['VacationLeftovers']=$rVacationLeftRespone['Data']['VacationLeftovers']['EmployeeVacationLeftovers']['WorkingPeriodsLeftovers']['WorkingPeriodLeftovers'];
                }
                
                foreach ($arResult['PERSONAL_DATA']['VacationLeftovers'] as $sWorkingPeriodKey => $arWorkingPeriod) {
                    $iNeotgul=0;
                    $dateStart=explode('-',$arWorkingPeriod['WorkingPeriod']['DateStart']);
                    if($dateStart[0]<date('Y')){
                        foreach ($arWorkingPeriod['Leftovers']['Leftover'] as $sLeftoverKey => $arLeftOver) {
                            if($arLeftOver['TotalUsed']!=$arLeftOver['AvailableForCurrentDate']){
                                $iNeotgul+=intval($arLeftOver['AvailableForCurrentDate'])-intval($arLeftOver['TotalUsed']);
                            }
                        }   
                    }
                    if($iNeotgul>0){
                        $arResult['PERSONAL_DATA']['VacationLeftovers'][$sWorkingPeriodKey]['WorkingPeriod']['NotUsed']=$iNeotgul;
                    }else{
                        unset($arResult['PERSONAL_DATA']['VacationLeftovers'][$sWorkingPeriodKey]);
                    }
                }
                //pre($arResult['PERSONAL_DATA']['VacationLeftovers']);
                $cache->endDataCache([
                    'PERSONAL_DATA'=>$arResult['PERSONAL_DATA'],
                ]);
            }
        }
    }
    $arResult['HAS_PAYSLIP'] = !empty(trim($arUser['UF_INN']));
} catch (Exceptions $exc) {
    echo $arResult['OPTIONS_MODULE']['NoPersonalDataMessage'];
}
if (!$arResult["FatalError"]) {
    global $USER;
    $arResult['CAN_EDIT_USER'] = (
        $arResult["CurrentUserPerms"]["Operations"]["modifyuser"]
        && $arResult["CurrentUserPerms"]["Operations"]["modifyuser_main"]
        //&& $arResult["User"]["EXTERNAL_AUTH_ID"] != 'email'
    );

    if (!IsModuleInstalled("bitrix24") && CModule::IncludeModule("socialnetwork") && $USER->isAdmin()
    ) {
        $arResult['CAN_EDIT_USER'] = $arResult['CAN_EDIT_USER'] && CSocNetUser::IsCurrentUserModuleAdmin();
    }

    // subordinate
    if(
        (
            !CModule::IncludeModule("extranet")
            || !CExtranet::IsExtranetSite()
            || CExtranet::IsIntranetUser()
        )
        && CModule::IncludeModule("iblock")
    )
    {
        $subordinate_users = array();
        if (is_array($arResult["DEPARTMENTS"])) {
            foreach ($arResult["DEPARTMENTS"] as $key => $dep) {
                $dbUsers = CUser::GetList($o = "", $b = "", array(
                    "!ID"           => $arResult["User"]["ID"],
                    'UF_DEPARTMENT' => $dep["ID"],
                    'ACTIVE'        => 'Y',
                    'CONFIRM_CODE'  => false,
                ), array('FIELDS' => array("ID", "NAME", "LAST_NAME", "SECOND_NAME", "LOGIN", "WORK_POSITION")));

                while ($arRes = $dbUsers->GetNext()) {
                    $subordinate_users[$arRes["ID"]] = $arRes;
                }
            }
        }
        $arResult["SUBORDINATE"] = $subordinate_users;
    }

    // user activity status
    if ($arResult["User"]["ACTIVE"] == "Y") {
        $arResult["User"]["ACTIVITY_STATUS"] = "active";
    }

    $obUser   = new CUser();
    $arGroups = $obUser->GetUserGroup($arResult["User"]['ID']);
    if (in_array(1, $arGroups)) {
        $arResult["User"]["ACTIVITY_STATUS"] = "admin";
    }

    if (IsModuleInstalled("bitrix24") && \Bitrix\Bitrix24\Integrator::isIntegrator($arResult["User"]['ID'])) {
        $arResult["User"]["ACTIVITY_STATUS"] = "integrator";
    }
    if (
        !is_array($arResult["User"]['UF_DEPARTMENT'])
        || empty($arResult["User"]['UF_DEPARTMENT'][0])
    ) {
        $arResult["User"]["ACTIVITY_STATUS"] = "extranet";
        $arResult["User"]["IS_EXTRANET"]     = true;
    } else {
        $arResult["User"]["IS_EXTRANET"] = false;
    }

    if ($arResult["User"]["ACTIVE"] == "N") {
        $arResult["User"]["ACTIVITY_STATUS"] = "fired";
    }

    if (
        $arResult["User"]["ACTIVE"] == "Y"
        && !empty($arResult["User"]["CONFIRM_CODE"])
    ) {
        $arResult["User"]["ACTIVITY_STATUS"] = "invited";
    }

    if ($arResult["User"]["EXTERNAL_AUTH_ID"] == "email") {
        $arResult["User"]["ACTIVITY_STATUS"] = "email";
    }

    if (
        $arResult["User"]["ID"] == $USER->GetID()
        && CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false)
        && !isset($_SESSION["SONET_ADMIN"])
    ) {
        $arResult["SHOW_SONET_ADMIN"] = true;
    }


    if ($arUser['UF_AUTH_OTHER_USER']) {
        foreach ($arUser['UF_AUTH_OTHER_USER'] as $userID) {
            $rsUserAuth = CUser::GetByID($userID);
            $arUserAuth = $rsUserAuth->Fetch();
            $userFullName = implode(' ', [$arUserAuth['LAST_NAME'], $arUserAuth['NAME'], $arUserAuth['SECOND_NAME']]);
            $arResult['USER_AUTH_SELECT'][$userID] = $userFullName;
        }
    }
}

if (\Bitrix\Main\Loader::includeModule("security")) {
    $arResult["IS_OTP_RECOVERY_CODES_ENABLE"] = \Bitrix\Security\Mfa\Otp::isRecoveryCodesEnabled();
}
$arResult['PERSONAL_DATA_DateOfBirth'] = $arResult['PERSONAL_DATA']['DateOfBirth']
                                                ? date('d.m.Y', strtotime($arResult['PERSONAL_DATA']['DateOfBirth']))
                                                : ($arResult['User']['UF_PERSONAL_BIRTHDAY']?:$arResult['User']['PERSONAL_BIRTHDAY']);

if(!empty($arResult['PERSONAL_DATA_DateOfBirth']) && ($arResult['User']['UF_DATEOFBIRTHHIDE'] === NULL || empty($arResult['User']['UF_PERSONAL_BIRTHDAY']))){
    $cuser = new CUser;
    $cuser->Update($arResult['User']['ID'], [
        'UF_DATEOFBIRTHHIDE'    => $arResult['User']['UF_DATEOFBIRTHHIDE'] === NULL?1:$arResult['User']['UF_DATEOFBIRTHHIDE'],
        'PERSONAL_BIRTHDAY'     => $arResult['PERSONAL_DATA_DateOfBirth'],
        'UF_PERSONAL_BIRTHDAY'  => $arResult['PERSONAL_DATA_DateOfBirth']
    ]);
}

$this->__component->arResultCacheKeys = array_merge($this->__component->arResultCacheKeys, ['PERSONAL_DATA_DateOfBirth']);
