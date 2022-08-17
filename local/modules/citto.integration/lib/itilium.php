<?php

namespace Citto\Integration;

use SoapVar;
use Exception;
use SoapClient;
use Monolog\Logger;
use SimpleXMLElement;
use ArgumentException;
use Bitrix\Main\Loader;
use Psr\Log\LoggerInterface;
use Bitrix\Main\Config\Option;
use Monolog\Handler\RotatingFileHandler;

require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

class Itilium
{
    /**
     * Сколько дней хранить логи
     *
     * @var int
     */
    private static $maxFiles = 90;

    /**
     * @var SoapClient
     */
    public $instance = null;

    /**
     * @var string Адрес WEB-сервиса
     */
    private $serviceUrl = 'https://s-itilium-web01.tularegion.local:50443/itilium/ws/api.1cws?wsdl';

    /**
     * @var string Пользователь для подключения к WEB-сервису
     */
    private $serviceLogin = 'Corportal';

    /**
     * @var string Пароль для подключения к WEB-сервису
     */
    private $servicePassword = 'gVbklU4xzQ';

    /**
     * @var string Идентификатор участника при вызовах WEB-сервиса
     */
    private $serviceParticipant = '880714c5-989c-11ec-8125-005056b3241b';

    /**
     * @var LoggerInterface
     */
    public $logger = null;

    /**
     * @var string На какой версии API Itilium основан код
     */
    public $APIVersion = '1.0.6';

