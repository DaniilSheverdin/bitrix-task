<?php

namespace Citto\Integration\Delo;

use Exception;
use CBPRuntime;
use CIMMessenger;
use CIBlockElement;
use CUserFieldEnum;
use CBitrixComponent;
use CBPAllTaskService;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Citto\Filesigner\Signer;
use Psr\Log\LoggerInterface;
use Citto\Integration\Delo as DeloObj;
use Monolog\{Handler\StreamHandler, Logger};
use Sprint\Migration\Helpers\HlblockHelper;
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;
use Citto\ControlOrders\Protocol\Component as Protocols;

require $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';

class BpSign
{
    public $signHLId = 0;

    public $entityDataClass = null;

    private $logger;

    /**
     * Конструктор
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        if ($logger) {
            $this->logger = $logger;
        } else {
            $this->logger = new Logger('default');
            $this->logger->pushHandler(
                new StreamHandler(
                    $_SERVER['DOCUMENT_ROOT'] . '/local/logs/bizproc/delo_sign.log'
                )
            );
        }

        Loader::includeModule('sprint.migration');
        $helper = new HlblockHelper();
        $this->signHLId = $helper->getHlblockId('DeloBPSign');

        if ($this->signHLId > 0) {
            Loader::includeModule('highloadblock');
            $hlblock = HLTable::getById($this->signHLId)->fetch();
            $entity = HLTable::compileEntity($hlblock);
            $this->entityDataClass = $entity->getDataClass();
        }
    }

    /**
     * Добавить запись HL
     *
     * @param array $arParams
     *
     * @return array
     *
     * @todo Синхронизация
     */
    public function add(array $arParams = null)
    {
        if (empty($arParams['UF_FILES'])) {
            throw new Exception('Empty files list');
        }
        if (empty($arParams['UF_USERS'])) {
            throw new Exception('Empty users list');
        }

        $arFields = [
            'UF_DATE_ADD'       => date('d.m.Y H:i:s'),
            'UF_IBLOCK'         => $arParams['UF_IBLOCK'] ?? 0,
            'UF_ELEMENT'        => $arParams['UF_ELEMENT'] ?? 0,
            'UF_WORKFLOW_ID'    => $arParams['UF_WORKFLOW_ID'] ?? '',
            'UF_ACTIVITY_NAME'  => $arParams['UF_ACTIVITY_NAME'] ?? '',
            'UF_FILES'          => Json::encode($arParams['UF_FILES']),
            'UF_AUTHOR'         => $arParams['UF_AUTHOR'] ?? $GLOBALS['USER']->GetID(),
            'UF_USERS'          => Json::encode($arParams['UF_USERS']),
        ];

        $obElement = $this->entityDataClass::add($arFields);

        try {
            $obDelo = new DeloObj();
            $iElementId = $obElement->getId();
            if(
                $arFields['UF_IBLOCK']==IBLOCK_ID_BP_REMOTE_WORK||
                $arFields['UF_IBLOCK']==IBLOCK_ID_BP_HELPMONEY_REPORT
            ){
                $arSync = $obDelo->addProjectFromBP($iElementId, 'signers');
            } elseif ($arFields['UF_IBLOCK'] == 620) {
                $arSync = $obDelo->addProjectFromBP($iElementId, 'adresses');
            } else{
                $arSync = $obDelo->addProjectFromBP($iElementId);
            }

            if ($arSync['error'] === false) {
                $this->entityDataClass::update(
                    $iElementId,
                    [
                        'UF_ISN' => $arSync['result']
                    ]
                );
                return $arSync['result'];
            }
            throw new Exception($arSync['error']);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Обновить запись HL
     *
     * @param int   $id
     * @param array $arParams
     *
     * @return bool
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @todo Синхронизация
     */
    public function update(int $id = 0, array $arParams = null)
    {
        if ($id <= 0) {
            throw new Exception('Empty id');
        }
        if (empty($arParams)) {
            throw new Exception('Empty params');
        }

        Loader::includeModule('sprint.migration');
        $helper     = new HlblockHelper();
        $arHlFields = $helper->getFields($this->signHLId);

        $arEnums        = [];
        $arFieldNames   = [];
        foreach ($arHlFields as $arField) {
            $arFieldNames[] = $arField['FIELD_NAME'];
            if ($arField['USER_TYPE_ID'] == 'enumeration') {
                $arEnums[ $arField['FIELD_NAME'] ] = [];
                $obEnum = new CUserFieldEnum();
                $dbres = $obEnum->GetList([], ['USER_FIELD_ID' => $arField['ID']]);
                while ($enum = $dbres->Fetch()) {
                    $arEnums[ $arField['FIELD_NAME'] ][ $enum['XML_ID'] ] = $enum['ID'];
                }
            }
        }

        $arUpdateFields = [];
        foreach ($arParams as $code => $value) {
            if (in_array($code, $arFieldNames)) {
                if (isset($arEnums[ $code ])) {
                    $arUpdateFields[ $code ] = $arEnums[ $code ][ $value ];
                } else {
                    $arUpdateFields[ $code ] = $value;
                }
            }
        }

        $arUpdateFields['UF_DATE_UPDATE'] = date('d.m.Y H:i:s');

        try {
            $this->entityDataClass::update($id, $arUpdateFields);

            $obDelo = new DeloObj();

            if (isset($arParams['VISA_SIGN']) && $arParams['VISA_SIGN'][0]['VISA_TYPE_ISN'] > 0) {
                $this->sign($id, $arParams);
            }

            if (isset($arParams['UF_WHERE'])) {
                $this->logger->debug('В АСЭД отправили запрос на удаление проекта ' . $id);
                $obDelo->delProject($id);
            }
        } catch (Exception $exc) {
            $this->logger->error($exc->getMessage());
            throw new Exception($exc->getMessage());
        }

        return true;
    }

    /**
     * Подписать все документы в БП
     *
     * @param int $id
     * @param array $arParams
     *
     * @return void
     */
    public function sign(int $id = 0, array $arParams = null)
    {
        $rsData = $this->entityDataClass::getList(
            [
                'filter' => [
                    'ID' => $id
                ]
            ]
        );
        $arSignData = [];
        while ($arData = $rsData->Fetch()) {
            $arData['UF_FILES'] = Json::decode($arData['UF_FILES']);
            $arData['UF_USERS'] = Json::decode($arData['UF_USERS']);
            $arSignData = $arData;
        }

        if (!empty($arSignData)) {
            Loader::includeModule('bizproc');
            Loader::includeModule('citto.filesigner');
            $obDeloUsers    = new Users();
            $arDeloUsers    = $obDeloUsers->getList();
            $runtime        = CBPRuntime::GetRuntime();
            $workflow       = $runtime->GetWorkflow($arSignData['UF_WORKFLOW_ID'], true);
            $activity       = $workflow->GetActivityByName($arSignData['UF_ACTIVITY_NAME']);
            $userId         = 0;

            $resWFTask = CBPAllTaskService::GetList(
                [],
                [
                    'WORKFLOW_ID'   => $arSignData['UF_WORKFLOW_ID'],
                    'ACTIVITY_NAME' => $arSignData['UF_ACTIVITY_NAME']
                ]
            );
            $bNeedSign = true;
            $taskId = 0;
            while ($rowWFTask = $resWFTask->Fetch()) {
                if ($rowWFTask['ACTIVITY'] == 'ApproveActivity') {
                    $bNeedSign = false;
                }
                $taskId = $rowWFTask['ID'];
            }
            /*
             * ID пользователя возьмём из HL.
             */
            $userId = $arSignData['UF_USERS'][0];

            /*
             * А теперь по ISN из подписи найдём реального юзера.
             */
            $rsUserData = $obDeloUsers->entityDataClass::getList(
                [
                    'filter' => [
                        'UF_ISN' => $arParams['VISA_SIGN'][0]['ISN_PERSON']
                    ],
                ]
            );
            if ($arUserData = $rsUserData->Fetch()) {
                if (!empty($arUserData['UF_USER_ESTIMATE'])) {
                    if (false !== mb_strpos($arUserData['UF_USER_ESTIMATE'], ',')) {
                        $userId = $arUserData['UF_USER_ESTIMATE'];
                        $this->logger->debug('Из визы нашли пользователя', $userId);
                    }
                }
            }

            if ($arParams['VISA_SIGN'][0]['VISA_TYPE_ISN'] == 1) {
                $preComment = 'Согласовано из АСЭД Дело. ';
                $bApprove = true;
            } elseif (!in_array($arParams['VISA_SIGN'][0]['VISA_TYPE_ISN'], [-1, 1])) {
                $preComment = 'Отклонено из АСЭД Дело. ';
                $bApprove = false;
            }

            $comment = '';

            if (!empty($arParams['VISA_SIGN'][0]['VISA_TYPE_NAME'])) {
                $comment .= $arParams['VISA_SIGN'][0]['VISA_TYPE_NAME'];
            }

            if (!empty($arParams['VISA_SIGN'][0]['REP_TEXT'])) {
                $comment .= ' (' . $arParams['VISA_SIGN'][0]['REP_TEXT'] . ')';
            }

            $bSigned = false;

            /*
             * Если подпись нужна, то подписываем файлы.
             */
            if ($bNeedSign) {
                /*
                 * Найти ISN файлов в истории изменений.
                 */
                $arProtFiles = [];
                foreach ($arParams['PROT'] as $arProtRow) {
                    if ($arProtRow['DESCRIBTION'] == 'Создание файла') {
                        preg_match('/ \(n(.*)\)/si', $arProtRow['COMMENT'], $arMatch);
                        $arProtFiles[ $arProtRow['REF_ISN'] ] = [
                            'ID' => $arMatch[1] ?: 0,
                            'NAME' => $arProtRow['COMMENT']
                        ];
                    }
                }

                try {
                    foreach ($arParams['FILE'] as $arFile) {
                        if (isset($arProtFiles[ $arFile['ISN'] ])) {
                            $protocolFile = $arProtFiles[ $arFile['ISN'] ];
                            if ($protocolFile['ID'] > 0) {
                                foreach ($arFile['EDS'] as $arSign) {
                                    $userId = 0;
                                    foreach ($arDeloUsers as $arUser) {
                                        if ($arUser['UF_ISN'] == $arSign['ISN_PERSON']) {
                                            $userId = $arUser['UF_USER_ESTIMATE'];
                                            break;
                                        }
                                    }
                                    $signFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/bp_sign/' . md5(serialize($arSign)) . '.sig';
                                    file_put_contents($signFile, $arSign['ANNOTAT']);
                                    $arFiles = Signer::getFiles(
                                        [$protocolFile['ID']],
                                        $userId
                                    );
                                    foreach ($arFiles as $file) {
                                        if (!$file['SIGNED']) {
                                            Signer::setSign(
                                                $file['ID'],
                                                $signFile,
                                                $activity->Position,
                                                json_decode(explode(',', $activity->Clearf), true)?:[],
                                                $userId
                                            );
                                        }
                                        $bSigned = true;
                                    }
                                    unlink($signFile);
                                }
                            }
                        }
                    }
                } catch (Exception $exc) {
                    $this->logger->error($exc->getMessage());
                    throw new Exception($exc->getMessage());
                }
            } else {
                $bSigned = true;
            }

            if ($bSigned) {
                $arEventParameters = array(
                    'IS_ASED'       => 'Y',
                    'USER_ID'       => $userId,
                    'REAL_USER_ID'  => $userId,
                    'APPROVE'       => $bApprove,
                    'COMMENT'       => $preComment . $comment
                );

                if ($bNeedSign) {
                    $this->logger->info('Файл подписан, двигаем БП.');
                } else {
                    $this->logger->info('Подпись не требуется, двигаем БП.');
                }
                $this->logger->info('$arEventParameters = ', $arEventParameters);

                CBPRuntime::SendExternalEvent(
                    $arSignData['UF_WORKFLOW_ID'],
                    $arSignData['UF_ACTIVITY_NAME'],
                    $arEventParameters
                );
            } else {
                $this->logger->info('Файл НЕ подписан, отправляем уведомление пользователю.');
                Loader::includeModule('im');
                Loader::includeModule('socialnetwork');
                if ($taskId > 0) {
                    CIMMessenger::Add([
                        'MESSAGE_TYPE'      => 'S',
                        'TO_USER_ID'        => $userId,
                        'FROM_USER_ID'      => 1,
                        'MESSAGE'           => 'Документ не подписан. Не получена электронная подпись.',
                        'AUTHOR_ID'         => 1,
                        'EMAIL_TEMPLATE'    => 'some',
                        'NOTIFY_TYPE'       => IM_NOTIFY_CONFIRM,
                        'NOTIFY_MODULE'     => 'bizproc',
                        'NOTIFY_TITLE'      => 'Документ не подписан. Не получена электронная подпись.',
                        'NOTIFY_BUTTONS' => [
                            [
                                'TITLE' => 'Приступить',
                                'VALUE' => 'Y',
                                'TYPE' => 'accept',
                                'URL' => '/company/personal/bizproc/' . $taskId . '/'
                            ]
                        ]
                    ]);
                }
            }
        }
    }

    /**
     * Найти запись в HL по ISN Дело
     *
     * @param string $isn
     *
     * @return int
     */
    public function getByISN(string $isn = null): int
    {
        if (empty($isn)) {
            return 0;
        }
        Loader::includeModule('sprint.migration');
        $helper = new HlblockHelper();
        $res = $this->entityDataClass::getList([
            'select'    => ['ID'],
            'filter'    => ['=UF_ISN' => $isn],
            'order'     => ['ID' => 'DESC'],
            'limit'     => 1
        ]);
        if ($row = $res->Fetch()) {
            return $row['ID'];
        }

        return 0;
    }
}
