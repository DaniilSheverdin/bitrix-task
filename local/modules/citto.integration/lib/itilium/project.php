<?php

namespace Citto\Integration\Itilium;

use Citto\Integration\Itilium;
use Exception;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use SoapClient;


class Project extends Itilium
{
    private $serviceParticipant = '880714c5-989c-11ec-8125-005056b3241b';
    private $serviceUrl = 'https://s-itilium-web01.tularegion.local:50443/itilium_test/ws/api.1cws?wsdl';

    public function __construct(LoggerInterface $logger = null)
    {
        if ($logger) {
            $this->logger = $logger;
        } else {
            $this->logger = new Logger('default');
            $this->logger->pushHandler(
                new RotatingFileHandler(
                    $_SERVER['DOCUMENT_ROOT'] . '/local/logs/itilium/main.log',
                    $this->maxFiles
                )
            );
        }

        try {
            if (false !== mb_strpos($_SERVER['SERVER_NAME'], 'localhost')) {
                $this->instance = new Itilium\Mock($this->serviceUrl, []);
            } else {
                $arOptions = [
                    'login'             => 'Corportal',
                    'password'          => '0z6eE@kye5n2',
                    'exceptions'        => true,
                    'trace'             => true,
                    'cache_wsdl'        => WSDL_CACHE_MEMORY,
                    'wsdl_cache_ttl'    => 86400,
                    'stream_context'    => stream_context_create([
                        'ssl' => [
                            'verify_peer'       => false,
                            'verify_peer_name'  => false,
                            'allow_self_signed' => true
                        ]
                    ]),
                ];

                $this->instance = new SoapClient($this->serviceUrl, $arOptions);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        } catch (ArgumentException $e) {
            throw new Exception($e->getMessage(), 2);
        }
    }

    public function updateChange(string $objectUid, string $sReason, string $sDeadline, string $sResponsible)
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        try {
            return $result = $this->call('UpdateChange', ['ObjectUID' => $objectUid, 'Change' => [
                'Reason'      => $sReason,
                'Deadline'    => $sDeadline,
                'Responsible' => ['UID' => $sResponsible]
            ]]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    public function setChangeStatus()
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }
        /*
         * @todo setChangeStatus
         *
         */
        throw new Exception(__METHOD__ . ' is not implemented', 500);
    }

    public function getChangeStatuses()
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        try {
            return $result = $this->call('GetChangeStatuses');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    public function call(
        string $operation = '',
        array $data = []
    ) {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }
        if (empty($operation)) {
            throw new Exception('Empty operation', 0);
        }

        try {
            $plainXml = $data['PLAIN_XML'] ?? false;
            unset($data['PLAIN_XML']);
            $arRequest = [
                'Participant'   => $this->serviceParticipant,
                'Operation'     => $operation,
                'Data'          => $data,
            ];

            /*
             * Если нужно передать сырой XML.
             */
            if ($plainXml && $this->instance instanceof SoapClient) {
                $obXml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><ns1:Request/>', null, false);
                $this->arrayToXml($arRequest, $obXml);
                $arReplaceFrom = [
                    '<?xml version="1.0" encoding="UTF-8"?>',
                    ' xmlns:ns1="ns1"',
                ];
                $arReplaceTo = [
                    '',
                    '',
                ];
                foreach ($plainXml as $val) {
                    $arReplaceFrom[] = '<ns1:' . $val . '><ns1:' . $val . '>';
                    $arReplaceTo[] = '</ns1:' . $val . '></ns1:' . $val . '>';
                }
                $strXml = str_replace($arReplaceFrom, $arReplaceTo, $obXml->asXML());
                $strSoapRequest = new SoapVar($strXml, \XSD_ANYXML);
                $result = $this->instance->__SoapCall('Request', [$strSoapRequest]);
            } else {
                $result = $this->instance->Request($arRequest);
            }
            $result = $this->objectToArray($result);

            if ($result['return']['Result']) {
                return $result['return']['Data'];
            } else {
                if ($this->logger) {
                    $this->logger->error('Ошибка: ' . $result['return']['Description'], $result);
                }
                throw new Exception($result['return']['Description'], 500);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        } catch (ArgumentException $e) {
            throw new Exception($e->getMessage(), 2);
        }
    }
}
