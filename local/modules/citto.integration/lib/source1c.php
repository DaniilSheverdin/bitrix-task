<?

namespace Citto\Integration;

use CPHPCache;
use Exception;
use SoapClient;

class EmptySoap
{
    function __get($name)
    {
        self::errorInfo();
        return [];
    }

    function __set($name, $val)
    {
        self::errorInfo();
    }

    function __call(string $name, array $arguments)
    {
        self::errorInfo();
        return [];
    }

    function __callStatic(string $name, array $arguments)
    {
        self::errorInfo();
        return [];
    }

    static function errorInfo()
    {
        echo 'Ошибка. Попробуйте позже';
    }
}

class Source1C
{
    const USER_TEST_GROUP       = 103;
    const SOURCE_1C_PROD        = 'http://172.21.242.237/hrm/ws/integrationws.1cws?wsdl';
    const SOURCE_1C_TEST        = 'http://172.21.242.246/copy_hrm/ws/integrationws.1cws?wsdl';
    const SOURCE_1C_SPRAVKI_TEST = 'http://172.21.242.237/mfcBGUtest/ws/integration.1cws?WSDL';
    const SOURCE_1C_SPRAVKI     = 'http://172.21.242.237:8880/spravki/ws/integration.1cws?WSDL';
    const SOURCE_1C_PROD__USER  = 'Programmer';
    const SOURCE_1C_PROD__PASS  = 'm54te87';
    const SOURCE_1C_TEST__USER  = 'Programmer';
    const SOURCE_1C_TEST__PASS  = 'm54te87';

    /**
     * Creates soap connection
     *
     * @param string $wsdl wsdl url
     * @return SoapClient
     */
    public function Connect1C($wsdl = null, $options = [])
    {

        if (isset($GLOBALS['USER']) && $GLOBALS['USER'] instanceof CUser && in_array(self::USER_TEST_GROUP, $GLOBALS['USER']->GetUserGroupArray())) {
            if (!isset($options['login'])) {
                $options['login'] = self::SOURCE_1C_TEST__USER;
                $options['password'] = self::SOURCE_1C_TEST__PASS;
            }
            if (empty($wsdl)) {
                $wsdl = self::SOURCE_1C_TEST;
            }
        } else {
            if (!isset($options['login'])) {
                $options['login'] = self::SOURCE_1C_PROD__USER;
                $options['password'] = self::SOURCE_1C_PROD__PASS;
            }
            if (empty($wsdl)) {
                $wsdl = self::SOURCE_1C_PROD;
            }
        }
        if (!isset($options['exceptions'])) {
            $options['exceptions'] = true;
        }
        $options['trace'] = true;
        try {
            $soapCl = new SoapClient($wsdl, $options);
        } catch (Exception $exc) {
            $soapCl = new EmptySoap();
        }
        return $soapCl;
    }

    public function Connect($sWSDL, $sUsername, $sPassword)
    {
        $options = array(
            'login'    => $sUsername,
            'password' => $sPassword,
        );
        try {
            $soapCl = new SoapClient($sWSDL, $options);
        } catch (Exception $exc) {
            $soapCl = new EmptySoap();
        }
        return $soapCl;
    }

    public function buildTree(array $listIdParent)
    {
        foreach ($listIdParent as $id => $node) {
            if ($node['PARENT_ID']) {
                $listIdParent[$node['PARENT_ID']]['sub'][$id] = &$listIdParent[$id];
                unset($node['PARENT_ID']);
            } else {
                $rootId = $id;
                echo $rootId . '<br>';
            }
        }

        return array($rootId => $listIdParent[$rootId]);
    }

    public function Get($rConnect, $sFunction, $arRequestParams = [])
    {
        $params = array(
            'RequestData' => array(
                'Operation' => $sFunction,
            ),
        );
        if (count($arRequestParams) > 0) {
            if ($sFunction == 'PaySlip') {
                $params = $arRequestParams;
            } else if (in_array($sFunction, ['GetIncomeStatements', 'AttachFileToObject'])) {
                $params = array(
                    'Operation' => $sFunction,
                    'Data' => $arRequestParams
                );
            } else {
                $params['RequestData']['Data'] = $arRequestParams;
            }
        }

        if ($_REQUEST['debug']=='Y') {
            echo '<pre>';
            print_r($params);
            echo '</pre>';
        }
        
        if ($sFunction == 'PaySlip') {
            $method = 'PaySlip';
        } else if (in_array($sFunction, ['GetIncomeStatements', 'AttachFileToObject'])) {
            $method = 'Request';
        } else {
            $method = 'Integration';
        }

        $response = null;
        try {
            if (!($rConnect instanceof \SoapClient)) {
                throw new \Exception('');
            }
            $response = $rConnect->__soapCall($method, array($params));
            if (empty($response->return)) {
                throw new \Exception('');
            }
            if (isset($response->return->result) && empty($response->return->result)) {
                throw new \Exception('');
            }
        } catch (\Exception $exc) {
            return $exc->getMessage();
            EmptySoap::errorInfo();
            $response = (object)[
                'return' => (object)[
                    'result' => false
                ]
            ];
        }

        return $response;
    }

