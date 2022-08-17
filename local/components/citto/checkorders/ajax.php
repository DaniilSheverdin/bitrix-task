<?php

namespace Citto\ControlOrders\Main;

use CFile;
use CPHPCache;
use Exception;
use CUserOptions;
use Bitrix\Main\IO;
use CIBlockElement;
use CIBlockSection;
use CBitrixComponent;
use RuntimeException;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Citto\Filesigner\Signer;
use Bitrix\DocumentGenerator;
use Citto\ControlOrders\Notify;
use Citto\ControlOrders\Orders;
use Citto\ControlOrders\Settings;
use Citto\Controlorders\Executors;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Sprint\Migration\Helpers\HlblockHelper;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;
use Citto\ControlOrders\Main\Component as MainComponent;
use Bitrix\DocumentGenerator\DataProvider\ArrayDataProvider;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Class AjaxController
 *
 * @package Citto\ControlOrders\Main
 */
class AjaxController extends Controller
{
    /**
     * Конфигурация действий
     *
     * @return array
     */
    public function configureActions(): array
    {
        $arParams = [
            'prefilters' => [
                new ActionFilter\Authentication(),
                new ActionFilter\HttpMethod(
                    [ActionFilter\HttpMethod::METHOD_POST]
                ),
                new ActionFilter\ContentType(
                    [ActionFilter\ContentType::JSON]
                ),
                new ActionFilter\Csrf(),
            ],
            'postfilters' => []
        ];

        return [
            'listPdfGenerate'       => $arParams,
            'classificatorTree'     => $arParams,
            'classificatorCreate'   => $arParams,
            'classificatorRename'   => $arParams,
            'classificatorDelete'   => $arParams,
            'classificatorSort'     => $arParams,
            'addOffer'              => $arParams,
            'addObject'             => $arParams,
            'deleteObject'          => $arParams,
            'getObject'             => $arParams,
            'getCounters'           => $arParams,
            'sendToSign'            => $arParams,
            'pdfGenerate'           => $arParams,
            'returnReport'          => $arParams,
            'sendToControl'         => $arParams,
            'acceptIspolnitel'      => $arParams,
            'setWidgetSettings'     => $arParams,
            'getOrdersMap'          => $arParams,
            'getWidgetStats'        => $arParams,
        ];
    }

    /**
     * listPdfGenerateAction
     *
     * @param mixed $data Список ID поручений.
     *
     * @return array
     *
     * @throws RuntimeException Невозможно создать папку.
     */
    public function listPdfGenerateAction($data)
    {
        CBitrixComponent::includeComponentClass('citto:checkorders');
        $obComponent = new MainComponent();
        $arIspolnitels = Executors::getList();
        $res = CIBlockElement::GetList(
            ['DATE_CREATE' => 'DESC'],
            [
                'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
                'ID'        => $data
            ],
            false,
            false,
            [
                'ID',
                'IBLOCK_ID',
                'NAME',
                'DETAIL_TEXT',
                'PROPERTY_DATE_CREATE',
                'PROPERTY_DATE_ISPOLN',
                'PROPERTY_POST',
                'PROPERTY_ISPOLNITEL',
                'DATE_CREATE',
                'PROPERTY_CONTROLER',
                'PROPERTY_NUMBER',
                'PROPERTY_ACTION',
                'PROPERTY_STATUS',
                'PROPERTY_CATEGORY',
                'PROPERTY_DATE_FACT_ISPOLN',
                'PROPERTY_DELEGATE_USER',
                'PROPERTY_SUBEXECUTOR_DATE',
            ]
        );

        $list = [];
        while ($arFields = $res->GetNext()) {
            $res2 = CIBlockElement::GetList(
                ['DATE_CREATE' => 'DESC'],
                [
                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                    'PROPERTY_PORUCH'   => (int)$arFields['ID'],
                    'PROPERTY_TYPE'     => 1132
                ],
                false,
                ['nPageSize' => 1],
                ['ID', 'PREVIEW_TEXT']
            );
            $arFields['ISPOLN'] = $res2->GetNext();

            $arFields['DATE_ISPOLN_TIMESTAMP'] = $arFields['PROPERTY_DATE_ISPOLN_VALUE'] != $obComponent->disableSrokDate ?
                                        strtotime($arFields['PROPERTY_DATE_ISPOLN_VALUE']) :
                                        0;

            $arFields['SUBEXECUTOR_DATE_TIMESTAMP'] = $arFields['PROPERTY_SUBEXECUTOR_DATE_VALUE'] != $obComponent->disableSrokDate ?
                                        strtotime($arFields['PROPERTY_SUBEXECUTOR_DATE_VALUE']) :
                                        0;

            $arFields['DATE_CREATE_TIMESTAMP']      = strtotime($arFields['PROPERTY_DATE_CREATE_VALUE']);
            $arFields['DATE_FACT_ISPOLN_TIMESTAMP'] = strtotime($arFields['PROPERTY_DATE_FACT_ISPOLN_VALUE']);
            $arFields['ISPOLNITEL_INT']             = $arFields['PROPERTY_ISPOLNITEL_VALUE'];
            $arFields['STATUS_INT']                 = $arFields['PROPERTY_ACTION_ENUM_ID'];
            $arFields['DELEGATE_USER_ID']           = $arFields['PROPERTY_DELEGATE_USER_VALUE'];
            $arFields['TEXT']                       = $arFields['~DETAIL_TEXT'];

            $numberInt = (int)$arFields['PROPERTY_NUMBER_VALUE'];
            if ($numberInt == 0) {
                $numberInt = trim($arFields['PROPERTY_NUMBER_VALUE']);
            } elseif (0 == mb_strpos($arFields['PROPERTY_NUMBER_VALUE'], '0')) {
                $numberInt = trim($arFields['PROPERTY_NUMBER_VALUE']);
            }
            $arFields['NUMBER_INT'] = $numberInt;

            $list[] = $arFields;
        }

        /**
         * @param $key
         * @param $order
         *
         * @return \Closure
         */
        function build_sorter($key, $order)
        {
            return static function ($a, $b) use ($key, $order) {
                return $order=='asc' ?
                    strnatcmp('str-' . $a[ $key ], 'str-' . $b[ $key ]) :
                    strnatcmp('str-' . $b[ $key ], 'str-' . $a[ $key ]);
            };
        }

        $list_id = 'control-orders-list';

        $grid_options = new GridOptions($list_id);
        $sort = $grid_options->getSorting(
            [
                'sort' => [
                    'DATE_CREATE_TIMESTAMP'   => 'asc',
                ],
                'vars' => [
                    'by'    => 'by',
                    'order' => 'order'
                ]
            ]
        );

        $by = array_keys($sort['sort'])[0];
        $order = $sort['sort'][ $by ];
        usort(
            $list,
            build_sorter($by, $order)
        );

        $arItems = [];
        $n = 1;
        foreach ($list as $arFields) {
            $arItems[] = [
                'Num'       => ($n++),
                'DateDoc'   => $arFields['NAME'] . ' № ' . $arFields['PROPERTY_NUMBER_VALUE'] . ' от ' . $arFields['PROPERTY_DATE_CREATE_VALUE'],
                'User'      => $arIspolnitels[ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ]['NAME'],
                'Srok'      => $arFields['PROPERTY_DATE_ISPOLN_VALUE'] != $obComponent->disableSrokDate ?
                                $arFields['PROPERTY_DATE_ISPOLN_VALUE'] :
                                'Без срока',
                'Date'      => $arFields['PROPERTY_DATE_FACT_ISPOLN_VALUE'],
                'Content'   => strip_tags($arFields['~DETAIL_TEXT']),
                'Course'    => strip_tags($arFields['ISPOLN']['~PREVIEW_TEXT']),
            ];
        }

        Loader::includeModule('documentgenerator');

        $file = new IO\File($_SERVER['DOCUMENT_ROOT'] . '/local/templates_docx/checkorders/poruch.docx');
        $body = new DocumentGenerator\Body\Docx($file->getContents());
        $body->normalizeContent();

        $body->setValues(
            [
                'TITLE'         => 'поручений, cодержащихся в правовых актах и протоколах, находящихся на контроле',
                'USERS'         => '',
                'DATE'          => '',
                'COUNT'         => count($arItems),
                'Items'         => new ArrayDataProvider(
                    $arItems,
                    [
                        'ITEM_NAME'     => 'Item',
                        'ITEM_PROVIDER' => ArrayDataProvider::class
                    ]
                ),
                'ItemsNum'      => 'Items.Item.Num',
                'ItemsDateDoc'  => 'Items.Item.DateDoc',
                'ItemsUser'     => 'Items.Item.User',
                'ItemsSrok'     => 'Items.Item.Srok',
                'ItemsDate'     => 'Items.Item.Date',
                'ItemsContent'  => 'Items.Item.Content',
                'ItemsCourse'   => 'Items.Item.Course',
            ]
        );
        $body->process();
        $strContent = $body->getContent();
        $docPath = '/upload/checkorders/';
        $strFileName = time() . '.docx';
        $path = $_SERVER['DOCUMENT_ROOT'] . $docPath;
        if (!mkdir($path, 0775, true) && !is_dir($path)) {
            throw new RuntimeException('Directory ' . $docPath . ' was not created');
        }
        file_put_contents($path . $strFileName, $strContent);
        return [
            'filename' => $strFileName
        ];
    }

