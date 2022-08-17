<?php

use CIntranetUtils;
use Bitrix\Main\Loader;

class CTasksReportHelperCustom extends CTasksReportHelper
{
    /**
     * В список колонок для каждого юзера добавить поле с подразделением
     *
     * @return array
     */
    public static function getColumnList()
    {
        $columnList = parent::getColumnList();

        foreach ($columnList as $key => $value) {
            $isUser = false;
            if (is_array($value)) {
                if (false !== mb_strpos($key, '_USER')) {
                    $isUser = true;
                }
                if (false !== mb_strpos($key, 'Member:')) {
                    $isUser = true;
                }
                if (false !== mb_strpos($key, 'RESPONSIBLE')) {
                    $isUser = true;
                }
            }

            if ($isUser) {
                $columnList[ $key ][] = 'UF_DEPARTMENT';
            }
        }

        return $columnList;
    }

    /**
     * Форматирование вывода полей
     * @param type &$rows
     * @param type &$columnInfo
     * @param type $total
     * @param type|null &$customChartData
     * @return type
     */
    public static function formatResults(&$rows, &$columnInfo, $total, &$customChartData = null)
    {
        parent::formatResults($rows, $columnInfo, $total, $customChartData);
        Loader::includeModule('intranet');
        foreach ($rows as &$row) {
            foreach ($row as $key => &$value) {
                if (false !== mb_strpos($key, 'UF_DEPARTMENT')) {
                    $depData = CIntranetUtils::GetDepartmentsData($value);
                    foreach ($value as $id => $depId) {
                        $value[ $id ] = $depData[ $depId ];
                    }
                }
            }
        }
        foreach ($columnInfo as &$col) {
            if (false !== mb_strpos($col['fieldName'], 'UF_DEPARTMENT')) {
                $col['humanTitle'] = str_replace($col['fieldName'], 'Подразделение', $col['humanTitle']);
                $col['fullHumanTitle'] = str_replace($col['fieldName'], 'Подразделение', $col['fullHumanTitle']);
            }
        }
    }

    /**
     * Форматирование HTML попапа на странице редактирования отчета
     * @param type $tree
     * @param type|bool $withReferencesChoose
     * @param type $level
     * @return type
     */
    public static function buildHTMLSelectTreePopup($tree, $withReferencesChoose = false, $level = 0)
    {
        if (is_array($withReferencesChoose)) {
            $filtrableGroups = $withReferencesChoose;
            $isRefChoose = true;
        } else {
            $filtrableGroups = [];
            $isRefChoose = $withReferencesChoose;
        }

        $html = '';

        $i = 0;

        foreach ($tree as $treeElem) {
            $isLastElem = (++$i == count($tree));

            $fieldDefinition = $treeElem['fieldName'];
            $branch = $treeElem['branch'];

            if (false !== mb_strpos($fieldDefinition, 'UF_DEPARTMENT')) {
                $treeElem['humanTitle'] = str_replace($fieldDefinition, 'Подразделение', $treeElem['humanTitle']);
                $treeElem['fullHumanTitle'] = str_replace($fieldDefinition, 'Подразделение', $treeElem['fullHumanTitle']);
            }

            $fieldType = null;
            $customColumnTypes = static::getCustomColumnTypes();
            if (array_key_exists($fieldDefinition, $customColumnTypes)) {
                $fieldType = $customColumnTypes[$fieldDefinition];
            } else {
                $fieldType = $treeElem['field'] ? static::getFieldDataType($treeElem['field']) : null;
            }

            // file fields is not filtrable
            if ($isRefChoose && ($fieldType === 'file' || $fieldType === 'disk_file')) {
                continue;
            }

            // multiple money fields is not filtrable
            if (
                $isRefChoose && $fieldType === 'money'
                && $treeElem['isUF'] === true
                && is_array($treeElem['ufInfo'])
                && isset($treeElem['ufInfo']['MULTIPLE'])
                && $treeElem['ufInfo']['MULTIPLE'] === 'Y'
            ) {
                continue;
            }

            if (empty($branch)) {
                // single field
                $htmlElem = static::buildSelectTreePopupElelemnt(
                    $treeElem['humanTitle'],
                    $treeElem['fullHumanTitle'],
                    $fieldDefinition,
                    $fieldType,
                    ($treeElem['isUF'] === true && is_array($treeElem['ufInfo'])) ? $treeElem['ufInfo'] : []
                );

                if ($isLastElem && $level > 0) {
                    $htmlElem = str_replace(
                        '<div class="reports-add-popup-item">',
                        '<div class="reports-add-popup-item reports-add-popup-item-last">',
                        $htmlElem
                    );
                }

                $html .= $htmlElem;
            } else {
                // add branch

                $scalarTypes = ['integer', 'float', 'string', 'text', 'boolean', 'file', 'disk_file', 'datetime',
                    'enum', 'employee', 'crm', 'crm_status', 'iblock_element', 'iblock_section', 'money'];
                if (
                    $isRefChoose
                    && !in_array($fieldDefinition, $filtrableGroups, true)
                    && (in_array($fieldType, $scalarTypes) || empty($fieldType))
                ) {
                    // ignore virtual branches (without references)
                    continue;
                }

                $html .= sprintf('<div class="reports-add-popup-item reports-add-popup-it-node">
                    <span class="reports-add-popup-arrow"></span><span
                        class="reports-add-popup-it-text">%s</span>
                </div>', $treeElem['humanTitle']);

                $html .= '<div class="reports-add-popup-it-children">';

                // add self
                if ($isRefChoose) {
                    $html .= static::buildSelectTreePopupElelemnt(
                        GetMessage('REPORT_CHOOSE').'...',
                        $treeElem['humanTitle'],
                        $fieldDefinition,
                        $fieldType
                    );
                }

                $html .= static::buildHTMLSelectTreePopup($branch, $withReferencesChoose, $level+1);

                $html .= '</div>';
            }
        }

        return $html;
    }

    /**
     * Перед построением запроса обновить данные
     * @param type &$select
     * @param type &$filter
     * @param type &$group
     * @param type &$order
     * @param type &$limit
     * @param type &$options
     * @param type|null &$runtime
     * @return type
     */
    public static function beforeViewDataQuery(&$select, &$filter, &$group, &$order, &$limit, &$options, &$runtime = null)
    {
        Loader::includeModule('intranet');
        if (isset($filter[1])) {
            foreach ($filter[1] as $key => &$value) {
                foreach ($value as $field => &$fVal) {
                    /**
                     * Департамент - собрать рекурсивно подотделы
                     */
                    if (false !== mb_strpos($field, 'UF_DEPARTMENT')) {
                        $arDeps = CIntranetUtils::GetDeparmentsTree($fVal, true);
                        $arDeps[] = $fVal;
                        $fVal = $arDeps;
                    }
                }
            }
        }
    }

    /**
     * Фильтровать поля с отделом только по значению равно
     *
     * @return array
     */
    public static function getCompareVariations()
    {
        return array_merge(parent::getCompareVariations(), [
            'CREATED_BY_USER.UF_DEPARTMENT' => ['EQUAL'],
            'RESPONSIBLE.UF_DEPARTMENT' => ['EQUAL'],
            'Member:TASK_COWORKED.USER.UF_DEPARTMENT' => ['EQUAL'],
            'CHANGED_BY_USER.UF_DEPARTMENT' => ['EQUAL'],
            'STATUS_CHANGED_BY_USER.UF_DEPARTMENT' => ['EQUAL'],
            'CLOSED_BY_USER.UF_DEPARTMENT' => ['EQUAL'],
        ]);
    }
}
