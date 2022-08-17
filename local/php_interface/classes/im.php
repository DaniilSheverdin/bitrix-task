<?php

namespace Citto;

use Citto\Controlorders\Settings;

class IM
{
    public function handleOnBeforeMessageNotifyAdd(&$arFields)
    {
        if ($arFields['NOTIFY_EVENT'] == 'invite_group') {
            /*
             * Не отправлять уведомления о вступлении в группу "Повышаем грамотность"
             */
            if (false !== mb_strpos($arFields['NOTIFY_MESSAGE'], '/workgroups/group/667/')) {
                return false;
            }
            if (false !== mb_strpos($arFields['NOTIFY_MESSAGE'], '/workgroups/group/635/')) {
                return false;
            }
            if (false !== mb_strpos($arFields['NOTIFY_MESSAGE'], '/workgroups/group/782/')) {
                return false;
            }
        } elseif (
            $arFields['NOTIFY_MODULE'] == 'tasks' &&
            $arFields['TO_USER_ID'] == 581
        ) {
            /*
             * Для Якушкиной принудительно отправлять уведомления отдельным шаблоном
             * Там в начало темы добавлено BP:
             */
            $arFields['EMAIL_TEMPLATE'] = 'IM_NEW_NOTIFY_SPECIAL';
            $arFields['NOTIFY_EMAIL_TEMPLATE'] = 'IM_NEW_NOTIFY_SPECIAL';
        }
    }

    public function handleOnGetNotifySchema()
    {
        if (!in_array($GLOBALS['USER']->GetID(), [570])) {
            return [];
        }
        $arReturn = [];
        $arDefSettings = [
            'SITE'      => 'Y',
            'MAIL'      => 'Y',
            'PUSH'      => 'Y',
            'XMPP'      => 'N',
            'DISABLED'  => [
                'xmpp',
                // 'push',
                // 'mail',
                // 'site',
            ],
        ];

        $arReturn['controlorders'] = [
            'NAME'      => 'Контроль поручений',
            'NOTIFY'    => [],
        ];

        foreach (Settings::$arEventMessageSettings as $id) {
            $arReturn['controlorders']['NOTIFY'][ $id ] = array_merge(
                [
                    'NAME' => Settings::$arEventMessageTitles[ $id ],
                ],
                $arDefSettings
            );
        }
        return $arReturn;
    }
}
