<?php

namespace Citto\Integration\Delo;

use CUser;
use Exception;
use SoapClient;
use Bitrix\Main\{Config\Option, Loader, Web\Json};
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;

class Users extends \Citto\Integration\Delo
{
    public $deloUsersHLId = 0;

    public $entityDataClass = null;

    /**
     * Конструктор
     *
     * @return null
     */
    public function __construct()
    {
        Loader::includeModule('highloadblock');
        $this->deloUsersHLId = HLTable::getList(
            ['filter' => ['NAME' => 'DeloUsers']]
        )->fetch()['ID'];

        if ($this->deloUsersHLId > 0) {
            $hlblock = HLTable::getById($this->deloUsersHLId)->fetch();
            $entity = HLTable::compileEntity($hlblock);
            $this->entityDataClass = $entity->getDataClass();
        }

        parent::__construct();
    }

    /**
     * Получить список исполнителей из Дело
     *
     * @param bool $onlyActive Брать только активных пользователей
     *
     * @return array
     */
    public function getList(bool $onlyActive = true): array
    {
        $arUsers = [];
        if ($this->entityDataClass !== null) {
            $arFilter = [];
            if ($onlyActive) {
                $arFilter['UF_ACTIVE'] = 1;
            }
            $rsData = $this->entityDataClass::getList(
                [
                    'filter' => $arFilter
                ]
            );
            while ($arData = $rsData->Fetch()) {
                $arUsers[ $arData['ID'] ] = $arData;
            }
        }

        return $arUsers;
    }

    /**
     * Построение дерева пользователей
     *
     * @param array $arUsers
     * @param int $id
     * @param array $arSelected
     *
     * @return array
     */
    private static function _tree(array $arUsers, int $id = 0, array $arSelected = []): array
    {
        $arReturn = [];

        usort(
            $arUsers[ $id ],
            function ($a, $b) {
                if ($a['WEIGHT'] != $b['WEIGHT']) {
                    return $a['WEIGHT'] < $b['WEIGHT'];
                }
                return strnatcmp($a['UF_NAME'], $b['UF_NAME']);
            }
        );

        foreach ($arUsers[ $id ] as $cat) {
            $arReturn[] = [
                'id' => $cat['ID'],
                'text' => $cat['UF_NAME'],
                'state' => [
                    'disabled' => isset($arUsers[ $cat['UF_ISN'] ]),
                    'selected' => in_array($cat['ID'], $arSelected),
                    'opened' => in_array($cat['ID'], $arSelected),
                ],
                'children' => (isset($cat['HAS_CHILDRED']) ?
                                self::_tree($arUsers, $cat['UF_ISN'], $arSelected) :
                                null
                            ),
                'a_attr' => [
                    'title' => $cat['UF_DUTY']
                ]
            ];
        }

        return $arReturn;
    }

    /**
     * Дерево исполнителей из Дело
     *
     * @return array
     */
    public function getTree(): array
    {
        $arUsers = $this->getList();

        $arResult = [];
        foreach ($arUsers as $arUser) {
            $arResult[ $arUser['UF_ISN_HIGH_NODE'] ][] = $arUser;
        }

        foreach ($arResult as $high => $list) {
            foreach ($list as $id => $row) {
                $hasChild = isset($tree[ $row['UF_ISN'] ]);
                $arResult[ $high ][ $id ]['HAS_CHILDRED'] = $hasChild;
                $arResult[ $high ][ $id ]['WEIGHT'] = $hasChild ? 100000 : 10;
            }
        }
        return $arResult;
    }

    /**
     * Получить дерево исполнителей для плагина jsTree
     *
     * @param array $arSelected
     *
     * @return array
     */
    public function getJsonTree(array $arSelected): array
    {
        $arUsers = $this->getTree();

        return self::_tree($arUsers, 0, $arSelected);
    }

