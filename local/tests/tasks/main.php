<?php

namespace Citto\Tests\Tasks;

use CTaskTemplates;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;

/**
 * Задачи
 */
class Main
{
    /**
     * В шаблоне задачи указан неактивный пользователь
     * @responsible 54
     * @run hourly
     */
    public static function testInactiveUserInTaskTemplate()
    {
        Loader::includeModule('tasks');
        $res = CTaskTemplates::getList(array('BASE_TEMPLATE_ID' => 'asc'));
        $arData = [];
        while ($item = $res->fetch()) {
            $arData[ $item['ID'] ] = [
                'ID' => $item['ID'],
                'TITLE' => $item['TITLE'],
                'CREATED_BY' => [$item['CREATED_BY']],
                'ACCOMPLICES' => $item['ACCOMPLICES'] ? unserialize($item['ACCOMPLICES']) : [],
                'AUDITORS' => $item['AUDITORS'] ? unserialize($item['AUDITORS']) : [],
                'RESPONSIBLES' => $item['RESPONSIBLES'] ? unserialize($item['RESPONSIBLES']) : [],
            ];
        }

        $arUsers = [];
        $arAllUsers = [];
        $orm = UserTable::getList([
            'select'    => ['ID', 'NAME', 'LAST_NAME', 'ACTIVE'],
        ]);
        while ($arUser = $orm->fetch()) {
            if ($arUser['ACTIVE'] == 'Y') {
                $arUsers[ $arUser['ID'] ] = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];
            }
            $arAllUsers[ $arUser['ID'] ] = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];
        }

        $arUserFields = [
            'CREATED_BY'    => 'Автор',
            'RESPONSIBLES'  => 'Ответственные',
            'ACCOMPLICES'   => 'Соисполнители',
            'AUDITORS'      => 'Наблюдатели',
        ];

        $arResult = [];
        foreach ($arData as $row) {
            $retData = [
                'ID' => $row['ID'],
                'TITLE' => $row['TITLE'],
            ];
            foreach (array_keys($arUserFields) as $field) {
                foreach ($row[ $field ] as $uId) {
                    if (!array_key_exists($uId, $arUsers)) {
                        $retData[ $field ][] = $uId;
                    }
                }
            }

            if (count($retData) > 2) {
                $arResult[] = $retData;
            }
        }

        if (!empty($arResult)) {
            $arMess = [];
            foreach ($arResult as $row) {
                $arMess[] = '<b>[' . $row['ID'] . '] ' . $row['TITLE'] . '</b>';
                foreach ($arUserFields as $field => $name) {
                    if (isset($row[ $field ])) {
                        $arNames = [];
                        if (is_array($row[ $field ])) {
                            foreach ($row[ $field ] as $uId) {
                                $arNames[] = $arAllUsers[ $uId ];
                            }
                        } else {
                            $arNames[] = $arAllUsers[ $row[ $field ] ];
                        }
                        $arMess[] = '<i>' . $name . ':</i> ' . implode(', ', $arNames);
                    }
                }
                $arMess[] = '';
            }

            return assert(false, implode("<br/>", $arMess));
        }

        return assert(true);
    }
}
