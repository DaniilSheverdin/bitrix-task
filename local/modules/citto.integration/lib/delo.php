<?php

namespace Citto\Integration;

use CFile;
use Exception;
use SoapClient;
use CIBlockElement;
use Monolog\Logger;
use CBitrixComponent;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use CIBlockPropertyEnum;
use Psr\Log\LoggerInterface;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ArgumentException;
use Citto\Integration\Delo\BpSign;
use Monolog\Handler\RotatingFileHandler;
use Citto\ControlOrders\Protocol\Component as Protocols;

require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

class Delo
{
    public $serviceUrl = 'http://172.21.254.53/ResolutionControlService/ResolutionControlService.dll/wsdl/IResolutionControlService';

    /**
     * Поле для синхронизации с Дело
     *
     * @var int
     */
    protected $deloTerm = 1;

    private $logger;

    /**
     * Поле для синхронизации с Дело
     * Группа документов
     *
     * @var string
     */
    // protected $deloDocGroup = '0.YS83.YS85.2YH4A.';
    protected $deloDocGroup = '0.YS83.YS85.3HI91.';

    public const DELO_STATUS_CREATED = 1;
    public const DELO_STATUS_TOVISA = 2;
    public const DELO_STATUS_VISED = 3;
    public const DELO_STATUS_TOSIGN = 4;
    public const DELO_STATUS_SIGNED = 5;
    public const DELO_STATUS_NOTSIGNED = 6;
    public const DELO_STATUS_ONREG_WOUTDEL = 7;
    public const DELO_STATUS_ONREG_WDEL = 8;
    public const DELO_STATUS_REGISTER = 9;

    /**
     * Конструктор
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function __construct(LoggerInterface $logger = null)
    {
        if ($logger) {
            $this->logger = $logger;
        } else {
            $this->logger = new Logger('default');
            $this->logger->pushHandler(
                new RotatingFileHandler(
                    $_SERVER['DOCUMENT_ROOT'] . '/local/logs/delo/main.log',
                    30
                )
            );
        }

        $moduleOptions = unserialize(Option::get('citto.integration', 'values'));
        if (!empty($moduleOptions['wsdl_path_delo'])) {
            $this->serviceUrl = $moduleOptions['wsdl_path_delo'];
        }
    }

    /**
     * Список статусов из битрикса
     *
     * @return array
     */
    public function getBitrixStatusList(): array
    {
        Loader::includeModule('iblock');
        CBitrixComponent::includeComponentClass('citto:checkorders.protocol');
        $obProtocol = new Protocols();
        $arResult = [];
        $res = CIBlockPropertyEnum::GetList(
            ['DEF' => 'DESC', 'SORT' => 'ASC'],
            ['IBLOCK_ID' => $obProtocol->protocolsIblockId, 'CODE' => 'DELO_STATUS']
        );
        while ($row = $res->GetNext()) {
            $arResult[ $row['XML_ID'] ] = $row;
        }

        return $arResult;
    }