    public $arMapAPI = [
        'AvailableOperations' => [
            'CLASS'     => 'Citto\Integration\Itilium',
            'METHOD'    => 'getAvailableOperations',
            'DESC'      => 'Предназначена для получения доступных для использования участником взаимодействия целевых операций',
        ],
        'GetTechnicalServices' => [
            'CLASS'     => 'Citto\Integration\Itilium',
            'METHOD'    => 'getTechnicalServices',
            'DESC'      => 'Предназначена для получения технических услуг достыпных участнику взаимодействия. Технические услуги используются в задачах (нарядах).',
        ],
        'GetPriorities' => [
            'CLASS'     => 'Citto\Integration\Itilium',
            'METHOD'    => 'getPriorities',
            'DESC'      => 'Предназначена для получения используемых в системе приоритетов задач (нарядов) и инцидентов (обращений).',
        ],
        'GetAPIVersion' => [
            'CLASS'     => 'Citto\Integration\Itilium',
            'METHOD'    => 'getAPIVersion',
            'DESC'      => 'Предназначена для получения текущей версии API.',
        ],
        'GetBusinessServices' => [
            'CLASS'     => 'Citto\Integration\Itilium',
            'METHOD'    => 'getBusinessServices',
            'DESC'      => 'Предназначена для получения бизнес-услуг достыпных участнику взаимодействия. Бизнес-услуги используются в инцидентах (обращениях).',
        ],

        'GetTaskStatuses' => [
            'CLASS'     => 'Citto\Integration\Itilium\Task',
            'METHOD'    => 'getStatuses',
            'DESC'      => 'Предназначена для получения списка состояний (статусов) задач (нарядов) доступных участнику взаимодействия.',
        ],
        'CreateTask' => [
            'CLASS'     => 'Citto\Integration\Itilium\Task',
            'METHOD'    => 'add',
            'DESC'      => 'Предназначена для создания в системе новой задачи.',
        ],
        'UpdateTask' => [
            'CLASS'     => 'Citto\Integration\Itilium\Task',
            'METHOD'    => '',
            'DESC'      => 'Предназначена для обновления данных задачи (наряда) по указанному идентификатору этой задачи.',
        ],
        'DeleteTask' => [
            'CLASS'     => 'Citto\Integration\Itilium\Task',
            'METHOD'    => '',
            'DESC'      => 'Предназначена для удаления задачи (наряда) из системы по уникальному идентификатору этой задачи.',
        ],
        'GetTasks' => [
            'CLASS'     => 'Citto\Integration\Itilium\Task',
            'METHOD'    => 'getList',
            'DESC'      => 'Предназначена для получения задач (нарядов) доступных участнику взаимодействия.',
        ],
        'GetTaskStatus' => [
            'CLASS'     => 'Citto\Integration\Itilium\Task',
            'METHOD'    => 'getStatus',
            'DESC'      => 'Предназначена для получения текущего статуса задачи (наряда) по уникальному идентификатору этого объекта.',
        ],
        'GetTaskStatusHistory' => [
            'CLASS'     => 'Citto\Integration\Itilium\Task',
            'METHOD'    => 'getStatusHistory',
            'DESC'      => 'Предназначена для получения истории изменения состояний (статусов) задачи (наряда) по уникальному идентификатору этой задачи (наряда).',
        ],
        'SetTaskStatus' => [
            'CLASS'     => 'Citto\Integration\Itilium\Task',
            'METHOD'    => '',
            'DESC'      => 'Предназначена для изменения состояния (статуса) указанной задачи (наряда).',
        ],
        'GetTaskClosureCodes' => [
            'CLASS'     => 'Citto\Integration\Itilium\Task',
            'METHOD'    => 'getClosureCodes',
            'DESC'      => 'Получение списка кодов закрытия задач (нарядов).',
        ],

        'AddFile' => [
            'CLASS'     => 'Citto\Integration\Itilium\File',
            'METHOD'    => '',
            'DESC'      => 'Предназначена для добавления к объекту нового файла.',
        ],
        'GetFile' => [
            'CLASS'     => 'Citto\Integration\Itilium\File',
            'METHOD'    => '',
            'DESC'      => 'Предназначена для получения данных файла, присоединенного к объекту.',
        ],
        'DeleteFile' => [
            'CLASS'     => 'Citto\Integration\Itilium\File',
            'METHOD'    => '',
            'DESC'      => 'Предназначена для удаления файла, присоединенного к объекту.',
        ],
        'GetFileList' => [
            'CLASS'     => 'Citto\Integration\Itilium\File',
            'METHOD'    => '',
            'DESC'      => 'Предназначена для получения списка присоединенных к объекту файлов.',
        ],

        'GetCommunication' => [
            'CLASS'     => 'Citto\Integration\Itilium\Message',
            'METHOD'    => '',
            'DESC'      => 'Предназначена для получения общения по объекту.',
        ],
        'AddCommunicationMessage' => [
            'CLASS'     => 'Citto\Integration\Itilium\Message',
            'METHOD'    => '',
            'DESC'      => 'Предназначена для добавления сообщения в общение по объекту.',
        ],

        'GetIncidentStatusHistory' => [
            'CLASS'     => 'Citto\Integration\Itilium\Incident',
            'METHOD'    => 'getStatusHistory',
            'DESC'      => 'Предназначена для получения истории изменения состояний (статусов) инцидента (обращения) по уникальному идентификатору этого инцидента (обращения).',
        ],
        'SetIncidentStatus' => [
            'CLASS'     => 'Citto\Integration\Itilium\Incident',
            'METHOD'    => '',
            'DESC'      => 'Предназначена для изменения состояния (статуса) указанного инцидента (обращения).',
        ],
        'GetIncidentStatus' => [
            'CLASS'     => 'Citto\Integration\Itilium\Incident',
            'METHOD'    => 'getStatus',
            'DESC'      => 'Предназначена для получения текущего статуса инцидента (обращения) по уникальному идентификатору этого инцидента (обращения).',
        ],
        'GetIncidents' => [
            'CLASS'     => 'Citto\Integration\Itilium\Incident',
            'METHOD'    => 'getList',
            'DESC'      => 'Предназначена для получения инцидентов (обращений).',
        ],
        'CreateIncident' => [
            'CLASS'     => 'Citto\Integration\Itilium\Incident',
            'METHOD'    => 'add',
            'DESC'      => 'Предназначена для создания в системе нового инцидента (обращения).',
        ],
        'UpdateIncident' => [
            'CLASS'     => 'Citto\Integration\Itilium\Incident',
            'METHOD'    => '',
            'DESC'      => 'Предназначена для обновления данных инцидента (обращения) по указанному идентификатору этого инцидента (обращения).',
        ],
        'DeleteIncident' => [
            'CLASS'     => 'Citto\Integration\Itilium\Incident',
            'METHOD'    => '',
            'DESC'      => 'Предназначена для удаления инцидента (обращения) из системы по уникальному идентификатору этого инцидента (обращения).',
        ],
        'GetIncidentStatuses' => [
            'CLASS'     => 'Citto\Integration\Itilium\Incident',
            'METHOD'    => 'getStatuses',
            'DESC'      => 'Предназначена для получения списка состояний (статусов) инцидентов (обращений) доступных участнику взаимодействия.',
        ],

        'GetUsers' => [
            'CLASS'     => 'Citto\Integration\Itilium\User',
            'METHOD'    => 'getList',
            'DESC'      => 'Предназначена для получения списка пользователей.',
        ],
        'GetInitiators' => [
            'CLASS'     => 'Citto\Integration\Itilium\User',
            'METHOD'    => 'getInitiators',
            'DESC'      => 'Предназначена для получения списка потребителей услуг.',
        ],
        'GetEmployees' => [
            'CLASS'     => 'Citto\Integration\Itilium\User',
            'METHOD'    => 'getEmployees',
            'DESC'      => 'Предназначена для получения списка сотрудников.',
        ],
        'GetIndividuals' => [
            'CLASS'     => 'Citto\Integration\Itilium\User',
            'METHOD'    => 'getIndividuals',
            'DESC'      => 'Получение списка физических лиц.',
        ],

        'UpdateChange' => [
            'CLASS'     => 'Citto\Integration\Itilium\Project',
            'METHOD'    => 'updateChange',
            'DESC'      => 'Обновление реквизитов проекта',
        ],
        'SetChangeStatus' => [
            'CLASS'     => 'Citto\Integration\Itilium\Project',
            'METHOD'    => 'setChangeStatus',
            'DESC'      => 'Установка статуса проекта',
        ],
        'GetChangeStatuses' => [
            'CLASS'     => 'Citto\Integration\Itilium\Project',
            'METHOD'    => 'getChangeStatuses',
            'DESC'      => 'Получение списка статусов проекта',
        ]
    ];

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
                    'login'             => $this->serviceLogin,
                    'password'          => $this->servicePassword,
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

