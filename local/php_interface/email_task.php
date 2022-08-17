<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) {
    die();
}

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler('mail', 'OnGetFilterList', ['EmailTasks', 'OnGetFilterList'], false, 101);
$eventManager->addEventHandler('mail', 'OnGetFilterListImap', ['EmailTasks', 'onGetFilterListImap'], false, 101);

class EmailTasks
{
    public static function OnGetFilterList()
    {
        return array(
            'ID'                    =>  'crm',
            'NAME'                  =>  'EmailTasks',
            'ACTION_INTERFACE'      =>  $_SERVER['DOCUMENT_ROOT'].'/local/php_interface/email_task_action.php',
            'PREPARE_RESULT_FUNC'   =>  array('EmailTasks', 'PrepareVars'),
            'CONDITION_FUNC'        =>  array('EmailTasks', 'EmailMessageCheck'),
            'ACTION_FUNC'           =>  array('EmailTasks', 'EmailMessageAdd')
        );
    }

    public static function EmailMessageCheck($arFields, $ACTION_VARS)
    {
        $arACTION_VARS = explode('&', $ACTION_VARS);
        for ($i = 0, $ic = count($arACTION_VARS); $i < $ic; $i++) {
            $v = $arACTION_VARS[$i];
            if ($pos = mb_strpos($v, '=')) {
                $name = mb_substr($v, 0, $pos);
                ${$name} = urldecode(mb_substr($v, $pos+1));
            }
        }
        return true;
    }

    public static function onGetFilterListImap()
    {
        return array(
            'ID'          => 'crm_imap',
            'NAME'        => 'EmailTasks',
            'ACTION_FUNC' => array('EmailTasks', 'imapEmailMessageAdd')
        );
    }

    private static function FindUserIDByEmail($email)
    {
        $email = trim(strval($email));
        if ($email === '') {
            return 0;
        }

        $dbUsers = CUser::GetList(
            ($by='ID'),
            ($order='ASC'),
            array('=EMAIL' => $email),
            array(
                'FIELDS' => array('ID'),
                'NAV_PARAMS' => array('nTopCount' => 1)
            )
        );

        $arUser = $dbUsers ? $dbUsers->Fetch() : null;
        return $arUser ? intval($arUser['ID']) : 0;
    }

    private static function ExtractEmailsFromBody($body)
    {
        $body = strval($body);

        $out = array();
        if (!preg_match_all('/\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i', $body, $out)) {
            return array();
        }

        $result = array();
        foreach ($out[0] as $email) {
            $email = mb_strtolower($email);
            if (!in_array($email, $result, true)) {
                $result[] = $email;
            }
        }

        return $result;
    }

    public static function imapEmailMessageAdd($msgFields)
    {
        return true;
    }

