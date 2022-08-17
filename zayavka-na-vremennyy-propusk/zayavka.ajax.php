<<<<<<< HEAD
<?
use Bitrix\Main\SystemException;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$REQUEST = \Bitrix\Main\Application::getInstance()->getContext()->getRequest(); 
if($REQUEST->isPost() && $REQUEST->get('zayavka-na-vremennyy-propusk-action')){
	$resp = (object)[
		 'code'    => "ERROR",
		 'message' => "Произошла ошибка, попробуйте позже2"
	];
	$APPLICATION->RestartBuffer();
	try{
		if(!check_bitrix_sessid()) throw new SystemException("Сессия просрочена, обновите страницу");

		if($REQUEST->get('zayavka-na-vremennyy-propusk-action') == "add"){
			$zayavka = new Zayavka;
			$zayavka->USER_ID             	= $USER->GetID();
			$zayavka->FIO_KOMU				= trim($REQUEST->get('FIO_KOMU'));
			$zayavka->VID_DOCUMENTA			= trim($REQUEST->get('VID_DOCUMENTA'));
			$zayavka->NOMER_DOCUMENTA		= trim($REQUEST->get('NOMER_DOCUMENTA'));
			$zayavka->FIO_PODAVSHEGO		= trim($USER->getFullName());
			$zayavka->DOLJNOST_PODAVSHEGO	= trim($REQUEST->get('DOLJNOST_PODAVSHEGO'));
			$zayavka->K_KOMU				= trim($REQUEST->get('K_KOMU'));
			$zayavka->KABINET				= trim($REQUEST->get('KABINET'));

			$DATE		= NULL;
			$VREMYA		= NULL;
			
			if(!preg_match("/^(?<Y>[0-9]{4})-(?<m>[0-9]{2})-(?<d>[0-9]{2})$/",$REQUEST->get('DATE'),$DATE)) throw new SystemException("Формат даты неверен");
			if(!preg_match("/^(?<H>[0-9]{2}):(?<i>[0-9]{2})$/",$REQUEST->get('VREMYA'),$VREMYA)) throw new SystemException("Формат времени неверен");

			$zayavka->VREMYA->setDate($DATE['Y'],$DATE['m'],$DATE['d']);
			$zayavka->VREMYA->setTime($VREMYA['H'],$VREMYA['i']);

			$zayavka->save();

			$resp->message = '<h3>Заявка добавлена</h3>';
			$resp->code = "OK";
		}
		if($REQUEST->get('zayavka-na-vremennyy-propusk-action') == "cancel"){

			$PASSWORD_SALT = substr($USER->GetParam('PASSWORD_HASH'),0,8);

			if($USER->GetParam('PASSWORD_HASH') != $PASSWORD_SALT.md5($PASSWORD_SALT.$REQUEST->get('password'))) throw new SystemException("Пароль введен неверно");
			
			$zayavka = Zayavka::one($REQUEST->get('ID'));
			if(!$zayavka) throw new SystemException("Заявка не найдена");
			if($zayavka->USER_ID != $USER->GetID()) throw new SystemException("Заявка не найдена среди Ваших");

			$zayavka->cancel();

			$resp->code = "OK";
			$resp->message = "Успешно";
		}
	}catch(SystemException $exc){
		 $resp->message = $exc->getMessage();
	}
	header('Content-type:application/json');
	echo json_encode($resp);
	die;
=======
<?
use Bitrix\Main\SystemException;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$REQUEST = \Bitrix\Main\Application::getInstance()->getContext()->getRequest(); 
if($REQUEST->isPost() && $REQUEST->get('zayavka-na-vremennyy-propusk-action')){
	$resp = (object)[
		 'code'    => "ERROR",
		 'message' => "Произошла ошибка, попробуйте позже2"
	];
	$APPLICATION->RestartBuffer();
	try{
		if(!check_bitrix_sessid()) throw new SystemException("Сессия просрочена, обновите страницу");

		if($REQUEST->get('zayavka-na-vremennyy-propusk-action') == "add"){
			$zayavka = new Zayavka;
			$zayavka->USER_ID             	= $USER->GetID();
			$zayavka->FIO_KOMU				= trim($REQUEST->get('FIO_KOMU'));
			$zayavka->VID_DOCUMENTA			= trim($REQUEST->get('VID_DOCUMENTA'));
			$zayavka->NOMER_DOCUMENTA		= trim($REQUEST->get('NOMER_DOCUMENTA'));
			$zayavka->FIO_PODAVSHEGO		= trim($USER->getFullName());
			$zayavka->DOLJNOST_PODAVSHEGO	= trim($REQUEST->get('DOLJNOST_PODAVSHEGO'));
			$zayavka->K_KOMU				= trim($REQUEST->get('K_KOMU'));
			$zayavka->KABINET				= trim($REQUEST->get('KABINET'));

			$DATE		= NULL;
			$VREMYA		= NULL;
			
			if(!preg_match("/^(?<Y>[0-9]{4})-(?<m>[0-9]{2})-(?<d>[0-9]{2})$/",$REQUEST->get('DATE'),$DATE)) throw new SystemException("Формат даты неверен");
			if(!preg_match("/^(?<H>[0-9]{2}):(?<i>[0-9]{2})$/",$REQUEST->get('VREMYA'),$VREMYA)) throw new SystemException("Формат времени неверен");

			$zayavka->VREMYA->setDate($DATE['Y'],$DATE['m'],$DATE['d']);
			$zayavka->VREMYA->setTime($VREMYA['H'],$VREMYA['i']);

			$zayavka->save();

			$resp->message = '<h3>Заявка добавлена</h3>';
			$resp->code = "OK";
		}
		if($REQUEST->get('zayavka-na-vremennyy-propusk-action') == "cancel"){

			$PASSWORD_SALT = substr($USER->GetParam('PASSWORD_HASH'),0,8);

			if($USER->GetParam('PASSWORD_HASH') != $PASSWORD_SALT.md5($PASSWORD_SALT.$REQUEST->get('password'))) throw new SystemException("Пароль введен неверно");
			
			$zayavka = Zayavka::one($REQUEST->get('ID'));
			if(!$zayavka) throw new SystemException("Заявка не найдена");
			if($zayavka->USER_ID != $USER->GetID()) throw new SystemException("Заявка не найдена среди Ваших");

			$zayavka->cancel();

			$resp->code = "OK";
			$resp->message = "Успешно";
		}
	}catch(SystemException $exc){
		 $resp->message = $exc->getMessage();
	}
	header('Content-type:application/json');
	echo json_encode($resp);
	die;
>>>>>>> e0a0eba79 (init)
}