<?php

namespace Citto\Tasks;

use CFile;
use CUser;
use CEvent;
use CTasks;
use CTaskItem;
use CSocNetGroup;
use CIMMessenger;
use DateInterval;
use CIBlockElement;
use CSocNetFeatures;
use Bitrix\Main\Event;
use DateTimeImmutable;
use Bitrix\Main\Loader;
use Bitrix\Disk\Driver;
use Bitrix\Main\Type\DateTime;
use Bitrix\Disk\AttachedObject;
use Bitrix\Tasks\Internals\Task\ProjectDependenceTable;

/**
 * Класс для работы с проектными инициативами
 */
class ProjectInitiative
{
    /**
     * ID БП с проектными инициативами
     */
    public static $bizProcId = 588;

    /**
     * ID группы с проектными инициативами
     */
    public static $groupId = 612;

    /**
     * Список групп, которые созданые без БП
     */
    public static $arAllGroupIds = [
        644,
        620,
        625,
        621,
        633,
        626,
        617,
        619,
        622,
        658,
        618,
        197,
        624,
        627,
        673,
        664,
        682,
        683,
        685,
        744,
        770,
        671,
        799,
        800,
        801,
        815,
        841,
        854,
        858,
    ];

    public static $arUserFields = [
        'UF_COMPLEXITY',
        'UF_UTILISATION_SU',
        'UF_JUSTIFICATION',
    ];

    public static $arTaskUserFields = [
        'UF_LAST_COMMENT',
        'UF_AUTO_590425175461',
        'UF_UTILISATION_SU',
        'UF_JUSTIFICATION',
    ];

    /**
     * Поиск БП по ID задачи
     *
     * @param int $ID ID задачи
     *
     * @return array
     */
    public static function getBizProcByTaskId(int $id = 0): array
    {
        Loader::includeModule('iblock');
        $arFilter = [
            'IBLOCK_ID' => self::$bizProcId,
            [
                'LOGIC' => 'OR',
                '=PROPERTY_TASK_ID' => $id,
                '=PROPERTY_SUBTASK1_ID' => $id,
                '=PROPERTY_SUBTASK2_ID' => $id,
            ]
        ];
        $res = CIBlockElement::GetList(
            false,
            $arFilter,
            false,
            false,
            [
                'ID',
                'NAME',
                'PROPERTY_FZ',
                'PROPERTY_TASK_ID',
                'PROPERTY_SUBTASK1_ID',
                'PROPERTY_SUBTASK2_ID',
                'PROPERTY_FILE_CONCEPT',
                'PROPERTY_FILE_TZ',
                'PROPERTY_FILE_USTAV',
                'PROPERTY_GROUP_ID',
            ]
        );
        $arReturn = [];
        while ($row = $res->GetNext()) {
            $arReturn = $row;
        }

        return $arReturn;
    }

    /**
     * Найти ID файла по ID аттача
     *
     * @param int $ID ID аттача
     *
     * @deprecated Уже не используется
     *
     * @return int
     */
    public static function getFileIdByAttachId(int $id = 0): int
    {
        Loader::includeModule('disk');
        $obAttach = AttachedObject::loadById($id);

        return $obAttach    // Bitrix\Disk\AttachedObject
            ->getObject()   // Bitrix\Disk\File
            ->getFileId();
    }

