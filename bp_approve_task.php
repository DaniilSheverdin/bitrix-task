<<<<<<< HEAD
<?php
define('NEED_AUTH', false);
define('NOT_CHECK_PERMISSIONS', true);
define('NO_MB_CHECK', true);
define('BP_IBLOCK', 484);
define('BP_TEMPLATE_ID', 365);

require $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php";
$APPLICATION->SetTitle("Согласование БП");
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?=$APPLICATION->GetTitle()?></title>
</head>
<body>
<?
try{
     \Bitrix\Main\Loader::includeModule("bizproc");
     $status   = isset($_GET['approve'])?(CBPTaskUserStatus::Yes):(CBPTaskUserStatus::No);
     $token    = NULL;
     $TASK_ID  = NULL;
     $USER_ID  = NULL;

     if(empty($_GET['token'])) throw new Exception("Ссылка недействительна");
     $token = $GLOBALS['bp_deccc']($_GET['token']);
     if(empty($token)) throw new Exception("Ссылка недействительна");
     $token = explode("_", $token, 2);
     if(count($token) != 2) throw new Exception("Ссылка недействительна");
     
     $TASK_ID = (int)$token[0];
     $USER_ID = (int)$token[1];
     if($TASK_ID < 0 || $USER_ID < 0) throw new Exception("Ссылка недействительна");

     $task = CBPTaskService::GetList(
          [],
          [
               'ID'           => $TASK_ID,
               'USER_ID'      => $USER_ID,
               'STATUS'       => CBPTaskStatus::Running,
               'USER_STATUS'  => CBPTaskUserStatus::Waiting,
          ],
          false,
          ['nTopCount'=>1],
          ['ID', 'NAME', 'WORKFLOW_ID', 'ACTIVITY', 'ACTIVITY_NAME', 'IS_INLINE']
     )->fetch();

     if(!$task) throw new Exception("Ссылка устарела");
     
     // $APPLICATION->SetTitle($task['NAME']);

     $taskErrors = [];
     CBPDocument::PostTaskForm($task, $USER_ID, ['INLINE_USER_STATUS' => $status], $taskErrors);
     if(!empty($taskErrors)){
          throw new Exception(array_reduce($taskErrors, function($carry, $item){ return $carry.$item['message']."."; }, ""));
     }
     ?>
     <div style="font-family:sans-serif">
          <h2><?=$task['NAME']?></h2>
          <p>
               <?if($status == CBPTaskUserStatus::Yes):?>
                    <div style="color:green">Согласовано</div>
               <?else:?>
                    <div style="color:red">Не согласовано</div>
               <?endif;?>
          </p>
          <p>
               <small>Страницу можно закрыть</small>
          </p>
     </div>
     <?
}catch(Exception $exc){
     echo $exc->getMessage();
     // $APPLICATION->SetTitle($exc->getMessage());
}
?>
</body>
</html>
<?
=======
<?php
define('NEED_AUTH', false);
define('NOT_CHECK_PERMISSIONS', true);
define('NO_MB_CHECK', true);
define('BP_IBLOCK', 484);
define('BP_TEMPLATE_ID', 365);

require $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php";
$APPLICATION->SetTitle("Согласование БП");
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?=$APPLICATION->GetTitle()?></title>
</head>
<body>
<?
try{
     \Bitrix\Main\Loader::includeModule("bizproc");
     $status   = isset($_GET['approve'])?(CBPTaskUserStatus::Yes):(CBPTaskUserStatus::No);
     $token    = NULL;
     $TASK_ID  = NULL;
     $USER_ID  = NULL;

     if(empty($_GET['token'])) throw new Exception("Ссылка недействительна");
     $token = $GLOBALS['bp_deccc']($_GET['token']);
     if(empty($token)) throw new Exception("Ссылка недействительна");
     $token = explode("_", $token, 2);
     if(count($token) != 2) throw new Exception("Ссылка недействительна");
     
     $TASK_ID = (int)$token[0];
     $USER_ID = (int)$token[1];
     if($TASK_ID < 0 || $USER_ID < 0) throw new Exception("Ссылка недействительна");

     $task = CBPTaskService::GetList(
          [],
          [
               'ID'           => $TASK_ID,
               'USER_ID'      => $USER_ID,
               'STATUS'       => CBPTaskStatus::Running,
               'USER_STATUS'  => CBPTaskUserStatus::Waiting,
          ],
          false,
          ['nTopCount'=>1],
          ['ID', 'NAME', 'WORKFLOW_ID', 'ACTIVITY', 'ACTIVITY_NAME', 'IS_INLINE']
     )->fetch();

     if(!$task) throw new Exception("Ссылка устарела");
     
     // $APPLICATION->SetTitle($task['NAME']);

     $taskErrors = [];
     CBPDocument::PostTaskForm($task, $USER_ID, ['INLINE_USER_STATUS' => $status], $taskErrors);
     if(!empty($taskErrors)){
          throw new Exception(array_reduce($taskErrors, function($carry, $item){ return $carry.$item['message']."."; }, ""));
     }
     ?>
     <div style="font-family:sans-serif">
          <h2><?=$task['NAME']?></h2>
          <p>
               <?if($status == CBPTaskUserStatus::Yes):?>
                    <div style="color:green">Согласовано</div>
               <?else:?>
                    <div style="color:red">Не согласовано</div>
               <?endif;?>
          </p>
          <p>
               <small>Страницу можно закрыть</small>
          </p>
     </div>
     <?
}catch(Exception $exc){
     echo $exc->getMessage();
     // $APPLICATION->SetTitle($exc->getMessage());
}
?>
</body>
</html>
<?
>>>>>>> e0a0eba79 (init)
die;