    /**
     * Список категорий для классификатора.
     *
     * @param bool $update
     *
     * @return array
     * @throws \Bitrix\Main\LoaderException
     */
    public static function classificatorTreeAction($update = true): array
    {
        $arReturn = [];
        $obCache = new CPHPCache();
        if (!$update && $obCache->InitCache(3600, md5(__METHOD__), '/citto/controlorders/ajax/classificator/')) {
            $arReturn = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            Loader::includeModule('iblock');

            $arFilter = [
                'IBLOCK_ID'         => Settings::$iblockId['ORDERS'],
                'ACTIVE_DATE'       => 'Y',
                'ACTIVE'            => 'Y',
                '!PROPERTY_THEME'   => false
            ];
            $arDisabled = [];
            $res = CIBlockElement::GetList([], $arFilter, false, false, ['ID', 'PROPERTY_THEME', 'PROPERTY_CAT_THEME']);
            while ($arFields = $res->GetNext()) {
                $arDisabled[ 'element_' . $arFields['PROPERTY_THEME_VALUE'] ] = $arFields['PROPERTY_THEME_VALUE'];
                $arDisabled[ 'section_' . $arFields['PROPERTY_CAT_THEME_VALUE'] ] = $arFields['PROPERTY_CAT_THEME_VALUE'];
            }

            $arFilter = [
                'IBLOCK_ID' => Settings::$iblockId['ORDERS_THEME'],
                'ACTIVE'    => 'Y',
            ];
            $sort = 0;
            $rsSect   = CIBlockSection::GetList(['SORT' => 'asc', 'TIMESTAMP_X' => 'desc'], $arFilter);
            while ($arSect = $rsSect->GetNext()) {
                $arReturn[ $arSect['ID'] ] = [
                    'id'            => 'section_' . $arSect['ID'],
                    'text'          => $arSect['NAME'],
                    'children'      => [],
                    'create_node'   => true,
                    'delete_node'   => !array_key_exists('section_' . $arSect['ID'], $arDisabled),
                    'rename_node'   => true,
                    'sort'          => $arSect['SORT'],
                ];
                if ($update) {
                    (new CIBlockSection())->Update($arSect['ID'], ['SORT' => $sort]);
                    $arReturn[ $arSect['ID'] ]['sort'] = $sort;
                    $sort += 10;
                }
            }

            $arSelect = [
                'ID',
                'NAME',
                'SORT',
                'IBLOCK_SECTION_ID',
            ];
            $arFilter = [
                'IBLOCK_ID'     => Settings::$iblockId['ORDERS_THEME'],
                'ACTIVE_DATE'   => 'Y',
                'ACTIVE'        => 'Y'
            ];
            $res = CIBlockElement::GetList(['SORT' => 'asc', 'TIMESTAMP_X' => 'desc'], $arFilter, false, false, $arSelect);
            $sort = [];
            while ($arFields = $res->GetNext()) {
                if (!isset($arReturn[ $arFields['IBLOCK_SECTION_ID'] ])) {
                    continue;
                }
                if (!isset($sort[ $arFields['IBLOCK_SECTION_ID'] ])) {
                    $sort[ $arFields['IBLOCK_SECTION_ID'] ] = 0;
                }
                $arReturn[ $arFields['IBLOCK_SECTION_ID'] ]['children'][ $arFields['ID'] ] = [
                    'id'            => 'element_' . $arFields['ID'],
                    'text'          => $arFields['NAME'],
                    'create_node'   => false,
                    'delete_node'   => !array_key_exists('element_' . $arFields['ID'], $arDisabled),
                    'rename_node'   => true,
                    'sort'          => $arFields['SORT'],
                ];
                if ($update) {
                    (new CIBlockElement())->Update($arFields['ID'], ['SORT' => $sort[ $arFields['IBLOCK_SECTION_ID'] ]]);
                    $arReturn[ $arFields['IBLOCK_SECTION_ID'] ]['children'][ $arFields['ID'] ]['sort'] = $sort[ $arFields['IBLOCK_SECTION_ID'] ];
                    $sort[ $arFields['IBLOCK_SECTION_ID'] ] += 10;
                }
            }

            /**
             * @param $key
             *
             * @return \Closure
             */
            function mySorter($key)
            {
                return static function ($a, $b) use ($key) {
                    return strnatcmp($a[ $key ], $b[ $key ]);
                };
            }

            usort($arReturn, mySorter('sort'));
            foreach ($arReturn as $key => $value) {
                usort($value['children'], mySorter('sort'));
                $arReturn[ $key ] = $value;
            }

            $arReturn = [
                0 => [
                    'id'            => 0,
                    'text'          => 'Тематики',
                    'children'      => $arReturn,
                    'delete_node'   => false,
                ],
            ];

            $obCache->EndDataCache($arReturn);

            if ($update) {
                BXClearCache(true, '/citto/controlorders/ajax/classificator/');
            }
        }

        return $arReturn;
    }

    /**
     * Создать раздел\элемент в классификаторе.
     *
     * @param mixed  $id
     * @param mixed  $parent
     * @param string $name
     *
     * @return void
     */
    public static function classificatorCreateAction($id, $parent, $name)
    {
        $curUserId = $GLOBALS['USER']->GetID();
        CBitrixComponent::includeComponentClass('citto:checkorders');
        $obComponent = new MainComponent();
        Loader::includeModule('iblock');
        $arFields = [
            'NAME'          => $name,
            'IBLOCK_ID'     => Settings::$iblockId['ORDERS_THEME'],
            'XML_ID'        => $id,
            'EXTERNAL_ID'   => $id,
            'MODIFIED_BY'   => $curUserId,
        ];
        $sectionId = 0;
        if (false !== mb_strpos($parent, 'section_')) {
            $sectionId = (int)str_replace('section_', '', $parent);
        } else {
            $arFilter = [
                'IBLOCK_ID' => Settings::$iblockId['ORDERS_THEME'],
                'XML_ID'    => $parent,
                'ACTIVE'    => 'Y'
            ];
            $res = CIBlockSection::GetList([], $arFilter, false, false, ['ID', 'XML_ID']);
            while ($row = $res->GetNext()) {
                if ($row['XML_ID'] == $parent) {
                    $sectionId = (int)$row['ID'];
                }
            }
        }
        if ($parent === 'j1_1') {
            $arFields['IBLOCK_SECTION_ID'] = $sectionId;
            $obSect = new CIBlockSection();
            $obSect->Add($arFields);
            $obComponent->log(
                0,
                'Создан элемент классификатора',
                [
                    'METHOD'    => __METHOD__,
                    'arFields'  => $arFields
                ]
            );
        } else {
            $arFields['IBLOCK_SECTION_ID'] = $sectionId;
            $obEl = new CIBlockElement();
            $obEl->Add($arFields);
            $obComponent->log(
                0,
                'Создан элемент классификатора',
                [
                    'METHOD'    => __METHOD__,
                    'arFields'  => $arFields
                ]
            );
        }

        BXClearCache(true, '/citto/controlorders/ajax/classificator/');
    }

    /**
     * Переименовать раздел\элемент в классификаторе.
     *
     * @param mixed  $id
     * @param string $name
     *
     * @return void
     */
    public static function classificatorRenameAction($id, $name)
    {
        $curUserId = $GLOBALS['USER']->GetID();
        CBitrixComponent::includeComponentClass('citto:checkorders');
        $obComponent = new MainComponent();
        $elementId = 0;
        $sectionId = 0;
        Loader::includeModule('iblock');
        if (false !== mb_strpos($id, 'element_')) {
            $elementId = (int)str_replace('element_', '', $id);
        } elseif (false !== mb_strpos($id, 'section_')) {
            $sectionId = (int)str_replace('section_', '', $id);
        } else {
            $arFilter = [
                'IBLOCK_ID' => Settings::$iblockId['ORDERS_THEME'],
                'XML_ID'    => $id,
                'ACTIVE'    => 'Y'
            ];
            $res = CIBlockElement::GetList([], $arFilter, false, false, ['ID', 'XML_ID']);
            while ($arFields = $res->GetNext()) {
                if ($arFields['XML_ID'] == $id) {
                    $elementId = (int)$arFields['ID'];
                }
            }

            if ($elementId <= 0) {
                $res = CIBlockSection::GetList([], $arFilter, false, false, ['ID', 'XML_ID']);
                while ($arFields = $res->GetNext()) {
                    if ($arFields['XML_ID'] == $id) {
                        $sectionId = (int)$arFields['ID'];
                    }
                }
            }
        }

        if ($elementId > 0) {
            $arFields = [
                'NAME'          => $name,
                'MODIFIED_BY'   => $curUserId,
            ];
            $obEl = new CIBlockElement();
            $obEl->Update($elementId, $arFields);
            $obComponent->log(
                0,
                'Изменён элемент классификатора',
                [
                    'METHOD'    => __METHOD__,
                    'elementId' => $elementId,
                    'arFields'  => $arFields,
                ]
            );
        } elseif ($sectionId > 0) {
            $arFields = [
                'NAME'          => $name,
                'MODIFIED_BY'   => $curUserId,
            ];
            $obSect = new CIBlockSection();
            $obSect->Update($sectionId, $arFields);
            $obComponent->log(
                0,
                'Изменён элемент классификатора',
                [
                    'METHOD'    => __METHOD__,
                    'sectionId' => $sectionId,
                    'arFields'  => $arFields,
                ]
            );
        }

        BXClearCache(true, '/citto/controlorders/ajax/classificator/');
    }

