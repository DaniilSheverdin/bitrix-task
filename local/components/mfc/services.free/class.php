<?php

use \Bitrix\Main\Application;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class Component extends CBitrixComponent
{
    private const SERVICE_URL = 'http://172.21.242.121/infobox/getfreedate?forSite=true';

    private $arUIDs = [
        'b3aef181-d0ff-4282-ab19-58c637be3ee6',
        '7bb9219d-8dec-4cbb-b458-a4e4e43e15b4',
        'bacf9a05-f975-4903-8a33-810442f8458a',
        '76054fe2-67e6-4352-a27a-7db081d7f7e4',
        'c75bf1b0-c88e-45f1-987b-722800fcbf00',
        'bec8efa0-ebc7-42e3-9f2d-8ef4facceef2',
        '2ebf57b1-f6fb-4d51-8a8f-3d39417791ff',
        '7f21ca5f-0e45-4b49-bb23-f951451daa96',
        '1a570b30-cdac-4689-9bb5-3214463fb2b2',
        '2adeec94-1c7f-4355-9f4b-35cdfe1b9678',
        'e973f638-0f78-4f53-8ff5-c1232560d8b5',
        '1a871407-2758-43f6-a0ef-db5f32ee7dd7',
        '00d9dfeb-63c3-40cf-a55c-d0df45da879c',
        'a64203ee-0588-4592-94b9-426f0d296e96',
        '35aeab87-fe5e-4bc5-92f7-d2ff9caf7454',
        '479f67d7-344e-47e1-b8f8-926d1a449695',
        '96ff82b9-b959-4330-bc8f-67a28de4f76c',
        'fe2caba6-0e13-454d-a155-777604b7004c',
        '04781841-7b32-4f4a-917e-2f5fbac66229',
        '7b65a555-c19f-46fd-8400-3465f173f38a',
        '4dcb6985-e4e6-4593-9ac3-f139b4687f46',
        '292e44a7-3c67-468a-94a0-c6b2982c0ece',
        '9026ac3d-3b2f-4cec-9666-0c895cfdd824',
        'c342c679-131a-4670-bdd8-4f48868fa6aa',
        '7e9301c0-717b-4518-98fd-74d64546681a',
        '9f62426c-f02b-4430-b8e5-7808a1d70fd4',
        '0eafe49d-408f-4397-92d5-cc153ae02a65',
        'a2155523-6005-48b8-906b-ec55497fc3a3',
        '5e0660f7-3bf2-4b1b-b9bb-6b62c216190f',
        '4dd162e2-55c3-4374-adea-7b5080902ae2',
        '13bf370c-0366-4842-a6b3-feaf29616a65',
        '4cec4b7f-832a-4f0c-b8d9-12da691ab158',
        '1ba90179-b418-4c60-aa28-c502696dee80',
        'f34e1a88-095c-4703-afe5-cb53e5594601',
        'de11f2c0-c942-4ac6-9a63-fdfd19620e54',
        '438c7434-1cfa-47bd-bdde-4a4f39c5d456',
        '2b45b1a7-1f13-411c-8beb-52b6645bfda3',
    ];

    private $arColumns = [
        ['id' => 'UID', 'name' => 'UID', 'default' => false],
        ['id' => 'DATE', 'name' => 'Дата', 'default' => true],
    ];

    private function file_get_contents_curl($url)
    {
        $obCh = curl_init();
        curl_setopt($obCh, CURLOPT_HEADER, 0);
        curl_setopt($obCh, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($obCh, CURLOPT_URL, $url);
        $arData = curl_exec($obCh);
        curl_close($obCh);

        return $arData;
    }

    private function getServices()
    {
        $arServices = [];
        $arRequestUIDs = Application::getInstance()->getContext()->getRequest()->get('uids');
        $this->arUIDs = ($arRequestUIDs) ? $arRequestUIDs : $this->arUIDs;

        foreach ($this->arUIDs as $sUid) {
            $arDates = json_decode($this->file_get_contents_curl(self::SERVICE_URL . '&serviceuuid=' . $sUid), true);

            foreach ($arDates as $sDate) {
                $arServices[]['data'] = [
                    'UID'  => $sUid,
                    'DATE' => $sDate
                ];
            }
        }

        return $arServices;
    }

    public function executeComponent()
    {
        $this->arResult['SERVICES'] = $this->getServices();
        $this->arResult['COLUMNS'] = $this->arColumns;

        $this->includeComponentTemplate();
    }
}
