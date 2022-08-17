<?php

namespace Citto\experiment;

use CFile;
use Exception;
use CBPDocument;
use CIBlockElement;
use BizProcDocument;
use CBitrixComponent;
use RuntimeException;
use ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Sprint\Migration\Helpers\IblockHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class ComponentAjaxController extends Controller
{
    /**
     * Конфигурация действий
     *
     * @return array
     */
    public function configureActions(): array
    {
        $arParams = [
            'prefilters'    => [
                new ActionFilter\Authentication(),
                new ActionFilter\HttpMethod(
                    [ActionFilter\HttpMethod::METHOD_POST]
                ),
                new ActionFilter\Csrf(),
            ],
            'postfilters'   => []
        ];

        return [
            'step1'         => $arParams,
            'removefile'    => $arParams,
            'hideuser'      => $arParams,
            'showuser'      => $arParams,
        ];
    }

    /**
     * Первый шаг запуска БП
     *
     * @param string $request
     *
     * @return string
     */
    public static function step1Action(string $request = '')
    {
        parse_str($request, $arRequest);

        if (empty($arRequest['DATE'])) {
            throw new Exception('Пустая дата уведомления');
        }
        if (empty($arRequest['RUKL'])) {
            throw new Exception('Не передан руководитель');
        }
        if (empty($arRequest['USER'])) {
            throw new Exception('Не переданы пользователи');
        }

        Loader::includeModule('bizproc');
        Loader::includeModule('iblock');
        Loader::includeModule('workflow');
        Loader::includeModule('sprint.migration');

        $helper = new IblockHelper();
        $iblockId = $helper->getIblockId('bizproc_experiment', 'bitrix_processes');

        $obEl = new CIBlockElement();
        $arLoadProductArray = [
            'IBLOCK_TYPE_ID'    => 'bitrix_processes',
            'IBLOCK_ID'         => $iblockId,
            'ACTIVE'            => 'Y',
            'NAME'              => 'Уведомление (Ознакомление с экспериментом)',
            'CREATED_BY'        => "user_".$arRequest['CURRENT_USER'],
            'PROPERTY_VALUES'   => [
                'DATE'  => $arRequest['DATE'],
                'RUKL'  => $arRequest['RUKL'],
                'USERS' => $arRequest['USER']
            ]
        ];

        $iElement = $obEl->Add($arLoadProductArray, false, true, false);
        $documentType = BizProcDocument::generateDocumentComplexType(
            $arLoadProductArray['IBLOCK_TYPE_ID'],
            $arLoadProductArray['IBLOCK_ID']
        );
        $arDocumentStates = CBPDocument::GetDocumentStates(
            $documentType,
            null,
            'Y'
        );

        $arErrorsTmp = [];
        foreach ($arDocumentStates as $arDocumentState) {
            if (mb_strlen($arDocumentState['ID']) <= 0) {
                CBPDocument::StartWorkflow(
                    $arDocumentState['TEMPLATE_ID'],
                    BizProcDocument::getDocumentComplexId(
                        $arLoadProductArray['IBLOCK_TYPE_ID'],
                        $iElement
                    ),
                    [],
                    $arErrorsTmp
                );
            }
        }
        if (empty($arErrorsTmp)) {
            return 'Документы успешно созданы. Необходимо перейти к согласованию';
        } else {
            throw new Exception(implode(PHP_EOL, $arErrorsTmp));
        }
    }

    /**
     * Удалить информацию о пользователе
     *
     * @param int $user
     * @param int $file
     *
     * @return string
     */
    public static function removefileAction(int $user = 0, int $file = 0)
    {
        if ($user <= 0) {
            throw new Exception('Не передан пользователь');
        }
        if ($file <= 0) {
            throw new Exception('Не передан файл');
        }

        Loader::includeModule('iblock');

        $helper = new IblockHelper();
        $iblockId = $helper->getIblockId('bizproc_experiment', 'bitrix_processes');
        $iblockFiles = $helper->getIblockId('bizproc_experiment_simple', 'bitrix_processes');

        $obIblockEl = new CIBlockElement();

        $sFindString = $user . ':' . $file . ':';
        $arFilter = [
            'IBLOCK_TYPE_ID'    => 'bitrix_processes',
            'IBLOCK_ID'         => $iblockId,
            'ACTIVE'            => 'Y',
            'PROPERTY_USERS'    => $user,
            '%PROPERTY_FILES'   => $sFindString,
        ];
        $res = CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            false,
            [
                'ID',
                'PROPERTY_USERS',
                'PROPERTY_FILES',
            ]
        );
        while ($row = $res->GetNext()) {
            $arUsers = $row['PROPERTY_USERS_VALUE'];
            foreach ($arUsers as $k => $v) {
                if ($v == $user) {
                    unset($arUsers[ $k ]);
                }
            }
            $arFiles = $row['PROPERTY_FILES_VALUE'];
            foreach ($arFiles as $k => $v) {
                if (false !== mb_strpos($v, $sFindString)) {
                    unset($arFiles[ $k ]);
                }
            }
            $obIblockEl->SetPropertyValues(
                $row['ID'],
                $iblockId,
                $arUsers,
                'USERS'
            );
            $obIblockEl->SetPropertyValues(
                $row['ID'],
                $iblockId,
                $arFiles,
                'FILES'
            );

            CFile::Delete($file);
        }

        $arFilter = [
            'IBLOCK_TYPE_ID'    => 'bitrix_processes',
            'IBLOCK_ID'         => $iblockFiles,
            'ACTIVE'            => 'Y',
            'PROPERTY_USER'     => $user,
            'PROPERTY_FILE'     => $file,
        ];
        $res = CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            false,
            [
                'ID'
            ]
        );
        while ($row = $res->GetNext()) {
            $obIblockEl->Update(
                $row['ID'],
                ['ACTIVE' => 'N']
            );
        }

        return 'Запись успешно удалена';
    }

    /**
     * Скрыть пользователя из списка
     *
     * @param int $user
     *
     * @return string
     */
    public static function hideuserAction(int $user = 0)
    {
        if ($user <= 0) {
            throw new Exception('Не передан пользователь');
        }

        $file = $_SERVER['DOCUMENT_ROOT'] . '/experiment/hidden_users.json';
        if (!file_exists($file)) {
            file_put_contents($file, '[]');
        }
        try {
            $arUsers = Json::decode(file_get_contents($file));
            $arUsers[] = $user;
            sort($arUsers);
            file_put_contents($file, Json::encode($arUsers));
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        } catch (ArgumentException $e) {
            throw new Exception($e->getMessage());
        }

        return 'Пользователь скрыт';
    }

    /**
     * Вернуть пользователя в список
     *
     * @param int $user
     *
     * @return string
     */
    public static function showuserAction(int $user = 0)
    {
        if ($user <= 0) {
            throw new Exception('Не передан пользователь');
        }

        $file = $_SERVER['DOCUMENT_ROOT'] . '/experiment/hidden_users.json';
        if (file_exists($file)) {
            try {
                $arUsers = Json::decode(file_get_contents($file));
                foreach ($arUsers as $k => $v) {
                    if ($v == $user) {
                        unset($arUsers[ $k ]);
                        break;
                    }
                }
                sort($arUsers);
                file_put_contents($file, Json::encode($arUsers));
            } catch (Exception | ArgumentException $e) {
                throw new Exception($e->getMessage());
            }
        }

        return 'Пользователь возвращён в список';
    }
}