    /**
     * Удалить раздел\элемент в классификаторе.
     *
     * @param mixed $id
     *
     * @return void
     */
    public static function classificatorDeleteAction($id)
    {
        $curUserId = $GLOBALS['USER']->GetID();
        CBitrixComponent::includeComponentClass('citto:checkorders');
        $obComponent = new MainComponent();
        $elementId = 0;
        $sectionId = 0;
        Loader::includeModule('iblock');
        if (false !== mb_strpos($id, 'element_')) {
            $elementId = (int)str_replace('element_', '', $id);
        } elseif (false !== mb_strpos($id, 'section_')) {
            $sectionId = (int)str_replace('section_', '', $id);
        } else {
            $arFilter = [
                'IBLOCK_ID' => Settings::$iblockId['ORDERS_THEME'],
                'XML_ID'    => $id,
                'ACTIVE'    => 'Y'
            ];
            $res = CIBlockElement::GetList([], $arFilter, false, false, ['ID', 'XML_ID']);
            while ($arFields = $res->GetNext()) {
                if ($arFields['XML_ID'] == $id) {
                    $elementId = (int)$arFields['ID'];
                }
            }

            if ($elementId <= 0) {
                $res = CIBlockSection::GetList([], $arFilter, false, false, ['ID', 'XML_ID']);
                while ($arFields = $res->GetNext()) {
                    if ($arFields['XML_ID'] == $id) {
                        $sectionId = (int)$arFields['ID'];
                    }
                }
            }
        }

        if ($elementId > 0) {
            $arFields = [
                'ACTIVE'        => 'N',
                'MODIFIED_BY'   => $curUserId,
            ];
            $obEl = new CIBlockElement();
            $obEl->Update($elementId, $arFields);
            $obComponent->log(
                0,
                'Удален элемент классификатора',
                [
                    'METHOD'    => __METHOD__,
                    'elementId' => $elementId,
                    'arFields'  => $arFields,
                ]
            );
        } elseif ($sectionId > 0) {
            $arFields = [
                'ACTIVE'        => 'N',
                'MODIFIED_BY'   => $curUserId,
            ];
            $obSect = new CIBlockSection();
            $obSect->Update($sectionId, $arFields);
            $obComponent->log(
                0,
                'Удален элемент классификатора',
                [
                    'METHOD'    => __METHOD__,
                    'sectionId' => $sectionId,
                    'arFields'  => $arFields,
                ]
            );
        }

        BXClearCache(true, '/citto/controlorders/ajax/classificator/');
    }

    /**
     * Изменить сортировку раздела\элемента в классификаторе.
     *
     * @param mixed   $id
     * @param integer $sort
     * @param         $section
     *
     * @return void
     * @throws \Bitrix\Main\LoaderException
     */
    public static function classificatorSortAction($id, $sort, $section)
    {
        $curUserId = $GLOBALS['USER']->GetID();
        $elementId = 0;
        $sectionId = 0;
        Loader::includeModule('iblock');
        if (false !== mb_strpos($id, 'element_')) {
            $elementId = (int)str_replace('element_', '', $id);
        } elseif (false !== mb_strpos($id, 'section_')) {
            $sectionId = (int)str_replace('section_', '', $id);
        } else {
            $arFilter = [
                'IBLOCK_ID' => Settings::$iblockId['ORDERS_THEME'],
                'XML_ID'    => $id,
                'ACTIVE'    => 'Y'
            ];
            $res = CIBlockElement::GetList([], $arFilter, false, false, ['ID', 'XML_ID']);
            while ($arFields = $res->GetNext()) {
                if ($arFields['XML_ID'] == $id) {
                    $elementId = (int)$arFields['ID'];
                }
            }

            if ($elementId <= 0) {
                $res = CIBlockSection::GetList([], $arFilter, false, false, ['ID', 'XML_ID']);
                while ($arFields = $res->GetNext()) {
                    if ($arFields['XML_ID'] == $id) {
                        $sectionId = (int)$arFields['ID'];
                    }
                }
            }
        }

        if ($elementId > 0) {
            $newSectionId = 0;
            if (false !== mb_strpos($section, 'section_')) {
                $newSectionId = (int)str_replace('section_', '', $section);
            } else {
                $arFilter = [
                    'IBLOCK_ID' => Settings::$iblockId['ORDERS_THEME'],
                    'XML_ID'    => $section,
                    'ACTIVE'    => 'Y'
                ];
                $res = CIBlockElement::GetList([], $arFilter, false, false, ['ID', 'XML_ID']);
                while ($arFields = $res->GetNext()) {
                    if ($arFields['XML_ID'] == $id) {
                        $newSectionId = (int)$arFields['ID'];
                    }
                }
            }

            $arFields = [
                'SORT'          => $sort,
                'MODIFIED_BY'   => $curUserId,
            ];
            if ($newSectionId > 0) {
                $arFields['IBLOCK_SECTION_ID'] = $newSectionId;
            }
            $obEl = new CIBlockElement();
            $obEl->Update($elementId, $arFields);
        } elseif ($sectionId > 0) {
            $arFields = [
                'SORT'          => $sort,
                'MODIFIED_BY'   => $curUserId,
            ];
            $obSect = new CIBlockSection();
            $obSect->Update($sectionId, $arFields);
        }

        BXClearCache(true, '/citto/controlorders/ajax/classificator/');
    }

    /**
     * Добавить предложение по модулю.
     *
     * @param string $text
     *
     * @return void
     */
    public function addOfferAction(string $text = '')
    {
        $curUserId = $GLOBALS['USER']->GetID();
        CBitrixComponent::includeComponentClass('citto:checkorders');
        $obComponent = new MainComponent();
        Loader::includeModule('sprint.migration');
        $helper = new HlblockHelper();
        $hlId = $helper->getHlblockId('ControlOrdersOffers');

        if ($hlId > 0) {
            Loader::includeModule('highloadblock');
            $hlblock = HLTable::getById($hlId)->fetch();
            $entity = HLTable::compileEntity($hlblock);
            $entityDataClass = $entity->getDataClass();

            $arFields = [
                'UF_DATE_ADD'   => date('d.m.Y H:i:s'),
                'UF_USER'       => $curUserId,
                'UF_TEXT'       => $text,
                'UF_STATUS'     => 388
            ];

            $entityDataClass::add($arFields);
            $obComponent->log(
                0,
                'Добавлено предложение пользователя',
                [
                    'METHOD'    => __METHOD__,
                    'arFields'  => $arFields,
                ]
            );
        }
    }

