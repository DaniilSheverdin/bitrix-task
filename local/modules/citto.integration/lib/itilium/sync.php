<?php

namespace Citto\Integration\Itilium;

use CTasks;
use Exception;
use Monolog\Logger;
use Bitrix\Main\Loader;
use Psr\Log\LoggerInterface;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\ArgumentException;
use Bitrix\Tasks\Kanban\StagesTable;
use Bitrix\Tasks\Kanban\TaskStageTable;
use Bitrix\Main\ObjectPropertyException;
use Monolog\Handler\RotatingFileHandler;
use Sprint\Migration\Helpers\HlblockHelper;
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;

require $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';

/**
 * Class Sync
 * @package Citto\Integration\Itilium
 */
class Sync
{
    /**
     * Сколько дней хранить логи
     *
     * @var int
     */
    private $maxFiles = 90;

    /**
     * @var LoggerInterface
     */
    private $logger = null;

    /**
     * @var string
     */
    public $entityDataClass = null;

    /**
     * Конструктор
     *
     * @param LoggerInterface|null $logger
     *
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function __construct(LoggerInterface $logger = null)
    {
        if ($logger) {
            $this->logger = $logger;
        } else {
            $this->logger = new Logger('default');
            $this->logger->pushHandler(
                new RotatingFileHandler(
                    $_SERVER['DOCUMENT_ROOT'] . '/local/logs/itilium/sync.log',
                    $this->maxFiles
                )
            );
        }

        Loader::includeModule('highloadblock');
        Loader::includeModule('sprint.migration');
        $helper = new HlblockHelper();
        $hlId = $helper->getHlblockId('ItiliumSync');
        $hlblock = HLTable::getById($hlId)->fetch();
        $entity = HLTable::compileEntity($hlblock);
        $this->entityDataClass = $entity->getDataClass();
    }

    /**
     * Добавить запись в HL синхронизации
     *
     * @param array $arFields Массив данных для вставки
     *
     * @return int
     * @throws Exception
     */
    public function add($arFields = [])
    {
        $this->logger->debug('add: ', $arFields);
        $arFields['UF_DATE_ADD'] = date('d.m.Y H:i:s');
        $arFields['UF_RETRY_COUNT'] = 0;
        $arFields['UF_SOURCE'] = json_encode($arFields['UF_SOURCE']??[], JSON_UNESCAPED_UNICODE);

        $result = $this->entityDataClass::add($arFields);

        return $result->getId();
    }