    public static function EmailMessageAdd($arMessageFields, $ACTION_VARS = null)
    {
        if (!defined('RULE_EMAILTASKS')) {
            return true;
        }
        \Bitrix\Main\Loader::includeModule('mail');
        \Bitrix\Main\Loader::includeModule('tasks');
        \Bitrix\Main\Loader::includeModule('disk');
        $date           = isset($arMessageFields['FIELD_DATE'])             ? $arMessageFields['FIELD_DATE']            : '';
        $msgID          = isset($arMessageFields['ID'])                     ? intval($arMessageFields['ID'])            : 0;
        $mailboxID      = isset($arMessageFields['MAILBOX_ID'])             ? intval($arMessageFields['MAILBOX_ID'])    : 0;
        $from           = isset($arMessageFields['FIELD_FROM'])             ? $arMessageFields['FIELD_FROM']            : '';
        $replyTo        = isset($arMessageFields['FIELD_REPLY_TO'])         ? $arMessageFields['FIELD_REPLY_TO']        : '';
        $to             = isset($arMessageFields['FIELD_TO'])               ? $arMessageFields['FIELD_TO']              : '';
        $cc             = isset($arMessageFields['FIELD_CC'])               ? $arMessageFields['FIELD_CC']              : '';
        $bcc            = isset($arMessageFields['FIELD_BCC'])              ? $arMessageFields['FIELD_BCC']             : '';
        $subject        = strip_tags(html_entity_decode(trim($arMessageFields['SUBJECT'])))     ?: 'Без темы';
        $body           = isset($arMessageFields['BODY'])                   ? strip_tags(html_entity_decode($arMessageFields['BODY_HTML']), '<img><p><br><div><a>')  : '';
        $task_RESPONSIBLE_IDs   = [];
        $task_ATTACHMENTS       = [];
        $task_AUDITORS          = [];
        $task_CREATED_BY        = null;
        $task_END_DATE_PLAN     = null;

        if ($arMessageFields['IS_SPAM'] || $arMessageFields['IS_SEEN'] || $arMessageFields['IS_TRASH']) {
            return;
        }
        
        $subject = trim(preg_replace("/^(Re|FW):/ui", '', $subject));

        try {
            $from_email = new \Bitrix\Main\Mail\Address($from);
            if (!$from_email->validate()) {
                return;
            }
            if (in_array(mb_strtolower($from_email->getEmail()), ['noreply_im@pos.gosuslugi.ru'])) {
                \Citto\Integration\Email::processMedialogia($subject, html_entity_decode($arMessageFields['BODY_HTML']));
                return;
            }
            if (in_array(mb_strtolower($from_email->getEmail()), ['omnitracker@tularegion.ru'])) {
                return;
            }

            $task_CREATED_BY = self::FindUserIDByEmail($from_email->getEmail());
            if (!$task_CREATED_BY) {
                if (mb_substr($from_email->getEmail(), -14) != '@tularegion.ru') {
                    return;
                }
                throw new \Exception('Не найден постановщик');
            }

            foreach (explode(',', $to) as $email_item) {
                $email_item = trim($email_item);
                if (empty($email_item)) {
                    continue;
                }
                $to_email = new \Bitrix\Main\Mail\Address($email_item);
                if (!$to_email->validate()) {
                    throw new \Exception('Неверно указан ответственный '.$email_item);
                }
                
                if (mb_strtolower($to_email->getEmail()) == 'corp@tularegion.ru') {
                    continue;
                }

                if (in_array(mb_strtolower($to_email->getEmail()), ['gau.to.cit@tularegion.ru','all@tularegion.ru'])) {
                    return;
                }
                $to_user_id = self::FindUserIDByEmail($to_email->getEmail());
                if (!$to_user_id) {
                    throw new \Exception('Не найден ответственный '.$email_item);
                }
                if (isset($task_RESPONSIBLE_IDs[$to_user_id])) {
                    continue;
                }
                $task_RESPONSIBLE_IDs[$to_user_id] = $to_user_id;
            }
            if (empty($task_RESPONSIBLE_IDs)) {
                throw new \Exception('Не указаны ответственные');
            }

            if ($cc) {
                foreach (explode(',', $cc) as $email_item) {
                    $email_item = trim($email_item);
                    if (empty($email_item)) {
                        continue;
                    }

                    $to_email = new \Bitrix\Main\Mail\Address($email_item);
                    if (!$to_email->validate()) {
                        throw new \Exception('Неверно указан получатель '.$email_item);
                    }
                    
                    if (mb_strtolower($to_email->getEmail()) == 'corp@tularegion.ru') {
                        continue;
                    }

                    $to_user_id = self::FindUserIDByEmail($to_email->getEmail());
                    if (!$to_user_id) {
                        throw new \Exception('Не найден ответственный '.$email_item);
                    }

                    if (isset($task_AUDITORS[ $to_user_id ])) {
                        continue;
                    }
                    $task_AUDITORS[ $to_user_id ] = $to_user_id;
                }
            }

            $attachment_res = \CMailAttachment::getList([], ['MESSAGE_ID' => $msgID]);
            while ($attachment = $attachment_res->fetch()) {
                $newAttachment = [
                    'name'          => strip_tags($attachment['FILE_NAME']),
                    'type'          => $attachment['CONTENT_TYPE'],
                    'attachment_id' => $attachment['ID'],
                    'MODULE_ID'     => 'mail',
                ];
                if ($attachment['FILE_ID']) {
                    $newAttachment += CFile::MakeFileArray($attachment['FILE_ID']);
                } elseif ($attachment['FILE_DATA']) {
                    $newAttachment['content'] = $attachment['FILE_DATA'];
                } else {
                    throw new Exception('Не удалось загрузить: '.strip_tags($attachment['FILE_NAME']));
                }

                $task_ATTACHMENTS[] = $newAttachment;
            }

            if (preg_match('/Срок\:\s*(?<date>[0-9]{2}\.[0-9]{2})(?<year>(\.[0-9]{4})?)/ui', $body, $matches)) {
                $task_END_DATE_PLAN = date('d.m.Y', strtotime($matches['date'].($matches['year']?$matches['year']:date('.Y'))));
                $task_END_DATE_PLAN .= ' 18:00:00';
            }
            if (preg_match_all("!(?<imgs>\<img .*? src=\"aid\:([^\>]+?)\>)!i", $body, $matches)) {
                $body = str_replace($matches['imgs'], '', $body);
            }

            // Обрезаем срок из тела письма
            $body = preg_replace('/Срок\:\s*(?<date>[0-9]{2}\.[0-9]{2})(?<year>(\.[0-9]{4})?)/ui', '', $body);

            // Удаляем подпись и всё, что после неё
            $arSignatures = ['С уважением'];
            foreach ($arSignatures as $sSignature) {
                $body = preg_replace("/$sSignature(.*)/usi", '', $body);
            }

            foreach ($task_RESPONSIBLE_IDs as $RESPONSIBLE_ID) {
                $task_XML_ID = md5(serialize([$subject,$task_CREATED_BY,$RESPONSIBLE_ID,$task_END_DATE_PLAN]));
                if (CTasks::GetList([], ['XML_ID'=>$task_XML_ID, '!STATUS'=>CTasks::STATE_COMPLETED ])->GetNext()) {
                    throw new \Exception('Задача уже поставлена');
                }

                $ar_fields = [
                    'TITLE'             => $subject,
                    'DESCRIPTION'       => $body,
                    'START_DATE_PLAN'   => $date,
                    'CREATED_BY'        => $task_CREATED_BY,
                    'RESPONSIBLE_ID'    => $RESPONSIBLE_ID,
                    'AUDITORS'          => $task_AUDITORS,
                    'XML_ID'            => $task_XML_ID,
                    'TASK_CONTROL'      => 'Y'
                ];
                if ($task_END_DATE_PLAN) {
                    $ar_fields['END_DATE_PLAN'] = $task_END_DATE_PLAN;
                    $ar_fields['DEADLINE'] = $task_END_DATE_PLAN;
                }
                
                if ($task_ATTACHMENTS) {
                    $UF_TASK_WEBDAV_FILES = [];
                    $storage = \Bitrix\Disk\Driver::getInstance()->getStorageByUserId($RESPONSIBLE_ID);
                    if (!$storage) {
                        throw new \Exception("Не удалось загрузить файлы. У пользователя $RESPONSIBLE_ID нет доступа к модулю диск");
                    }

                    $folder = $storage->getRootObject();
                    $folder = $folder->getChild([
                        '=NAME' => 'Tasks files',
                        'TYPE'  => \Bitrix\Disk\Internals\FolderTable::TYPE_FOLDER
                    ]);
                    if (!$folder) {
                        $folder = $storage->addFolder([
                                'NAME'      => 'Tasks files',
                                'CREATED_BY'=> $RESPONSIBLE_ID
                        ]);
                        if (!$folder) {
                            throw new \Exception('Не удалось создать папку для задач. '.implode(',', array_map(function ($error) {
                                return $error->getMessage();
                            }, $storage->getErrors())));
                        }
                    }
                    $sub_folder = $folder->addSubFolder([
                        'NAME'      => date('d_m_Y_H_i_s__').md5($subject),
                        'CREATED_BY'=> $RESPONSIBLE_ID
                    ]);
                    if (!$sub_folder) {
                        throw new \Exception('Не удалось создать папку для задачи. '.implode(',', array_map(function ($error) {
                            return $error->getMessage();
                        }, $folder->getErrors())));
                    }

                    foreach ($task_ATTACHMENTS as $task_ATTACHMENT) {
                        $file = $sub_folder->uploadFile($task_ATTACHMENT, [
                            'NAME'          => $task_ATTACHMENT['name'],
                            'CREATED_BY'    => $RESPONSIBLE_ID,
                        ], [], true);
                        if (!$file) {
                            throw new \Exception('Не удалось загрузить файл ' . $task_ATTACHMENT['name']);
                        }
                        $UF_TASK_WEBDAV_FILES[] = \Bitrix\Disk\Uf\FileUserType::NEW_FILE_PREFIX . $file->getId();
                    }

                    $ar_fields['UF_TASK_WEBDAV_FILES'] = $UF_TASK_WEBDAV_FILES;
                }

                $obTask = new CTasks();
                $task_id = $obTask->Add($ar_fields, [
                    'SPAWNED_BY_AGENT'      => 'Y',
                    'CHECK_RIGHTS_ON_FILES' => 'N',
                    'USER_ID'               => $RESPONSIBLE_ID,
                ]);
                if (!$task_id) {
                    $LAST_ERROR = '';
                    if ($e = $GLOBALS['APPLICATION']->GetException()) {
                        $LAST_ERROR = $e->GetString();
                    }
                    throw new Exception('Не удалось добавить задачу. ' . $LAST_ERROR);
                }

                self::moveToStage($task_id, $RESPONSIBLE_ID, 1);
            }
            \Bitrix\Main\Mail\Event::send([
                'EVENT_NAME'    => 'EMAILTASKS_SUCCESS',
                'LID'           => 's1',
                'C_FIELDS'      => [
                    'TASK_ID'   => $task_id,
                    'USER_ID'   => $task_CREATED_BY,
                    'EMAIL'     => $from_email->getEmail(),
                    'SUBJECT'   => $subject,
                ],
            ]);
        } catch (\Exception $exc) {
            \Bitrix\Main\Mail\Event::send([
                'EVENT_NAME'    => 'EMAILTASKS_ERROR',
                'LID'           => 's1',
                'C_FIELDS'      => [
                    'EMAIL'     => $from_email->getEmail(),
                    'SUBJECT'   => $subject,
                    'MESSAGE'   => $exc->getMessage()
                ],
            ]);

            file_put_contents(
                $_SERVER['DOCUMENT_ROOT'].'/../newcorp_arch/emailTask.log',
                (
                    'EmailMessageAdd' . PHP_EOL
                    . (isset($from_email)?$from_email->getEmail():'') . PHP_EOL
                    . $exc->getMessage() . PHP_EOL
                    .print_r($exc->getTrace(), true).PHP_EOL
                ),
                FILE_APPEND
            );
        }
        return true;
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
        CModule::IncludeModule('tasks');
        // Найдем колонку
        $comletedStage = \Bitrix\Tasks\Kanban\StagesTable::getList([
            'filter'    => [
                '=ENTITY_ID'    => $userId,
                '=ENTITY_TYPE'  => \Bitrix\Tasks\Kanban\StagesTable::WORK_MODE_USER,
            ],
            'order'     => ['SORT' => 'ASC'],
            'limit'     => 1,
            'offset'    => ($stageId - 1),
        ])->fetch();

        // Проверяем, существует ли уже эта задача в разделе 'Мой план'
        $check = \Bitrix\Tasks\Kanban\TaskStageTable::GetList([
            'filter' => [
                '=TASK_ID'              => $taskId,
                '=STAGE.ENTITY_ID'      => $userId,
                '=STAGE.ENTITY_TYPE'    => \Bitrix\Tasks\Kanban\StagesTable::WORK_MODE_USER,
            ],
            'limit' => 1
        ])->fetch();

        if (empty($check)) {
            $upsert = \Bitrix\Tasks\Kanban\TaskStageTable::add([
                'STAGE_ID'  => $comletedStage['ID'],
                'TASK_ID'   => $taskId
            ]);
        }
    }

    public static function PrepareVars()
    {
        return '';
    }
}