    /**
     * Добавить новый объект в базу
     *
     * @param array $data Массив данных объекта.
     *
     * @return array
     *
     * @throws Exception При неудачной попытке добавить объект.
     */
    public function addObjectAction(array $data = [])
    {
        CBitrixComponent::includeComponentClass('citto:checkorders');
        $obComponent = new MainComponent();
        global $USER;
        $arObject = [
            'MODIFIED_BY'       => $USER->GetID(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID'         => Settings::$iblockId['ORDERS_OBJECT'],
            'NAME'              => $data['name'],
            'DETAIL_TEXT'       => json_encode($data['fias'], JSON_UNESCAPED_UNICODE),
            'ACTIVE'            => 'Y',
        ];
        $obEl = new CIBlockElement();
        $return = [];
        if ($data['id'] > 0) {
            $obEl->Update($data['id'], $arObject);
            $obComponent->log(
                0,
                'Изменен объект',
                [
                    'METHOD'    => __METHOD__,
                    'arFields'  => $arObject,
                ]
            );
            $return = [
                'id' => $data['id'],
                'html' => $obComponent->renderObject($data['id'], true),
            ];
        } elseif ($id = $obEl->Add($arObject)) {
            $obComponent->log(
                0,
                'Добавлен новый объект',
                [
                    'METHOD'    => __METHOD__,
                    'arFields'  => $arObject,
                ]
            );
            $return = [
                'id' => $id,
                'html' => $obComponent->renderObject($id, true),
            ];
        }
        if (!empty($return)) {
            if (!empty($data['orders'])) {
                $objectId = (int)$return['id'];
                $arOrders = $data['orders'];
                $arOrders = array_map('intval', $arOrders);
                $obOrders = new Orders();
                foreach ($arOrders as $order) {
                    $arOrderObjects = $obOrders->getProperty(abs($order), 'OBJECT', true);
                    $arOrderObjects = array_map('intval', $arOrderObjects);
                    if ($order < 0) {
                        $arOrderObjects = array_diff($arOrderObjects, [$objectId]);
                    } else {
                        $arOrderObjects[] = $objectId;
                    }

                    if (empty($arOrderObjects)) {
                        $arOrderObjects = false;
                    }

                    CIBlockElement::SetPropertyValuesEx(
                        abs($order),
                        false,
                        [
                            'OBJECT' => array_unique($arOrderObjects),
                        ]
                    );
                }
            }
            return $return;
        }
        throw new Exception('Не удалось добавить объект, повторите запрос');
    }

    /**
     * Удалить объект из базы
     *
     * @param int $id ID объекта.
     *
     * @throws Exception При неудачной попытке удалить объект.
     */
    public function deleteObjectAction(int $id = 0)
    {
        if ($id <= 0) {
            throw new Exception('Не удалось удалить объект, повторите запрос');
        }
        $obEl = new CIBlockElement();
        if ($obEl->Update($id, ['ACTIVE' => 'N'])) {
            return true;
        }
        throw new Exception('Не удалось удалить объект, повторите запрос');
    }

    /**
     * Получить список объектов
     *
     * @param integer $id Если передан параметр, вернёт только этот элемент.
     *
     * @return array
     */
    public function getObjectAction(int $id = 0)
    {
        CBitrixComponent::includeComponentClass('citto:checkorders');
        $obComponent = new MainComponent();
        $arFilter = [
            'IBLOCK_ID' => Settings::$iblockId['ORDERS_OBJECT'],
            'ACTIVE'    => 'Y',
        ];
        if ($id > 0) {
            $arFilter['ID'] = $id;
        }
        $arSelect = [
            'ID',
            'NAME',
            'DETAIL_TEXT',
        ];
        $arReturn = [];
        $res = CIBlockElement::GetList(['NAME' => 'ASC'], $arFilter, false, false, $arSelect);
        while ($arFields = $res->GetNext()) {
            $arFields['DETAIL_TEXT'] = $arFields['DETAIL_TEXT'] ? Json::decode($arFields['~DETAIL_TEXT']) : [];

            $arReturn[] = [
                'id'        => $arFields['ID'],
                'name'      => $arFields['NAME'],
                'address'   => $arFields['DETAIL_TEXT']['value'] ?? '',
                'html'      => $obComponent->renderObject($arFields['ID'], true),
            ];
        }

        return $arReturn;
    }

    /**
     * Получить счётчики меню.
     *
     * @return array
     */
    public function getCountersAction()
    {
        CBitrixComponent::includeComponentClass('citto:checkorders');
        $obComponent = new MainComponent();
        $permisions = $obComponent->getPermisions();
        $curUserId = $GLOBALS['USER']->GetID();
        $filterData = $obComponent->getListFilter('temp_menu_filter_' . $curUserId);
        $filterData['!PROPERTY_VIEWS'] = '%,' . $curUserId . ',%';
        $arFilter = $filterData;
        $arCounters = [];
        unset(
            $arFilter['PROPERTY_ACTION'],
            $arFilter['PROPERTY_CONTROLER_RESH'],
            $arFilter['PROPERTY_CONTROLER_STATUS']
        );
        $arFilter['!PROPERTY_ACTION'] = [
            Settings::$arActions['DRAFT'],
            Settings::$arActions['ARCHIVE'],
        ];
        $arCounters['FULL'] = CIBlockElement::GetList([], $arFilter, [], false, []);
        foreach (Settings::$arActions as $code => $actionId) {
            $arFilter = array_merge(
                $filterData,
                [
                    'PROPERTY_ACTION' => $actionId
                ]
            );
            if (
                $code == 'NEW' &&
                !$permisions['full_access'] &&
                !$permisions['protocol']
            ) {
                $arExecutors = Executors::getList();
                foreach ($arFilter[0][-1]['PROPERTY_DELEGATION'] as $key => $value) {
                    if (in_array($arExecutors[ $value ]['PROPERTY_TYPE_CODE'], ['zampred', 'gubernator'])) {
                        unset($arFilter[0][-1]['PROPERTY_DELEGATION'][ $key ]);
                    }
                }
            }
            unset(
                $arFilter['!PROPERTY_ACTION'],
                $arFilter['PROPERTY_CONTROLER_RESH'],
                $arFilter['PROPERTY_CONTROLER_STATUS']
            );
            $arCounters[ $code ] = CIBlockElement::GetList([], $arFilter, [], false, []);
        }

        return $arCounters;
    }

    /**
     * Отправить отчет на визирование и подпись.
     *
     * @param int $reportId ID отчёта
     * @param int $orderId  ID отчёта
     *
     * @return boolean
     */
    public function sendToSignAction(int $reportId = 0, int $orderId = 0)
    {
        CBitrixComponent::includeComponentClass('citto:checkorders');
        $obComponent = new MainComponent();
        $permisions = $obComponent->getPermisions();
        $curUserId = $GLOBALS['USER']->GetID();

        $arOrder = (new Orders())->getById($orderId);
        $res = CIBlockElement::GetList(
            [
                'DATE_CREATE' => 'DESC'
            ],
            [
                'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                'PROPERTY_PORUCH'   => $orderId,
                'ID'                => $reportId,
            ],
            false,
            false,
            $obComponent->arReportFields
        );
        if ($row = $res->GetNext()) {
            $arParams['ENUM'] = $obComponent->getEnums(Settings::$iblockId['ORDERS']);
            $arParams['COMMENT_ENUM'] = $obComponent->getEnums(Settings::$iblockId['ORDERS_COMMENT']);
            $setStatus = '';
            if (empty($row['PROPERTY_VISA_VALUE'])) {
                $setStatus = 'TOSIGN';
            } else {
                $visaTypeCode = $arParams['COMMENT_ENUM']['VISA_TYPE'][ $row['PROPERTY_VISA_TYPE_ENUM_ID'] ]['EXTERNAL_ID'];
                $arSendVisaMsg = [];
                $arVisas = [];
                foreach ($row['PROPERTY_VISA_VALUE'] as $visaKey => $visaRow) {
                    [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
                    $arVisas[] = $obComponent->getUserFullName($userId);
                    if (in_array($status, ['E', 'S'])) {
                        /*
                         * Если тип визирования = по порядку,
                         * то уведомление отправить только 1 визирующему
                         */
                        if ($visaTypeCode == 'after') {
                            if (empty($arSendVisaMsg)) {
                                $arSendVisaMsg[] = $userId;
                            }
                        } else {
                            $arSendVisaMsg[] = $userId;
                        }
                    }
                }

                if (!empty($arSendVisaMsg)) {
                    $setStatus = 'TOVISA';
                    Notify::send(
                        [$row['PROPERTY_PORUCH_VALUE']],
                        'VISA',
                        $arSendVisaMsg
                    );
                } else {
                    $setStatus = 'TOSIGN';
                }
            }

            if (!empty($setStatus)) {
                $arIspolnitels = Executors::getList();
                $iSigner = (int)$row['PROPERTY_SIGNER_VALUE'];
                if ($iSigner <= 0) {
                    $iSigner = $arIspolnitels[ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_RUKOVODITEL_VALUE'];
                }
                CIBlockElement::SetPropertyValuesEx(
                    $row['PROPERTY_PORUCH_VALUE'],
                    false,
                    [
                        'WORK_INTER_STATUS' => $arParams['ENUM']['WORK_INTER_STATUS'][ $setStatus ]['ID']
                    ]
                );
                CIBlockElement::SetPropertyValuesEx(
                    $row['ID'],
                    false,
                    [
                        'COMMENT'       => false,
                        'STATUS'        => $arParams['COMMENT_ENUM']['STATUS'][ $setStatus ]['ID'],
                        'CURRENT_USER'  => $iSigner,
                    ]
                );
                $arComFilter = [
                    '!ID'               => $row['ID'],
                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                    'ACTIVE'            => 'Y',
                    'PROPERTY_TYPE'     => 1131,
                    'PROPERTY_PORUCH'   => $row['PROPERTY_PORUCH_VALUE'],
                ];
                $resCom = CIBlockElement::GetList(
                    [
                        'DATE_CREATE' => 'DESC'
                    ],
                    $arComFilter,
                    false,
                    false,
                    $obComponent->arReportFields
                );
                while ($arComFields = $resCom->GetNext()) {
                    $arNewVisa = [];
                    foreach ($arComFields['PROPERTY_VISA_VALUE'] as $row) {
                        $arNewVisa[] = str_replace(':E:', ':S:', $row);
                    }
                    if (empty($arNewVisa)) {
                        $arNewVisa = false;
                    }
                    CIBlockElement::SetPropertyValuesEx(
                        $arComFields['ID'],
                        false,
                        [
                            'VISA'          => $arNewVisa,
                            'CURRENT_USER'  => false,
                            'STATUS'        => false,
                        ]
                    );
                }

                $comment = strip_tags($row['~DETAIL_TEXT']);
                if (!empty($arVisas)) {
                    $comment .= '<br/><br/>Визирующие: ' . implode(', ', $arVisas);
                }
                $obComponent->addToLog('Добавлен комментарий исполнителя', $comment);

                $obComponent->log(
                    $row['PROPERTY_PORUCH_VALUE'],
                    'Добавлен комментарий исполнителя',
                    [
                        'METHOD'    => __METHOD__,
                        'REQUEST'   => $_REQUEST,
                        'reportId'  => $reportId,
                        'orderId'   => $orderId,
                        '$comment'  => $comment,
                    ]
                );
            }

            return true;
        }

        return false;
    }

    /**
     * pdfGenerateAction
     *
     * @param string  $data        Текст отчёта.
     * @param string  $action_head Тип выгружаемого отчета.
     * @param integer $id          ID протокола.
     * @param string  $visa        Список людей, кем согласован отчет.
     * @param integer $file_id     ID существующего файла.
     * @param integer $report_id   ID отчёта.
     *
     * @return array
     */
    public function pdfGenerateAction(
        string $data = '',
        string $action_head = '',
        int $id = 0,
        string $visa = '',
        int $file_id = 0,
        int $report_id = 0
    ) {
        Loader::includeModule('citto.filesigner');
        $fileName = $id . '_' . $file_id . '_' . time();
        if ($file_id > 0) {
            $arFileData = Signer::getFiles([$file_id])[ $file_id ];

            $path = $_SERVER['DOCUMENT_ROOT'] . $arFileData['SRC'];
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/checkorders/' . $fileName . '.pdf', file_get_contents($path));

            $pdf = CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'] . '/upload/checkorders/' . $fileName . '.pdf');
            $newFileId = CFile::SaveFile($pdf, 'checkorders');

            foreach ($arFileData['SIGNS'] as $userId => $userData) {
                foreach ($userData['SIGNS'] as $arSign) {
                    $arFileArray            = CFile::GetFileArray($arSign['ID']);
                    $arFile                 = CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'] . $arSign['SRC']);
                    $arFile['MODULE_ID']    = 'pdf_file';
                    $arFile['name']         = $arFileArray['ORIGINAL_NAME'];
                    $arFile['type']         = 'application/x-pkcs7-certreqresp';
                    $arFile['description']  = $arFileArray['DESCRIPTION'];
                    $arFile['external_id']  = str_replace($file_id, $newFileId, $arFileArray['EXTERNAL_ID']);

                    CFile::SaveFile($arFile, 'pdfile', true);
                }
            }

            return [
                'sessid'    => bitrix_sessid(),
                'file_id'   => $newFileId,
            ];
        }
        $html = $data;
        if ($id > 0) {
            $res = CIBlockElement::GetList(
                [
                    'DATE_CREATE' => 'DESC'
                ],
                [
                    'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
                    'ID'        => $id,
                    'ACTIVE'    => 'Y',
                ],
                false,
                [
                    'nPageSize' => 1
                ],
                [
                    'ID',
                    'NAME',
                    'PROPERTY_NUMBER',
                    'PROPERTY_DATE_CREATE',
                    'PROPERTY_ISPOLNITEL',
                    'PROPERTY_DATE_ISPOLN',
                    'DETAIL_TEXT',
                ]
            );
            if ($arFields = $res->GetNext()) {
                if ($report_id > 0 && empty($data)) {
                    $resReport = CIBlockElement::GetList(
                        [
                            'DATE_CREATE' => 'DESC'
                        ],
                        [
                            'IBLOCK_ID' => Settings::$iblockId['ORDERS_COMMENT'],
                            'ID'        => $report_id,
                            'ACTIVE'    => 'Y',
                        ],
                        false,
                        [
                            'nPageSize' => 1
                        ],
                        [
                            'ID',
                            'DETAIL_TEXT',
                            'PROPERTY_VISA',
                            'PROPERTY_ECP',
                            'PROPERTY_FILE_ECP'
                        ]
                    );

                    if ($arReportFields = $resReport->GetNext()) {
                        if ($arReportFields['PROPERTY_ECP_VALUE'] != '') {
                            return [
                                'sessid'    => bitrix_sessid(),
                                'file_id'   => $arReportFields['PROPERTY_FILE_ECP_VALUE'],
                            ];
                        }
                        $data = $arReportFields['~DETAIL_TEXT'];
                        global $userFields;
                        $arVisa = [];
                        if (count($arReportFields['PROPERTY_VISA_VALUE']) > 0) {
                            foreach ($arReportFields['PROPERTY_VISA_VALUE'] as $vKey => $visaRow) {
                                [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
                                if ($status == 'Y') {
                                    $userData = $userFields($userId);
                                    $arVisa[] = $userData['FIO_INIC_REV'];
                                }
                            }
                        }
                        $visa = implode(', ', $arVisa);
                    }
                    $action_head = 'ispolnitel_main';
                }
                switch ($action_head) {
                    case 'ispolnitel_main':
                        $arResult['ISPOLNITELS'] = Executors::getList();
                        $html = '<h1>Отчет об исполнении поручения</h1><br><br>';
                        $html .= '<u><b>Документ:</b></u> ' . $arFields['NAME'] . ' № ' . $arFields['PROPERTY_NUMBER_VALUE'] . ' от ' . $arFields['PROPERTY_DATE_CREATE_VALUE'] . '<br><br>';
                        $html .= '<u><b>Исполнитель:</b></u> ' . $arResult['ISPOLNITELS'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ]['NAME'] . '<br><br>';
                        $html .= '<u><b>Срок исполнения:</b></u> ' . ($arFields['PROPERTY_DATE_ISPOLN_VALUE'] != '31.12.2099' ?
                            $arFields['PROPERTY_DATE_ISPOLN_VALUE'] :
                            'Без срока') . '<br><br>';
                        $html .= '<u><b>Дата предоставления отчета:</b></u> ' . date('d.m.Y') . '<br><br>';

                        $html .= '<u><b>Содержание поручения:</b></u> ' . $arFields['~DETAIL_TEXT'] . '<br><br>';
                        $html .= '<u><b>Содержание отчета:</b></u> ' . $data;
                        if (!empty($visa)) {
                            $html .= '<u><b>Согласовано:</b></u> ' . $visa . '<br><br>';
                        }
                        break;
                    case 'controler':
                        $html = '<h1>Отчет контролера</h1><br><br>';
                        $html .= $data;
                        break;
                    case 'kurator':
                        $html = '<h1>Заметка Куратора</h1><br><br>';
                        $html .= $data;
                        break;
                    default:
                        $html = $data;
                        break;
                }
            }
        } else {
            switch ($action_head) {
                case 'ispolnitel_main':
                    $html = '<h1>Отчет исполнителя</h1><br><br>';
                    $html .= $data;
                    break;
                case 'controler':
                    $html = '<h1>Отчет контролера</h1><br><br>';
                    $html .= $data;
                    break;
                case 'kurator':
                    $html = '<h1>Заметка Куратора</h1><br><br>';
                    $html .= $data;
                    break;
                default:
                    $html = $data;
                    break;
            }
        }

        $pdfFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/checkorders/' . $fileName . '.pdf';
        $respFile = fopen($pdfFile, 'w+');
        $respCode = null;
        $ch = curl_init(\Citto\Filesigner\File::CONVERTER_URL);
        curl_setopt($ch, CURLOPT_USERPWD, \Citto\Filesigner\File::CONVERTER_AUTH);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FILE, $respFile);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'html'          => $html,
            'src'           => null,
            'mpdf_params'   => [],
        ]));
        curl_exec($ch);
        $respCode = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        fclose($respFile);
        if ($respCode !== 200) {
            throw new Exception('Не удалось создать PDF (' . $respCode . ')');
        }

        $pdf = CFile::MakeFileArray($pdfFile);
        $fileId = CFile::SaveFile($pdf, 'checkorders');
        unlink($pdfFile);
        return [
            'sessid'    => bitrix_sessid(),
            'file_id'   => $fileId,
            'filename'  => $fileName,
        ];
    }