    public function GetArray($rConnect, $sFunction, $arRequestParams = array(), $bNoCache = false)
    {
        $cfunction = function ($rConnect, $sFunction, $arRequestParams) {
            return self::Get($rConnect, $sFunction, $arRequestParams);
        };

        if ($bNoCache) {
            $cacheid = $sFunction . (serialize($arRequestParams));
            $response = self::returnResultCache1C(86400, $cacheid, $cfunction, $rConnect, $sFunction, $arRequestParams);
        } else {
            $response = $cfunction($rConnect, $sFunction, $arRequestParams);
        }

        $sResult = json_decode(json_encode($response->return), true);
        if ($sResult === NULL) {
            $sResult = (array)$response->return;
        }

        if ($_REQUEST['debug']=='Y') {
            echo '<pre>';
            print_r($params);
            echo '</pre>';
        }
        return $sResult;
    }

    public function GetSubdivisions($rConnect, $bCache = true)
    {
        $cfunction = function ($rConnect) {
            return self::Get($rConnect, 'Subdivisions');
        };
        $rRespone = self::returnResultCache1C(($bCache ? 86400 : 0), 'Subdivisions', $cfunction, $rConnect);
        $arSubdivisions = [];
        foreach ($rRespone->return->Data->Subdivisions->Subdivision as $key => $value) {
            $arSubdivisions[ $value->ID ] = [
                'ID'            => $value->ID,
                'PARENT_ID'     => $value->ParentID,
                'FULLNAME'      => $value->FullName,
                'SHORTNAME'     => $value->ShortName,
                'ORGANISATION'  => $value->OrganizationID,
            ];
        }
        return $arSubdivisions;
    }

    public function GetEmployyes($rConnect, $bCache = true)
    {
        global $morphFunct;
        $cfunction = function($rConnect) {
            return self::Get($rConnect, 'Employees');
        };
        $rRespone = self::returnResultCache1C(($bCache ? 86400 : 0), 'Employees', $cfunction, $rConnect);
        $arEmployees = [];
        $arPositions = [];
        foreach ($rRespone->return->Data->Employees->Employee as $key => $value) {
            $arEmployee = array(
                'SID'                   => $value->SID,
                'NAME'                  => $value->Name,
                'SUBDIVISION'           => $value->SubdivisionID,
                'INN'                   => $value->INN,
                'SNILS'                 => $value->SNILS,
                'SUBDIVISIONNAME'       => $value->SubdivisionName,
                'POSITION'              => $value->PositionID,
                'DECRET'                => $value->OnParentalLeave,
                'POSITIONNAME'          => $value->PositionName,
                'LOGIN'                 => $value->NameAD,
                'EMAIL'                 => $value->Mail,
                'POSITION_TYPE'         => $value->PositionType,
                'WORKBOOK_ELECTRONIC'   => $value->WorkBookInElectronicForm,
                'ORGANISATION'          => $value->OrganizationID,
                'BIRTHDATE'             => $value->DateOfBirth,
                'FULL_POSITION'         => $value->FullPositionName,
                'FULL_POSITION_DAT'     => !empty(trim($value->FullPositionNameDat)) ?
                                                trim($value->FullPositionNameDat) :
                                                $morphFunct($value->FullPositionName, 'Д'),
                'FULL_POSITION_ROD'     => !empty(trim($value->FullPositionNameGen)) ?
                                                trim($value->FullPositionNameGen) :
                                                $morphFunct($value->FullPositionName, 'Р'),
                'FULL_POSITION_TV'      => !empty(trim($value->FullPositionNameIns)) ?
                                                trim($value->FullPositionNameIns) :
                                                $morphFunct($value->FullPositionName, 'Т'),
            );

            if (!array_key_exists($arEmployee['POSITION'], $arPositions)) {
                $arPositions[ $arEmployee['POSITION'] ] = $arEmployee['POSITIONNAME'];
            }
            $arEmployees[] = $arEmployee;
        }
        return [
            'POSITIONS' => $arPositions,
            'EMPLOYEES' => $arEmployees
        ];
    }

    private function returnResultCache1C(
        $timeSeconds,
        $cacheId,
        $cfunction = '',
        $rConnect = '',
        $sFunction = '',
        $arRequestParams = []
    ) {
        $obCache = new CPHPCache();
        if ($obCache->InitCache($timeSeconds, $cacheId, '/citto/integration/')) {
            $result = $obCache->GetVars();
        } else {
            $result = $cfunction($rConnect, $sFunction, $arRequestParams);
            if ($result->return->result) {
                $obCache->StartDataCache();
                $obCache->EndDataCache($result);
            }
        }
        return $result;
    }
}
