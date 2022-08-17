<?php

namespace Citto\Components;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\DB\Exception;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use CBitrixComponent;
use \Bitrix\Tasks\Integration\Forum\Task\Comment;

include_once $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';

Loader::includeModule('iblock');
Loader::IncludeModule('tasks');

class Doctortask extends CBitrixComponent
{
    public const EXCHANGE_TOKEN = 'gLjr32RWh32grghe3h23Pjw';

    private const TEST_CREATOR = 6129;

    private const DOCTOR_URL = 'https://172.21.254.82/api-visit/index.php';

    public function onPrepareComponentParams($arParams)
    {
        return $arParams;
    }

    public function executeComponent()
    {
        $template = $this->getTemplateName();
        // @todo:
        /*$_REQUEST = [
             "chname" => "312",
             "cashid" => 990001110,
             "schedid" => 3020009035,
             "jid" => 990001205,
             "depcode" => 990046956,
             "dcode" => 1020000370,
             "iscommon" => 0,
             "chid" => '-1',
             "workdate" => "23.12.2021",
             "bhour" => 8,
             "bmin" => 0,
             "fhour" => 8,
             "fmin" => 15,
             "pcode" => "860022711",
             "fio" => "Савельева Диана Николаевна",
             "phone" => "+7(950)913−39-20",
             "email" => "dzenbrahman@yandex.ru",
             "diagnosis" => "J06.8 - Другие острые инфекции верхних дыхательных путей множественной локализации",
             "doccode" => "0d1020000370c-1",
             "lpu" => "ГУЗ Плавская центральная районная больница имени С.С. Гагарина",
             "doc" => "Колесникова Л.Ю.",
             "worktime" => "23.12.2021 08:00",
             "token" => "gLjr32RWh32grghe3h23Pjw"
        ];*/

        // @todo:
        /* $_REQUEST = [
             'token' => 'gLjr32RWh32grghe3h23Pjw',
             'schedid' => 3020009035,
             'confirmed' => '1',
             'email' => "dzenbrahman@yandex.ru",
             'pcode' => '860022711'
         ];*/

        // @todo:
        /*$_REQUEST = [
            'token' => 'gLjr32RWh32grghe3h23Pjw',
            'taskid' => 98405,
            'cancel' => '1'
        ];*/


        $this->arResult['request'] = $_REQUEST;

        if (isset($this->arResult['request']['token']) && $this->arResult['request']['token'] == self::EXCHANGE_TOKEN) {

            if (isset($this->arResult['request']['jid'])) {
                $this->arResult['JSON'] = $this->_createTask();
            } elseif (isset($this->arResult['request']['confirmed'])) {
                if ($this->arResult['request']['confirmed'] == '1') {
                    $this->arResult['JSON'] = $this->_mailConfirm();
                } else {
                    $this->arResult['JSON'] = $this->_mailUnconfirmed();
                }
            } elseif (isset($this->arResult['request']['cancel']) && isset($this->arResult['request']['taskid'])) {
                $this->arResult['JSON'] = $this->_cancelTask();
            } else {
                $this->arResult['JSON'] = ['status' => false, 'result' => 'Unknown error'];
            }
        } else {
            $this->arResult['JSON'] = ['status' => false, 'result' => 'Invalidate token'];
        }
        $this->includeComponentTemplate();
    }