    /**
     * Добавить задачу на портал
     *
     * @param array $arFields
     *
     * @return int
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function addBitrixTask($arFields = [])
    {
        $this->logger->debug('Входные параметры', $arFields);
        Loader::includeModule('tasks');
        $arParams = [
            // 'projectId'     => \Bitrix\Tasks\Util::generateUUID(false),
            'author'        => 'roman.saushkin@tularegion.ru',
            'review'        => [
                'Diana.Saveleva@tularegion.ru',
            ],
            // 'deadline'      => '30.04.2021 18:00:00',
            // 'title'         => 'Тестовая задача',
            // 'description'   => "Описание\r\n\r\nТекст",
            // 'justification' => "Обоснование\r\n\r\nТекст",
            'group'         => 'unknown',
            'responsible'   => 'roman.saushkin@tularegion.ru',
        ];

        $arTaskFields = [
            'TITLE'         => $arFields['title'],
            'CREATED_BY'    => 570,
            'RESPONSIBLE_ID'=> 570,
            'XML_ID'        => $arFields['projectId'],
            'TASK_CONTROL'  => 'Y',
            'DEADLINE'      => date('d.m.Y H:i:s', strtotime($arFields['deadline'])),
            'DESCRIPTION'   => nl2br($arFields['description'] .
                                "\r\n\r\nОбоснование:\r\n" . $arFields['justification'] .
                                "\r\n\r\nКонтакты:\r\n" . $arFields['contacts']),
        ];
        $this->logger->debug('$arTaskFields', $arTaskFields);

        $obTask = new CTasks();
        $taskId = $obTask->Add($arTaskFields, [
            'SPAWNED_BY_AGENT'      => 'Y',
            'CHECK_RIGHTS_ON_FILES' => 'N',
            'USER_ID'               => $arTaskFields['RESPONSIBLE_ID'],
        ]);
        if (!$taskId) {
            $LAST_ERROR = '';
            if ($e = $GLOBALS['APPLICATION']->GetException()) {
                $LAST_ERROR = $e->GetString();
            }
            $this->logger->error('Не удалось добавить задачу', [$LAST_ERROR]);
            throw new Exception('Не удалось добавить задачу. ' . $LAST_ERROR);
        } else {
            $this->moveToStage($taskId, $arTaskFields['RESPONSIBLE_ID'], 1);
            $arSyncData = [
                'UF_TYPE'       => 'TASK',
                'UF_GUID'       => $arFields['projectId'],
                'UF_TASK_ID'    => $taskId,
            ];
            $this->add($arSyncData);

            return $taskId;
        }
    }

    /**
     * Создать объект в Itilium.
     *
     * @param array $arFields Массив полей для создания.
     *
     * @return string
     * @throws Exception
     */
    public function start(array $arFields = [])
    {
        $this->logger->debug('Входные параметры', $arFields);
        $arAddFields = $arFields;

        try {
            $arCreateFields = $arFields['UF_SOURCE'];
            $tsStart = is_object($arCreateFields['DATE_START']) ?
                        $arCreateFields['DATE_START']->getTimeStamp() :
                        strtotime($arCreateFields['DATE_START']);
            $tsFinish = is_object($arCreateFields['DATE_FINISH']) ?
                        $arCreateFields['DATE_FINISH']->getTimeStamp() :
                        strtotime($arCreateFields['DATE_FINISH']);
            $arCreateFields['DATE_START'] = date('Y-m-d H:i:s', $tsStart);
            $arCreateFields['DATE_FINISH'] = date('Y-m-d H:i:s', $tsFinish);
            if (!empty($arCreateFields['PARENT'])) {
                $arCreateFields['PARENT_GUID'] = $arCreateFields['PARENT'];
            }

            $obSyncIncident = new Incident($this->logger);
            $arAddFields['UF_INCIDENT_GUID'] = $obSyncIncident->add($arCreateFields);

            if ($arCreateFields['CREATE_TASK']) {
                $arCreateFields['PARENT_GUID'] = $arAddFields['UF_INCIDENT_GUID'];
                $obSyncTask = new Task($this->logger);
                $arAddFields['UF_TASK_GUID'] = $obSyncTask->add($arCreateFields);
            }
            unset(
                $arCreateFields['SERVICE'],
                $arCreateFields['SERVICE_COMPONENT']
            );

            $arAddFields['UF_SOURCE'] = $arCreateFields;
        } catch (Exception $e) {
            $this->logger->error('Ошибка: ' . $e->getMessage(), []);
            // throw new Exception($e->getMessage());
        }

        return $this->add($arAddFields);
    }

    /**
     * Перенести задачу в столбец Моего плана.
     *
     * @param int $taskId ID задачи
     * @param int $userId ID пользователя
     * @param int $stageId Номер столбца на канбане
     *
     * @return void
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function moveToStage(int $taskId, int $userId, int $stageId)
    {
        Loader::includeModule('tasks');
        // Найдем колонку
        $comletedStage = StagesTable::getList([
            'filter'    => [
                '=ENTITY_ID'    => $userId,
                '=ENTITY_TYPE'  => StagesTable::WORK_MODE_USER,
            ],
            'order'     => ['SORT' => 'ASC'],
            'limit'     => 1,
            'offset'    => ($stageId - 1),
        ])->fetch();

        // Проверяем, существует ли уже эта задача в разделе "Мой план"
        $check = TaskStageTable::GetList([
            'filter' => [
                '=TASK_ID'              => $taskId,
                '=STAGE.ENTITY_ID'      => $userId,
                '=STAGE.ENTITY_TYPE'    => StagesTable::WORK_MODE_USER,
            ],
            'limit' => 1
        ])->fetch();

        if (empty($check)) {
            $upsert = TaskStageTable::add([
                'STAGE_ID'  => $comletedStage['ID'],
                'TASK_ID'   => $taskId
            ]);
        }
    }

    /**
     * Получить строку синхронизации по ID.
     *
     * @param int $id ID синхронизации.
     *
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getById(int $id = 0)
    {
        if ($id <= 0) {
            throw new Exception('Empty ID', -1);
        }

        $res = $this->entityDataClass::getById($id);
        if ($row = $res->fetch()) {
            return $row;
        }

        return [];
    }

    public function getCreatorObject(
        string $type,
        LoggerInterface $logger = null
    ) {
        $obCreator = null;
        switch ($type) {
            case 'BIZPROC_TASK':
                $obCreator = new Task($logger);
                break;
            case 'BIZPROC_INCIDENT':
                $obCreator = new Incident($logger);
                break;
            default:
                break;
        }

        return $obCreator;
    }
}
