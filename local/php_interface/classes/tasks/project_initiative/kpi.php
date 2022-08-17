<?php

namespace Citto\Tasks\ProjectInitiative;

use Exception;
use DateTimeImmutable;
use Sprint\Migration\Helpers\HlblockHelper;
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;

/**
 * Класс для работы с KPI проекта.
 *
 * @see https://corp.tularegion.local/company/personal/user/2440/tasks/task/view/73021/
 */
class Kpi
{
    /* @var int */
    private $hlId = null;

    /* @var string */
    private $entityClass = null;

    /**
     * Конструктор
     *
     * @return void
     */
    public function __construct()
    {
        $helper = new HlblockHelper();

        $this->hlId = $helper->getHlblockId('ProjectsKPI');

        $hlblock           = HLTable::getById($this->hlId)->fetch();
        $entity            = HLTable::compileEntity($hlblock);
        $this->entityClass = $entity->getDataClass();
    }

    /**
     * Получить KPI проекта
     *
     * @param int $projectId ID проекта.
     *
     * @return array
     */
    public function getRows(int $projectId = 0)
    {
        $res = $this->entityClass::getList([
            'order'     => [
                'UF_SORT' => 'ASC'
            ],
            'filter'    => [
                'UF_ACTIVE'     => 'Y',
                'UF_PROJECT'    => $projectId,
            ],
        ]);
        $arResult = [];
        while ($row = $res->fetch()) {
            $row['PERCENT'] = $this->calcRow($row);
            $arResult[ $row['ID'] ] = $row;
        }

        return $arResult;
    }

    /**
     * Процент выполнения KPI
     *
     * @param array $array Строка KPI.
     *
     * @return float
     */
    public function calcRow(array $array = [])
    {
        if (empty($array) || empty($array['UF_CURRENT']) || empty($array['UF_TARGET'])) {
            return 0;
        }
        $percent = ($array['UF_CURRENT'] / $array['UF_TARGET']) * 100;
        return number_format($percent, 2, ',', '');
    }

    /**
     * Процент выполнения KPI проекта
     *
     * @param int $projectId ID проекта.
     *
     * @return float
     */
    public function calc(int $projectId = 0)
    {
        if (empty($projectId)) {
            return 0;
        }
        $arRows = $this->getRows($projectId);
        if (empty($arRows)) {
            return 0;
        }
        $arPercent = [];
        foreach ($arRows as $row) {
            $arPercent[] = $row['PERCENT'];
        }

        $percent = array_sum($arPercent) / count($arRows);

        return number_format($percent, 2, ',', '');
    }

    /**
     * Получить строку по ID.
     *
     * @param int $id ID строки
     *
     * @return array
     */
    public function getById(int $id = 0)
    {
        try {
            $res = $this->entityClass::getById($id);
            if ($row = $res->fetch()) {
                if (empty($row['UF_HISTORY'])) {
                    $row['UF_HISTORY'] = [];
                } else {
                    $row['UF_HISTORY'] = json_decode($row['UF_HISTORY'], true);
                }
                return $row;
            }

            return [];
        } catch (Exception $e) {
            throw new Exception($e->getMessage);
        }
    }

    /**
     * Добавить новую строку в KPI.
     *
     * @param array $array Массив данных.
     *
     * @return void
     */
    public function add(array $array = [])
    {
        try {
            $strCurrentDate = (new DateTimeImmutable())->format('d.m.Y H:i:s');
            $arFields = [
                'UF_ACTIVE'         => 'Y',
                'UF_PROJECT'        => (int)$array['UF_PROJECT'],
                'UF_SORT'           => $array['UF_SORT'] ?? 500,
                'UF_USER'           => $GLOBALS['USER']->GetID(),
                'UF_DATE_ADD'       => $strCurrentDate,
                'UF_DATE_UPDATE'    => $strCurrentDate,
                'UF_NAME'           => $array['UF_NAME'] ?? '',
                'UF_DESCRIPTION'    => $array['UF_DESCRIPTION'] ?? '',
                'UF_TARGET'         => $array['UF_TARGET'] ?? '',
                'UF_CURRENT'        => $array['UF_CURRENT'] ?? '',
                'UF_HISTORY'        => [],
            ];
            $arFields['UF_HISTORY'][] = [
                'USER'          => $GLOBALS['USER']->GetID(),
                'DATE'          => $strCurrentDate,
                'UF_TARGET'     => $array['UF_TARGET'] ?? '',
                'UF_CURRENT'    => $array['UF_CURRENT'] ?? '',
            ];
            $arFields['UF_HISTORY'] = json_encode($arFields['UF_HISTORY'], JSON_UNESCAPED_UNICODE);

            $res = $this->entityClass::add($arFields);
        } catch (Exception $e) {
            throw new Exception($e->getMessage);
        }
        
        return $res->getId();
    }

    /**
     * Обновить строку KPI.
     *
     * @param int   $id    ID строки.
     * @param array $array Массив новых данных.
     *
     * @return void
     */
    public function update(int $id = 0, array $array = [])
    {
        try {
            $arCurrentData = $this->getById($id);
            $arFields = [
                'UF_SORT'           => $array['UF_SORT'] ?? 500,
                'UF_NAME'           => $array['UF_NAME'] ?? '',
                'UF_DESCRIPTION'    => $array['UF_DESCRIPTION'] ?? '',
                'UF_TARGET'         => $array['UF_TARGET'] ?? '',
                'UF_CURRENT'        => $array['UF_CURRENT'] ?? '',
            ];
            foreach ($arFields as $key => $value) {
                if ($value == $arCurrentData[ $key ]) {
                    unset($arFields[ $key ]);
                }
            }

            if (!empty($arFields)) {
                $strCurrentDate = (new DateTimeImmutable())->format('d.m.Y H:i:s');
                if (array_key_exists('UF_TARGET', $arFields) || array_key_exists('UF_CURRENT', $arFields)) {
                    $arHistoryRow = [
                        'USER'          => $GLOBALS['USER']->GetID(),
                        'DATE'          => $strCurrentDate,
                        'UF_TARGET'     => $arFields['UF_TARGET'] ?? $arCurrentData['UF_TARGET'],
                        'UF_CURRENT'    => $arFields['UF_CURRENT'] ?? $arCurrentData['UF_CURRENT'],
                    ];
                    $arFields['UF_HISTORY'] = $arCurrentData['UF_HISTORY'];
                    $arFields['UF_HISTORY'][] = $arHistoryRow;
                    $arFields['UF_HISTORY'] = json_encode($arFields['UF_HISTORY'], JSON_UNESCAPED_UNICODE);
                }

                $arFields['UF_DATE_UPDATE'] = $strCurrentDate;
                $this->entityClass::update($id, $arFields);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage);
        }
    }

    /**
     * Деактивировать строку KPI.
     *
     * @param int $id ID строки.
     *
     * @return void
     */
    public function delete(int $id = 0)
    {
        try {
            $arCurrentData = $this->getById($id);
            $arFields = [
                'UF_ACTIVE'         => 'N',
                'UF_DATE_UPDATE'    => (new DateTimeImmutable())->format('d.m.Y H:i:s'),
            ];

            $this->entityClass::update($id, $arFields);
        } catch (Exception $e) {
            throw new Exception($e->getMessage);
        }
    }
}