    public function returnReportAction(
        int $orderId = 0,
        int $reportId = 0,
        string $comment = ''
    ) {
        if (empty($comment)) {
            throw new Exception('Не указан комментарий', 1);
            return;
        }
        $curUserId = $GLOBALS['USER']->GetID();
        CBitrixComponent::includeComponentClass('citto:checkorders');
        $obComponent = new MainComponent();
        $arComFilter = [
            'ID'                => $reportId,
            'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
            'ACTIVE'            => 'Y',
            'PROPERTY_TYPE'     => 1131,
            'PROPERTY_PORUCH'   => $orderId,
        ];
        $resCom = CIBlockElement::GetList(
            [
                'DATE_CREATE' => 'DESC'
            ],
            $arComFilter,
            false,
            false,
            $obComponent->arReportFields
        );
        if ($arComFields = $resCom->GetNext()) {
            CIBlockElement::SetPropertyValuesEx(
                $arComFields['ID'],
                false,
                [
                    'COMMENT'       => '(' . date('d.m.Y H:i:s') . ') [' . $obComponent->getUserFullName($curUserId) . '] ' . $comment,
                    'CURRENT_USER'  => $arComFields['PROPERTY_USER_VALUE'],
                    'STATUS'        => false,
                ]
            );
            $obComponent->log(
                $orderId,
                'Отчет возвращен исполнителю',
                [
                    'METHOD'    => __METHOD__,
                    'REQUEST'   => $_REQUEST,
                ]
            );
            CIBlockElement::SetPropertyValuesEx(
                $orderId,
                false,
                [
                    'WORK_INTER_STATUS' => false,
                    'VIEWS'             => false,
                ]
            );
        }
        return [
            'ORDER' => $orderId,
            'REPORT' => $reportId,
            'COMMENT' => $comment,
        ];
    }

