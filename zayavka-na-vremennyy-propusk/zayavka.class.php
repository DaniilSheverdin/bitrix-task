<<<<<<< HEAD
<?
use Bitrix\Main\SystemException;
define('OCTAGRAMM_DSN',"sqlsrv:Server=172.21.237.199,55955;Database=OCTAGRAM022019");
define('OCTAGRAMM_USER',"scud-web");
define('OCTAGRAMM_PASSWORD',"Qwerty1@3");

define("LATE_MAX", 60*60*2);
define("WORK_TIME_FROM", 9);
define("WORK_TIME_TO", 22);
define("PASSKEY_COUNT", 250);
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * FIRST_NAME - номер карточки
 * MIDDLE_NAME - XML_ID
 * LAST_NAME - FIO_KOMU; VID_DOCUMENTA_STR:NOMER_DOCUMENTA
 * TABLE_NUMBER - ОПД ЗАПРОСИТЬ/ОПД НЕ ЗАПРАШИВАТЬ;DATA_VYDACHI;FIO_PODAVSHEGO;DOLJNOST_PODAVSHEGO;K_KOMU;KABINET
 * SHORT_COMMENT - Выдан/Не Выдан
 */
class OctagramZayavka{
	private static $octagramm_pdo = NULL;

    private const GROUP_SID = "S-1-D1B919F2-09D7-460a-85C8-2B14939DBF65";
	public static function init(){
		if(self::$octagramm_pdo == NULL){
            self::$octagramm_pdo = new PDO(OCTAGRAMM_DSN, OCTAGRAMM_USER ,OCTAGRAMM_PASSWORD, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
    }

	public static function getEmptySlots(){
		self::init();
		$QUERY = "SELECT
						SID
					FROM
						OCTAGRAM022019.dbo.USERS
					WHERE
						GROUP_SID = ?
						AND MIDDLE_NAME IS NULL
						AND LAST_NAME IS NULL
						AND TABLE_NUMBER IS NULL
						AND SHORT_COMMENT IS NULL";
		$stmt = self::$octagramm_pdo->prepare($QUERY);
		$stmt->execute([self::GROUP_SID]);
		// return $stmt->fetchAll(PDO::FETCH_COLUMN);
		return ["12123"];
	}
	public static function add(Zayavka $zayavka){
		self::init();
		$emptySlots = self::getEmptySlots();
		if(!$emptySlots) return false;
		$emptySlotsSID = current($emptySlots);
		
		$QUERY = "UPDATE
						OCTAGRAM022019.dbo.USERS
					SET
					  	MIDDLE_NAME = :MIDDLE_NAME,
					  	LAST_NAME = :LAST_NAME,
					  	TABLE_NUMBER = :TABLE_NUMBER,
						SHORT_COMMENT = NULL
					WHERE
						SID = :SID
						AND GROUP_SID = :GROUP_SID
						AND MIDDLE_NAME IS NULL
						AND LAST_NAME IS NULL
						AND TABLE_NUMBER IS NULL
						AND SHORT_COMMENT IS NULL";

		$LAST_NAME = [
			$zayavka->FIO_KOMU,
			$zayavka->VID_DOCUMENTA_STR,
			$zayavka->NOMER_DOCUMENTA
		];
		$TABLE_NUMBER = [
			$zayavka->IS_NEW_USER?"ОПД ЗАПРОСИТЬ":"ОПД НЕ ЗАПРАШИВАТЬ",
			$zayavka->DATA_VYDACHI,
			$zayavka->FIO_PODAVSHEGO,
			$zayavka->DOLJNOST_PODAVSHEGO,
			$zayavka->K_KOMU,
			$zayavka->KABINET
		];

		// $stmt = self::$octagramm_pdo->prepare($QUERY);
		print_r([
		// $stmt->execute([
				':MIDDLE_NAME'	=> $zayavka->XML_ID,
				':LAST_NAME'	=> implode(",",$LAST_NAME),
				':SID'			=> $emptySlotsSID,
				':GROUP_SID'	=> self::GROUP_SID,
				':TABLE_NUMBER'	=> implode(",",$TABLE_NUMBER)
		// ]);	
		]);	
		// return $stmt->rowCount() > 0;
		echo $QUERY;
		return true;
	}
	public static function get(Zayavka $zayavka){
		self::init();
		$return = [
			'ARRIVE'=> NULL,
			'LEAVE'	=> NULL,
			'GIVEN'	=> false,
		];

		$QUERY = "SELECT
						SID,
						SHORT_COMMENT
					FROM
						OCTAGRAM022019.dbo.USERS
					WHERE
						MIDDLE_NAME = :MIDDLE_NAME
						AND GROUP_SID = :GROUP_SID";

		$stmt = self::$octagramm_pdo->prepare($QUERY);
		$stmt->execute([
			':MIDDLE_NAME'	=> $zayavka->XML_ID,
			':GROUP_SID'	=> self::GROUP_SID,
		]);
		$slotSID = $stmt->fetchColumn();
		if(!$slotSID) return NULL;

		$return['GIVEN'] = (bool)$stmt->fetchColumn(1);
		
		$QUERY2 = "SELECT
						EVENT_TYPE_ID,
						EVENT_TIME
					FROM
						OCTAGRAM022019.dbo.EVENTS
					WHERE
						EVENT_TYPE_ID IN (101, 102)
						AND USER_SID = :USER_SID
						AND EVENT_TIME >= CONVERT(datetime, :DATE_FROM, 104)
						AND EVENT_TIME <= CONVERT(datetime, :DATE_TO, 104)
					GROUP BY
						EVENT_TYPE_ID, EVENT_TIME
					ORDER BY
						ID DESC";

		$stmt2 = self::$octagramm_pdo->prepare($QUERY2);
		$stmt2->execute([
			':USER_SID'		=> $slotSID,
			':DATE_FROM'	=> $zayavka->VREMYA->format('d.m.Y')." 00:00:00.000",
			':DATE_TO'		=> $zayavka->VREMYA->format('d.m.Y')." 23:59:00.000",
		]);
		$events = $stmt2->fetchAll();
		foreach($events as $event){
			if($event['EVENT_TYPE_ID'] == "101"){
				$return['ARRIVE'] = $event['EVENT_TIME'];
			}elseif($event['EVENT_TYPE_ID'] == "101"){
				$return['LEAVE'] = $event['EVENT_TIME'];
			}
		}
		return $return;
	}
	public static function clearAll(){
		self::init();
		$QUERY = "UPDATE
						OCTAGRAM022019.dbo.USERS
					SET
						MIDDLE_NAME = NULL,
						LAST_NAME = NULL,
						TABLE_NUMBER = NULL,
						SHORT_COMMENT = NULL
					WHERE 
						GROUP_SID = ?";

		// $stmt = self::$octagramm_pdo->prepare($QUERY);
		// $stmt->execute([self::GROUP_SID]);
		echo $QUERY;
	}
	public static function clear(Zayavka $zayavka){
		self::init();
		$QUERY = "UPDATE
						OCTAGRAM022019.dbo.USERS
					SET
						MIDDLE_NAME = NULL,
						LAST_NAME = NULL,
						TABLE_NUMBER = NULL,
						SHORT_COMMENT = NULL
					WHERE
						MIDDLE_NAME = :MIDDLE_NAME
						AND GROUP_SID = :GROUP_SID";

		// $stmt = self::$octagramm_pdo->prepare($QUERY);
		// $stmt->execute([
        //     ':MIDDLE_NAME'	=> $zayavka->XML_ID,
        //     ':GROUP_SID'	=> self::GROUP_SID
        // ]);
		echo $QUERY;
	}
	public static function cancel(Zayavka $zayavka){
		self::init();
		$QUERY = "UPDATE
						OCTAGRAM022019.dbo.USERS
					SET
						MIDDLE_NAME = NULL,
						LAST_NAME = NULL,
						TABLE_NUMBER = NULL,
						SHORT_COMMENT = NULL
					WHERE
						SHORT_COMMENT IS NULL
						AND MIDDLE_NAME = :MIDDLE_NAME
						AND GROUP_SID = :GROUP_SID
						AND SID NOT IN(SELECT USER_SID FROM OCTAGRAM022019.dbo.EVENTS WHERE EVENT_TIME >= CONVERT(datetime, :DATE, 104))";
        // $stmt = self::$octagramm_pdo->prepare($QUERY);
        // $stmt->execute([
        //     ':MIDDLE_NAME'	=> $zayavka->XML_ID,
        //     ':GROUP_SID'	=> self::GROUP_SID,
        //     ':DATE'			=> $zayavka->VREMYA->format('d.m.Y')." 00:00:00.000",
        // ]);
		// return $stmt->rowCount() > 0;
		echo $QUERY;
		return false;
	}
}

class Zayavka{
	public static $STATUS_GIVEN;
	public static $STATUS_WAITING;
	public static $STATUS_ARRIVE;
	public static $STATUS_LEAVE;
	public static $STATUS_NOT_USED;
	public static $STATUS_LOADED;
	public static $STATUS_CANCELED;
	public static $STATUS_NO_SLOTS;

	public static $VID_DOCUMENTA_PASPORT_RF;
	public static $VID_DOCUMENTA_PASPORT_FOREIGN;

	private static $iblock_id = NULL;
	private static $iblock_props = [];

	public $ID = NULL;
	public $XML_ID = NULL;
	public $DATA_VYDACHI = NULL;
	public $FIO_KOMU = NULL;
	public $VID_DOCUMENTA = NULL;
	public $FIO_PODAVSHEGO = NULL;
	public $USER_ID = NULL;
	public $DOLJNOST_PODAVSHEGO = NULL;
	public $K_KOMU = NULL;
	public $KABINET = NULL;
	public $NOMER_DOCUMENTA = NULL;
    public $STATUS = NULL;
	
    /**
     * 
     * @var \DateTime;
     */
	public $VREMYA_DT = NULL;
	public function __set($name, $value) {
        if($name == "VREMYA"){
			$this->VREMYA_DT->modify($value);
		}
    }

    public function __get($name){
        if($name == "VREMYA"){
			return $this->VREMYA_DT;
		}
        if($name == "VID_DOCUMENTA_STR"){
			return self::$iblock_props['VID_DOCUMENTA']['VALUES'][$this->VID_DOCUMENTA]['VALUE'];
		}
        if($name == "STATUS_STR"){
			return self::$iblock_props['STATUS']['VALUES'][$this->STATUS]['VALUE'];
		}
		if($name == "IS_NEW_USER"){
			$zayavki = self::getBF([
				'PROPERTY_STATUS'			=> [self::$STATUS_LEAVE,self::$STATUS_ARRIVE,self::$STATUS_GIVEN],
				'=PROPERTY_FIO_KOMU' 		=> $this->FIO_KOMU,
				'PROPERTY_VID_DOCUMENTA'	=> $this->VID_DOCUMENTA,
				'=PROPERTY_NOMER_DOCUMENTA'	=> $this->NOMER_DOCUMENTA,
			]);
			return empty($zayavki);
		}
		return null;
	}
	public static function init(){
		if(self::$iblock_id == NULL){
			\Bitrix\Main\Loader::includeModule('iblock');
			$zayavka_iblock = \Bitrix\Iblock\IblockTable::getList([
				'select'  => ['ID'],
				'filter'  => ['CODE'=>"zayavka_na_propusk"],
				'limit'   => 1,
			])->fetch();
			if(!$zayavka_iblock) throw new SystemException("Инфоблок не найден");

			self::$iblock_id = $zayavka_iblock['ID'];

			$res = \Bitrix\Iblock\PropertyTable::getList([
				'select'  => ['ID','CODE','PROPERTY_TYPE','IS_REQUIRED','NAME'],
				'filter'  => ['IBLOCK_ID'=>self::$iblock_id]
			])->fetchAll();
			foreach($res as $ob){
				if($ob['PROPERTY_TYPE'] == "L"){
					$ob['VALUES'] = [];

					array_walk(\Bitrix\Iblock\PropertyEnumerationTable::getList([
						'select'  => ['ID','VALUE','XML_ID'],
						'filter'  => ['PROPERTY_ID'=>$ob['ID']]
					])->fetchAll(), function($val) use (&$ob){
						$ob['VALUES'][$val['ID']] = $val;
					});
				}
				self::$iblock_props[$ob['CODE']] = $ob;
				unset($ob);
			}

			
			foreach(self::$iblock_props['STATUS']['VALUES'] as $val){
				self::${"STATUS_".$val['XML_ID']} = $val['ID'];
			}
			foreach(self::$iblock_props['VID_DOCUMENTA']['VALUES'] as $val){
				self::${"VID_DOCUMENTA_".$val['XML_ID']} = $val['ID'];
			}
		}
	}
	
    public function __construct(){
		self::init();
		$this->VREMYA_DT = new \DateTime();
	}
	
    public function inOctagramm(){
		return in_array(
							$this->STATUS,
							[
								Zayavka::$STATUS_WAITING,
								Zayavka::$STATUS_GIVEN,
								Zayavka::$STATUS_ARRIVE
							]
					);
			
	}
    public function cancel(){
        if(!$this->cancelable()) throw new SystemException("Невозможно отменить. Возможно пропуск уже выдан");
        if($this->inOctagramm()){
            if(!OctagramZayavka::cancel($this)) throw new SystemException("Невозможно отменить. Возможно пропуск уже выдан");
		}
       
        CIBlockElement::SetPropertyValuesEx($this->ID, self::$iblock_id, ['STATUS' => self::$STATUS_CANCELED]);
    }
    public function cancelable(){
        return in_array($this->STATUS,[self::$STATUS_LOADED,self::$STATUS_WAITING,self::$STATUS_NO_SLOTS]);
	}
	
    public static function dateFromGet(){
		$date = new \DateTime();
		if($date->format('H') < WORK_TIME_FROM){
			$date->setTime(WORK_TIME_FROM,0);
		}
		return $date;
	}
    public static function dateToGet(){
		$date = new \DateTime();
		$date->setTime(WORK_TIME_TO,0);
		return $date;
	}
    public function vremyaInBorders(){
		return $this->VREMYA > self::dateFromGet() && $this->VREMYA < self::dateToGet();
	}
    public function ticketSend(){
		\Bitrix\Main\Loader::includeModule('nkhost.phpexcel');
		require_once ($GLOBALS['PHPEXCELPATH'] . '/PHPExcel/IOFactory.php');
		
		$xls = PHPExcel_IOFactory::load(__DIR__."/propusk_shablon.xlsx");

		$xls->setActiveSheetIndex(0);
		$sheet = $xls->getActiveSheet();

		$sheet->setCellValue('D4', $this->VREMYA->format('d'));
		$sheet->setCellValue('N4', $this->VREMYA->format('d'));
		$sheet->setCellValue('F4', $this->VREMYA->format('m'));
		$sheet->setCellValue('P4', $this->VREMYA->format('m'));
		$sheet->setCellValue('I4', $this->VREMYA->format('Y')."г.");
		$sheet->setCellValue('V4', $this->VREMYA->format('Y')."г.");
		
		$sheet->setCellValue('B6', $this->FIO_KOMU);
		$sheet->setCellValue('L6', $this->FIO_KOMU);
		
		$sheet->setCellValue('F9', "паспорт");
		
		$sheet->setCellValue('A14', $this->FIO_PODAVSHEGO);
		$sheet->setCellValue('K10', $this->FIO_PODAVSHEGO);
		$sheet->setCellValue('A15', $this->DOLJNOST_PODAVSHEGO);
		$sheet->setCellValue('K11', $this->DOLJNOST_PODAVSHEGO);
		
		$sheet->setCellValue('D18', $this->KABINET);
		$sheet->setCellValue('N15', $this->KABINET);

		$sheet->setCellValue('N13', $this->K_KOMU);
		$sheet->setCellValue('R17', $this->VREMYA->format('H'));
		$sheet->setCellValue('T17', $this->VREMYA->format('i'));
		
		$tmpfname = tempnam("/tmp", "zayavka_file");
		$objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
		$objWriter->save($tmpfname);

		$zayavka_file = [
			'name'		    => "Заявка на временный пропуск для ".mb_substr($this->FIO_KOMU, 0, 50).".xlsx",
			'type'		    => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
			'tmp_name'	    => $tmpfname,
			'error'		    => 0,
			'size'		    => filesize($tmpfname),
			'MODULE_ID'	    => "main",
		];
		
		$zayavka_file_id = (int)CFile::SaveFile($zayavka_file, "zayavka_file", true);
		if($zayavka_file_id<=0) throw new Exception("Ошибка загрузки файла");
		unlink($tmpfname);

		\Bitrix\Main\Mail\Event::send([
			'EVENT_NAME'=> "ZNVP_NEW",
			'LID'       => "s1",
			'FILE'		=> [$zayavka_file_id],
			'C_FIELDS'  => [
				'EMAIL'					=> $GLOBALS['USER']->GetEmail(),
				'DATA_VYDACHI'          => $this->DATA_VYDACHI,
				'VREMYA'          		=> $this->VREMYA->format('d.m.Y H:i:s'),
				'FIO_PODAVSHEGO'        => $this->FIO_PODAVSHEGO,
				'FIO_KOMU'              => $this->FIO_KOMU,
				'VID_DOCUMENTA'         => $this->VID_DOCUMENTA,
				'NOMER_DOCUMENTA'       => $this->NOMER_DOCUMENTA,
				'DOLJNOST_PODAVSHEGO'   => $this->DOLJNOST_PODAVSHEGO,
				'K_KOMU'                => $this->K_KOMU,
				'KABINET'               => $this->KABINET,
			]
		]); 
	}
    public function save(){
		if(empty($this->DATA_VYDACHI)){
			$this->DATA_VYDACHI = ConvertTimeStamp($this->VREMYA->format('U'), "FULL");
		}
		if(empty($this->STATUS)){
			$this->STATUS = self::$STATUS_LOADED;
		}
		
		$ar_props	= [];
		foreach(self::$iblock_props as $iblock_prop){
			$val = $this->{$iblock_prop['CODE']};
			
			if(empty($val) && $iblock_prop['IS_REQUIRED'] == "Y") throw new SystemException("Не заполнено поле: ".$iblock_prop['NAME']);

		   	if($iblock_prop['CODE'] == "VREMYA"){
				$val = ConvertTimeStamp($val->format('U'),"FULL");
		   	}
		   	$ar_props[$iblock_prop['CODE']] = $val;
		}

		if($this->ID){
			$ar_fields = [
				'MODIFIED_BY' => 1,
			];
			if(in_array($this->STATUS,[self::$STATUS_CANCELED,self::$STATUS_NOT_USED,self::$STATUS_LEAVE])){
				$ar_fields['ACTIVE'] = "N";
			}
			$el = new CIBlockElement;
			$el->Update($this->ID, $ar_fields);
		}else{
			$ar_fields = [
				'NAME'				=> "Заказ разового пропуска",
				'MODIFIED_BY'		=> $this->USER_ID,
				'IBLOCK_SECTION_ID' => false,
				'IBLOCK_ID'         => self::$iblock_id,
				'ACTIVE'            => "Y",
				'XML_ID'			=> md5(serialize([
					'FIO_KOMU'          => $this->FIO_KOMU,
					'VID_DOCUMENTA'     => $this->VID_DOCUMENTA,
					'NOMER_DOCUMENTA'   => $this->NOMER_DOCUMENTA,
					'VREMYA'            => $this->VREMYA->format('U'),
			   ]))
			];
	
			if(!$this->vremyaInBorders()) throw new SystemException("На данную дату/время невозможно записаться");
			
			if(\Bitrix\Iblock\ElementTable::getList([
				'select'  => ['ID'],
				'filter'  => ['ACTIVE'=>"Y",'XML_ID'=>$ar_fields['XML_ID']],
				'limit'   => 1,
			])->fetch()) throw new SystemException("Заявка на данное время для указанного лица уже подавалась");
	
			if(CIBlockElement::GetList([], [
				'ACTIVE'			=> "Y",
				'>PROPERTY_VREMYA'	=> $this->VREMYA->format('Y-m-d')." 00:00:00",
				'<PROPERTY_VREMYA'	=> $this->VREMYA->format('Y-m-d')." 23:59:59",
			], [], false, ['ID']) >= PASSKEY_COUNT) throw new SystemException("Достигнуто максимальное количество заявок");
			
			$this->XML_ID = $ar_fields['XML_ID'];
			
			// if($this->VREMYA->format('Y-m-d') == date('Y-m-d')){
			// 	if(!OctagramZayavka::add($this)) throw new SystemException("Достигнуто максимальное количество заявок");
			// 	$ar_props['STATUS'] = $this->STATUS = self::$STATUS_WAITING;
			// }

			$zayavka_add = new CIBlockElement;
			$this->ID = $zayavka_add->add($ar_fields);
			if(!$this->ID) throw new SystemException($zayavka_add->LAST_ERROR);

			$this->ticketSend();
		}

		CIBlockElement::SetPropertyValuesEx($this->ID, self::$iblock_id, $ar_props, ['NewElement'=>"Y",'DoNotValidateLists'=>"Y"]);
	}
	
	public static function one($ID){
		self::init();

        $zayavki = self::get(NULL,NULL,$ID);
        return current($zayavki);
    }
	public static function get(\DateTime $data_priyoma=NULL, int $user_id=NULL, $ID=NULL, $STATUS=NULL){
		self::init();

		$filter = [
			'IBLOCK_ID' => self::$iblock_id,
		];
		if($STATUS){
			$filter['PROPERTY_STATUS'] = $STATUS;
		}
		if($ID){
			$filter['ID'] = $ID;
		}
		if($user_id){
			$filter['=PROPERTY_USER_ID'] = $user_id;
		}
		if($data_priyoma){
			$filter['>PROPERTY_VREMYA'] = $data_priyoma->format('Y-m-d')." 00:00:00";
			$filter['<PROPERTY_VREMYA'] = $data_priyoma->format('Y-m-d')." 23:59:59";
		}
	
		return self::getBF($filter);
	}
	public static function getBF($filter = []){
		$zayavki = [];
		$res =	CIBlockElement::GetList(['PROPERTY_VREMYA'=>'ASC'], $filter,false,false,["ID","IBLOCK_ID","NAME","PROPERTY_*"]);
		while($ob = $res->GetNextElement()){
			$ob_fields= $ob->GetFields();
			$ob_props = $ob->GetProperties();
			
			$zayavka = new self;
			
			foreach(self::$iblock_props as $zayavka_prop){
				$op_prop_val = $ob_props[$zayavka_prop['CODE']]['VALUE'];

				$zayavka->{$zayavka_prop['CODE']} = is_array($op_prop_val)
														? implode(", ",$op_prop_val)
														: $op_prop_val;
			}
			$zayavka->ID				= $ob_fields['ID'];
			$zayavka->XML_ID			= $ob_fields['XML_ID'];
			$zayavka->STATUS			= $ob_props['STATUS']['VALUE_ENUM_ID'];
			$zayavka->VID_DOCUMENTA		= $ob_props['VID_DOCUMENTA']['VALUE_ENUM_ID'];
			
			$zayavki[$zayavka->ID] = $zayavka;
			unset($zayavka);
		}
		return $zayavki;
	}

	public static function getProps(){
		self::init();
		return self::$iblock_props;
	}
}

=======
<?
use Bitrix\Main\SystemException;
define('OCTAGRAMM_DSN',"sqlsrv:Server=172.21.237.199,55955;Database=OCTAGRAM022019");
define('OCTAGRAMM_USER',"scud-web");
define('OCTAGRAMM_PASSWORD',"Qwerty1@3");

define("LATE_MAX", 60*60*2);
define("WORK_TIME_FROM", 9);
define("WORK_TIME_TO", 22);
define("PASSKEY_COUNT", 250);
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * FIRST_NAME - номер карточки
 * MIDDLE_NAME - XML_ID
 * LAST_NAME - FIO_KOMU; VID_DOCUMENTA_STR:NOMER_DOCUMENTA
 * TABLE_NUMBER - ОПД ЗАПРОСИТЬ/ОПД НЕ ЗАПРАШИВАТЬ;DATA_VYDACHI;FIO_PODAVSHEGO;DOLJNOST_PODAVSHEGO;K_KOMU;KABINET
 * SHORT_COMMENT - Выдан/Не Выдан
 */
class OctagramZayavka{
	private static $octagramm_pdo = NULL;

    private const GROUP_SID = "S-1-D1B919F2-09D7-460a-85C8-2B14939DBF65";
	public static function init(){
		if(self::$octagramm_pdo == NULL){
            self::$octagramm_pdo = new PDO(OCTAGRAMM_DSN, OCTAGRAMM_USER ,OCTAGRAMM_PASSWORD, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
    }

	public static function getEmptySlots(){
		self::init();
		$QUERY = "SELECT
						SID
					FROM
						OCTAGRAM022019.dbo.USERS
					WHERE
						GROUP_SID = ?
						AND MIDDLE_NAME IS NULL
						AND LAST_NAME IS NULL
						AND TABLE_NUMBER IS NULL
						AND SHORT_COMMENT IS NULL";
		$stmt = self::$octagramm_pdo->prepare($QUERY);
		$stmt->execute([self::GROUP_SID]);
		// return $stmt->fetchAll(PDO::FETCH_COLUMN);
		return ["12123"];
	}
	public static function add(Zayavka $zayavka){
		self::init();
		$emptySlots = self::getEmptySlots();
		if(!$emptySlots) return false;
		$emptySlotsSID = current($emptySlots);
		
		$QUERY = "UPDATE
						OCTAGRAM022019.dbo.USERS
					SET
					  	MIDDLE_NAME = :MIDDLE_NAME,
					  	LAST_NAME = :LAST_NAME,
					  	TABLE_NUMBER = :TABLE_NUMBER,
						SHORT_COMMENT = NULL
					WHERE
						SID = :SID
						AND GROUP_SID = :GROUP_SID
						AND MIDDLE_NAME IS NULL
						AND LAST_NAME IS NULL
						AND TABLE_NUMBER IS NULL
						AND SHORT_COMMENT IS NULL";

		$LAST_NAME = [
			$zayavka->FIO_KOMU,
			$zayavka->VID_DOCUMENTA_STR,
			$zayavka->NOMER_DOCUMENTA
		];
		$TABLE_NUMBER = [
			$zayavka->IS_NEW_USER?"ОПД ЗАПРОСИТЬ":"ОПД НЕ ЗАПРАШИВАТЬ",
			$zayavka->DATA_VYDACHI,
			$zayavka->FIO_PODAVSHEGO,
			$zayavka->DOLJNOST_PODAVSHEGO,
			$zayavka->K_KOMU,
			$zayavka->KABINET
		];

		// $stmt = self::$octagramm_pdo->prepare($QUERY);
		print_r([
		// $stmt->execute([
				':MIDDLE_NAME'	=> $zayavka->XML_ID,
				':LAST_NAME'	=> implode(",",$LAST_NAME),
				':SID'			=> $emptySlotsSID,
				':GROUP_SID'	=> self::GROUP_SID,
				':TABLE_NUMBER'	=> implode(",",$TABLE_NUMBER)
		// ]);	
		]);	
		// return $stmt->rowCount() > 0;
		echo $QUERY;
		return true;
	}
	public static function get(Zayavka $zayavka){
		self::init();
		$return = [
			'ARRIVE'=> NULL,
			'LEAVE'	=> NULL,
			'GIVEN'	=> false,
		];

		$QUERY = "SELECT
						SID,
						SHORT_COMMENT
					FROM
						OCTAGRAM022019.dbo.USERS
					WHERE
						MIDDLE_NAME = :MIDDLE_NAME
						AND GROUP_SID = :GROUP_SID";

		$stmt = self::$octagramm_pdo->prepare($QUERY);
		$stmt->execute([
			':MIDDLE_NAME'	=> $zayavka->XML_ID,
			':GROUP_SID'	=> self::GROUP_SID,
		]);
		$slotSID = $stmt->fetchColumn();
		if(!$slotSID) return NULL;

		$return['GIVEN'] = (bool)$stmt->fetchColumn(1);
		
		$QUERY2 = "SELECT
						EVENT_TYPE_ID,
						EVENT_TIME
					FROM
						OCTAGRAM022019.dbo.EVENTS
					WHERE
						EVENT_TYPE_ID IN (101, 102)
						AND USER_SID = :USER_SID
						AND EVENT_TIME >= CONVERT(datetime, :DATE_FROM, 104)
						AND EVENT_TIME <= CONVERT(datetime, :DATE_TO, 104)
					GROUP BY
						EVENT_TYPE_ID, EVENT_TIME
					ORDER BY
						ID DESC";

		$stmt2 = self::$octagramm_pdo->prepare($QUERY2);
		$stmt2->execute([
			':USER_SID'		=> $slotSID,
			':DATE_FROM'	=> $zayavka->VREMYA->format('d.m.Y')." 00:00:00.000",
			':DATE_TO'		=> $zayavka->VREMYA->format('d.m.Y')." 23:59:00.000",
		]);
		$events = $stmt2->fetchAll();
		foreach($events as $event){
			if($event['EVENT_TYPE_ID'] == "101"){
				$return['ARRIVE'] = $event['EVENT_TIME'];
			}elseif($event['EVENT_TYPE_ID'] == "101"){
				$return['LEAVE'] = $event['EVENT_TIME'];
			}
		}
		return $return;
	}
	public static function clearAll(){
		self::init();
		$QUERY = "UPDATE
						OCTAGRAM022019.dbo.USERS
					SET
						MIDDLE_NAME = NULL,
						LAST_NAME = NULL,
						TABLE_NUMBER = NULL,
						SHORT_COMMENT = NULL
					WHERE 
						GROUP_SID = ?";

		// $stmt = self::$octagramm_pdo->prepare($QUERY);
		// $stmt->execute([self::GROUP_SID]);
		echo $QUERY;
	}
	public static function clear(Zayavka $zayavka){
		self::init();
		$QUERY = "UPDATE
						OCTAGRAM022019.dbo.USERS
					SET
						MIDDLE_NAME = NULL,
						LAST_NAME = NULL,
						TABLE_NUMBER = NULL,
						SHORT_COMMENT = NULL
					WHERE
						MIDDLE_NAME = :MIDDLE_NAME
						AND GROUP_SID = :GROUP_SID";

		// $stmt = self::$octagramm_pdo->prepare($QUERY);
		// $stmt->execute([
        //     ':MIDDLE_NAME'	=> $zayavka->XML_ID,
        //     ':GROUP_SID'	=> self::GROUP_SID
        // ]);
		echo $QUERY;
	}
	public static function cancel(Zayavka $zayavka){
		self::init();
		$QUERY = "UPDATE
						OCTAGRAM022019.dbo.USERS
					SET
						MIDDLE_NAME = NULL,
						LAST_NAME = NULL,
						TABLE_NUMBER = NULL,
						SHORT_COMMENT = NULL
					WHERE
						SHORT_COMMENT IS NULL
						AND MIDDLE_NAME = :MIDDLE_NAME
						AND GROUP_SID = :GROUP_SID
						AND SID NOT IN(SELECT USER_SID FROM OCTAGRAM022019.dbo.EVENTS WHERE EVENT_TIME >= CONVERT(datetime, :DATE, 104))";
        // $stmt = self::$octagramm_pdo->prepare($QUERY);
        // $stmt->execute([
        //     ':MIDDLE_NAME'	=> $zayavka->XML_ID,
        //     ':GROUP_SID'	=> self::GROUP_SID,
        //     ':DATE'			=> $zayavka->VREMYA->format('d.m.Y')." 00:00:00.000",
        // ]);
		// return $stmt->rowCount() > 0;
		echo $QUERY;
		return false;
	}
}

class Zayavka{
	public static $STATUS_GIVEN;
	public static $STATUS_WAITING;
	public static $STATUS_ARRIVE;
	public static $STATUS_LEAVE;
	public static $STATUS_NOT_USED;
	public static $STATUS_LOADED;
	public static $STATUS_CANCELED;
	public static $STATUS_NO_SLOTS;

	public static $VID_DOCUMENTA_PASPORT_RF;
	public static $VID_DOCUMENTA_PASPORT_FOREIGN;

	private static $iblock_id = NULL;
	private static $iblock_props = [];

	public $ID = NULL;
	public $XML_ID = NULL;
	public $DATA_VYDACHI = NULL;
	public $FIO_KOMU = NULL;
	public $VID_DOCUMENTA = NULL;
	public $FIO_PODAVSHEGO = NULL;
	public $USER_ID = NULL;
	public $DOLJNOST_PODAVSHEGO = NULL;
	public $K_KOMU = NULL;
	public $KABINET = NULL;
	public $NOMER_DOCUMENTA = NULL;
    public $STATUS = NULL;
	
    /**
     * 
     * @var \DateTime;
     */
	public $VREMYA_DT = NULL;
	public function __set($name, $value) {
        if($name == "VREMYA"){
			$this->VREMYA_DT->modify($value);
		}
    }

    public function __get($name){
        if($name == "VREMYA"){
			return $this->VREMYA_DT;
		}
        if($name == "VID_DOCUMENTA_STR"){
			return self::$iblock_props['VID_DOCUMENTA']['VALUES'][$this->VID_DOCUMENTA]['VALUE'];
		}
        if($name == "STATUS_STR"){
			return self::$iblock_props['STATUS']['VALUES'][$this->STATUS]['VALUE'];
		}
		if($name == "IS_NEW_USER"){
			$zayavki = self::getBF([
				'PROPERTY_STATUS'			=> [self::$STATUS_LEAVE,self::$STATUS_ARRIVE,self::$STATUS_GIVEN],
				'=PROPERTY_FIO_KOMU' 		=> $this->FIO_KOMU,
				'PROPERTY_VID_DOCUMENTA'	=> $this->VID_DOCUMENTA,
				'=PROPERTY_NOMER_DOCUMENTA'	=> $this->NOMER_DOCUMENTA,
			]);
			return empty($zayavki);
		}
		return null;
	}
	public static function init(){
		if(self::$iblock_id == NULL){
			\Bitrix\Main\Loader::includeModule('iblock');
			$zayavka_iblock = \Bitrix\Iblock\IblockTable::getList([
				'select'  => ['ID'],
				'filter'  => ['CODE'=>"zayavka_na_propusk"],
				'limit'   => 1,
			])->fetch();
			if(!$zayavka_iblock) throw new SystemException("Инфоблок не найден");

			self::$iblock_id = $zayavka_iblock['ID'];

			$res = \Bitrix\Iblock\PropertyTable::getList([
				'select'  => ['ID','CODE','PROPERTY_TYPE','IS_REQUIRED','NAME'],
				'filter'  => ['IBLOCK_ID'=>self::$iblock_id]
			])->fetchAll();
			foreach($res as $ob){
				if($ob['PROPERTY_TYPE'] == "L"){
					$ob['VALUES'] = [];

					array_walk(\Bitrix\Iblock\PropertyEnumerationTable::getList([
						'select'  => ['ID','VALUE','XML_ID'],
						'filter'  => ['PROPERTY_ID'=>$ob['ID']]
					])->fetchAll(), function($val) use (&$ob){
						$ob['VALUES'][$val['ID']] = $val;
					});
				}
				self::$iblock_props[$ob['CODE']] = $ob;
				unset($ob);
			}

			
			foreach(self::$iblock_props['STATUS']['VALUES'] as $val){
				self::${"STATUS_".$val['XML_ID']} = $val['ID'];
			}
			foreach(self::$iblock_props['VID_DOCUMENTA']['VALUES'] as $val){
				self::${"VID_DOCUMENTA_".$val['XML_ID']} = $val['ID'];
			}
		}
	}
	
    public function __construct(){
		self::init();
		$this->VREMYA_DT = new \DateTime();
	}
	
    public function inOctagramm(){
		return in_array(
							$this->STATUS,
							[
								Zayavka::$STATUS_WAITING,
								Zayavka::$STATUS_GIVEN,
								Zayavka::$STATUS_ARRIVE
							]
					);
			
	}
    public function cancel(){
        if(!$this->cancelable()) throw new SystemException("Невозможно отменить. Возможно пропуск уже выдан");
        if($this->inOctagramm()){
            if(!OctagramZayavka::cancel($this)) throw new SystemException("Невозможно отменить. Возможно пропуск уже выдан");
		}
       
        CIBlockElement::SetPropertyValuesEx($this->ID, self::$iblock_id, ['STATUS' => self::$STATUS_CANCELED]);
    }
    public function cancelable(){
        return in_array($this->STATUS,[self::$STATUS_LOADED,self::$STATUS_WAITING,self::$STATUS_NO_SLOTS]);
	}
	
    public static function dateFromGet(){
		$date = new \DateTime();
		if($date->format('H') < WORK_TIME_FROM){
			$date->setTime(WORK_TIME_FROM,0);
		}
		return $date;
	}
    public static function dateToGet(){
		$date = new \DateTime();
		$date->setTime(WORK_TIME_TO,0);
		return $date;
	}
    public function vremyaInBorders(){
		return $this->VREMYA > self::dateFromGet() && $this->VREMYA < self::dateToGet();
	}
    public function ticketSend(){
		\Bitrix\Main\Loader::includeModule('nkhost.phpexcel');
		require_once ($GLOBALS['PHPEXCELPATH'] . '/PHPExcel/IOFactory.php');
		
		$xls = PHPExcel_IOFactory::load(__DIR__."/propusk_shablon.xlsx");

		$xls->setActiveSheetIndex(0);
		$sheet = $xls->getActiveSheet();

		$sheet->setCellValue('D4', $this->VREMYA->format('d'));
		$sheet->setCellValue('N4', $this->VREMYA->format('d'));
		$sheet->setCellValue('F4', $this->VREMYA->format('m'));
		$sheet->setCellValue('P4', $this->VREMYA->format('m'));
		$sheet->setCellValue('I4', $this->VREMYA->format('Y')."г.");
		$sheet->setCellValue('V4', $this->VREMYA->format('Y')."г.");
		
		$sheet->setCellValue('B6', $this->FIO_KOMU);
		$sheet->setCellValue('L6', $this->FIO_KOMU);
		
		$sheet->setCellValue('F9', "паспорт");
		
		$sheet->setCellValue('A14', $this->FIO_PODAVSHEGO);
		$sheet->setCellValue('K10', $this->FIO_PODAVSHEGO);
		$sheet->setCellValue('A15', $this->DOLJNOST_PODAVSHEGO);
		$sheet->setCellValue('K11', $this->DOLJNOST_PODAVSHEGO);
		
		$sheet->setCellValue('D18', $this->KABINET);
		$sheet->setCellValue('N15', $this->KABINET);

		$sheet->setCellValue('N13', $this->K_KOMU);
		$sheet->setCellValue('R17', $this->VREMYA->format('H'));
		$sheet->setCellValue('T17', $this->VREMYA->format('i'));
		
		$tmpfname = tempnam("/tmp", "zayavka_file");
		$objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
		$objWriter->save($tmpfname);

		$zayavka_file = [
			'name'		    => "Заявка на временный пропуск для ".mb_substr($this->FIO_KOMU, 0, 50).".xlsx",
			'type'		    => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
			'tmp_name'	    => $tmpfname,
			'error'		    => 0,
			'size'		    => filesize($tmpfname),
			'MODULE_ID'	    => "main",
		];
		
		$zayavka_file_id = (int)CFile::SaveFile($zayavka_file, "zayavka_file", true);
		if($zayavka_file_id<=0) throw new Exception("Ошибка загрузки файла");
		unlink($tmpfname);

		\Bitrix\Main\Mail\Event::send([
			'EVENT_NAME'=> "ZNVP_NEW",
			'LID'       => "s1",
			'FILE'		=> [$zayavka_file_id],
			'C_FIELDS'  => [
				'EMAIL'					=> $GLOBALS['USER']->GetEmail(),
				'DATA_VYDACHI'          => $this->DATA_VYDACHI,
				'VREMYA'          		=> $this->VREMYA->format('d.m.Y H:i:s'),
				'FIO_PODAVSHEGO'        => $this->FIO_PODAVSHEGO,
				'FIO_KOMU'              => $this->FIO_KOMU,
				'VID_DOCUMENTA'         => $this->VID_DOCUMENTA,
				'NOMER_DOCUMENTA'       => $this->NOMER_DOCUMENTA,
				'DOLJNOST_PODAVSHEGO'   => $this->DOLJNOST_PODAVSHEGO,
				'K_KOMU'                => $this->K_KOMU,
				'KABINET'               => $this->KABINET,
			]
		]); 
	}
    public function save(){
		if(empty($this->DATA_VYDACHI)){
			$this->DATA_VYDACHI = ConvertTimeStamp($this->VREMYA->format('U'), "FULL");
		}
		if(empty($this->STATUS)){
			$this->STATUS = self::$STATUS_LOADED;
		}
		
		$ar_props	= [];
		foreach(self::$iblock_props as $iblock_prop){
			$val = $this->{$iblock_prop['CODE']};
			
			if(empty($val) && $iblock_prop['IS_REQUIRED'] == "Y") throw new SystemException("Не заполнено поле: ".$iblock_prop['NAME']);

		   	if($iblock_prop['CODE'] == "VREMYA"){
				$val = ConvertTimeStamp($val->format('U'),"FULL");
		   	}
		   	$ar_props[$iblock_prop['CODE']] = $val;
		}

		if($this->ID){
			$ar_fields = [
				'MODIFIED_BY' => 1,
			];
			if(in_array($this->STATUS,[self::$STATUS_CANCELED,self::$STATUS_NOT_USED,self::$STATUS_LEAVE])){
				$ar_fields['ACTIVE'] = "N";
			}
			$el = new CIBlockElement;
			$el->Update($this->ID, $ar_fields);
		}else{
			$ar_fields = [
				'NAME'				=> "Заказ разового пропуска",
				'MODIFIED_BY'		=> $this->USER_ID,
				'IBLOCK_SECTION_ID' => false,
				'IBLOCK_ID'         => self::$iblock_id,
				'ACTIVE'            => "Y",
				'XML_ID'			=> md5(serialize([
					'FIO_KOMU'          => $this->FIO_KOMU,
					'VID_DOCUMENTA'     => $this->VID_DOCUMENTA,
					'NOMER_DOCUMENTA'   => $this->NOMER_DOCUMENTA,
					'VREMYA'            => $this->VREMYA->format('U'),
			   ]))
			];
	
			if(!$this->vremyaInBorders()) throw new SystemException("На данную дату/время невозможно записаться");
			
			if(\Bitrix\Iblock\ElementTable::getList([
				'select'  => ['ID'],
				'filter'  => ['ACTIVE'=>"Y",'XML_ID'=>$ar_fields['XML_ID']],
				'limit'   => 1,
			])->fetch()) throw new SystemException("Заявка на данное время для указанного лица уже подавалась");
	
			if(CIBlockElement::GetList([], [
				'ACTIVE'			=> "Y",
				'>PROPERTY_VREMYA'	=> $this->VREMYA->format('Y-m-d')." 00:00:00",
				'<PROPERTY_VREMYA'	=> $this->VREMYA->format('Y-m-d')." 23:59:59",
			], [], false, ['ID']) >= PASSKEY_COUNT) throw new SystemException("Достигнуто максимальное количество заявок");
			
			$this->XML_ID = $ar_fields['XML_ID'];
			
			// if($this->VREMYA->format('Y-m-d') == date('Y-m-d')){
			// 	if(!OctagramZayavka::add($this)) throw new SystemException("Достигнуто максимальное количество заявок");
			// 	$ar_props['STATUS'] = $this->STATUS = self::$STATUS_WAITING;
			// }

			$zayavka_add = new CIBlockElement;
			$this->ID = $zayavka_add->add($ar_fields);
			if(!$this->ID) throw new SystemException($zayavka_add->LAST_ERROR);

			$this->ticketSend();
		}

		CIBlockElement::SetPropertyValuesEx($this->ID, self::$iblock_id, $ar_props, ['NewElement'=>"Y",'DoNotValidateLists'=>"Y"]);
	}
	
	public static function one($ID){
		self::init();

        $zayavki = self::get(NULL,NULL,$ID);
        return current($zayavki);
    }
	public static function get(\DateTime $data_priyoma=NULL, int $user_id=NULL, $ID=NULL, $STATUS=NULL){
		self::init();

		$filter = [
			'IBLOCK_ID' => self::$iblock_id,
		];
		if($STATUS){
			$filter['PROPERTY_STATUS'] = $STATUS;
		}
		if($ID){
			$filter['ID'] = $ID;
		}
		if($user_id){
			$filter['=PROPERTY_USER_ID'] = $user_id;
		}
		if($data_priyoma){
			$filter['>PROPERTY_VREMYA'] = $data_priyoma->format('Y-m-d')." 00:00:00";
			$filter['<PROPERTY_VREMYA'] = $data_priyoma->format('Y-m-d')." 23:59:59";
		}
	
		return self::getBF($filter);
	}
	public static function getBF($filter = []){
		$zayavki = [];
		$res =	CIBlockElement::GetList(['PROPERTY_VREMYA'=>'ASC'], $filter,false,false,["ID","IBLOCK_ID","NAME","PROPERTY_*"]);
		while($ob = $res->GetNextElement()){
			$ob_fields= $ob->GetFields();
			$ob_props = $ob->GetProperties();
			
			$zayavka = new self;
			
			foreach(self::$iblock_props as $zayavka_prop){
				$op_prop_val = $ob_props[$zayavka_prop['CODE']]['VALUE'];

				$zayavka->{$zayavka_prop['CODE']} = is_array($op_prop_val)
														? implode(", ",$op_prop_val)
														: $op_prop_val;
			}
			$zayavka->ID				= $ob_fields['ID'];
			$zayavka->XML_ID			= $ob_fields['XML_ID'];
			$zayavka->STATUS			= $ob_props['STATUS']['VALUE_ENUM_ID'];
			$zayavka->VID_DOCUMENTA		= $ob_props['VID_DOCUMENTA']['VALUE_ENUM_ID'];
			
			$zayavki[$zayavka->ID] = $zayavka;
			unset($zayavka);
		}
		return $zayavki;
	}

	public static function getProps(){
		self::init();
		return self::$iblock_props;
	}
}

>>>>>>> e0a0eba79 (init)
Zayavka::init();