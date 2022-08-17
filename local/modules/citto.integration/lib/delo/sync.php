<?php

namespace Citto\Integration\Delo;

use Exception;
use CIBlockElement;
use CBitrixComponent;
use Psr\Log\LoggerInterface;
use Bitrix\Main\{Loader, Web\Json};
use Monolog\{Handler\StreamHandler, Logger};
use Sprint\Migration\Helpers\HlblockHelper;
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;
use Citto\ControlOrders\Protocol\Component as Protocols;

require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

class Sync
{
    public $changeLogHLId = 0;

    public $entityDataClass = null;

    private $logger;

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
                new StreamHandler(
                    $_SERVER['DOCUMENT_ROOT'] . '/local/logs/checkorders.protocol/Sync_default.log'
                )
            );
        }

        Loader::includeModule('sprint.migration');
        $helper = new HlblockHelper();
        $this->changeLogHLId = $helper->getHlblockId('DeloSync');

        if ($this->changeLogHLId > 0) {
            Loader::includeModule('highloadblock');
            $hlblock    = HLTable::getById($this->changeLogHLId)->fetch();
            $entity     = HLTable::compileEntity($hlblock);

            $this->entityDataClass = $entity->getDataClass();
        }
    }

    /**
     * Найти протокол по ISN
     *
     * @param string|null $isn
     *
     * @return int
     * @throws \Bitrix\Main\LoaderException
     */
    public function findProtocolIdByISN(string $isn = null): int
    {
        if (empty($isn)) {
            return 0;
        }
        Loader::includeModule('iblock');
        CBitrixComponent::includeComponentClass('citto:checkorders.protocol');
        $obProtocol = new Protocols();
        $arFilter = [
            'IBLOCK_ID'         => $obProtocol->protocolsIblockId,
            'PROPERTY_DELO_ISN' => $isn
        ];
        $res = CIBlockElement::GetList(false, $arFilter, false, false, ['ID']);
        if ($row = $res->GetNext()) {
            return $row['ID'];
        }

        return 0;
    }

    /**
     * Посчитать хэш данных
     *
     * @param array|null $arParams
     *
     * @return string
     */
    protected function calcHash(array $arParams = null): string
    {
        asort($arParams);

        return md5(serialize($arParams));
    }

    /**
     * Получить последний лог от Дело
     *
     * @param int $iProtocolId
     * @param int $isn
     *
     * @return array
     */
    public function getLastChanges(int $iProtocolId = 0, int $isn = 0): array
    {
        $arParams = [
            'filter'    => [
                '=UF_PROTOCOL'  => $iProtocolId,
                '=UF_ISN'       => $isn
            ],
            'order'     => [
                'UF_DATE' => 'DESC'
            ],
            'limit'     => 1,
            'offset'    => 0
        ];
        $rsData = $this->entityDataClass::getList($arParams);
        if ($arData = $rsData->Fetch()) {
            return $arData;
        }

        return [];
    }

    /**
     * Фиксация изменений в логе
     *
     * @param array $arParams
     *
     * @return array
     */
    public function fixChanges(array $arParams = null): array
    {
        if (empty($arParams['ISN'])) {
            $this->logger->notice('Пустой ISN = ' . $arParams['ISN']);
            return [
                'result'    => false,
                'error'     => 'Empty ISN'
            ];
        }

        $iProtocolId = $this->findProtocolIdByISN($arParams['ISN']);

        if ($iProtocolId <= 0) {
            $obBpSign   = new BpSign($this->logger);
            $bpSignId   = $obBpSign->getByISN($arParams['ISN']);
            if ($bpSignId > 0) {
                $this->logger->info('Найдена запись активити подписи с ISN = ' . $arParams['ISN']);
                try {
                    $obBpSign->update($bpSignId, $arParams);
                    return [
                        'result'    => true,
                        'error'     => false
                    ];
                } catch (Exception $exc) {
                    $this->logger->error($exc->getMessage());
                }
            } else {
                $this->logger->notice('Не найден протокол с ISN = ' . $arParams['ISN']);
                return [
                    'result'    => false,
                    'error'     => 'Unknown protocol'
                ];
            }
        }

        CBitrixComponent::includeComponentClass('citto:checkorders.protocol');
        $obProtocol = new Protocols();
        $arProtocol = $obProtocol->getDetailData($iProtocolId);
        $hash = $this->calcHash($arParams);

        $arLastChanges = $this->getLastChanges($iProtocolId, $arParams['ISN']);

        if (!empty($arLastChanges)) {
            $this->logger->notice('Уже были изменения проекта, сверяемся с предыдущими');
            if ($arLastChanges['UF_HASH'] !== $hash) {
                $this->logger->notice('Хэш не совпал, добавляем запись лога');
                // TODO changes
                $arNewFields = [
                    'UF_PROTOCOL'   => $iProtocolId,
                    'UF_ISN'        => $arParams['ISN'],
                    'UF_DATE'       => date('d.m.Y H:i:s'),
                    'UF_HASH'       => $hash,
                    'UF_JSON'       => Json::encode($arParams)
                ];
                $this->entityDataClass::add($arNewFields);
            }
        } else {
            $this->logger->notice('Это первая запись лога по проекту');
            $arNewFields = [
                'UF_PROTOCOL'   => $iProtocolId,
                'UF_ISN'        => $arParams['ISN'],
                'UF_DATE'       => date('d.m.Y H:i:s'),
                'UF_HASH'       => $hash,
                'UF_JSON'       => Json::encode($arParams)
            ];
            $this->entityDataClass::add($arNewFields);
        }

        return [
            'result' => true
        ];
    }
}
