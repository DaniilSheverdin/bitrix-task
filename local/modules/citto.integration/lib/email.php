<?

namespace Citto\Integration;

use CUser;
use CEvent;
use CTasks;
use CTaskItem;
use CIntranetUtils;
use Monolog\Logger;
use CTaskCommentItem;
use Bitrix\Main\Loader;
use Bitrix\Tasks\Kanban\StagesTable;
use Bitrix\Tasks\Kanban\TaskStageTable;
use Monolog\Handler\RotatingFileHandler;

class Email
{
    public static function processMedialogia(string $subject = '', string $text = '')
    {
        Loader::includeModule('tasks');
        Loader::includeModule('forum');
        Loader::includeModule('intranet');
        $logger = new Logger('default');
        $logger->pushHandler(
            new RotatingFileHandler(
                $_SERVER['DOCUMENT_ROOT'] . '/local/logs/email/medialogia.log',
                90
            )
        );

        preg_match_all('/\<td width="200" (.*)\>(.*)\<\/td\>/Usi', $text, $matches1);
        preg_match_all('/\<td width="340" (.*)\>(.*)\<\/td\>/Usi', $text, $matches2);
        preg_match('/Для просмотра деталей(.*)\<\/td\>/Usi', $text, $matches3);
        $array = [];
        foreach ($matches1[2] as $k => $v) {
            $title = trim(strip_tags($v));
            $value = trim(strip_tags($matches2[2][ $k ]));

            $title = str_replace("\r\n", ' ', $title);
            $value = str_replace("\r\n", ' ', $value);
            $value = str_replace('&amp;', '&', $value);
            $value = preg_replace('/[\s]{2,}/', ' ', $value);
            $array[ $title ] = $value;
        }

        $link = trim(strip_tags($matches3[0]));
        $link = str_replace("\r\n", ' ', $link);
        $link = preg_replace('/[\s]{2,}/', ' ', $link);

        $incidentId = $array['№ Инцидента:'] ?? 0;
        if ($incidentId < 0 && preg_match_all('/incident=([0-9]+)/si', $link, $matches4)) {
            $incidentId = $matches4[1][0];
        }

        $description = $subject . '<br/><br/>';
        $description .= '<table class="data-table">' . PHP_EOL;
        foreach ($array as $key => $value) {
            $description .= '<tr>' . PHP_EOL;
            $description .= '<td><b>' . $key . '</b></td>' . PHP_EOL;
            $description .= '<td>' . $value . '</td>' . PHP_EOL;
            $description .= '</tr>' . PHP_EOL;
        }
        $description .= '</table><br/><br/>';
        $description .= $link;

        $parser = new \forumTextParser();
        $description = $parser->convert($description);
        $description = str_replace(
            ['&lt;', '&gt;', '<noindex>', '</noindex>', ],
            ['<', '>', '', '', ],
            $description
        );

        $arResponsibles = [
            44,     // Никитина Наталья
            1545,   // Горбунова Ирина
            228,    // Смирнова Ольга
            233,    // Трофимов Михаил
            231,    // Савина Елена
            229,    // Вертинская Галина
            259,    // Зотова Галина
        ];

        $responsibleId = 0;

        foreach ($arResponsibles as $uId) {
            if (!CIntranetUtils::IsUserAbsent($uId)) {
                $responsibleId = $uId;
                break;
            }
        }

        if ($responsibleId <= 0) {
            $responsibleId = $arResponsibles[0];
        }

        $arAccomplices = array_diff($arResponsibles, [$responsibleId]);

        $taskXmlId = md5('MEDIALOGIA-' . $incidentId??$link);
        $res = CTasks::GetList(
            [],
            [
                'XML_ID'    => $taskXmlId,
                '!STATUS'   => CTasks::STATE_COMPLETED
            ]
        );
        $taskId = 0;
        if ($row = $res->Fetch()) {
            $taskId = $row['ID'];

            $oTaskItem = CTaskItem::getInstance($taskId, 1);
            $arFields = [
                'AUTHOR_ID'     => 1,
                'POST_MESSAGE'  => $description,
            ];
            CTaskCommentItem::add($oTaskItem, $arFields);
        } else {
            $arFields = [
                'TITLE'             => $subject,
                'DESCRIPTION'       => $description,
                'CREATED_BY'        => 1,
                'RESPONSIBLE_ID'    => $responsibleId,
                'ACCOMPLICES'       => $arAccomplices,
                'XML_ID'            => $taskXmlId,
                'GROUP_ID'          => 845,
                'TASK_CONTROL'      => 'Y'
            ];
            $obTask = new CTasks();
            $task_id = $obTask->Add($arFields, [
                'SPAWNED_BY_AGENT'      => 'Y',
                'CHECK_RIGHTS_ON_FILES' => 'N',
                'USER_ID'               => $responsibleId,
            ]);
            if (!$task_id) {
                $LAST_ERROR = '';
                if ($e = $GLOBALS['APPLICATION']->GetException()) {
                    $LAST_ERROR = $e->GetString();
                }
                $logger->error('Не удалось добавить задачу. ' . $LAST_ERROR, []);
            }
            foreach ($arResponsibles as $uId) {
                self::moveToStage($task_id, $uId, 1);
            }
        }

        $dbRes = CUser::GetList(
            $by = 'ID',
            $sort = 'ASC',
            ['ID' => implode('|', $arResponsibles)]
        );
        $arEmails = [];
        while ($arUser = $dbRes->GetNext()) {
            $arEmails[ $arUser['ID'] ] = $arUser['EMAIL'];
        }

        $arFields = [
            'SENDER'    => 'corp-noreply@tularegion.ru',
            'RECEIVER'  => implode(';', $arEmails),
            'TITLE'     => $subject,
            'MESSAGE'   => $text,
        ];
        $event = new CEvent();
        $event->Send('BIZPROC_HTML_MAIL_TEMPLATE', 'nh', $arFields, "N");
    }

    /**
     * Перенести задачу в столбец Моего плана
     * @param int $taskId
     * @param int $userId
     * @param int $stageId
     * @return type
     *
     * @todo Собрать в одном месте
     */
    private static function moveToStage($taskId, $userId, $stageId)
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

        // Проверяем, существует ли уже эта задача в разделе 'Мой план'
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

}