                // $moduleOptions = unserialize(Option::get('citto.integration', 'values'));
                // if (!empty($moduleOptions['wsdl_itilium'])) {
                //     $this->serviceUrl = $moduleOptions['wsdl_itilium'];
                // }

                $this->instance = new SoapClient($this->serviceUrl, $arOptions);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        } catch (ArgumentException $e) {
            throw new Exception($e->getMessage(), 2);
        }
    }

    /**
     * Обёртка для вызова API
     *
     * @param string $operation Метод API.
     * @param array  $data      Массив параметров.
     * @return array
     */
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

    /**
     * Рекурсивно конвертирует объект в массив.
     *
     * @param object|array $object Объект, из которого нужно получить массив.
     *
     * @return array
     */
    public function objectToArray($object)
    {
        if (is_object($object)) {
            return array_map(array($this, 'objectToArray'), get_object_vars($object));
        } elseif (is_array($object)) {
            return array_map(array($this, 'objectToArray'), $object);
        } else {
            return $object;
        }
    }

    /**
     * Преобразование массива в XML для отправки сырых данных в SOAP.
     *
     * @param array $array           Массив, из которого собрать XML.
     * @param SimpleXMLElement &$xml Объект, в который вставить данные из массива.
     *
     * @return void
     */
    public function arrayToXml($array, &$xml)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $key = explode(':', $key)[0];
                    $subnode = $xml->addChild("ns1:$key", null, 'ns1');
                    $this->arrayToXml($value, $subnode);
                } else {
                    $subnode = $xml->addChild("item$key", null, 'ns1');
                    $this->arrayToXml($value, $subnode);
                }
            } else {
                if (!is_numeric($key)) {
                    $key = explode(':', $key)[0];
                    $xml->addChild("ns1:$key", $value, 'ns1');
                } else {
                    $xml->addChild("item$key", $value, 'ns1');
                }
            }
        }
    }

    /**
     * Нормализовать ответ SOAP.
     * Если в массиве содержится 1 элемент, то структура ответа другая.
     *
     * @param array $array Массив ответа SOAP.
     *
     * @return array
     */
    public function normalizeArray(array $array = [])
    {
        if (!empty($array) && !isset($array[0])) {
            $array = [$array];
        }

        return $array;
    }

    /**
     * Получить версию API сервера.
     *
     * @return string
     */
    public function getAPIVersion()
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        try {
            $result = $this->call('GetAPIVersion');

            return $result['APIVersion'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * Получить доступные операции.
     *
     * @return array
     */
    public function getAvailableOperations()
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        try {
            $result = $this->call('AvailableOperations');

            return $result['AvailableOperations']['OperationDescription'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * Возвращает список бизнес услуг, и их составляющих,
     * доступных участнику взаимодействия.
     *
     * @return array
     */
    public function getBusinessServices()
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        try {
            $result = $this->call('GetBusinessServices');

            return $result['Services']['Service'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * Получение используемых в системе приоритетов задач (нарядов) и инцидентов (обращений).
     *
     * @return array
     */
    public function getPriorities()
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        try {
            $result = $this->call('GetPriorities');

            return $result['Priorities']['Priority'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * Возвращает список актуальных технических услуг, и их составляющих,
     * доступных участнику взаимодействия.
     *
     * @return array
     */
    public function getTechnicalServices()
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        try {
            $result = $this->call('GetTechnicalServices');

            return $result['Services']['Service'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * Различие версий API.
     *
     * @param string $old ID старой версии.
     * @param string $new ID новой версии.
     *
     * @return array
     */
    public function diffApiVersions($old = '1.0.5', $new = '1.0.6')
    {
        $oldFile = __DIR__ . '/itilium/api_methods/' . $old . '.json';
        $newFile = __DIR__ . '/itilium/api_methods/' . $new . '.json';

        if (!file_exists($oldFile) || !file_exists($newFile)) {
            throw new Exception('Unknown version');
        }

        $arOld = json_decode(file_get_contents($oldFile), true);
        $arNew = json_decode(file_get_contents($newFile), true);
        $arOldOperations = [];
        foreach ($arOld as $val) {
            $arOldOperations[ $val['Operation'] ] = $val['Description'];
        }
        $arNewOperations = [];
        foreach ($arNew as $val) {
            $arNewOperations[ $val['Operation'] ] = $val['Description'];
        }

        $arDiff = [
            'ADDED'     => [],
            'DELETED'   => [],
            'MODIFIED'  => [],
        ];
        foreach ($arOldOperations as $operation => $description) {
            if (!isset($arNewOperations[ $operation ])) {
                $arDiff['DELETED'][ $operation ] = $description;
            } elseif ($description != $arNewOperations[ $operation ]) {
                $arDiff['MODIFIED'][ $operation ] = [
                    $old => $description,
                    $new => $arNewOperations[ $operation ],
                ];
            }
        }
        foreach ($arNewOperations as $operation => $description) {
            if (!isset($arOldOperations[ $operation ])) {
                $arDiff['ADDED'][ $operation ] = $description;
            }
        }
        
        return $arDiff;
    }

    public function getList(
        string $method = '',
        array $arFilter = []
    ) {
        if (!empty($arFilter) && !isset($arFilter['Filter']['FilterElement'])) {
            $arFilter = $this->getListFilter($arFilter);
        }

        try {
            return $this->call($method, $arFilter);
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('Ошибка: ' . $e->getMessage(), $arFilter);
            }
            throw new Exception($e->getMessage(), 1);
        }
    }

    public function getListFilter(array $arFilter = [])
    {
        $arFilterCondition = [];
        $logic = $arFilter['LOGIC'] ?? 'AND';
        foreach ($arFilter as $field => $value) {
            if ($field == 'LOGIC') {
                continue;
            }
            if (is_array($value) && count($value) == 1) {
                $value = current($value);
            }
            $arFilterCondition[] = $this->getFilterQuery($field, $value);
        }

        $arConditions = [];
        if (count($arFilterCondition) > 1) {
            foreach ($arFilterCondition as $value) {
                $arConditions['FilterElement:' . mt_rand(0, 10000) ] = [
                    'Condition' => $value,
                ];
            }
        } else {
            $arConditions = [
                'FilterElement' => [
                    'Condition' => $arFilterCondition[0],
                ],
            ];
        }

        

        $arResult = [
            'PLAIN_XML' => [
                'Condition'
            ],
            'Filter' => [
                'FilterElement' => [
                    'Group' => array_merge(
                        [
                            'Type' => $logic,
                        ],
                        $arConditions
                    ),
                ],
            ],
        ];
  
        return $arResult;
    }

    public function getFilterQuery($field = '', $value)
    {
        $comparsion = 'Equal';
        if (is_array($value)) {
            $comparsion = 'InList';
            if (mb_substr($field, 0, 1) == '!') {
                $field = mb_substr($field, 1);
                $comparsion = 'NotInList';
            }
            return [
                'Field'             => $field,
                'ComparisonType'    => $comparsion,
                'Value'             => implode(';', $value),
            ];
        } elseif (mb_substr($field, 0, 2) == '>=') {
            $field = mb_substr($field, 2);
            $comparsion = 'GreaterOrEqual';
        } elseif (mb_substr($field, 0, 2) == '<=') {
            $field = mb_substr($field, 2);
            $comparsion = 'LessOrEqual';
        } if (mb_substr($field, 0, 1) == '>') {
            $field = mb_substr($field, 1);
            $comparsion = 'Greater';
        } elseif (mb_substr($field, 0, 1) == '<') {
            $field = mb_substr($field, 1);
            $comparsion = 'Less';
        } elseif (mb_substr($field, 0, 1) == '!') {
            $field = mb_substr($field, 1);
            $comparsion = 'NotEqual';
        } elseif (mb_substr($field, 0, 1) == '%') {
            $field = mb_substr($field, 1);
            $comparsion = 'Contains';
        }

        $arResult = [
            'Field'             => $field,
            'ComparisonType'    => $comparsion,
            'Value'             => $value,
        ];

        return $arResult;
    }
}