    /**
     * Отправить протокол в Дело
     *
     * @param int $iProtocolId ID протокола
     *
     * @return array
     */
    public function addProject(int $iProtocolId = 0): array
    {
        if ($iProtocolId <= 0) {
            throw new Exception('Empty protocolId');
        }

        $this->logger->info(__METHOD__);
        $this->logger->info('$iProtocolId: ' . $iProtocolId);

        CBitrixComponent::includeComponentClass('citto:checkorders.protocol');
        $obProtocol = new Protocols();

        $arData = $obProtocol->getDetailData($iProtocolId);

        $arDeloUsers = (new Delo\Users())->getList();

        // Key => bMulti
        $arUserKeys = [
            'EXECUTOR' => false,
            'SIGNER' => true, // Если в будущем подписантов будет несколько
            'VISAS' => true
        ];
        $arUserData = [];
        $arSendUsers = [];
        foreach ($arUserKeys as $key => $bMulti) {
            $arUserData[ $key ] = [];
            $arUsers = $arData['PROPERTY_' . $key . '_VALUE'];
            if (!is_array($arUsers)) {
                $arUsers = [
                    $arUsers
                ];
            }

            foreach ($arUsers as $userId) {
                // если подписант есть среди визирующих, то не отправлять ему на визу
                if ($key == 'VISAS' && $userId == $arData['PROPERTY_SIGNER_VALUE']) {
                    continue;
                }

                $arUser = [
                    'DUE' => $arDeloUsers[ $userId ]['UF_DUE'],
                    'ISN' => $arDeloUsers[ $userId ]['UF_ISN'],
                ];
                if ($bMulti) {
                    $arUserData[ $key ][] = $arUser;
                    $arSendUsers[ $key ][] = $userId;
                } else {
                    $arUserData[ $key ] = $arUser;
                    $arSendUsers[ $key ] = $userId;
                }
            }
        }

        $arRequest = [
            'TERM' => $this->deloTerm,
            'DUE_DOCGROUP' => $this->deloDocGroup,
            'EXECUTOR' => $arUserData['EXECUTOR'],
            'VISAS' => $arUserData['VISAS'],
            'SIGNERS' => $arUserData['SIGNER'],
            'FILES' => []
        ];

        // При повторной отправке, передать ISN существующего проекта
        if (!empty($arData['PROPERTY_DELO_ISN_VALUE'])) {
            $arRequest['ISN'] = $arData['PROPERTY_DELO_ISN_VALUE'];
        }

        $documentPath = $obProtocol->generateDocument($iProtocolId);
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $documentPath;
        $fileContent = file_get_contents($filePath);

        $arRequest['FILES'][] = [
            'FILENAME' => 'Перечень поручений.docx',
            'ANNOTAT' => base64_encode($fileContent)
        ];

        $arReturn = [
            'result' => false,
            'error' => false,
        ];

        try {
            $obClient = new SoapClient($this->serviceUrl);
            $sData = $obClient->addPRJ(Json::encode($arRequest));
            $sData = str_replace(["\r\n", "\r", "\n"], '', $sData);
            $sData = str_replace("\t", " ", $sData);
            $arSyncData = Json::decode($sData);
            if ($arSyncData['result'] === 'success') {
                $arReturn['result'] = [
                    'ISN' => $arSyncData['ISN'],
                    'NUMBER' => $arSyncData['FREE_NUM'],
                    'DATE' => $arSyncData['PRJ_DATE'],
                    'VERSION' => $arSyncData['PRJ_VERSION'],
                ];

                $arDescription = [
                    'ISN' => $arSyncData['ISN'],
                    'DELO_NUMBER' => $arSyncData['FREE_NUM'],
                    'DELO_DATE' => $arSyncData['PRJ_DATE'],
                    'VERSION' => $arSyncData['PRJ_VERSION'],
                    'DATE' => time(),
                    'EXECUTOR' => $arSendUsers['EXECUTOR'],
                    'VISAS' => $arSendUsers['VISAS'],
                    'SIGNER' => $arSendUsers['SIGNER'][0]
                ];

                CIBlockElement::SetPropertyValueCode(
                    $iProtocolId,
                    'FILES',
                    [
                        0 => [
                            'VALUE' => CFile::MakeFileArray($filePath),
                            'DESCRIPTION' => Json::encode($arDescription)
                        ]
                    ]
                );
            } else {
                throw new Exception($arSyncData['Message']);
            }
        } catch (Exception | ArgumentException $e) {
            $arReturn['error'] = $e->getMessage();
        }
        return $arReturn;
    }