    /**
     * Отправить отчёт на контроль или дальше
     *
     * @param int $orderId  ID поручения
     * @param int $reportId ID отчета
     * @param int $fileId   ID файла
     *
     * @return boolean
     */
    public function sendToControlAction(
        int $orderId = 0,
        int $reportId = 0,
        int $fileId = 0
    ) {
        CBitrixComponent::includeComponentClass('citto:checkorders');
        $obComponent = new MainComponent();
        $permisions = $obComponent->getPermisions();
        $curUserId = $GLOBALS['USER']->GetID();

        $arOrder = (new Orders())->getById($orderId);
        $arExecutors = Executors::getList();
        $arParams['ENUM'] = $obComponent->getEnums(Settings::$iblockId['ORDERS']);
        $arParams['COMMENT_ENUM'] = $obComponent->getEnums(Settings::$iblockId['ORDERS_COMMENT']);

        $res = CIBlockElement::GetList(
            [
                'DATE_CREATE' => 'DESC'
            ],
            [
                'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                'PROPERTY_PORUCH'   => $orderId,
                'ID'                => $reportId,
            ],
            false,
            false,
            $obComponent->arReportFields
        );
        $arReport = [];
        if ($row = $res->GetNext()) {
            $arReport = $row;
        }

        $el = new CIBlockElement();

        $arLoadProductArray = [
            'MODIFIED_BY'       => $curUserId,
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
            'PROPERTY_VALUES'   => [
                'PORUCH'        => $orderId,
                'USER'          => $curUserId,
                'TYPE'          => 1131,
                'DOCS'          => $arReport['PROPERTY_DOCS_VALUE'] ?? false,
                'VISA'          => $arReport['PROPERTY_VISA_VALUE'] ?? false,
                'VISA_TYPE'     => $arReport['PROPERTY_VISA_TYPE_VALUE'] ?? false,
                'BROKEN_SROK'   => 'N',
                'DATE_FACT'     => $arReport['PROPERTY_DATE_FACT_VALUE'],
                'SIGNER'        => $curUserId,
                'STATUS'        => false,
                'ECP'           => $fileId > 0 ? $curUserId : false,
                'FILE_ECP'      => $fileId > 0 ? $fileId : false,
            ],
            'NAME'              => $curUserId . '-' . $orderId . '-' . date('d-m-Y_H:i:s'),
            'ACTIVE'            => 'Y',
            'PREVIEW_TEXT'      => $arReport['~DETAIL_TEXT'],
            'DETAIL_TEXT'       => $arReport['~DETAIL_TEXT'],
        ];

        $arDelegator = [];
        /*
         * Если поручение делегировано сверху, то проверим тип того кто делегировал
         */
        if (
            !empty($arOrder['PROPERTY_DELEGATION_VALUE']) &&
            (int)$arOrder['PROPERTY_DELEGATION_VALUE'][0] > 0
        ) {
            $arDelegator = $arExecutors[ $arOrder['PROPERTY_DELEGATION_VALUE'][0] ];
        }

        $arSendDelegId = [
            250900, // Якушкина Г.И.
            250902, // Гремякова О.П.
        ];
        if (
            $arDelegator['PROPERTY_TYPE_CODE'] == 'gubernator' &&
            $curUserId != $arDelegator['PROPERTY_RUKOVODITEL_VALUE']
        ) {
            $arLoadProductArray['PROPERTY_VALUES']['CURRENT_USER'] = $arDelegator['PROPERTY_RUKOVODITEL_VALUE'];
            $arLoadProductArray['PROPERTY_VALUES']['STATUS'] = $arParams['COMMENT_ENUM']['STATUS']['TOVISA']['ID'];

            $newReportId = $el->Add($arLoadProductArray);

            $arSendMsgUsers = $arDelegator['PROPERTY_IMPLEMENTATION_VALUE'];

            if (in_array($arDelegator['ID'], $arSendDelegId)) {
                $arSendMsgUsers[] = $arDelegator['PROPERTY_RUKOVODITEL_VALUE'];
            }
            if (!empty($arSendMsgUsers)) {
                Notify::send(
                    [$orderId],
                    'VISA',
                    $arSendMsgUsers
                );
            }
            CIBlockElement::SetPropertyValuesEx(
                $orderId,
                false,
                [
                    'WORK_INTER_STATUS' => $arParams['ENUM']['WORK_INTER_STATUS']['TOVISA']['ID'],
                ]
            );
        } elseif (
            $arDelegator['PROPERTY_TYPE_CODE'] == 'zampred' &&
            $curUserId != $arDelegator['PROPERTY_RUKOVODITEL_VALUE']
        ) {
            $setStatus = 'TOSIGN';
            $arLoadProductArray['PROPERTY_VALUES']['CURRENT_USER'] = $arDelegator['PROPERTY_RUKOVODITEL_VALUE'];
            $arLoadProductArray['PROPERTY_VALUES']['STATUS'] = $arParams['COMMENT_ENUM']['STATUS']['TOSIGN']['ID'];

            $newReportId = $el->Add($arLoadProductArray);

            $arSendMsgUsers = $arDelegator['PROPERTY_IMPLEMENTATION_VALUE'];

            if (in_array($arDelegator['ID'], $arSendDelegId)) {
                $arSendMsgUsers[] = $arDelegator['PROPERTY_RUKOVODITEL_VALUE'];
            }
            if (!empty($arSendMsgUsers)) {
                Notify::send(
                    [$orderId],
                    'SIGN',
                    $arSendMsgUsers
                );
            }
            CIBlockElement::SetPropertyValuesEx(
                $orderId,
                false,
                [
                    'WORK_INTER_STATUS' => $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID'],
                ]
            );
        } else {
            /*
             * Нарушение срока представления отчета фиксируется только
             * по главному исполнителю, когда он нажал на кнопку "Отправить на контроль"
             */
            if (strtotime($arOrder['PROPERTY_CURRENT_DATE_ISPOLN_VALUE'] . ' 23:59:59') < time()) {
                $arLoadProductArray['PROPERTY_VALUES']['BROKEN_SROK'] = 'Y';
            }

            if ($newReportId = $el->Add($arLoadProductArray)) {
                $obComponent->log(
                    $orderId,
                    'Отправлено на контроль',
                    [
                        'METHOD'    => __METHOD__,
                        'REQUEST'   => $_REQUEST,
                        'orderId'   => $orderId,
                        'reportId'  => $reportId,
                        'fileId'  => $fileId,
                    ]
                );
                $obComponent->addToLog('Отправлено на контроль', '', $orderId);
                CIBlockElement::SetPropertyValuesEx(
                    $orderId,
                    false,
                    [
                        'ACTION'            => Settings::$arActions['CONTROL'],
                        'DATE_FACT_ISPOLN'  => date('d.m.Y'),
                        'CONTROLER_STATUS'  => $arParams['ENUM']['CONTROLER_STATUS']['on_beforing']['ID'],
                        'WORK_INTER_STATUS' => false,
                    ]
                );
                $arNewVisa = [];
                foreach ($arLoadProductArray['PROPERTY_VALUES']['VISA'] as $row) {
                    $arNewVisa[] = str_replace(':E:', ':S:', $row);
                }
                /*
                 * Убрать текущего пользователя и возможность визирования у последнего отчёта
                 */
                CIBlockElement::SetPropertyValuesEx(
                    $newReportId,
                    false,
                    [
                        'CURRENT_USER'  => false,
                        'STATUS'        => $arParams['COMMENT_ENUM']['STATUS']['SIGN']['ID'],
                        'VISA'          => empty($arNewVisa) ? false : $arNewVisa,
                    ]
                );
                CIBlockElement::SetPropertyValuesEx(
                    $reportId,
                    false,
                    [
                        'CURRENT_USER'  => false,
                        'STATUS'        => $arParams['COMMENT_ENUM']['STATUS']['SIGN']['ID'],
                    ]
                );
            } else {
                $obComponent->log(
                    $orderId,
                    'Ошибка отправки на контроль',
                    [
                        'METHOD'    => __METHOD__,
                        'REQUEST'   => $_REQUEST,
                        'orderId'   => $orderId,
                        'reportId'  => $reportId,
                        'fileId'    => $fileId,
                        'ERROR'     => $el->LAST_ERROR,
                    ]
                );
            }
        }

        return [
            'ORDER' => $orderId,
            'REPORT' => $reportId,
            'FILE' => $fileId,
        ];
    }

