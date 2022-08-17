<?php

namespace Sprint\Migration;


class Version20200322223400 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

    
            $iblockId = $helper->Iblock()->getIblockIdIfExists('docs_migration','docs');
    
    
                $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Фамилия',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_LAST_NAME',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Именные компоненты',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_NAME',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Пол',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_SEX',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Сведено',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_DATE_SVEDENO',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => 'Date',
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Дата пересечения',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_DATE_PERESECHENIYA',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => 'Date',
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Страна отбытия',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_COUNTRY',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Данные паспорт-центр',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_PASSPORT',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Адрес',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_ADDRESS',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Законный представитель',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_LEGAL_REPRES',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Дата рождения представителя',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_LEGAL_REPRES_DATE',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => 'Date',
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Дата окончания карантина +13',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_LAST_DATE',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => 'Date',
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Адрес представителя',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_ADDRESS_REPRES',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Дата рождения',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_DATE_BIRTHDAY',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => 'Date',
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Дата регистрации с',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_DATE_REG_START',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => 'Date',
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Дата регистрации по',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_DATE_REG_END',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => 'Date',
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Принимающая сторона',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_INCOMING_SIDE',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'ФИО ИГ лат',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_FIO_LAT',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Постановление',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_RESOLUTION',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Дата вручения постановления',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_DATE_RESOLUTION',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => 'Date',
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'ГУЗ наблюдения',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_GUZ_NAV',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'L',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
  'VALUES' => 
  array (
    0 => 
    array (
      'VALUE' => 'ГУЗ "Алексинская районная больница № 1 им. проф. В.Ф.Снегирева" АМЛДЦ',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '7ec9bd5d3ced3a2b9eb53683f1afc851',
    ),
    1 => 
    array (
      'VALUE' => 'ГУЗ "Алексинская районная больница № 1 имени профессора В.Ф. Снегирева" Поликлиника №5',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4fb8759ead66f320173c90fde565a44e',
    ),
    2 => 
    array (
      'VALUE' => 'ГУЗ "Амбулатория п. Рассвет"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4bb70a38e19486253e4b330c38694492',
    ),
    3 => 
    array (
      'VALUE' => 'ГУЗ "Амбулатория п. Рассвет" Амбулаторно-поликлиническое отделение №3',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ffcaab3c96ed885da6210bcbe4c3c56e',
    ),
    4 => 
    array (
      'VALUE' => 'ГУЗ "Белевская центральная районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'cba9cff66b6c3746db4cdce2a4e0a156',
    ),
    5 => 
    array (
      'VALUE' => 'ГУЗ "Богородицкая центральная районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '995a55c0cbfc50ef5b1581362dac221f',
    ),
    6 => 
    array (
      'VALUE' => 'ГУЗ "Богородицкая центральная районная больница" Товарковская сельская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '67369f26b96322f896d2fd63f0d4b174',
    ),
    7 => 
    array (
      'VALUE' => 'ГУЗ "Веневская центральная районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '35b818f5d59f9bac3787d7e810b7d244',
    ),
    8 => 
    array (
      'VALUE' => 'ГУЗ "Воловская центральная районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3668fe2beca960adb84796d6388bc74f',
    ),
    9 => 
    array (
      'VALUE' => 'ГУЗ "ГБ№1 Г.ТУЛЫ)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '53f6d196e247e887167e0b0e19784538',
    ),
    10 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница № 10 г.Тулы" ',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '50fbe5b92c1e43d684253d8c6f62853d',
    ),
    11 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница № 11 г. Тулы"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2c1cc677ef64d905eb438caa529ec41b',
    ),
    12 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница № 3 г. Тулы"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4e2bc05c52976833c8b1c65b3c36c5e4',
    ),
    13 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница № 3 г. Тулы" Филиал №1"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'de8335f17f9321be6bc01c68dddd746b',
    ),
    14 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница № 7 г. Тулы" Поликлиника №3',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f46f0b0401d7d9f315f219e8382a86dc',
    ),
    15 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница № 7" г. Тулы',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5cb94c7896790789046b7ec40d25f532',
    ),
    16 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница № 9 г. Тулы" Поликлиника №2',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'cc8e5188fabb0b8b81b01594db382daa',
    ),
    17 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница № 9 г. Тулы" Поликлиника №3',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '7976cfbf52ba52c4edc3872cb1aa8cc0',
    ),
    18 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница №7" Поликлиника №2',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'd3c8a28d962f1de00c700fa1bfcde48a',
    ),
    19 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница №9 г. Тула" Поликлиника №1',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ddd7e2f0921194e128a27bd154da427d',
    ),
    20 => 
    array (
      'VALUE' => 'ГУЗ "Городская клиническая больница № 2 г. Тулы имени Е.Г. Лазарева" (г. Тула ул. Комсомольская д.1)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'bfd150b3b07d5bf5a03ec243a8d65ffa',
    ),
    21 => 
    array (
      'VALUE' => 'ГУЗ "Городская клиническая больница № 2 г. Тулы имени Е.Г. Лазарева" (г.Тула ул. Лейтейзена д.1)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3259ab14bd1d32447d4afb14f6690b42',
    ),
    22 => 
    array (
      'VALUE' => 'ГУЗ "Городская клиническая больница № 2 г. Тулы имени Е.Г. Лазарева" (г.Тула ул.Дегтярева д.52)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'abf4313288677e430585f2f81e0d655b',
    ),
    23 => 
    array (
      'VALUE' => 'ГУЗ "ДГКБ г. Тулы"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ad625739d3aebf9a6077c3b6d604a905',
    ),
    24 => 
    array (
      'VALUE' => 'ГУЗ "Донская городская больница №1"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f1614f9e5ca0b9cdfd215496f90d6c12',
    ),
    25 => 
    array (
      'VALUE' => 'ГУЗ "Дубенская центральная районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '257b68d60faf2181507db0a925d326ea',
    ),
    26 => 
    array (
      'VALUE' => 'ГУЗ "Ефремовская районная больница имени А.И.Козлова"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e59ad481afacd9432091ce69a97e666f',
    ),
    27 => 
    array (
      'VALUE' => 'ГУЗ "Ефремовская районная больница имени А.И.Козлова" Ступинская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a5845607d551b683c9d83899fad638df',
    ),
    28 => 
    array (
      'VALUE' => 'ГУЗ "Ефремовская районная больница имени А.И.Козлова" филиал №1',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '17377337636804b85eca37b35fc84b16',
    ),
    29 => 
    array (
      'VALUE' => 'ГУЗ "Заокская центральная районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'da64500dd50aa3fae13f7968364f51f7',
    ),
    30 => 
    array (
      'VALUE' => 'ГУЗ "Кимовская ЦРБ"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'fbebd6c2c037d4226214b8efb4e0c32e',
    ),
    31 => 
    array (
      'VALUE' => 'ГУЗ "Куркинская ЦРБ"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '37d8c5c35096f5d090f1bed218b2d4fb',
    ),
    32 => 
    array (
      'VALUE' => 'ГУЗ "Ленинская районная больница" Алешинская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'cffc640a8e513c0bb4e5f5eea9e0c6a9',
    ),
    33 => 
    array (
      'VALUE' => 'ГУЗ "Ленинская районная больница" Рождественская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '80c9ef9ede010961d91ab8096712387f',
    ),
    34 => 
    array (
      'VALUE' => 'ГУЗ "Ленинская РБ"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5db3dc00fe8e2c11ba90292690f6f3d3',
    ),
    35 => 
    array (
      'VALUE' => 'ГУЗ "Ленинская РБ" Поликлиника поселка Шатск',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'd58ae9fe21d9d069cf68766ce18dbb1a',
    ),
    36 => 
    array (
      'VALUE' => 'ГУЗ "Новомосковская городская клиническая больница"  филиал №1',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'be7eb8b1afaf2a510af5e23a380bd852',
    ),
    37 => 
    array (
      'VALUE' => 'ГУЗ "Новомосковская городская клиническая больница" Амбулатория №3 филиал №4',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3e6ff926429c4cf06997ec035786a4c9',
    ),
    38 => 
    array (
      'VALUE' => 'ГУЗ "Новомосковская городская клиническая больница" Поликлиническое отделение №1 филиала №3 ',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'd943b2e38b4a84dd56008e216d055343',
    ),
    39 => 
    array (
      'VALUE' => 'ГУЗ "Новомосковская городская клиническая больница" Поликлиническое отделение №5 филиал №3 ',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'cc3a877064c3f755b582e7f5270e66e1',
    ),
    40 => 
    array (
      'VALUE' => 'ГУЗ "Одоевская центральная районная больница им. П.П.Белоусова"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0b756a70c2f872f8a1c9fbf6b098d806',
    ),
    41 => 
    array (
      'VALUE' => 'ГУЗ "Одоевская центральная районная больница" Амбулатория Тула - 50',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ab9b159cfd4f705f4ad0ac9e4c4ed45c',
    ),
    42 => 
    array (
      'VALUE' => 'ГУЗ "Одоевская центральная районная больница" бывш. Арсеньевская ЦРБ',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e5e49003217db32df9bbadd21cea6828',
    ),
    43 => 
    array (
      'VALUE' => 'ГУЗ "Плавская ЦРБ им. С.С. Гагарина"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '29a6a0d57bef798475e39d4c37008a07',
    ),
    44 => 
    array (
      'VALUE' => 'ГУЗ "Плавская ЦРБ им. С.С. Гагарина" Чернский филиал',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6ba93a4051b9d8ce3861676c81abdcdd',
    ),
    45 => 
    array (
      'VALUE' => 'ГУЗ "Родильный дом № 1 г. Тулы"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ec81960ac0221dad5c1ec409640813ec',
    ),
    46 => 
    array (
      'VALUE' => 'ГУЗ "Суворовская центральная районная больница" Агеевская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'd010aa6c3c6919e34e8fbe704540f24e',
    ),
    47 => 
    array (
      'VALUE' => 'ГУЗ "Суворовская центральная районная больница" Чекалинская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '617aa83a80184763f1fb1e096c955382',
    ),
    48 => 
    array (
      'VALUE' => 'ГУЗ "Суворовская центральная районная больница" Черепетская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '27ccc55d4a654eb541bddeb6e61d5b50',
    ),
    49 => 
    array (
      'VALUE' => 'ГУЗ "Суворовская ЦРБ"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'd016fe490f6fab8ceafbf85d91eff813',
    ),
    50 => 
    array (
      'VALUE' => 'ГУЗ "ТГКБСМП им. Д.Я.Ваныкина" (Бывш. ',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'be884df0d738317f1ef1a8de7274c00e',
    ),
    51 => 
    array (
      'VALUE' => 'ГУЗ "ТГКБСМП им. Д.Я.Ваныкина" Поликлиника для взрослых №2',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6ad71c3bd2bbf26ab04c7fc64a57649d',
    ),
    52 => 
    array (
      'VALUE' => 'ГУЗ "Тепло-Огаревская центральная районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b6d80b7ac0f604cf363ee347986d09d4',
    ),
    53 => 
    array (
      'VALUE' => 'ГУЗ "ТОСП" филиал №8',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '66eeca2fadf9a1ea37107069bd18db0d',
    ),
    54 => 
    array (
      'VALUE' => 'ГУЗ "Тульская областная клиническая больница № 2 им. Л.Н. Толстого" Поликлиническое отделение Филиала № 1',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0f5c6b0589460fecf4f8b4fa7eaed43e',
    ),
    55 => 
    array (
      'VALUE' => 'ГУЗ "Тульский областной госпиталь ветеранов войн и труда"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'fa179ed1077a213719c907f740be67c1',
    ),
    56 => 
    array (
      'VALUE' => 'ГУЗ "Тульский областной перинатальный центр"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4db6bff2da4fb241c3b7c34405862538',
    ),
    57 => 
    array (
      'VALUE' => 'ГУЗ "Узловская районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'acbe60352962b728aa51a992e9834a90',
    ),
    58 => 
    array (
      'VALUE' => 'ГУЗ "Узловская районная больница" Поликлиника №3',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f97012e7eef53c12031032d090f29b32',
    ),
    59 => 
    array (
      'VALUE' => 'ГУЗ "Щекинская районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5b3e1103e9f64e472266d8bc812125de',
    ),
    60 => 
    array (
      'VALUE' => 'ГУЗ "Щекинская районная больница" Филиал №1 ',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c23678593a25abf42b2cb1e1f46e4b06',
    ),
    61 => 
    array (
      'VALUE' => 'ГУЗ "Щекинская районная больница" Филиал №2',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '952f91ac1cbf9109a9b9ef8665803fda',
    ),
    62 => 
    array (
      'VALUE' => 'ГУЗ "Щекинская районная больница" филиал №3',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'bc7fda9f3dad619ecbbc02535ca1ae7b',
    ),
    63 => 
    array (
      'VALUE' => 'ГУЗ "Щекинская районная больница" Филиал №4',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3a219ee3b2ba8d4dd17aeea1dcd1889f',
    ),
    64 => 
    array (
      'VALUE' => 'ГУЗ "Щекинская районная больница" Филиал №5 ',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e784d1d2de7aa331eefb4f48d2fcb554',
    ),
    65 => 
    array (
      'VALUE' => 'ГУЗ "Щекинская районная больница" Филиал №6',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a5aabdf7054ca5e1ab96649842ae6063',
    ),
    66 => 
    array (
      'VALUE' => 'ГУЗ "Ясногорская районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '413bdd7db3c7561b0537842bf105638b',
    ),
    67 => 
    array (
      'VALUE' => 'ГУЗ "Ясногорская районная больница" Денисовская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a3f46d57d5b82536bbd3cbcd32f8ebf0',
    ),
    68 => 
    array (
      'VALUE' => 'ГУЗ "Ясногорская районная больница" Иваньковская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5f0680ec43c60d15468104bd9174c3f8',
    ),
    69 => 
    array (
      'VALUE' => 'ГУЗ "Ясногорская районная больница" Ревякинская городская больница',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '580ab2ef2259059bf41cbc60e94743d9',
    ),
    70 => 
    array (
      'VALUE' => 'Гуз 1',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5e3b601b3f43254dfa36e1d73262f84c',
    ),
    71 => 
    array (
      'VALUE' => 'Гуз 2',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c34716f703cfcc6e905a2df1a5ad272d',
    ),
    72 => 
    array (
      'VALUE' => 'Гуз 3',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b0790437961e7a2c8b4be10b1498f27d',
    ),
    73 => 
    array (
      'VALUE' => 'ГУЗ ТО "Киреевская центральная районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '324aefeccc416ac70392f8d858849c67',
    ),
    74 => 
    array (
      'VALUE' => 'ГУЗ ТО "Киреевская центральная районная больница" Болоховская поликлиника',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '8dd69c633882887e605e09d2a2b2d5ec',
    ),
    75 => 
    array (
      'VALUE' => 'ГУЗ ТО "Киреевская центральная районная больница" Бородинская поликлиника',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '39ce6f6fbae120a06325d592bc55aef0',
    ),
    76 => 
    array (
      'VALUE' => 'ГУЗ ТО "Киреевская центральная районная больница" Липковская поликлиника',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '7c0733b258a963598a98933bcf15af2d',
    ),
    77 => 
    array (
      'VALUE' => 'ГУЗ ТО "Киреевская центральная районная больница" Шварцевская поликлиника',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'dc9b69776aa7255c5674b41198046a8a',
    ),
  ),
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'ГУЗ госпитализация',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_GUZ_HOSP',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'L',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
  'VALUES' => 
  array (
    0 => 
    array (
      'VALUE' => 'ГУЗ "Алексинская районная больница № 1 им. проф. В.Ф.Снегирева" АМЛДЦ',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '69de6a8907083e75949a25423bec597e',
    ),
    1 => 
    array (
      'VALUE' => 'ГУЗ "Алексинская районная больница № 1 имени профессора В.Ф. Снегирева" Поликлиника №5',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '03af38af5a17ad0385d95627769987dd',
    ),
    2 => 
    array (
      'VALUE' => 'ГУЗ "Амбулатория п. Рассвет"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '05359544c5ac4d3f4cc05d4e05ba79aa',
    ),
    3 => 
    array (
      'VALUE' => 'ГУЗ "Амбулатория п. Рассвет" Амбулаторно-поликлиническое отделение №3',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c4cf7e37a286bc64129210b1811f74ba',
    ),
    4 => 
    array (
      'VALUE' => 'ГУЗ "Белевская центральная районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '697eae7724b89f2f0ea59d6ecf506e03',
    ),
    5 => 
    array (
      'VALUE' => 'ГУЗ "Богородицкая центральная районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '24673885b83e5280e8e639c4f0381049',
    ),
    6 => 
    array (
      'VALUE' => 'ГУЗ "Богородицкая центральная районная больница" Товарковская сельская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '7446f2fc6fb83fb146bc48281589a35d',
    ),
    7 => 
    array (
      'VALUE' => 'ГУЗ "Веневская центральная районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ff1455344c05b2cd727973ed9a6f4106',
    ),
    8 => 
    array (
      'VALUE' => 'ГУЗ "Воловская центральная районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'aaad9d04a9e12e57fb5c0e6903a97453',
    ),
    9 => 
    array (
      'VALUE' => 'ГУЗ "ГБ№1 Г.ТУЛЫ)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '28d00a2dbb7dc24e0dc0b2e203ea1a5b',
    ),
    10 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница № 10 г.Тулы" ',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '39e4a3f8bac2fe72d5dd32e1c13ea6fe',
    ),
    11 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница № 11 г. Тулы"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5edefc01c710407acae89806430cb2ca',
    ),
    12 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница № 3 г. Тулы"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6c6186c5f5459e8c3d8bf29cedea256c',
    ),
    13 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница № 3 г. Тулы" Филиал №1"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'fd0a278488ef3d5a80d9f6d5f05f516d',
    ),
    14 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница № 7 г. Тулы" Поликлиника №3',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f0e8a01eb6cb525d369c5c5876ff77fa',
    ),
    15 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница № 7" г. Тулы',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '653fa0b7c0430a2d523850dd77443556',
    ),
    16 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница № 9 г. Тулы" Поликлиника №2',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '490abd2c4d8ec0e670cca2d82c2e2d62',
    ),
    17 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница № 9 г. Тулы" Поликлиника №3',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f291ae88674d0e29a692ff28e321e4e9',
    ),
    18 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница №7" Поликлиника №2',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '8ec7184f92c285d8d84288742256a838',
    ),
    19 => 
    array (
      'VALUE' => 'ГУЗ "Городская больница №9 г. Тула" Поликлиника №1',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e802e7f234f3537e266bc0ee23bc9f90',
    ),
    20 => 
    array (
      'VALUE' => 'ГУЗ "Городская клиническая больница № 2 г. Тулы имени Е.Г. Лазарева" (г. Тула ул. Комсомольская д.1)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3b06c3fcf6ff3accf1c8d68a1e1d5c13',
    ),
    21 => 
    array (
      'VALUE' => 'ГУЗ "Городская клиническая больница № 2 г. Тулы имени Е.Г. Лазарева" (г.Тула ул. Лейтейзена д.1)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '7ffb355ce84ce038575752136f979bfb',
    ),
    22 => 
    array (
      'VALUE' => 'ГУЗ "Городская клиническая больница № 2 г. Тулы имени Е.Г. Лазарева" (г.Тула ул.Дегтярева д.52)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c6e7716ea60c3de752d8a28771d7072f',
    ),
    23 => 
    array (
      'VALUE' => 'ГУЗ "ДГКБ г. Тулы"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b3f20ccf33ac35f071d734d9c43a5902',
    ),
    24 => 
    array (
      'VALUE' => 'ГУЗ "Донская городская больница №1"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ef6ad204f9fef7068dfcdf2bc4f57ee2',
    ),
    25 => 
    array (
      'VALUE' => 'ГУЗ "Дубенская центральная районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5e4887a2615b637a938a2f622d03478f',
    ),
    26 => 
    array (
      'VALUE' => 'ГУЗ "Ефремовская районная больница имени А.И.Козлова"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3a05ba2f3befa2e65afd6c91a9103bcd',
    ),
    27 => 
    array (
      'VALUE' => 'ГУЗ "Ефремовская районная больница имени А.И.Козлова" Ступинская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b59e7595efe492d0c489453fbd7ebd3e',
    ),
    28 => 
    array (
      'VALUE' => 'ГУЗ "Ефремовская районная больница имени А.И.Козлова" филиал №1',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2067bdf0395df393fe2742bb0fdf0be5',
    ),
    29 => 
    array (
      'VALUE' => 'ГУЗ "Заокская центральная районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b3889f52e35645682a6b1e57847dd199',
    ),
    30 => 
    array (
      'VALUE' => 'ГУЗ "Кимовская ЦРБ"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '8eff65025a11c6bc161ee9c2ab0c8456',
    ),
    31 => 
    array (
      'VALUE' => 'ГУЗ "Куркинская ЦРБ"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '568d588c4c12f3687ec03c94947eec2a',
    ),
    32 => 
    array (
      'VALUE' => 'ГУЗ "Ленинская районная больница" Алешинская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1bb88e97729e19803d2599077a5990da',
    ),
    33 => 
    array (
      'VALUE' => 'ГУЗ "Ленинская районная больница" Рождественская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'df4a43788506335ecb8e6be6be6480a6',
    ),
    34 => 
    array (
      'VALUE' => 'ГУЗ "Ленинская РБ"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '7c74ed036eed4d7894a471884a970fcc',
    ),
    35 => 
    array (
      'VALUE' => 'ГУЗ "Ленинская РБ" Поликлиника поселка Шатск',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6df1260de77e4c7d81b4e8984c74d251',
    ),
    36 => 
    array (
      'VALUE' => 'ГУЗ "Новомосковская городская клиническая больница"  филиал №1',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '243a7f269f6cffb5143535283e0e09fc',
    ),
    37 => 
    array (
      'VALUE' => 'ГУЗ "Новомосковская городская клиническая больница" Амбулатория №3 филиал №4',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c8e253e76e6c543c37605085490d44c1',
    ),
    38 => 
    array (
      'VALUE' => 'ГУЗ "Новомосковская городская клиническая больница" Поликлиническое отделение №1 филиала №3 ',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3b5b199920e391cc532d80e65bcd7000',
    ),
    39 => 
    array (
      'VALUE' => 'ГУЗ "Новомосковская городская клиническая больница" Поликлиническое отделение №5 филиал №3 ',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5c6f5cec44031a31e97db468f5c00fd1',
    ),
    40 => 
    array (
      'VALUE' => 'ГУЗ "Одоевская центральная районная больница им. П.П.Белоусова"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '33938ac0765f5c9aed952d5d2e734716',
    ),
    41 => 
    array (
      'VALUE' => 'ГУЗ "Одоевская центральная районная больница" Амбулатория Тула - 50',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'bcb79e2a4dee8813c09d6b59e1ada270',
    ),
    42 => 
    array (
      'VALUE' => 'ГУЗ "Одоевская центральная районная больница" бывш. Арсеньевская ЦРБ',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'eeda4f1b81ae22e8e8d89b5e346a4904',
    ),
    43 => 
    array (
      'VALUE' => 'ГУЗ "Плавская ЦРБ им. С.С. Гагарина"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4efcc73d74c0f6ea89c0d078a1da4d8a',
    ),
    44 => 
    array (
      'VALUE' => 'ГУЗ "Плавская ЦРБ им. С.С. Гагарина" Чернский филиал',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '71c806721331a33fd413eab3cd4e0713',
    ),
    45 => 
    array (
      'VALUE' => 'ГУЗ "Родильный дом № 1 г. Тулы"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6d75703b8d976cd0cec24844f71e6c79',
    ),
    46 => 
    array (
      'VALUE' => 'ГУЗ "Суворовская центральная районная больница" Агеевская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3d68e0b328069eac019caad4f94e4640',
    ),
    47 => 
    array (
      'VALUE' => 'ГУЗ "Суворовская центральная районная больница" Чекалинская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b10d50d9c48f23667396ac023a214820',
    ),
    48 => 
    array (
      'VALUE' => 'ГУЗ "Суворовская центральная районная больница" Черепетская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '915b93236a3d319776ef93c81b183d06',
    ),
    49 => 
    array (
      'VALUE' => 'ГУЗ "Суворовская ЦРБ"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'da0bd615c3fde2e854873dd9b8f93f74',
    ),
    50 => 
    array (
      'VALUE' => 'ГУЗ "ТГКБСМП им. Д.Я.Ваныкина" (Бывш. ',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5a17497adda162709478ceba20b0bc2b',
    ),
    51 => 
    array (
      'VALUE' => 'ГУЗ "ТГКБСМП им. Д.Я.Ваныкина" Поликлиника для взрослых №2',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a0f4b7d9969dd82557c95c33d2081b09',
    ),
    52 => 
    array (
      'VALUE' => 'ГУЗ "Тепло-Огаревская центральная районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ea196b3d1c57639a7a08640b284c3868',
    ),
    53 => 
    array (
      'VALUE' => 'ГУЗ "ТОСП" филиал №8',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0bf012dc308c3544ee5454b6ccdefd10',
    ),
    54 => 
    array (
      'VALUE' => 'ГУЗ "Тульская областная клиническая больница № 2 им. Л.Н. Толстого" Поликлиническое отделение Филиала № 1',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'dfc751282c06c1347c9ec1ce03b660b0',
    ),
    55 => 
    array (
      'VALUE' => 'ГУЗ "Тульский областной госпиталь ветеранов войн и труда"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a82dcb4ddaf3332c05e197a293777b1e',
    ),
    56 => 
    array (
      'VALUE' => 'ГУЗ "Тульский областной перинатальный центр"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'cef2374f2a43d49068b80bac99cb4fec',
    ),
    57 => 
    array (
      'VALUE' => 'ГУЗ "Узловская районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c838bc777cdf5a09611dafca2cc1e67d',
    ),
    58 => 
    array (
      'VALUE' => 'ГУЗ "Узловская районная больница" Поликлиника №3',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5349e80dd3c8a3751b02cd18b6c77560',
    ),
    59 => 
    array (
      'VALUE' => 'ГУЗ "Щекинская районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '42f0a7d01358b494a1eb99b0af4ba222',
    ),
    60 => 
    array (
      'VALUE' => 'ГУЗ "Щекинская районная больница" Филиал №1 ',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e463b73589346082bd2a1c09f5768c93',
    ),
    61 => 
    array (
      'VALUE' => 'ГУЗ "Щекинская районная больница" Филиал №2',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ce9077d3a9338f2d47643957b91eb849',
    ),
    62 => 
    array (
      'VALUE' => 'ГУЗ "Щекинская районная больница" филиал №3',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b6d744b3e513b87fa834f1a7ff072e12',
    ),
    63 => 
    array (
      'VALUE' => 'ГУЗ "Щекинская районная больница" Филиал №4',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a13dfadd859dc6e227cc0ab1a593c363',
    ),
    64 => 
    array (
      'VALUE' => 'ГУЗ "Щекинская районная больница" Филиал №5 ',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '832551668c4d597b9548b85ea1a91cc4',
    ),
    65 => 
    array (
      'VALUE' => 'ГУЗ "Щекинская районная больница" Филиал №6',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '15509364cfedc8b534b3b12ef46831a2',
    ),
    66 => 
    array (
      'VALUE' => 'ГУЗ "Ясногорская районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4325b98defa04e673fd70f60802f4a54',
    ),
    67 => 
    array (
      'VALUE' => 'ГУЗ "Ясногорская районная больница" Денисовская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'cad66b39eda9aeead5eac4198193150b',
    ),
    68 => 
    array (
      'VALUE' => 'ГУЗ "Ясногорская районная больница" Иваньковская амбулатория',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '99fe9e0ca17c9ee59a2f76b25159131c',
    ),
    69 => 
    array (
      'VALUE' => 'ГУЗ "Ясногорская районная больница" Ревякинская городская больница',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2caa9373ad3d3053e7a714ee03516121',
    ),
    70 => 
    array (
      'VALUE' => 'Гуз 1',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a34f4ebba824d8de96c42cbfe383c416',
    ),
    71 => 
    array (
      'VALUE' => 'Гуз 1',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '8b93a43b6546190e12e86c31fb572c24',
    ),
    72 => 
    array (
      'VALUE' => 'Гуз 1',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3b3d006ebee228115efac44a20db0fb0',
    ),
    73 => 
    array (
      'VALUE' => 'ГУЗ ТО "Киреевская центральная районная больница"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6e065b70826ac9b264e948e6491f3443',
    ),
    74 => 
    array (
      'VALUE' => 'ГУЗ ТО "Киреевская центральная районная больница" Болоховская поликлиника',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4251a2a1eee6555289de8dc111724d06',
    ),
    75 => 
    array (
      'VALUE' => 'ГУЗ ТО "Киреевская центральная районная больница" Бородинская поликлиника',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '04fedd66997b441ba06a73668a069fb5',
    ),
    76 => 
    array (
      'VALUE' => 'ГУЗ ТО "Киреевская центральная районная больница" Липковская поликлиника',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '71aaf5823ea07cc61ebdbaadcce27ae0',
    ),
    77 => 
    array (
      'VALUE' => 'ГУЗ ТО "Киреевская центральная районная больница" Шварцевская поликлиника',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '65d1067af10b29a8b89e5b03d02224d4',
    ),
  ),
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Дата 1',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_D1',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => 'Date',
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Результат 1',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_R1',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Дата 2',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_D2',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => 'Date',
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Результат 2',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_R2',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Дата 3',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_D3',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => 'Date',
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Результат 3',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_R3',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Эпид.анамнез',
  'ACTIVE' => 'Y',
  'SORT' => '900',
  'CODE' => 'ATT_EPID',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Степень кантакта',
  'ACTIVE' => 'Y',
  'SORT' => '900',
  'CODE' => 'ATT_STEP',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Место работы контактного',
  'ACTIVE' => 'Y',
  'SORT' => '900',
  'CODE' => 'ATT_WORK',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Должность контактного',
  'ACTIVE' => 'Y',
  'SORT' => '900',
  'CODE' => 'ATT_POSITION',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Место контакта',
  'ACTIVE' => 'Y',
  'SORT' => '900',
  'CODE' => 'ATT_PLACE_CONTACT',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Дата начала карантина',
  'ACTIVE' => 'Y',
  'SORT' => '900',
  'CODE' => 'ATT_DATE_QUARANT',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => 'Date',
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Диагноз',
  'ACTIVE' => 'Y',
  'SORT' => '900',
  'CODE' => 'ATT_DIAGN',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
        
    
    }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
