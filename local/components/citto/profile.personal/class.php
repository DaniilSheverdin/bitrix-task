<?php

namespace Citto\Profile;

use CUser;
use CJSCore;
use Exception;
use CBitrixComponent;
use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Config\Option;
use Citto\Integration\Source1C;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Extension::load(['ui.buttons.icons', 'ui.dialogs.messagebox']);

class Personal extends CBitrixComponent
{
    /**
     * Запуск компонента
     *
     * @return null
     *
     * @throws LoaderException
     */
    public function executeComponent()
    {
        try {
            global $APPLICATION, $USER;

            $hasAccess = false;

            if ($USER->GetID() == $this->arParams['USER_ID']) {
                $hasAccess = true;
            }
            if ($USER->IsAdmin()) {
                $hasAccess = true;
            }
            if (!$hasAccess) {
                throw new Exception("Ошибка доступа");
            }
            Loader::includeModule('citto.integration');
            Extension::load(
                [
                    'ui.forms',
                    'ui.buttons',
                    'ui.buttons.icons',
                    'ui.dialogs',
                    'ui.dialogs.messagebox'
                ]
            );
            CJSCore::Init(['jquery', 'popup', 'ui']);

            $this->arResult['User'] = $this->getUserPortalData($this->arParams['USER_ID']);

            if (!empty($this->arResult['User']['UF_SID'])) {
                $this->arResult['PERSONAL_DATA'] = $this->getUserOnesData($this->arResult['User']['UF_SID']);
            }

            if (empty($this->arResult['PERSONAL_DATA'])) {
                throw new Exception("Нет данных");
            }
            $this->arResult['HAS_PAYSLIP'] = !empty(trim($this->arResult['User']['UF_INN']));

            $template = 'personal';
            $title = 'Личные данные';
            if ($this->arParams['TEMPLATE'] == 'career') {
                $template = 'career';
                $title = 'Карьера';
            }
            $this->includeComponentTemplate($template);

            $title .= ': ' . $this->arResult['User']['LAST_NAME'] . ' ' . $this->arResult['User']['NAME'];
            $APPLICATION->SetTitle($title);
        } catch (Exception $e) {
            // ShowError($e->getMessage());
        }
    }

    /**
     * Информация о пользователе
     *
     * @param int $iUserId
     *
     * @return array
     */
    public function getUserPortalData(int $iUserId = 0): array
    {
        if ($iUserId <= 0) {
            throw new Exception('Не передан ID пользователя');
        }
        $arUser = CUser::GetByID($iUserId)->GetNext();
        $arUser['XML_ID'] = isset($arUser['XML_ID']) ? trim($arUser['XML_ID']) : null;
        $arUser['UF_SID'] = isset($arUser['UF_SID']) ? trim($arUser['UF_SID']) : null;
        return $arUser;
    }

    /**
     * Получить данные из 1С по пользователю.
     * @param string $strXmlId
     * @param string $method
     * @return array
     */
    public function getUserOnesData(
        string $strXmlId = '',
        string $method = 'PersonalData'
    ): array {
        if (empty($strXmlId)) {
            throw new Exception('Не передан ID пользователя');
        }
        $arReturn = [];
        $cache = Cache::createInstance();
        if ($cache->initCache(86400, __METHOD__ . $method . $strXmlId . date('d.m.Y'), '/citto/profile.personal/')) {
            $arReturn = $cache->getVars();
        }

        if (empty($arReturn) || $cache->startDataCache()) {
            try {
                $obConnect = Source1C::Connect1C();
                $arData = Source1C::GetArray(
                    $obConnect,
                    $method,
                    [
                        'EmployeeID' => $strXmlId
                    ]
                );
                if ($arData['result']) {
                    if (is_array($arData['Data'])) {
                        $arReturn = $arData['Data'][ $method ];
                    } elseif ($method === 'ReferenceLensData') {
                        $arReturn = (array)$arData['Data']->$method;
                        $arReturn['Family'] = json_decode(json_encode($arReturn['Family']), true);
                        $arReturn['WorkActivity'] = json_decode(json_encode($arReturn['WorkActivity']), true);
                        $arReturn['Photo'] = (array)$arReturn['Photo'];
                    }
                    if ($method === 'PersonalData') {
                        $rVacationRespone = Source1C::GetArray(
                            $obConnect,
                            'VacationSchedule',
                            [
                                'EmployeeID' => $strXmlId
                            ],
                            true
                        );
                        if ($rVacationRespone['result'] == 1) {
                            $arReturn['VacationList'] = $rVacationRespone['Data']['VacationSchedule'];
                        }

                        $rVacationLeftRespone = Source1C::GetArray(
                            $obConnect,
                            'VacationLeftovers',
                            [
                                'SIDorINNList' => [
                                    'SIDorINN' => $strXmlId
                                ]
                            ],
                            true
                        );

                        if ($rVacationLeftRespone['result'] == 1) {
                            $arReturn['VacationLeftovers'] = $rVacationLeftRespone['Data']['VacationLeftovers']['EmployeeVacationLeftovers']['WorkingPeriodsLeftovers']['WorkingPeriodLeftovers'];
                        }
                        
                        foreach ($arReturn['VacationLeftovers'] as $sWorkingPeriodKey => $arWorkingPeriod) {
                            $iNeotgul = 0;
                            $dateStart = explode('-', $arWorkingPeriod['WorkingPeriod']['DateStart']);

                            if ($dateStart[0] < date('Y')) {
                                foreach ($arWorkingPeriod['Leftovers']['Leftover'] as $arLeftOver) {
                                    if ($arLeftOver['TotalUsed'] != $arLeftOver['AvailableForCurrentDate']) {
                                        $iNeotgul += intval($arLeftOver['AvailableForCurrentDate'])-intval($arLeftOver['TotalUsed']);
                                    }
                                }
                            }

                            if ($iNeotgul > 0) {
                                $arReturn['VacationLeftovers'][ $sWorkingPeriodKey ]['WorkingPeriod']['NotUsed'] = $iNeotgul;
                            } else {
                                unset($arReturn['VacationLeftovers'][ $sWorkingPeriodKey ]);
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                $arReturn = [];
            }
            $cache->endDataCache($arReturn);
        }

        return $arReturn;
    }
}