    /**
     * Принять или отклонить предложение о новых исполнителях.
     *
     * @param int    $id      ID предложения.
     * @param string $value   Решение.
     * @param string $comment Комментарий при отклонении.
     *
     * @return boolean
     */
    public function acceptIspolnitelAction(
        int $id = 0,
        string $value = 'Y',
        string $comment = ''
    ) {
        if (!in_array($value, ['Y', 'N', 'D'])) {
            throw new Exception('Unknown value');
        }
        if ($value == 'N' && empty($comment)) {
            throw new Exception('Empty comment');
        }

        CBitrixComponent::includeComponentClass('citto:checkorders');
        $obComponent = new MainComponent();
        $obComponent->initFields();

        $helper = new HlblockHelper();
        $hlId = $helper->getHlblockId('ControlOrdersResolution');
        Loader::includeModule('highloadblock');
        $hlblock = HLTable::getById($hlId)->fetch();
        $entity = HLTable::compileEntity($hlblock);
        $entityDataClass = $entity->getDataClass();

        $arResolution = $entityDataClass::getById($id)->fetch();

        $permisions = $obComponent->getPermisions();
        $curUserId = $GLOBALS['USER']->GetID();

        $arOrder = (new Orders())->getById($arResolution['UF_ORDER']);
        $arExecutors = Executors::getList();

        if ($value == 'Y') {
            $arParams['ENUM'] = $obComponent->getEnums(Settings::$iblockId['ORDERS']);
            $arParams['COMMENT_ENUM'] = $obComponent->getEnums(Settings::$iblockId['ORDERS_COMMENT']);

            if (!empty($arResolution['UF_ISPOLNITEL'])) {
                if (false !== mb_strpos($arResolution['UF_ISPOLNITEL'], 'DEP')) {
                    $newIspolnitel = (int)str_replace('DEP', '', $arResolution['UF_ISPOLNITEL']);

                    $oldIspolnitel = $arOrder['PROPERTY_ISPOLNITELI_VALUE'];

                    $resDelegation = CIBlockElement::GetProperty(
                        Settings::$iblockId['ORDERS'],
                        $arResolution['UF_ORDER'],
                        'sort',
                        'asc',
                        [
                            'CODE' => 'DELEGATION'
                        ]
                    );
                    $arDelegation = [];
                    while ($rowDelegation = $resDelegation->GetNext()) {
                        if (empty($rowDelegation['VALUE'])) {
                            continue;
                        }
                        $arDelegation[] = [
                            'VALUE'         => $rowDelegation['VALUE'],
                            'DESCRIPTION'   => $rowDelegation['~DESCRIPTION'],
                        ];
                    }

                    if (empty($arDelegation)) {
                        $arDelegation[] = [
                            'VALUE'         => $oldIspolnitel,
                            'DESCRIPTION'   => '',
                        ];
                    }
                    $arDelegation[] = [
                        'VALUE'         => $newIspolnitel,
                        'DESCRIPTION'   => json_encode([
                            'DATE_ADD'      => date('d.m.Y'),
                            'DATE_ADD_TS'   => time(),
                        ]),
                    ];
                    CIBlockElement::SetPropertyValuesEx(
                        $arResolution['UF_ORDER'],
                        false,
                        [
                            'DELEGATION' => $arDelegation,
                            'ISPOLNITEL' => $newIspolnitel,
                        ]
                    );
                    $message = 'Делегировано новому исполнителю ' . $arExecutors[ $newIspolnitel ]['NAME'];
                    $subMessage = false;
                    if (!empty($arResolution['UF_COMMENT'])) {
                        $subMessage = 'Комментарий: ' . $arResolution['UF_COMMENT'];
                    }
                    $obComponent->log(
                        $arResolution['UF_ORDER'],
                        $message,
                        [
                            'METHOD'    => __METHOD__,
                            'REQUEST'   => $_REQUEST,
                        ]
                    );
                    $obComponent->addToLog($message, $subMessage, $arResolution['UF_ORDER']);
                } else {
                    CIBlockElement::SetPropertyValuesEx(
                        $arResolution['UF_ORDER'],
                        false,
                        [
                            'ACTION'            => Settings::$arActions['WORK'],
                            'DELEGATE_USER'     => $arResolution['UF_ISPOLNITEL'],
                            'DELEGATE_COMMENT'  => $arResolution['UF_COMMENT']??'',
                        ]
                    );
                    $message = 'Принято на исполнение, делегировано на ' . $obComponent->getUserFullName($arResolution['UF_ISPOLNITEL']);
                    $obComponent->log(
                        $arResolution['UF_ORDER'],
                        $message,
                        [
                            'METHOD'    => __METHOD__,
                            'REQUEST'   => $_REQUEST,
                        ]
                    );
                    $obComponent->addToLog($message);
                }
            }

            if (!empty($arResolution['UF_SUBEXECUTOR'])) {
                $arResolution['UF_SUBEXECUTOR'] = json_decode($arResolution['UF_SUBEXECUTOR'], true);
                $arResolution['UF_SUBEXECUTOR'] = array_filter($arResolution['UF_SUBEXECUTOR']);
                $arAccomplices = $arOrder['PROPERTY_ACCOMPLICES_VALUE'];
                $arSubExecutors = [];
                foreach ($arOrder['PROPERTY_SUBEXECUTOR_VALUE'] as $key => $subVal) {
                    $depId = $subVal;
                    $userId = $arOrder['~PROPERTY_SUBEXECUTOR_DESCRIPTION'][ $key ];
                    if (false !== mb_strpos($subVal, ':')) {
                        $val = explode(':', $subVal);
                        $depId = $val[0];
                        $userId = $val[1];
                    }
                    $arSubExecutors[ $depId ] = [
                        'VALUE'         => $depId . ':' . $userId,
                        'DESCRIPTION'   => $userId,
                    ];
                }
                foreach ($arResolution['UF_SUBEXECUTOR'] as $subRow) {
                    if (empty($subRow)) {
                        continue;
                    }
                    if (false !== mb_strpos($subRow, 'DEP')) {
                        $subRow = str_replace('DEP', '', $subRow);
                        $arSubExecutors[ $subRow ] = [
                            'VALUE'         => $subRow . ':0',
                            'DESCRIPTION'   => 0
                        ];
                    } else {
                        $arAccomplices[] = $subRow;
                    }
                }
                $arAccomplices = array_unique(array_filter($arAccomplices));
                $arSubExecutors = array_unique(array_filter($arSubExecutors));
                if (empty($arAccomplices)) {
                    $arAccomplices = false;
                }
                if (empty($arSubExecutors)) {
                    $arSubExecutors = false;
                }

                CIBlockElement::SetPropertyValuesEx(
                    $arResolution['UF_ORDER'],
                    false,
                    [
                        'ACCOMPLICES'       => $arAccomplices,
                        'SUBEXECUTOR'       => $arSubExecutors,
                        'DELEGATE_COMMENT'  => $arResolution['UF_COMMENT']??'',
                    ]
                );
                $arNames = [];
                foreach ($arAccomplices as $uId) {
                    $arNames[] = $obComponent->getUserFullName($uId);
                }
                foreach ($arSubExecutors as $uId) {
                    $arNames[] = $arExecutors[ $uId ]['NAME'];
                }

                if (!empty($arNames)) {
                    $obComponent->log(
                        $arResolution['UF_ORDER'],
                        'Соисполнители: ' . implode(', ', $arNames),
                        [
                            'METHOD'    => __METHOD__,
                            'REQUEST'   => $_REQUEST,
                        ]
                    );
                    $obComponent->addToLog('Соисполнители: ' . implode(', ', $arNames));
                }
            }

            $rsData = $entityDataClass::getList([
                'filter' => [
                    '!ID'           => $id,
                    'UF_ORDER'      => $arResolution['UF_ORDER'],
                    'UF_APPROVE'    => $obComponent->arResolutionStatus['E']['ID'],
                ],
                'order'  => [
                    'UF_DATE' => 'DESC',
                ],
            ]);
            while ($arRes = $rsData->fetch()) {
                $arUpdate = [
                    'UF_APPROVE'        => $obComponent->arResolutionStatus['D']['ID'],
                    'UF_APPROVE_USER'   => $curUserId,
                    'UF_APPROVE_DATE'   => date('d.m.Y H:i:s'),
                ];

                $entityDataClass::update($arRes['ID'], $arUpdate);
            }
        }

        $arUpdate = [
            'UF_APPROVE'        => $obComponent->arResolutionStatus[ $value ]['ID'],
            'UF_APPROVE_USER'   => $curUserId,
            'UF_APPROVE_DATE'   => date('d.m.Y H:i:s'),
            'UF_REJECT_COMMENT' => trim($comment),
        ];

        $entityDataClass::update($id, $arUpdate);

        if ($value == 'N') {
            $message = '[B]Проект резолюции отклонен[/B]#BR##BR#';
            $message .= $arOrder['NAME'];
            $message .= ' № ' . $arOrder['PROPERTY_NUMBER_VALUE'];
            $message .= ' от ' . $arOrder['PROPERTY_DATE_CREATE_VALUE'];
            if (!empty($arResolution['UF_ISPOLNITEL'])) {
                $message .= '#BR##BR#[B]Исполнитель[/B]: ' . $obComponent->getUserFullName($arResolution['UF_ISPOLNITEL']);
            }
            if (!empty($arResolution['UF_SROK'])) {
                $message .= '#BR#[B]Срок для исполнителя[/B]: ' . $arResolution['UF_SROK'];
            }
            if (!empty($arResolution['UF_SUBEXECUTOR'])) {
                $arSubExec = json_decode($arResolution['UF_SUBEXECUTOR'], true);
                foreach ($arSubExec as $key => $value) {
                    $arSubExec[ $value ] = $value;
                    unset($arSubExec[ $key ]);
                }
                $arSubExec = array_map(function ($id) use ($obComponent, $arExecutors) {
                    if (false === mb_strpos($id, 'DEP')) {
                        return $obComponent->getUserFullName($id);
                    } else {
                        return $arExecutors[ str_replace('DEP', '', $id) ]['NAME'];
                    }
                }, $arSubExec);

                $message .= '#BR#[B]Соисполнители[/B]: ' . implode(', ', $arSubExec);
            }
            if (!empty($arResolution['UF_COMMENT'])) {
                $message .= '#BR#[B]Комментарий[/B]: ' . $arResolution['UF_COMMENT'];
            }

            if (!empty($comment)) {
                $message .= '#BR##BR#[B]Замечание руководителя:[/B] ' . $comment;
            }

            $link = 'https://' . $_SERVER['SERVER_NAME'] . '/control-orders/?detail=' . $arOrder['ID'];

            $message .= '#BR##BR#Перейти к поручению:#BR#[URL=' . $link . ']' . $arOrder['~DETAIL_TEXT'] . '[/URL]';

            Notify::send(
                [$arOrder['ID']],
                'ACCEPT_ISPOLNITEL_REJECT',
                [$arResolution['UF_AUTHOR']],
                $message,
                true,
                []
            );
        }

        return true;
    }

