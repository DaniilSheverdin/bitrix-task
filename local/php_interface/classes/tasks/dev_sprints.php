<?php

namespace Citto\Tasks;

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;

/**
 * Класс для работы со спринтами разработки
 */
class DevSprints
{
    public $hlId = 0;

    public $entityDataClass = null;

    /**
     * Конструктор
     *
     * @return null
     */
    public function __construct()
    {
        Loader::includeModule('highloadblock');
        $this->hlId = HLTable::getList(
            ['filter' => ['NAME' => 'DevSprints']]
        )->fetch()['ID'];

        if ($this->hlId > 0) {
            $hlblock = HLTable::getById($this->hlId)->fetch();
            $entity = HLTable::compileEntity($hlblock);
            $this->entityDataClass = $entity->getDataClass();
        }
    }

    public function getList(int $id = 0)
    {
        $arSprints = [];
        if ($this->entityDataClass !== null) {
            $rsData = $this->entityDataClass::getList(
                [
                    'order' => [
                        'UF_START' => 'DESC',
                    ],
                ]
            );
            while ($arData = $rsData->fetch()) {
                $arData['NAME'] = $this->getName($arData);
                $arSprints[ $arData['ID'] ] = $arData;
            }
        }

        return $arSprints;
    }

    public function getByID(int $id = 0)
    {
        $arData = [];
        if ($this->entityDataClass !== null) {
            $rsData = $this->entityDataClass::getList(
                [
                    'filter' => [
                        'ID' => $id,
                    ],
                ]
            );
            if ($arData = $rsData->fetch()) {
                $arData['NAME'] = $this->getName($arData);
                return $arData;
            }
        }

        return $arData;
    }

    public function getName(array $arFields = [])
    {
        $name = $arFields['UF_NAME'];
        $dates = $arFields['UF_START']->format('d.m.Y');
        $dates .= ' - ';
        $dates .= $arFields['UF_END']->format('d.m.Y');

        if (empty($name)) {
            $name = $dates;
        } else {
            $name .= ' (' . $dates . ')';
        }

        return $name;
    }
}
