<?php

namespace Citto;

class Tasks
{
    /**
     * Обработчик ДО добавления задачи
     *
     * @param array $arFields Поля задачи
     *
     * @return void
     */
    public function handleBeforeTaskAdd(&$arFields)
    {
        if ($arFields['CREATED_BY'] != 1) {
            $arFields['TASK_CONTROL'] = 'Y';
        }
    }

    /**
     * Обработчик ДО изменения задачи
     *
     * @param int   $ID         ID задачи
     * @param array $arFields   Поля задачи
     * @param array $arTaskCopy Копия задачи
     *
     * @return void
     */
    public function handleBeforeTaskUpdate($ID, &$arFields, &$arTaskCopy)
    {
        if ($arFields['CREATED_BY'] != 1) {
            $arFields['TASK_CONTROL'] = 'Y';
        }
    }
}