    private function _createTask()
    {

        $CREATED_BY = $this->_getUserCreator();
        $VIEWED_BY = $this->_getUserViewer();
        //$CREATED_BY = self::TEST_CREATOR;

        $objDbOtdels = \CIBlockSection::GetList(
            ["SORT" => "ASC"],
            ['IBLOCK_ID' => IBLOCK_ID_STRUCTURE, 'ACTIVE' => 'Y', 'UF_GUZ_JID' => trim($this->arResult['request']['jid'])],
            false,
            ['UF_HEAD', 'UF_HEAD2']
        );

        $fetchResultJID = $objDbOtdels->Fetch();

        if (!empty($fetchResultJID['UF_HEAD'])) {
            $RESPONSIBLE_ID = $fetchResultJID['UF_HEAD'];
        } elseif (!empty($fetchResultJID['UF_HEAD2'])) {
            $RESPONSIBLE_ID = $fetchResultJID['UF_HEAD2'];
        } else {
            $RESPONSIBLE_ID = $CREATED_BY;
        }

        // @todo: тестирование назначения задачи
        //$RESPONSIBLE_ID = 598;

        $obTaskRes = \CTaskItem::add([
            'TITLE' => "Заявка на ложное посещение врача на doctor71.ru (visit-doctor71-{$this->arResult['request']['schedid']})",
            'DESCRIPTION' => $this->_taskText(),
            'DEADLINE' => (new \DateTime())->modify("+30 days")->format("d.m.Y") . ' 18:00:00',
            'PRIORITY' => 1,
            'RESPONSIBLE_ID' => $RESPONSIBLE_ID,
            'CREATED_BY' => $CREATED_BY,
            'SITE_ID' => SITE_ID
        ], $CREATED_BY);

        $taskID = (is_object($obTaskRes) && $obTaskRes->getId()) ? $obTaskRes->getId() : false;
        $obTaskRes->update(['AUDITORS' => [$VIEWED_BY]]);
        return ['status' => (!empty($taskID)), 'result' => $taskID ? 'complite' : 'create task error', 'taskid' => $taskID, 'token' => self::EXCHANGE_TOKEN];
    }

    private function _cancelTask()
    {

        $CREATED_BY = $this->_getUserCreator();
        //$CREATED_BY = self::TEST_CREATOR;

        $obListTask = \CTaskItem::getInstance($this->arResult['request']['taskid'], $CREATED_BY);

        if ($obListTask) {
            $arListTask = $obListTask->getData();

            if (isset($arListTask['ID']) && $arListTask['STATUS'] <= 3) {
                $oTaskItem = new \CTaskItem($this->arResult['request']['taskid'], $CREATED_BY);

                Comment::add($this->arResult['request']['taskid'], [
                    'AUTHOR_ID' => $CREATED_BY,
                    'POST_MESSAGE' => 'Заявка отменена пользователем',
                ]);


                $obTaskRes = $oTaskItem->Update([
                    'STATUS' => 5
                ],
                    ['SKIP_ACCESS_CONTROL' => true]);

                return ['status' => true, 'result' => 'Заявка отменена пользователем'];
            } else {
                return ['status' => false, 'result' => 'Задача по заявке не найдена'];
            }
        } else {
            return ['status' => false, 'result' => 'Задача по заявке не найдена'];
        }
    }

    private function _taskText()
    {
        $html = "
          На сайте doctor71.ru пользователь подал жалобу в разделе «Я не посещал врача» на отсутствие посещения врача.<br>
          Необходимо дать ответ пользователю в течение 30 дней.<br>
          Информация о пользователе:
          <ul>
             <li>УИП: {$this->arResult['request']['pcode']},</li>
             <li>телефон: {$this->arResult['request']['phone']},</li> 
             <li>электронная почта: {$this->arResult['request']['email']}</li> 
          </ul>
          Информация о ГУЗ:
          <ul>
            <li>наименование ГУЗ: {$this->arResult['request']['lpu']},</li>
            <li>ФИО врача: {$this->arResult['request']['doc']},</li>
            <li>дата приема: {$this->arResult['request']['worktime']},</li>
             <li>диагноз: {$this->arResult['request']['diagnosis']}.</li>
          </ul><a target=\"_blank\" class=\"task-view-button start ui-btn ui-btn-success\" href=\"/doctor71api/?token={$this->arResult['request']['token']}&email={$this->arResult['request']['email']}&pcode={$this->arResult['request']['pcode']}&schedid={$this->arResult['request']['schedid']}&confirmed=1\">
            Посещение аннулировано</a><br><a target=\"_blank\" class=\"task-view-button start ui-btn ui-btn-success\" href=\"/doctor71api/?token={$this->arResult['request']['token']}&email={$this->arResult['request']['email']}&pcode={$this->arResult['request']['pcode']}&schedid={$this->arResult['request']['schedid']}&confirmed=0\">
			Корректировка не требуется</a>
        ";

        return $html;
    }

