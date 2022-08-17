<?php

namespace Citto\Tasks;

use CUser;
use CEvent;
use CTasks;
use CTaskItem;
use CIMMessenger;
use DateInterval;
use CIBlockElement;
use Bitrix\Main\Loader;

/**
 * Класс для контроля поставленных из БП задач
 */
class BPTaskControl
{
    /**
     * Котроль завершения задачи и действия при событии
     * @param $ID
     * @param $arFields
     * @param $arTaskCopy
     */
    public static function handlerTasksComplite($ID, &$arFields, &$arTaskCopy)
    {
        if ($ID > 0 && $arFields['STATUS'] == CTasks::STATE_COMPLETED
            && $arTaskCopy['STATUS'] != CTasks::STATE_COMPLETED
            && isset($arFields['META:PREV_FIELDS'])
            && $arFields['META:PREV_FIELDS']['TITLE'] == 'Прохождение курсов по листу адаптации'
        ) {

            $boolRes = CIMMessenger::Add(
                [
                    'MESSAGE_TYPE' => 'S',
                    'TO_USER_ID' => $arFields['META:PREV_FIELDS']['RESPONSIBLE_ID'],
                    'FROM_USER_ID' => 1,
                    'MESSAGE' => 'Вы прошли курсы по листу адаптации. Курсы проверит администратор, при необходимости нужно будет их перепройти. В случае успешного завершения курсов Вам поступит уведомление.',
                    'AUTHOR_ID' => 1,
                    'EMAIL_TEMPLATE' => 'some',
                    'NOTIFY_TYPE' => IM_NOTIFY_FROM
                ]
            );
        }
    }

    public static function handlerTasksItiliumStatus($ID, &$arFields, &$arTaskCopy)
    {
        if ($sStatus = $arFields['STATUS'] && $sItiliumID = $arFields['META:PREV_FIELDS']['UF_ITILIUM_UID']) {
            $arStatuses = [
                CTasks::STATE_NEW                  => 'd4e0527b-11e0-11ea-80ec-005056b3241b', /* Назначен */
                CTasks::STATE_PENDING              => 'e204864f-11e0-11ea-80ec-005056b3241b', /* В работе */
                CTasks::STATE_IN_PROGRESS          => 'e204864f-11e0-11ea-80ec-005056b3241b', /* В работе */
                CTasks::STATE_SUPPOSEDLY_COMPLETED => '4faa8392-11e1-11ea-80ec-005056b3241b', /* Выполнен.Требуется подтверждение */
                CTasks::STATE_COMPLETED            => 'f151e061-11e0-11ea-80ec-005056b3241b', /* Выполнен */
                CTasks::STATE_DEFERRED             => '4770146c-1684-11ea-80ec-005056b3241b', /* Пауза */
                CTasks::STATE_DECLINED             => '96054743-11e1-11ea-80ec-005056b3241b', /* Отклонен */
            ];

        }
    }
}
