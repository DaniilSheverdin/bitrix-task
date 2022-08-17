<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
use Monolog\Logger;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Contract\Controllerable;
use Monolog\Handler\RotatingFileHandler;

global $APPLICATION;
\Bitrix\Main\Loader::includeModule('citto.filesigner');

class Filesigner extends \CBitrixComponent implements Controllerable
{
    public function configureActions()
    {
        return [
            'SignSave' => [
                'prefilters'  => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),// на тесте не отрабатывает
                ],
                'postfilters' => [],
            ],
        ];
    }

    public function SignSaveAction(
        $sessid,
        $fileid,
        $pos,
        $clearf,
        $check_sign = false
    ) {
        require_once $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";
        $logger = new \Monolog\Logger('SignSaveAction');
        $logger->pushHandler(
            new RotatingFileHandler(
                $_SERVER['DOCUMENT_ROOT'] . '/local/logs/filesigner/SignSaveAction.log',
                30
            )
        );
        $logger->info('$sessid', [$sessid]);
        $logger->info('$fileid', [$fileid]);
        $logger->info('$pos', [$pos]);
        $logger->info('$clearf', [$clearf]);
        $logger->info('$check_sign', [$check_sign]);
        $logger->info('$_REQUEST', [$_REQUEST]);
        $logger->info('$_FILES', [$_FILES]);
        try {
            if (!check_bitrix_sessid()) {
                throw new Exception("Необходимо войти");
            }
            if (empty($_FILES['sign']) || $_FILES['sign']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Файл подписи не передан");
            }
            $file = current(\Citto\Filesigner\Signer::getFiles([$fileid]));
            if (!$file['SIGNED'] || $_REQUEST['double_sign'] == 'true') {
                \Citto\Filesigner\Signer::setSign(
                    $file['ID'],
                    $_FILES['sign']['tmp_name'],
                    $pos,
                    json_decode($clearf, true)?:[],
                    null,
                    ($check_sign == 'true' ? true : false)
                );
            }
        } catch (Exception $exc) {
            $result = new \Bitrix\Main\Result();
            $result->addError(new \Bitrix\Main\Error($exc->getMessage()));
            return \Bitrix\Main\Engine\Response\AjaxJson::createError($result->getErrorCollection());
        }
        return true;
    }

    public function onPrepareComponentParams($arParams)
    {
        return $arParams;
    }

    public function executeComponent()
    {
        set_time_limit(20);
        try {
            $this->arResult['FILES'] = \Citto\Filesigner\Signer::getFiles($this->arParams['~FILES']);
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }

        $this->includeComponentTemplate();
    }
}