    /**
     * Агент для синхронизации с АСЭД
     *
     * @return string
     *
     * @todo ЛОГИ!
     */
    public static function sync(): string
    {
        $self = new static();

        if ($self->logger) {
            $self->logger->info(__METHOD__);
        }

        if ($self->entityDataClass === null) {
            return __METHOD__ . '();';
        }

        $obClient = new SoapClient($self->serviceUrl);

        $arExist = [];
        $arPortalUsers = [];

        $arUsers = $self->getList();
        foreach ($arUsers as $row) {
            $arExist[ $row['UF_DUE'] ] = $row;
        }

        if ($self->logger) {
            $self->logger->info('Сейчас в HL ' . count($arExist) . ' активных записей');
        }

        $res = CUser::GetList(
            $by = 'ID',
            $order = 'asc',
            ['ACTIVE'=>'Y'],
            ['SELECT' => ['UF_*']]
        );
        while ($row = $res->Fetch()) {
            if (empty($row['LAST_NAME'])) {
                continue;
            }
            if (empty($row['WORK_POSITION'])) {
                continue;
            }

            $strName = $row['LAST_NAME'] . ' ';
            $strName .= mb_substr($row['NAME'], 0, 1) . '.';
            $strName .= mb_substr($row['SECOND_NAME'], 0, 1) . '.';

            $strName = str_replace('ё', 'е', $strName);
            $strName1 = $strName . '-' . str_replace(' ', '', $row['WORK_POSITION']);
            $strName2 = $strName . '-' . str_replace(' ', '', $row['UF_WORK_POSITION']);

            $arPortalUsers[ trim(mb_strtolower($strName1)) ][] = $row;
            $arPortalUsers[ trim(mb_strtolower($strName2)) ][] = $row;
        }

        // Синхронизация с АСЭД - добавление\обновление пользователей
        $iUpdated   = 0;
        $iAdded     = 0;
        try {
            $last       = 0;
            $fetch      = 50;

            do {
                $arRequest = [
                    'OFFSET'    => $last,
                    'FETCH'     => $fetch
                ];
                $sData = $obClient->get_depatment(Json::encode($arRequest));

                /*
                 * "Администрация МО р\\п Первомайский Щекинского р-на"
                 */
                if (false !== mb_strpos($sData, 'р\\п')) {
                    $sData = str_replace("р\\п", 'р/п', $sData);
                }

                $sData = str_replace(["\r\n", "\r", "\n"], '', $sData);
                $sData = str_replace("\t", " ", $sData);

                $arData = Json::decode($sData);
                if ($arData !== null) {
                    foreach ($arData['DEPARTMENT'] as $row) {
                        $arUser = [
                            'UF_ACTIVE' => 1
                        ];
                        foreach ($row as $k => $v) {
                            $arUser['UF_' . $k ] = $v;
                        }

                        if (!empty($arUser['UF_DUTY'])) {
                            // Поиск пользователя на портале
                            $searchKey = mb_strtolower($arUser['UF_NAME'] . '-' . str_replace([' ', '–'], ['', '-'], $arUser['UF_DUTY']));
                            foreach ($arPortalUsers as $strName => $arTmpUsers) {
                                if (false !== mb_strpos($strName, $searchKey)) {
                                    $arFind = [];
                                    $countUsers = count($arTmpUsers);
                                    foreach ($arTmpUsers as $user) {
                                        // Так избавляемся от дублей
                                        if ($countUsers > 1) {
                                            if (empty($user['UF_LAST_1C_UPD'])) {
                                                continue;
                                            }
                                            if (date('Y', strtotime($user['UF_LAST_1C_UPD'])) < 2020) {
                                                continue;
                                            }
                                            if (date('Y', strtotime($user['LAST_LOGIN'])) < 2020) {
                                                continue;
                                            }
                                        }
                                        $arFind[] = $user['ID'];
                                    }

                                    $arUser['UF_USER_ESTIMATE'] = implode(',', $arFind);
                                    break;
                                }
                            }
                        }

                        if (array_key_exists($arUser['UF_DUE'], $arExist)) {
                            $arUser['UF_DATE_UPDATE'] = date('d.m.Y H:i:s');
                            if (!empty($arExist[ $arUser['UF_DUE'] ]['UF_USER_ESTIMATE'])) {
                                unset($arUser['UF_USER_ESTIMATE']);
                            }
                            $result = $self->entityDataClass::update(
                                $arExist[ $arUser['UF_DUE'] ]['ID'],
                                $arUser
                            );
                            $iUpdated++;
                            unset($arExist[ $arUser['UF_DUE'] ]);
                        } else {
                            $arUser['UF_DATE_CREATE'] = date('d.m.Y H:i:s');
                            $result = $self->entityDataClass::add($arUser);
                            $iAdded++;
                        }
                    }
                }

                $last += $fetch;
            } while ($arData['count'] > 0);
        } catch (Exception $e) {
            echo '<pre>ERROR = ';
            print_r($e->getMessage());
            echo '</pre>';
            if ($self->logger) {
                $self->logger->error($e->getMessage());
            }
        }

        if ($self->logger) {
            $self->logger->info('Добавлено ' . $iAdded . ' записей');
            $self->logger->info('Обновлено ' . $iAdded . ' записей');
            $self->logger->info('Требуется деактивировать ' . count($arExist) . ' записей');
        }

        // Если пользователь есть на портале, но нет в АСЭД - деактивировать
        if (count($arExist) < 200) {
            foreach ($arExist as $exist) {
                if ($exist['UF_ACTIVE'] == 0) {
                    continue;
                }
                $arUpdate = [
                    'UF_DATE_UPDATE'    => date('d.m.Y H:i:s'),
                    'UF_ACTIVE'         => 0,
                    'UF_USER_ESTIMATE'  => '',
                ];
                $result = $self->entityDataClass::update($exist['ID'], $arUpdate);
            }
        }

        return __METHOD__ . '();';
    }
}
