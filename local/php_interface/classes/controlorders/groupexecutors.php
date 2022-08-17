<?php

namespace Citto\ControlOrders;

use Bitrix\Main\Loader;
use Sprint\Migration\Helpers\HlblockHelper;
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;

class GroupExecutors
{
    private $hlId = 0;

    public $table = null;

    public function __construct()
    {
        Loader::includeModule('sprint.migration');
        $helper = new HlblockHelper();
        $this->hlId = $helper->getHlblockId('ControlOrdersUserGroups');
        if ($this->hlId > 0) {
            Loader::includeModule('highloadblock');
            $hlblock = HLTable::getById($this->hlId)->fetch();
            $entity = HLTable::compileEntity($hlblock);
            $this->table = $entity->getDataClass();
        }
    }

    /**
     * Получить список групп исполнителей
     *
     * @return array
     */
    public function getList()
    {
        $arGroups = [];
        $rsData   = $this->table::getList([
            'select' => ['*'],
            'filter' => [],
            'order'  => ['UF_SORT' => 'ASC'],
        ]);
        while ($arRes = $rsData->fetch()) {
            $arGroups[ $arRes['ID'] ] = $arRes;
        }

        return $arGroups;
    }

    /**
     * Получить список исполнителей из группы или из списка кодов
     *
     * @param int $groupId  ID группы исполнителей
     * @param array $arList Массив с типами исполнителей
     *
     * @return array
     */
    public function getExecutorsList(int $groupId = 0, array $arList = [])
    {
        if ($groupId > 0) {
            $arGroups = $this->getList();
            if (!isset($arGroups[ $groupId ])) {
                return [];
            }
            $arList = $arGroups[ $groupId ]['UF_LIST'];
        }
        $arExecutors = Executors::getList();

        $arResult = [];
        $arFinded = [];
        foreach ($arList as $delegateType) {
            if ((int)$delegateType > 0 && isset($arExecutors[ $delegateType ])) {
                $row = $arExecutors[ $delegateType ];
                if (in_array($row['ID'], $arFinded)) {
                    continue;
                }
                $arResult[] = [
                    'ID'    => $row['ID'],
                    'NAME'  => $row['NAME'],
                    'TYPE'  => $row['PROPERTY_TYPE_VALUE'],
                ];
                $arFinded[] = $arExecutors[ $delegateType ]['ID'];
            } elseif ((int)$delegateType < 0 && isset($arExecutors[ ($delegateType*-1) ])) {
                $arFinded[] = ($delegateType*-1);
            } else {
                foreach ($arExecutors as $row) {
                    if (in_array($row['ID'], $arFinded)) {
                        continue;
                    }
                    if ($row['PROPERTY_TYPE_CODE'] == $delegateType) {
                        $arResult[] = [
                            'ID'    => $row['ID'],
                            'NAME'  => $row['NAME'],
                            'TYPE'  => $row['PROPERTY_TYPE_VALUE'],
                        ];
                        $arFinded[] = $row['ID'];
                    }
                }
            }
        }

        return $arResult;
    }
}