    /**
     * Сохранить настройки виджета статистики.
     *
     * @param array $data Список виджетов.
     *
     * @return boolean
     */
    public function setWidgetSettingsAction(array $data = []) {
        $userId = $GLOBALS['USER']->GetID();
        CUserOptions::SetOption('citto:checkorders', 'widget_stats', $data, false, $userId);
        return true;
    }

    /**
     * Получить элементы для карты
     *
     * @return array
     */
    public function getOrdersMapAction() {
        CBitrixComponent::includeComponentClass('citto:checkorders');
        $obComponent = new MainComponent();

        $list_id = 'control-orders-list-map';

        Loader::includeModule('iblock');

        $res = CIBlockElement::GetList(
            false,
            ['IBLOCK_ID' => 511, 'ACTIVE' => 'Y',],
            false,
            false,
            ['ID', 'NAME', 'DETAIL_TEXT',]
        );
        $arObjects = [];
        while ($arFields = $res->GetNext()) {
            $arFields['DADATA'] = json_decode($arFields['~DETAIL_TEXT'], true);
            $arFields['ITEMS'] = [];
            $arObjects[ $arFields['ID'] ] = $arFields;
        }

        $arFilter = $obComponent->getListFilter($list_id);
        $arFilter['!PROPERTY_OBJECT'] = false;
        $arFilter['PROPERTY_POSITION_TO'] = false;

        $res = CIBlockElement::GetList(
            false,
            $arFilter,
            false,
            false,
            [
                'ID',
                'NAME',
                'DETAIL_TEXT',
                'PROPERTY_TYPE',
                'PROPERTY_NUMBER',
                'PROPERTY_OBJECT',
                'PROPERTY_DATE_CREATE',
                'PROPERTY_ACTION',
            ]
        );
        while ($arFields = $res->GetNext()) {
            foreach ($arFields['PROPERTY_OBJECT_VALUE'] as $object) {
                $arObjects[ $object ]['ITEMS'][] = $arFields;
            }
        }

        $arItems = [];
        foreach ($arObjects as $object) {
            if (empty($object['ITEMS'])) {
                continue;
            }

            $color = 'blue';
            $description = '<h5>Объект "' . $object['NAME'] . '"</h5>';
            if (!empty($object['DADATA']['value'])) {
                $description .= '<b>Адрес:</b> ' . $object['DADATA']['value'] . '<br/><br/>';
            }

            $description .= '<b>Поручения:</b><ul>';
            $arStats = [];

            foreach ($object['ITEMS'] as $order) {
                if ($order['PROPERTY_ACTION_ENUM_ID'] == 1140) {
                    $arStats['archive']++;
                } else if (
                    in_array('7qCIhAcZ', $order['PROPERTY_TYPE_VALUE']) ||
                    in_array('no_ispoln', $order['PROPERTY_TYPE_VALUE'])
                ) {
                    $color = 'red';
                    $arStats['no_ispoln']++;
                } else {
                    $arStats['work']++;
                }
                $text = trim(strip_tags($order['~DETAIL_TEXT']));
                $defText = $order['NAME'] . ' № ' . $order['PROPERTY_NUMBER_VALUE'] . ' от ' . $order['PROPERTY_DATE_CREATE_VALUE'];
                if (empty($text)) {
                    $text = $defText;
                } else {
                    $subText = mb_substr($text, 0, 150);
                    if ($text != $subText) {
                        $text = '<span title="' . ($defText . "\r\n\r\n" . $text) . '">' . $subText . '...</span>';
                    }
                }
                $description .= '<li><a href="/control-orders/?detail=' . $order['ID'] . '" target="_blank" title="' . strip_tags($order['~DETAIL_TEXT']) . '">' . $text . '</a></li>';
            }
            $description .= '</ul>';

            if (!empty($arStats)) {
                $description .= '<table border="1">';
                if ($arStats['archive']) {
                    $description .= '
                    <tr>
                        <th>В архиве:</th>
                        <td><a href="/control-orders/?action_filter=1140">' . $arStats['archive'] . '</a></td>
                    </tr>
                    ';
                }
                if ($arStats['work']) {
                    $description .= '
                    <tr>
                        <th>В работе:</th>
                        <td><a href="/control-orders/?objectId=' . $object['ID'] . '&from_stats=map_blue" target="_blank">' . $arStats['work'] . '</a></td>
                    </tr>
                    ';
                }
                if ($arStats['no_ispoln']) {
                    $description .= '
                    <tr>
                        <th>Просрочено:</th>
                        <td><a href="/control-orders/?objectId=' . $object['ID'] . '&from_stats=map_red" target="_blank">' . $arStats['no_ispoln'] . '</a></td>
                    </tr>
                    ';
                }
                $description .= '</table>';
            }

            $arItems[] = [
                'coord' => [
                    $object['DADATA']['data']['geo_lon'],
                    $object['DADATA']['data']['geo_lat'],
                ],
                'name' => $object['NAME'] . ' (' . count($object['ITEMS']) . ')',
                'desc' => $description,
                'preset' => 'islands#' . $color . 'CircleDotIcon',
            ];
        }

        return $arItems;
    }

    public function getWidgetStatsAction() {
        CBitrixComponent::includeComponentClass('citto:checkorders');
        $obComponent = new MainComponent();

        return $obComponent->getWidgetStats();
    }
}
