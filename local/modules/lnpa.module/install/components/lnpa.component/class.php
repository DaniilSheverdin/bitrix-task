<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


use Bitrix\Main\Loader;
use Bex\Bbc\Basis;
if (!Loader::includeModule('bex.bbc')) {
    return false;
}

class LnpaComponent extends Basis{
    public function executeMain(){

    }
}