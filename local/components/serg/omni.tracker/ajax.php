<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Request;

class AddOMNIStatisticAjaxController extends Controller
{
    /**
     * @param Request|null $request
     * @throws LoaderException
     */
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        Loader::includeModule('iblock');
    }

    /**
     * @return array
     */
    public function configureActions(): array
    {
        return [
            'addStatistic' => [
                'prefilters' => []
            ],
            'getDepartmentsByUserID' => [
                'prefilters' => []
            ],

        ];
    }

    /**
     * @param $user_id
     * @param $department_id
     * @param $create
     * @param $complete
     * @param $wrong
     * @param $percent_exec
     * @return array
     */
    public function addStatisticAction($user_id, $department_id, $create, $complete, $wrong, $percent_exec)
    {
        if ($user_id && $department_id && $create && $complete && $wrong && $percent_exec) {
            global $USER;
            $strDepartmentName = '';

            $obNewElement = new CIBlockElement();

            $arPropsNewElement = [
                'ATT_DEPARTMENT' => $department_id,
                'ATT_USER' => $user_id,
                'ATT_CREATED' => $create,
                'ATT_COMPLETED' => $complete,
                'ATT_DEFECTION' => $wrong,
                'ATT_PERCENT' => $percent_exec,
            ];

            $rsUser = CUser::GetByID($user_id);
            $arUser = $rsUser->Fetch();

            $strUserFullName = $arUser['LAST_NAME'].' '.$arUser['NAME'].' '.$arUser['SECOND_NAME'];

            $rsDepartment = CIBlockSection::GetByID($department_id);
            if ($arDepartment = $rsDepartment->GetNext()) {
                $strDepartmentName = $arDepartment['NAME'];
            }

            $arLoadNewElement = array(
                "MODIFIED_BY"    => $USER->GetID(),
                "IBLOCK_SECTION_ID" => false,
                "IBLOCK_ID"      => IBLOCK_ID_OMNI_TRACKER,
                "PROPERTY_VALUES"=> $arPropsNewElement,
                "NAME"           => $strUserFullName,
                "ACTIVE"         => "Y",
            );

            if ($intNewElementID = $obNewElement->Add($arLoadNewElement)) {
                $arData['id'] = $intNewElementID;
                $arData['newData'] = [
                    'department' => $strDepartmentName,
                    'user' => $strUserFullName,
                    'created' => $create,
                    'completed' => $complete,
                    'defection' => $wrong,
                    'percent' => $percent_exec,
                ];
            } else {
                $arData['error']['text'] = $obNewElement->LAST_ERROR;
            }
        } else {
            $arData['error'] = true;
        }

        return $arData;
    }

    /**
     * @param $id
     * @return array
     */
    public function getDepartmentsByUserIDAction($id)
    {

        $rsUser = CUser::GetByID($id);
        $arUser = $rsUser->Fetch();

        $arDepartments = [];
        $intCount = 0;

        foreach ($arUser['UF_DEPARTMENT'] as $departmentID) {
            $rsDepartment = CIBlockSection::GetByID($departmentID);
            if ($arDepartment = $rsDepartment->GetNext()) {
                $arDepartments[$intCount]['id'] = $arDepartment['ID'];
                $arDepartments[$intCount]['text'] = $arDepartment['NAME'];
            }

            $intCount++;
        }

        return $arDepartments;
    }
}