    /**
     * Обработчик изменения задачи
     *
     * @param int   $ID         ID задачи
     * @param array $arFields   Поля задачи
     * @param array $arTaskCopy Копия задачи
     *
     * @return void
     */
    public function handleTaskUpdate($ID, &$arFields, &$arTaskCopy)
    {
        $iGroupId = $arFields['META:PREV_FIELDS']['GROUP_ID'];

        // Группы, созданные из задач "Проектная инициатива"
        Loader::includeModule('iblock');
        $arFilter = [
            'IBLOCK_ID'         => self::$bizProcId,
            'PROPERTY_GROUP_ID' => $iGroupId
        ];
        $res = CIBlockElement::GetList(
            false,
            $arFilter,
            false,
            false,
            ['ID']
        );
        $isProject = ($res->SelectedRowsCount() > 0);

        Loader::includeModule('disk');
        $arBizProc = self::getBizProcByTaskId($ID);
        $arChecklist = $arFields['META:PREV_FIELDS']['CHECKLIST'];

        if (!empty($arBizProc)) {
            // Проектная инициатива
            if ($arBizProc['PROPERTY_TASK_ID_VALUE'] == $ID) {
                // Изменена "Основная задача"
                foreach ($arChecklist as $arItem) {
                    if (false !== stripos($arItem['TITLE'], 'принятие решения')) {
                        //  В типовой проектной инициативе при отметке в чек листе
                        // "принятие решения о взятии в проработку" необходимо
                        // пересчитать срок подзадачи "Инициация" = дата отметки плюс + 2 недели
                        if ($arItem['IS_COMPLETE'] == 'Y') {
                            $obDate = new DateTimeImmutable($arItem['TOGGLED_DATE']->format('d.m.Y H:i:s'));
                            $obDate = $obDate->add(new DateInterval('P2W'));
                            $oTaskItem = CTaskItem::getInstance(
                                $arBizProc['PROPERTY_SUBTASK1_ID_VALUE'],
                                $GLOBALS['USER']->GetID()??1
                            );
                            $obDateInitiation = new DateTimeImmutable($oTaskItem->getData()['DEADLINE']);
                            if ($obDateInitiation < $obDate) {
                                $arUpdate = [
                                    'DEADLINE' => $obDate->format('d.m.Y H:i:s')
                                ];
                                $oTaskItem->update($arUpdate);
                            }
                        }
                    }
                }
            } elseif ($arBizProc['PROPERTY_SUBTASK1_ID_VALUE'] == $ID) {
                // Изменена "Подзадача Инициация"
                foreach ($arChecklist as $arItem) {
                    if (empty($arItem['ATTACHMENTS'])) {
                        continue;
                    }
                    if (false !== stripos($arItem['TITLE'], 'концепт')) {
                        $sFiles = implode(', ', array_keys($arItem['ATTACHMENTS']));
                        self::setField($arBizProc['ID'], 'FILE_CONCEPT', $sFiles);
                    }
                }
            } elseif ($arBizProc['PROPERTY_SUBTASK2_ID_VALUE'] == $ID) {
                // Изменена "Подзадача Планирование"
                foreach ($arChecklist as $arItem) {
                    if (empty($arItem['ATTACHMENTS'])) {
                        continue;
                    }
                    if (false !== stripos($arItem['TITLE'], 'техническое задание')) {
                        $sFiles = implode(', ', array_keys($arItem['ATTACHMENTS']));
                        self::setField($arBizProc['ID'], 'FILE_TZ', $sFiles);
                    } elseif (false !== stripos($arItem['TITLE'], 'устав')) {
                        $sFiles = implode(', ', array_keys($arItem['ATTACHMENTS']));
                        self::setField($arBizProc['ID'], 'FILE_USTAV', $sFiles);
                    }
                }
            }

            $arTasks = [
                'TASK' => $arBizProc['PROPERTY_TASK_ID_VALUE'],
                'SUBTASK1' => $arBizProc['PROPERTY_SUBTASK1_ID_VALUE'],
                'SUBTASK2' => $arBizProc['PROPERTY_SUBTASK2_ID_VALUE'],
            ];
            self::setDeadLine($arTasks);
        } elseif ($isProject) {
            // Задача в проекте
            if (false !== mb_strpos($arFields['META:PREV_FIELDS']['TITLE'], 'Приемка')) {
                $obDriver = Driver::getInstance();
                $obStorage = $obDriver->getStorageByGroupId($iGroupId);
                $obRootFolder = $obStorage->getRootObject();
                $securityContext = $obDriver->getFakeSecurityContext();
                $obFolder = $obRootFolder->getChild(
                    [
                        '=NAME' => 'Закрывающие документы',
                    ]
                );
                if (!$obFolder) {
                    return;
                }
                // Найти все файлы в папке "Закрывающие документы"
                $arCurrentFiles = $obFolder->getChildren($securityContext);
                $arUploadedFiles = [];
                foreach ($arCurrentFiles as $obFile) {
                    $arUploadedFiles[] = [
                        'ID' => $obFile->getId(),
                        'NAME' => $obFile->getName(),
                        'SIZE' => $obFile->getSize(),
                    ];
                }
                foreach ($arChecklist as $arItem) {
                    if (empty($arItem['ATTACHMENTS'])) {
                        continue;
                    }
                    if (
                        false !== mb_strpos(mb_strtolower($arItem['TITLE']), 'протокол') ||
                        false !== mb_strpos(mb_strtolower($arItem['TITLE']), 'акт')
                    ) {
                        $arFiles = array_keys($arItem['ATTACHMENTS']);
                        foreach ($arFiles as $attachId) {
                            $fileId = AttachedObject::loadById($attachId)
                                ->getObject()
                                ->getFileId();
                            $arFile = CFile::GetByID($fileId)
                                ->Fetch();
                            $bUpload = true;
                            foreach ($arUploadedFiles as $arCurrentFile) {
                                // Файл с таким именем есть на диске
                                if ($arCurrentFile['NAME'] == $arFile['ORIGINAL_NAME']) {
                                    // и их размер совпадает - не загружаем
                                    if ($arCurrentFile['SIZE'] == $arFile['FILE_SIZE']) {
                                        $bUpload = false;
                                    }
                                }
                            }

                            if ($bUpload) {
                                // Загружаем новый файл
                                $arNewFile = CFile::MakeFileArray($fileId);
                                $arNewFile['name'] = $arFile['ORIGINAL_NAME'];
                                $obFolder->uploadFile(
                                    $arNewFile,
                                    [
                                        'NAME' => $arFile['ORIGINAL_NAME'],
                                        'CREATED_BY' => $GLOBALS['USER']->GetID() ?? 1
                                    ]
                                );
                            }
                        }
                    }
                }
            }

            if (
                $iGroupId > 0 &&
                (
                    (
                        // Завершена без перевода на контроль
                        $arFields['STATUS'] == CTasks::STATE_COMPLETED &&
                        !in_array(
                            $arTaskCopy['STATUS'],
                            [
                                CTasks::STATE_SUPPOSEDLY_COMPLETED,
                                CTasks::STATE_COMPLETED,
                            ]
                        )
                    ) || (
                        // Переведена на контроль
                        $arFields['STATUS'] == CTasks::STATE_SUPPOSEDLY_COMPLETED &&
                        !in_array(
                            $arTaskCopy['STATUS'],
                            [
                                CTasks::STATE_SUPPOSEDLY_COMPLETED,
                                CTasks::STATE_COMPLETED,
                            ]
                        )
                    )
                )
            ) {
                $arTitles = [
                    'Исполнение',
                    'Приемка',
                    'Опытная эксплуатация',
                    'Завершение',
                ];
                if (in_array($arFields['META:PREV_FIELDS']['TITLE'], $arTitles)) {
                    Loader::includeModule('socialnetwork');
                    $arGroupInfo = CSocNetGroup::getById($iGroupId);
                    $groupLink = 'https://corp.tularegion.local/workgroups/group/' . $iGroupId . '/';
                    $taskLink = $groupLink . 'tasks/task/view/' . $ID . '/';
                    $strMessage = 'Закрыта задача "<a href="' . $taskLink . '">' . $arFields['META:PREV_FIELDS']['TITLE'] . '</a>" в проекте "<a href="' . $groupLink . '">' . $arGroupInfo['NAME'] . '</a>".';
                    $strTitle = strip_tags($strMessage);
                    if (!empty($arFields['META:PREV_FIELDS']['UF_LAST_COMMENT'])) {
                        $strMessage .= '<br/><br/>Комментарий: ' . $arFields['META:PREV_FIELDS']['UF_LAST_COMMENT'];
                    }

                    $arUsers = [
                        41,  // Прокудин
                        107, // Коняева
                    ];
                    $dbRes = CUser::GetList(
                        $by = 'ID',
                        $sort = 'ASC',
                        ['ID' => implode('|', $arUsers)]
                    );
                    $arEmails = [];
                    while ($arUser = $dbRes->GetNext()) {
                        $arEmails[ $arUser['ID'] ] = $arUser['EMAIL'];
                    }

                    $arFields = [
                        'SENDER'    => 'corp-noreply@tularegion.ru',
                        'RECEIVER'  => implode(';', $arEmails),
                        'TITLE'     => $strTitle,
                        'MESSAGE'   => $strMessage
                    ];
                    $event = new CEvent();
                    $event->Send('BIZPROC_HTML_MAIL_TEMPLATE', 'nh', $arFields, "N");
                }
            }
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
        $iGroupId = $arTaskCopy['GROUP_ID'];
        if ($iGroupId == self::$groupId && isset($arFields['DEADLINE'])) {
            if ($arFields['CHANGED_BY'] != $arTaskCopy['CREATED_BY']) {
                if (strtotime($arFields['DEADLINE']) != strtotime($arTaskCopy['DEADLINE'])) {
                    $GLOBALS['APPLICATION']->throwException('Изменение крайнего срока запрещено');
                    return false;
                }
            }
        }
    }

    /**
     * Обновление свойства БП
     *
     * @param int $id
     * @param string $code
     * @param string $value
     *
     * @return void
     */
    private static function setField(
        int $id = 0,
        string $code = '',
        string $value = ''
    ) {
        Loader::includeModule('iblock');
        CIBlockElement::SetPropertyValuesEx(
            $id,
            self::$bizProcId,
            [
                $code => $value
            ]
        );
    }

    /**
     * Установка максимального ДЛ для родительской задачи
     *
     * @param array $arTasks
     *
     * @return void
     */
    private static function setDeadLine(array $arTasks = []): void
    {
        if (empty($arTasks)) {
            return;
        }
        Loader::includeModule('tasks');
        $resTask = CTasks::GetList(
            ['ID' => 'ASC'],
            ['ID' => $arTasks],
            ['ID', 'TITLE', 'PARENT_ID', 'STATUS', 'DEADLINE']
        );
        $arDates = [];
        while ($arTask = $resTask->GetNext()) {
            $arDates[ $arTask['ID'] ] = strtotime($arTask['DEADLINE']);
        }
        $iMaxDate = max($arDates);
        if ($arDates[ $arTasks['TASK'] ] != $iMaxDate) {
            $oTaskItem = CTaskItem::getInstance(
                $arTasks['TASK'],
                $GLOBALS['USER']->GetID()??1
            );
            $arUpdate = [
                'DEADLINE' => date('d.m.Y H:i:s', $iMaxDate)
            ];
            $oTaskItem->update($arUpdate);
        }
    }

    /**
     * Можно ли из задачи создать группу
     *
     * @param int $ID ID задачи
     *
     * @return bool
     */
    public function canCreateGroup(int $ID = 0): bool
    {
        $arBizProc = self::getBizProcByTaskId($ID);
        if ($arBizProc['PROPERTY_TASK_ID_VALUE'] == $ID) {
            return !empty($arBizProc['PROPERTY_FILE_USTAV_VALUE']) &&
                    empty($arBizProc['PROPERTY_GROUP_ID_VALUE']);
        }

        return false;
    }

    /**
     * Доступен ли функционал отчётности в группе
     *
     * @param int $iGroupId ID группы соц сети
     *
     * @deprecated
     *
     * @return bool
     */
    public static function isProject(int $iGroupId = 0): bool
    {
        // Группа "Проектные инициативы"
        if ($iGroupId === self::$groupId) {
            return true;
        }

        if (in_array($iGroupId, self::$arAllGroupIds)) {
            return true;
        }

        // Группы, созданные из задач "Проектная инициатива"
        Loader::includeModule('iblock');
        $arFilter = [
            'IBLOCK_ID'         => self::$bizProcId,
            'PROPERTY_GROUP_ID' => $iGroupId
        ];
        $res = CIBlockElement::GetList(
            false,
            $arFilter,
            false,
            false,
            ['ID']
        );
        return $res->SelectedRowsCount() > 0;
    }

    /**
     * Расчитать процент завершения Прокта\Проектной инициативы
     *
     * @param int $iGroupId
     * @param int $taskId
     * @param float $maxPercent
     *
     * @return array
     */
    public static function calcTasksPercent(
        int $iGroupId = 0,
        int $taskId = 0,
        int $level = 0
    ): array {
        $level++;
        Loader::includeModule('tasks');

        $arFilter = [];
        if ($taskId > 0) {
            $arFilter = [
                '::SUBFILTER-1' => [
                    '::LOGIC' => 'OR',
                    'ID' => $taskId,
                    'PARENT_ID' => $taskId
                ]
            ];
        }
        if ($iGroupId > 0) {
            $arFilter['GROUP_ID'] = $iGroupId;
        }
        $arTasks = [];
        $resTask = CTasks::GetList(
            ['ID' => 'ASC'],
            $arFilter,
            [
                'ID',
                'TITLE',
                'PARENT_ID',
                'GROUP_ID',
                'STATUS',
                'REAL_STATUS',
                'CREATED_DATE',
                'CLOSED_DATE',
                'DEADLINE',
                'RESPONSIBLE_ID',
                'TAGS',
                'UF_JUSTIFICATION',
                'UF_UTILISATION_SU',
            ]
        );
        while ($arTask = $resTask->GetNext()) {
            $arTask['DEADLINE_DT'] = $arTask['DEADLINE'] ? new DateTimeImmutable($arTask['DEADLINE']) : null;
            $arTask['CREATED_DATE_DT'] = $arTask['CREATED_DATE'] ? new DateTimeImmutable($arTask['CREATED_DATE']) : null;
            $arTask['CLOSED_DATE_DT'] = $arTask['CLOSED_DATE'] ? new DateTimeImmutable($arTask['CLOSED_DATE']) : null;
            $arTasks[ $arTask['ID'] ] = $arTask;
            if ($arTask['ID'] != $taskId) {
                $arSubTasks = self::calcTasksPercent(
                    $iGroupId,
                    $arTask['ID'],
                    $level
                );
                foreach ($arSubTasks as $arSubTask) {
                    $arTasks[ $arSubTask['ID'] ] = $arSubTask;
                }
            }
        }

        if ($level == 1) {
            $arParent = [];
            foreach ($arTasks as $arTask) {
                $arParent[ (int)$arTask['PARENT_ID'] ][] = $arTask['ID'];
            }

            foreach ($arParent[ $taskId ] as $iTaskId) {
                $arTasks[ $iTaskId ]['MAX_PERCENT'] = 100 / count($arParent[ $taskId ]);
            }
            unset($arParent[0]);
            unset($arParent[ $taskId ]);
            foreach ($arParent as $parentId => $arSubTasks) {
                if (count($arSubTasks) >= 2) {
                    $iPercent = $arTasks[ $parentId ]['MAX_PERCENT'] / count($arSubTasks);
                    $arTasks[ $parentId ]['MAX_PERCENT'] = 0;
                } else {
                    $iPercent = $arTasks[ $parentId ]['MAX_PERCENT'] / (count($arSubTasks)+1);
                    $arTasks[ $parentId ]['MAX_PERCENT'] = $iPercent;
                }
                foreach ($arSubTasks as $iTaskId) {
                    $arTasks[ $iTaskId ]['MAX_PERCENT'] = $iPercent;
                }
            }

            foreach ($arTasks as $iTaskId => $arTask) {
                if (!isset($arTask['MAX_PERCENT'])) {
                    $arTasks[ $iTaskId ]['MAX_PERCENT'] = 0;
                }
            }
        }
        return $arTasks;
    }

    /**
     * Расчитать отклонение от плана проекта
     *
     * @see https://corp.tularegion.local/company/personal/user/2440/tasks/task/view/62678/
     *
     * @param int $iGroupId ID группы
     *
     * @return array
     */
    public static function calcProjectDeviation(int $iGroupId = 0)
    {
        Loader::includeModule('tasks');

        $arFilter = [
            'GROUP_ID'  => $iGroupId,
            'ONLY_ROOT_TASKS' => 'N',
        ];

        $arTasks = [];
        $arErrors = [];
        $arSuccess = [];
        $arDependenceTasks = [];

        $resTask = CTasks::GetList(
            ['ID' => 'ASC'],
            $arFilter,
            ['*']
        );

        while ($arTask = $resTask->GetNext()) {
            $iParentID = $arTask['PARENT_ID'];

            if (is_null($iParentID)) {
                $arTask['LEVEL'] = 1;
            }

            if ($arTasks[$iParentID]) {
                if ($arTasks[$iParentID]['LEVEL'] > 1) {
                    continue;
                }

                $arTasks[$iParentID]['CHILDRENS'][] = $arTask['ID'];
                $arTask['LEVEL'] = 2;
            }

            if (empty($arTask['DEADLINE'])) {
                $arErrors['Есть задачи без крайнего срока'][] = $arTask['ID'];
            }
            if (empty($arTask['START_DATE_PLAN'])) {
                $arErrors['Есть задачи без даты начала'][] = $arTask['ID'];
                $arTask['START_DATE_PLAN'] = date('01.01.Y', strtotime($arTask['END_DATE_PLAN']));
            }
            if (empty($arTask['END_DATE_PLAN'])) {
                $arErrors['Есть задачи без даты окончания'][] = $arTask['ID'];
            }

            if (!empty($arTask['DEADLINE']) && !empty($arTask['END_DATE_PLAN'])) {
                $sEndTask = new DateTimeImmutable($arTask['END_DATE_PLAN']);
                $sDeadLine = new DateTimeImmutable($arTask['DEADLINE']);
                $obDiff = $sDeadLine->diff($sEndTask);
                $iDaysDeviation = 0;

                if ($obDiff->invert === 1) {
                    $iDaysDeviation = $obDiff->days;
                }

                $arTask['DEVIATION'] = $iDaysDeviation;
                $arTask['TASK_FOR_DEVIATION'] = 'Y';
            }

            $arTasks[$arTask['ID']] = $arTask;
        }

        /* Ищем связки по Ганту */
        if ($arTasks) {
            $obProjectDep = ProjectDependenceTable::getList([
                'filter' => [
                    'TASK_ID' => array_keys($arTasks)
                ]
            ]);

            while ($arProj = $obProjectDep->fetch()) {
                if ($arProj['TASK_ID'] != $arProj['DEPENDS_ON_ID']) {
                    $arDependenceTasks[$arProj['TASK_ID']][] = $arProj['DEPENDS_ON_ID'];
                    $arDependenceTasks[$arProj['DEPENDS_ON_ID']][] = $arProj['TASK_ID'];
                }
            }
        }

        $arDeviation = [0];
        $arDependenceTasksTMP = [];
        foreach ($arTasks as $arTask) {
            if ($arTask['TASK_FOR_DEVIATION'] == 'Y' && $arTask['LEVEL'] == 1) {

                $arChildrensDeviation = [
                    'DEPENDENCE' => [],
                    'NOT_DEPENDENCE' => [],
                ];

                foreach ($arTask['CHILDRENS'] as $iChildren) {
                    $iChildDeviation = $arTasks[$iChildren]['DEVIATION'];
                    if (array_intersect($arDependenceTasks[$iChildren], $arTask['CHILDRENS'])) {
                        $arDependenceTasksTMP[$iChildren] = $iChildDeviation;
                    } else {
                        $arChildrensDeviation['NOT_DEPENDENCE'][] = $iChildDeviation;
                    }
                }

                foreach ($arDependenceTasks as $iChildren => $arTasks) {
                    if (in_array($iChildren, $arDependenceTasksTMP)) {
                        continue;
                    }

                    $arDependenceTasksInfo = [];
                    $arDependenceTasksInfo[] = '<a href=\'/workgroups/group/' . $arTasks[ $iChildren ]['GROUP_ID'] . '/tasks/task/view/' . $iChildren . '/\' target=\'_blank\'>' . $iChildren . '</a>';

                    $i = $arDependenceTasksTMP[$iChildren];
                    foreach ($arTasks as $iTask) {
                        array_push($arDependenceTasksTMP, $iTask);
                        $i += $arDependenceTasksTMP[$iTask];
                        $arDependenceTasksInfo[] = '<a href=\'/workgroups/group/' . $arTasks[ $iTask ]['GROUP_ID'] . '/tasks/task/view/' . $iTask . '/\' target=\'_blank\'>' . $iTask . '</a>';
                    }

                    $arChildrensDeviation['DEPENDENCE'][] = $i;
                    $arSuccess['Есть связанные задачи:'][] = $i . "(" .  implode(',', $arDependenceTasksInfo) . ")";
                }

                $iChildrenDeviation = max([max($arChildrensDeviation['DEPENDENCE']), max($arChildrensDeviation['NOT_DEPENDENCE'])]);
                $iParenDeviation = $arTask['DEVIATION'];
                $arDeviation[] = ($iChildrenDeviation > 0 && $iParenDeviation < $iChildrenDeviation) ? $iChildrenDeviation : $iParenDeviation;
            }
        }

        if (!empty($arErrors)) {
            foreach ($arErrors as $code => $tasks) {
                $tooltip .= $code . ': ' . implode(', ', $tasks) . "\r\n";
                $tooltipHtml .= '<b>' . $code . '</b>: ';
                $arTaskLinks = [];
                foreach ($tasks as $taskId) {
                    $arTaskLinks[] = '<a href=\'/workgroups/group/' . $arTasks[ $taskId ]['GROUP_ID'] . '/tasks/task/view/' . $taskId . '/\' target=\'_blank\'>' . $taskId . '</a>';
                }
                $tooltipHtml .= implode(', ', $arTaskLinks) . ' <br/><br/>';
            }
        }

        if (!empty($arSuccess)) {
            foreach ($arSuccess as $code => $arItem) {
                $tooltip .= $code . ': ' . implode(', ', $arItem) . "\r\n";
                $tooltipHtml .= '<b>' . $code . '</b>: ';
                $tooltipHtml .= implode(",", $arItem) . ' <br/><br/>';
            }
        }

        return [
            'tooltip'       => $tooltip,
            'tooltip_html'  => $tooltipHtml,
            'sum'           => max($arDeviation),
        ];
    }

    /**
     * Уведомление об окончании срока приёмки
     *
     * @param int $taskId
     *
     * @return string|void
     */
    public static function agentNotification(int $taskId = 0)
    {
        if ($taskId <= 0) {
            return;
        }

        $sAgentName = __METHOD__ . '(' . $taskId . ');';

        Loader::includeModule('tasks');
        $oTaskItem = CTaskItem::getInstance($taskId, 1);
        $arTask = $oTaskItem->getData();
        if ($arTask['STATUS'] >= 5) {
            return;
        }
        if (empty($arTask['DEADLINE'])) {
            return $sAgentName;
        }

        $obDeadLine = new DateTimeImmutable($arTask['DEADLINE']);
        $obNow = new DateTimeImmutable();
        $obDiff = $obNow->diff($obDeadLine);

        if ($obDiff->days == 14) {
            Loader::includeModule('im');
            Loader::includeModule('socialnetwork');

            $arGroup = CSocNetGroup::getById($arTask['GROUP_ID']);
            $sMessage = 'Проект «<a href="/workgroups/group/#GROUP_ID#/">#GROUP_NAME#</a>» #BR# #BR# Через 14 дней наступит крайний срок задачи «<a href="/workgroups/group/#GROUP_ID#/tasks/task/view/#TASK_ID#/">#TASK_NAME#</a>» #BR# #BR# Необходимо поставить заявку на предоставление подрядчику доступа в официальный репозиторий ГАУ ТО «ЦИТ» для размещения исходных кодов и предоставления инструкций по сборке.';

            $sMessage = str_replace(
                [
                    '#GROUP_ID#',
                    '#GROUP_NAME#',
                    '#TASK_ID#',
                    '#TASK_NAME#'
                ],
                [
                    $arTask['GROUP_ID'],
                    $arGroup['NAME'],
                    $arTask['ID'],
                    $arTask['TITLE']
                ],
                $sMessage
            );

            $arFields = [
                "MESSAGE_TYPE" => "S",
                "TO_USER_ID" => $arTask['RESPONSIBLE_ID'],
                "FROM_USER_ID" => 1,
                "MESSAGE" => $sMessage,
                "AUTHOR_ID" => 1,
                "EMAIL_TEMPLATE" => "some",
            ];
            CIMMessenger::Add($arFields);
        } elseif ($obDiff->days < 14) {
            return '';
        }

        return $sAgentName;
    }

    /**
     * Отправить письмо, если задача просрочена
     *
     * @param Event $arData
     *
     * @return boolean
     */
    public function handleTaskExpire(Event $arData)
    {
        $arParams = $arData->getParameters();
        if (empty($arParams['TASK'])) {
            return true;
        }
        if (empty($arParams['TASK']['GROUP_ID'])) {
            return true;
        }

        $bSendMessage = false;
        if ($arParams['TASK']['GROUP_ID'] == self::$groupId) {
            $bSendMessage = true;
        } else {
            Loader::includeModule('iblock');
            $arFilter = [
                'IBLOCK_ID'         => self::$bizProcId,
                'PROPERTY_GROUP_ID' => $arParams['TASK']['GROUP_ID']
            ];
            $res = CIBlockElement::GetList(
                false,
                $arFilter,
                false,
                false,
                ['ID']
            );
            $bSendMessage = ($res->SelectedRowsCount() > 0);
        }

        if ($bSendMessage) {
            $MESS = [];
            $MESS['TASKS_STATUS_1'] = 'Новая';
            $MESS['TASKS_STATUS_2'] = 'Ждет выполнения';
            $MESS['TASKS_STATUS_3'] = 'Выполняется';
            $MESS['TASKS_STATUS_4'] = 'Ждет контроля';
            $MESS['TASKS_STATUS_5'] = 'Завершена';
            $MESS['TASKS_STATUS_6'] = 'Отложена';
            $MESS['TASKS_STATUS_7'] = 'Отклонена';
            Loader::includeModule('tasks');
            $oTaskItem = CTaskItem::getInstance($arParams['TASK_ID'], 1);
            $arTask = $oTaskItem->getData();
            $strMessage = file_get_contents(__DIR__ . '/expire.html');
            if (strtotime($arTask['DEADLINE']) > time() || !$arTask['DEADLINE'] || !strtotime($arTask['DEADLINE'])) {
                return true;
            }
            $arReplace = [
                '{{ID}}'            => $arTask['ID'],
                '{{TITLE}}'         => $arTask['TITLE'],
                '{{STATUS}}'        => $MESS['TASKS_STATUS_' . $arTask['REAL_STATUS']],
                '{{AUTHOR}}'        => implode(' ', [$arTask['CREATED_BY_LAST_NAME'], $arTask['CREATED_BY_NAME']]),
                '{{RESPONSIBLE_ID}}'=> $arTask['RESPONSIBLE_ID'],
                '{{RESPONSIBLE}}'   => implode(' ', [$arTask['RESPONSIBLE_LAST_NAME'], $arTask['RESPONSIBLE_NAME']]),
                '{{DATE_CREATE}}'   => $arTask['CREATED_DATE'],
                '{{DEADLINE}}'      => $arTask['DEADLINE']
            ];

            $strMessage = strtr($strMessage, $arReplace);
            $strTitle = 'ПРОСРОЧЕНА : ' . $arReplace['{{ID}}'] . ' : ' . $arReplace['{{TITLE}}'] . ':' . $arReplace['{{STATUS}}'];

            $arUsers = [
                // 41,  // Прокудин
                107, // Коняева
            ];
            $arGroup = CSocNetGroup::getById($arParams['TASK']['GROUP_ID']);
            $arUsers[] = $arGroup['OWNER_ID'];
            $dbRes = CUser::GetList(
                $by = 'ID',
                $sort = 'ASC',
                ['ID' => implode('|', array_unique($arUsers))]
            );
            $arEmails = [];
            while ($arUser = $dbRes->GetNext()) {
                $arEmails[ $arUser['ID'] ] = $arUser['EMAIL'];
            }

            $arFields = [
                'SENDER'    => 'corp-noreply@tularegion.ru',
                'RECEIVER'  => implode(';', $arEmails),
                'TITLE'     => $strTitle,
                'MESSAGE'   => $strMessage
            ];
            $event = new CEvent();
            $event->Send('BIZPROC_HTML_MAIL_TEMPLATE', 'nh', $arFields, "N");
        }

        return true;
    }

    /**
     * Добавляем свои фичи в проект
     *
     * @param array  &$newAllowedFeatures Список фич проекта.
     * @param string $siteId              Текущий сайт.
     * @return void
     */
    public function handleOnFillSocNetFeaturesList(&$newAllowedFeatures, $siteId)
    {
        /**
         * Вывод KPI проекта
         * @see https://corp.tularegion.local/company/personal/user/2440/tasks/task/view/73021/
         */
        $newAllowedFeatures['group_kpi'] = [
            'allowed' => ['G'],
            'operations' => [],
            'minoperation' => [],
            'title' => 'KPI',
        ];
    }

    /**
     * Заполнить меню группы своими пунктами
     *
     * @param array &$arResult Текущие данные из компонента меню.
     * @param array $arData    Информация о текущей группе.
     *
     * @return void
     */
    public function handleOnFillSocNetMenu(&$arResult, $arData)
    {
        $arNewFeatures = [];
        if (
            ($arResult['CurrentUserPerms']['UserIsMember'] || $GLOBALS['USER']->IsAdmin())
            && $arResult['Group']['ID'] == ProjectInitiative::$groupId
        ) {
            $arNewFeatures['reporting'] = [
                'url'       => SITE_DIR . 'workgroups/group/' . $arResult['Group']['ID'] . '/reporting/',
                'name'      => 'Отчётность',
                'canView'   => true,
            ];
        }

        $arNewFeatures['risks'] = [
            'url'       => SITE_DIR . 'workgroups/group/' . $arResult['Group']['ID'] . '/risks/',
            'name'      => 'Риски',
            'canView'   => true,
        ];

        $arFeaturesTmp = [];
        $dbResultTmp = CSocNetFeatures::GetList(
            [],
            ["ENTITY_ID" => $arResult['Group']['ID'], "ENTITY_TYPE" => SONET_ENTITY_GROUP]
        );
        while ($arResultTmp = $dbResultTmp->GetNext()) {
            $arFeaturesTmp[ $arResultTmp["FEATURE"] ] = $arResultTmp;
        }
        if ($arFeaturesTmp['group_kpi']['ACTIVE'] == 'Y') {
            $arNewFeatures['kpi'] = [
                'url'       => SITE_DIR . 'workgroups/group/' . $arResult['Group']['ID'] . '/kpi/',
                'name'      => is_null($arResult['ActiveFeatures']['group_kpi']) ? 'KPI' : $arResult['ActiveFeatures']['group_kpi'],
                'canView'   => true,
            ];
        }

        foreach ($arNewFeatures as $name => $arFeature) {
            $arResult["Urls"][ $name ]      = $arFeature['url'];
            $arResult["CanView"][ $name ]   = $arFeature['canView'];
            $arResult["Title"][ $name ]     = $arFeature['name'];
        }
    }

    /**
     * Обработка собственных ссылок в проектах.
     *
     * @param array &$arDefaultUrlTemplates404
     * @param array &$arCustomPagesPath
     * @param array $arParams
     *
     * @return void
     */
    public static function handleOnParseSocNetComponentPath(&$arDefaultUrlTemplates404, &$arCustomPagesPath, $arParams)
    {
        $arDefaultUrlTemplates404['group_reporting'] = 'group/#group_id#/reporting/';
        $arParams['SEF_URL_TEMPLATES']['group_reporting'] = 'group/#group_id#/reporting/';

        $arDefaultUrlTemplates404['group_kpi'] = 'group/#group_id#/kpi/';
        $arParams['SEF_URL_TEMPLATES']['group_kpi'] = 'group/#group_id#/kpi/';

        $arDefaultUrlTemplates404['group_risks'] = 'group/#group_id#/risks/';
        $arParams['SEF_URL_TEMPLATES']['group_risks'] = 'group/#group_id#/risks/';
    }
}
