<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}
CBitrixComponent::includeComponentClass("citto:bizproc");

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Request;

class BizprocAjaxController extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }
}