    private function _getUserCreator()
    {
        Loader::IncludeModule("tasks");

        $CREATED_BY = 6033;

        /*$objDbMian = \CIBlockSection::GetList(
            ["SORT" => "ASC"],
            ['IBLOCK_ID' => IBLOCK_ID_STRUCTURE, 'ACTIVE' => 'Y', 'ID' => 458],
            false,
            ['UF_HEAD', 'UF_HEAD2']
        );

        $arMainHeads = $objDbMian->Fetch();

        if (isset($arMainHeads['UF_HEAD'])) {
            $CREATED_BY = $arMainHeads['UF_HEAD'];
        } else {
            $CREATED_BY = $arMainHeads['UF_HEAD2'];
        }*/

        return $CREATED_BY;
    }

    private function _getUserViewer()
    {
        Loader::IncludeModule("tasks");

        $VIEWED_BY = 4915;

        return $VIEWED_BY;
    }

    private function _mailConfirm()
    {
        $arMainResult = ['status' => true, 'result' => 'complite'];
        $arMainResult = array_merge($arMainResult, $this->arResult['request']);

        $CREATED_BY = $this->_getUserCreator();
        //$CREATED_BY = self::TEST_CREATOR;

        $obListTask = \CTasks::GetList(
            ['ID' => 'desc'],
            [
                'TITLE' => '%visit-doctor71-' . $this->arResult['request']['schedid'] . '%',
                'CREATED_BY' => $CREATED_BY,
                '<STATUS' => '3'
            ],
            ['ID']
        );

        if ($obListTask) {
            $arListTask = $obListTask->Fetch();

            if ($CREATED_BY && isset($arListTask['ID'])) {
                $this->arResult['request']['taskid'] = $arListTask['ID'];
                $oTaskItem = new \CTaskItem($this->arResult['request']['taskid'], $CREATED_BY);

                Comment::add($this->arResult['request']['taskid'], [
                    'AUTHOR_ID' => $CREATED_BY,
                    'POST_MESSAGE' => 'По заявке посещение врача аннулированно',
                ]);

                $obTaskRes = $oTaskItem->Update(
                    [
                        'STATUS' => 5
                    ],
                    [
                        'SKIP_ACCESS_CONTROL' => true
                    ]
                );

                $ch = curl_init(self::DOCTOR_URL);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, "1C-Bitrix");
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $arMainResult);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                curl_exec($ch);
                curl_close($ch);

                LocalRedirect('/doctor71api/confirm.php');
            } else {
                $arMainResult['status'] = false;
                $arMainResult['result'] = 'task not exist';
            }
        } else {
            $arMainResult['status'] = false;
            $arMainResult['result'] = 'task not exist';
        }
        return $arMainResult;
    }

    private function _mailUnconfirmed()
    {
        $arMainResult = ['status' => true, 'result' => 'complite'];
        $arMainResult = array_merge($arMainResult, $this->arResult['request']);

        $CREATED_BY = $this->_getUserCreator();
        //$CREATED_BY = self::TEST_CREATOR;

        $obListTask = \CTasks::GetList(
            ['ID' => 'desc'],
            [
                'TITLE' => '%visit-doctor71-' . $this->arResult['request']['schedid'] . '%',
                'CREATED_BY' => $CREATED_BY,
                '<STATUS' => '3'
            ],
            ['ID']
        );

        if ($obListTask) {
            $arListTask = $obListTask->Fetch();

            if ($CREATED_BY && isset($arListTask['ID'])) {
                $this->arResult['request']['taskid'] = $arListTask['ID'];
                $oTaskItem = new \CTaskItem($this->arResult['request']['taskid'], $CREATED_BY);

                Comment::add($this->arResult['request']['taskid'], [
                    'AUTHOR_ID' => $CREATED_BY,
                    'POST_MESSAGE' => 'Корректировки по заявке не требуются',
                ]);

                $obTaskRes = $oTaskItem->Update(
                    [
                        'STATUS' => 5
                    ],
                    [
                        'SKIP_ACCESS_CONTROL' => true
                    ]
                );

                $ch = curl_init(self::DOCTOR_URL);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, "1C-Bitrix");
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
                curl_setopt($ch, CURLOPT_TIMEOUT, 120);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $arMainResult);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                curl_exec($ch);
                /*        $content = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $curlError = curl_error($ch);*/
                curl_close($ch);

                LocalRedirect('/doctor71api/confirm.php');
            } else {
                $arMainResult['status'] = false;
                $arMainResult['result'] = 'task not exist';
            }
        } else {
            $arMainResult['status'] = false;
            $arMainResult['result'] = 'task not exist';
        }

        return $arMainResult;
    }
}