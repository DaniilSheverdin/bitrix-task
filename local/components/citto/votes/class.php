<?php

namespace Citto\Votes;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use CBitrixComponent;
use CIBlockElement;
use CFile;
use Bitrix\Main\UserTable;
use CIntranetUtils;
use Citto\Mentoring\Users as MentoringUsers;
use Citto\Vaccinationcovid19\Component as MainComponent;
use CModule;
use \Bitrix\Im\Integration\Intranet\Department as Department;
use CGroup;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class Component extends CBitrixComponent
{
    private $arVotes = [];
    private $bIsVoted = null;

    private function getVotes()
    {
        if (!$this->arVotes) {
            $arSelect = [
                "ID",
                "NAME",
                "DATE_CREATE",
                "CREATED_BY",
                "PROPERTY_VARIABLES",
                "PROPERTY_MULTIPLE",
            ];

            $obRecords = CIBlockElement::GetList($arOrder, ['IBLOCK_CODE' => 'VOTES'], false, [], $arSelect);
            $arRecords = [];

            while ($arRecord = $obRecords->fetch()) {
                $arRecords[$arRecord['ID']] = [
                    'NAME'      => $arRecord['NAME'],
                    'MULTIPLE'  => ($arRecord['PROPERTY_MULTIPLE_VALUE']) ? 'Y' : 'N',
                    'VARIABLES' => $arRecord['PROPERTY_VARIABLES_VALUE'],
                ];
            }

            $this->arVotes = $arRecords;
        }

        return $arRecords;
    }

    private function requiredFields($arRequest)
    {
        $sStatus = 'SUCCESS';
        $arVotes = $this->getVotes();
        foreach ($arVotes as $iID => &$arVote) {
            if (!$arRequest[$iID]) {
                $arVote['REQUIRED'] = 'Не пройден опрос';
                $sStatus = 'REQUIRED';
            } else {
                $arVote['CHECKED'] = $arRequest[$iID];
            }
        }

        return [
            'VOTES'  => $arVotes,
            'STATUS' => $sStatus
        ];
    }

    private function getResults($arFilters = [])
    {
        $arSelect = [
            "ID",
            "NAME",
            "DATE_CREATE",
            "CREATED_BY",
            "PROPERTY_USER",
            "PROPERTY_RESULTS",
        ];

        $obRecords = CIBlockElement::GetList($arOrder, array_merge(['IBLOCK_CODE' => 'RESULTS'], $arFilters), false, [], $arSelect);
        $arRecords = [];
        $iCount = 0;

        while ($arRecord = $obRecords->fetch()) {
            $iCount++;
            $arResults = (array)json_decode($arRecord['PROPERTY_RESULTS_VALUE']);

            foreach ($arResults as $iVoteID => $arResult) {
                foreach ($arResult->CHECKED as $iChecked) {
                    if (!$arRecords[$iVoteID][$iChecked]) {
                        $arRecords[$iVoteID][$iChecked] = 1;
                    } else {
                        $arRecords[$iVoteID][$iChecked]++;
                    }
                }
            }

        }

        return [
            'COUNT' => $iCount,
            'ITEMS' => $arRecords
        ];
    }

    private function isVoted()
    {
        if (is_null($this->isVoted)) {
            global $USER;
            $iUserID = $USER->GetID();
            $bIsVoted = ($this->getResults(['PROPERTY_USER' => $iUserID])['COUNT'] > 0) ? true : false;
            $this->isVoted = $bIsVoted;
        }

        return $this->isVoted;
    }

    private function addResults($arVotes)
    {
        global $USER;
        $iUserID = $USER->GetID();

        $arIblock = \CIBlock::GetList(
            [],
            [
                'CODE' => 'RESULTS'
            ],
            true
        )->fetch();

        $arResults = $this->getResults(['PROPERTY_USER' => $iUserID])['ITEMS'];

        if (!$arResults) {
            $iElementID = (new CIBlockElement)->Add([
                    'IBLOCK_ID'       => $arIblock['ID'],
                    'NAME'            => $iUserID,
                    'PROPERTY_VALUES' => [
                        'USER'    => $iUserID,
                        'RESULTS' => json_encode($arVotes)
                    ]
                ]
            );
        }

        return $iElementID;
    }

    private function isShowResult()
    {
        global $USER;
        return ($USER->IsAdmin() || $USER->GetID() == 45);
    }

    public function executeComponent()
    {
        $arRequest = Application::getInstance()->getContext()->getRequest()->getPostList()->getValues();
        if ($arRequest['submit'] == 'y') {
            [
                'VOTES'  => $this->arResult['VOTES'],
                'STATUS' => $this->arResult['STATUS']
            ] = $this->requiredFields($arRequest);

            if ($this->arResult['STATUS'] == 'SUCCESS') {
                $this->addResults($this->arResult['VOTES']);
            }
        } else {
            $this->arResult['VOTES'] = $this->getVotes();
        }

        $this->arResult['RESULTS'] = $this->getResults();
        $this->arResult['IS_VOTED'] = $this->isVoted();
        $this->arResult['SHOW_RESULT'] = $this->isShowResult();

        if ($_GET['page']) {
            $this->includeComponentTemplate("template-{$_GET['page']}");
        } else {
            $this->includeComponentTemplate();
        }
    }
}