    /**
     * Отправить БП на подпись в Дело
     *
     * @param int $iSignId
     *
     * @return array
     */
    public function addProjectFromBP(int $iSignId = 0, string $sSigners = 'visas'): array
    {
        if ($iSignId <= 0) {
            throw new Exception('Empty SignId');
        }

        $this->logger->info(__METHOD__);
        $this->logger->info('$iSignId: ' . $iSignId);

        $obBpSign = new BpSign();
        $rsData = $obBpSign->entityDataClass::getList(
            [
                'filter' => [
                    'ID' => $iSignId
                ]
            ]
        );
        $arSignData = [];
        while ($arData = $rsData->Fetch()) {
            $arData['UF_USERS'] = Json::decode($arData['UF_USERS']);
            $arData['UF_FILES'] = Json::decode($arData['UF_FILES']);
            $arSignData = $arData;
        }

        $this->logger->debug('Информация об активити', $arSignData);

        $arDeloUsers = (new Delo\Users())->getList();

        $arExecutor = [];
        $arSigner = [];
        foreach ($arDeloUsers as $arUser) {
            if (empty($arUser['UF_USER_ESTIMATE'])) {
                continue;
            }
            if ($arUser['UF_USER_ESTIMATE'] == $arSignData['UF_AUTHOR']) {
                $arExecutor = [
                    'DUE' => $arUser['UF_DUE'],
                    'ISN' => $arUser['UF_ISN'],
                ];
            }
            if (in_array($arUser['UF_USER_ESTIMATE'], $arSignData['UF_USERS'])) {
                $arSigner = [
                    'DUE' => $arUser['UF_DUE'],
                    'ISN' => $arUser['UF_ISN'],
                ];
            }
        }

        if (empty($arExecutor)) {
            $this->logger->error('Не найден пользователь UF_AUTHOR в Дело');

            $obBpSign->entityDataClass::update(
                $iSignId,
                [
                    'UF_ERRORS' => 'Не найден пользователь ' . $arSignData['UF_AUTHOR']
                ]
            );
            return [
                'result'    => '',
                'error'     => false,
            ];
        }

        if (empty($arSigner)) {
            $this->logger->error('Не найден пользователь UF_USERS в Дело');

            $obBpSign->entityDataClass::update(
                $iSignId,
                [
                    'UF_ERRORS' => 'Не найден пользователь ' . $arSignData['UF_USERS']
                ]
            );
            return [
                'result'    => '',
                'error'     => false,
            ];
        }

        $arRequest = [
            'TERM'          => $this->deloTerm,
            'DUE_DOCGROUP'  => $this->deloDocGroup,
            'EXECUTOR'      => $arExecutor,
            'FILES'         => [],
            // 'VISAS'         => [],
            // 'SIGNERS'       => [$arSigner],
        ];

        if ($sSigners == 'adresses') {
            $arRequest['ADRESSEES'] = [$arSigner];
            unset($arRequest['TERM']);
            unset($arRequest['EXECUTOR']);
        } elseif ($sSigners == 'signers') {
            $arRequest['SIGNERS'] = [$arSigner];
            $arRequest['VISAS'] = [];
        } else {
            $arRequest['VISAS'] = [$arSigner];
            $arRequest['SIGNERS'] = [];
        }

        if (!empty($arSignData['UF_ISN'])) {
            $arRequestp['ISN'] = $arSignData['UF_ISN'];
        }

        $this->logger->debug('Запрос в Дело', ['TYPE' => $sSigners, 'REQUEST' => $arRequest]);

        foreach ($arSignData['UF_FILES'] as $fileId) {
            $arFile = CFile::GetFileArray($fileId);
            if ($arFile['FILE_SIZE'] > 0) {
                $fileExt = pathinfo($arFile['ORIGINAL_NAME'], PATHINFO_EXTENSION);
                $fileName = $arFile['ORIGINAL_NAME'];
                $fileName = mb_substr($fileName, 0, -(mb_strlen($fileExt)+1));
                $fileName = $fileName . ' (n' . $arFile['ID'] . ').' . $fileExt;
                $arRequest['FILES'][] = [
                    'FILENAME' => $fileName,
                    'ANNOTAT' => base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . $arFile['SRC']))
                ];
            }
        }

        $arReturn = [
            'result'    => false,
            'error'     => false,
        ];

        try {
            $obClient   = new SoapClient($this->serviceUrl);
            $sData      = $obClient->addPRJ(Json::encode($arRequest));
            $sData      = str_replace(["\r\n", "\r", "\n"], '', $sData);
            $sData      = str_replace("\t", " ", $sData);
            $arSyncData = Json::decode($sData);
            $this->logger->debug('Ответ из Дело', $arSyncData);
            if ($arSyncData['result'] === 'success') {
                $arReturn['result'] = $arSyncData['ISN'];
            } else {
                throw new Exception($arSyncData['Message']);
            }
        } catch (Exception | ArgumentException $e) {
            $this->logger->error('Exception: ' . $e->getMessage());
            $arReturn['error'] = $e->getMessage();

            $obBpSign->entityDataClass::update(
                $iSignId,
                [
                    'UF_ERRORS' => $arReturn['error']
                ]
            );
        }

        $this->logger->debug('$arReturn', $arReturn);

        return $arReturn;
    }

    /**
     * Удалить проект в Дело
     *
     * @param int $iSignId
     *
     * @return array
     */
    public function delProject(int $iSignId = 0): array
    {
        if ($iSignId <= 0) {
            throw new Exception('Empty SignId');
        }

        $this->logger->info(__METHOD__);
        $this->logger->info('$iSignId: ' . $iSignId);

        $obBpSign = new BpSign();
        $rsData = $obBpSign->entityDataClass::getList(
            [
                'filter' => [
                    'ID' => $iSignId
                ]
            ]
        );
        $arSignData = [];
        while ($arData = $rsData->Fetch()) {
            $arData['UF_USERS'] = Json::decode($arData['UF_USERS']);
            $arData['UF_FILES'] = Json::decode($arData['UF_FILES']);
            $arSignData = $arData;
        }

        $this->logger->info('$arSignData = ', $arSignData);

        $arDeloUsers = (new Delo\Users())->getList();

        $arExecutor = [];
        foreach ($arDeloUsers as $arUser) {
            if ($arUser['UF_USER_ESTIMATE'] == $arSignData['UF_AUTHOR']) {
                $arExecutor = [
                    'DUE' => $arUser['UF_DUE'],
                    'ISN' => $arUser['UF_ISN'],
                ];
            }
        }

        $arRequest = [
            'EXECUTOR'  => $arExecutor,
            'ISN'       => $arSignData['UF_ISN']
        ];

        $arReturn = [
            'result'    => false,
            'error'     => false,
        ];

        $this->logger->info('$arRequest = ', $arRequest);

        try {
            $obClient   = new SoapClient($this->serviceUrl);
            $sData      = $obClient->delPRJ(Json::encode($arRequest));
            $sData      = str_replace(["\r\n", "\r", "\n"], '', $sData);
            $sData      = str_replace("\t", " ", $sData);
            $arSyncData = Json::decode($sData);
            $this->logger->info('$arSyncData = ', $arSyncData);

            if ($arSyncData['result'] === 'success') {
                $arReturn['result'] = $arSyncData['ISN'];
            } else {
                throw new Exception($arSyncData['Message']);
            }
        } catch (Exception | ArgumentException $e) {
            $arReturn['error'] = $e->getMessage();
        }
        return $arReturn;
    }

    /**
     * Получить протокол из Дело
     *
     * @param string $id ID протокола
     * @param string $date ID протокола
     * @param string $isn ISN из Дело
     *
     * @return array
     */
    public function getData(string $id = null, string $date = '', string $isn = ''): array
    {
        if (empty($id)) {
            throw new Exception('Empty id');
        }
        if (empty($date)) {
            throw new Exception('Empty date');
        }

        $this->logger->info(__METHOD__);
        $this->logger->info('$id: ' . $id);
        $this->logger->info('$date: ' . $date);
        $this->logger->info('$isn: ' . $isn);

        $arRequest = [
            'FREE_NUM' => $id,
            'DOC_DATE' => $date
        ];
        if (!empty($isn)) {
            $arRequest = [
                'ISN_DOC' => $isn
            ];
        }

        $arReturn = [
            'result'    => false,
            'error'     => false,
        ];

        try {
            ini_set('default_socket_timeout', '300');
            $obClient = new SoapClient(
                $this->serviceUrl,
                [
                    'connection_timeout' => 600
                ]
            );
            $sData = $obClient->get_data(Json::encode($arRequest));
            $sData = str_replace(["\r\n", "\r", "\n"], '', $sData);
            $sData = str_replace("\t", " ", $sData);
            $arSyncData = Json::decode($sData);
            if ($arSyncData['result'] === 'success') {
                $docPath = '/upload/checkorders.import/';
                $strName = md5(serialize($arRequest));
                $strPath = $_SERVER['DOCUMENT_ROOT'] . $docPath;
                if (!mkdir($strPath, 0775, true) && !is_dir($strPath)) {
                    throw new Exception('Directory "' . $docPath . '" was not created');
                }
                file_put_contents($strPath . $strName . '.json', $sData);

                $arReturn['result'] = $strName;
            } else {
                if (isset($arSyncData['rcs'])) {
                    $arReturn['result'] = $arSyncData['rcs'];
                }
                throw new Exception($arSyncData['Message']);
            }
        } catch (Exception | ArgumentException $e) {
            $arReturn['error'] = $e->getMessage();
        }
        return $arReturn;
    }

    /**
     * Получить статус из Дело
     *
     * @param string $isn ISN из Дело
     *
     * @return array
     */
    public function getStage(string $isn = ''): array
    {
        if (empty($isn)) {
            throw new Exception('Empty isn');
        }

        $this->logger->info(__METHOD__);
        $this->logger->info('$isn: ' . $isn);

        $arRequest = [
            'ISN' => $isn
        ];

        $arReturn = [
            'result'    => false,
            'error'     => false,
        ];

        try {
            ini_set('default_socket_timeout', '300');
            $obClient = new SoapClient(
                $this->serviceUrl,
                [
                    'connection_timeout' => 600
                ]
            );
            $sData = $obClient->get_prj_stage(Json::encode($arRequest));
            $sData = str_replace(["\r\n", "\r", "\n"], '', $sData);
            $sData = str_replace("\t", " ", $sData);
            $arSyncData = Json::decode($sData);
            if ($arSyncData['result'] === 'success') {
                $arReturn['result'] = $arSyncData['stage'];
            } else {
                throw new Exception('Проект не найден в АСЭД Дело');
            }
        } catch (Exception | ArgumentException $e) {
            $arReturn['error'] = $e->getMessage();
        }
        return $arReturn;
    }
}
