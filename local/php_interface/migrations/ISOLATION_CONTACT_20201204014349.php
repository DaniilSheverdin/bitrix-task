<?php

namespace Sprint\Migration;


class ISOLATION_CONTACT_20201204014349 extends Version
{

    protected $description = "Новые свойства";

    public function up() {
        $helper = new HelperManager();

    
            $iblockId = $helper->Iblock()->getIblockIdIfExists('docs_migration','docs');
    
    
                $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Район',
  'ACTIVE' => 'Y',
  'SORT' => '1',
  'CODE' => 'ATT_AREA',
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
      'VALUE' => 'Алексинский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5d66621d778c20bd7f6fa1eea91b6e39',
    ),
    1 => 
    array (
      'VALUE' => 'Арсеньевский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6ea2b2acb3dbc0c09c9cfde30001998a',
    ),
    2 => 
    array (
      'VALUE' => 'Белевский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '590ee2382105bc605824b498b36c8903',
    ),
    3 => 
    array (
      'VALUE' => 'Богородицкий р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e496808a3d614c22bcd6ffdb6200f211',
    ),
    4 => 
    array (
      'VALUE' => 'Веневский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '329ba70d0444ccac117ef731756f2c7a',
    ),
    5 => 
    array (
      'VALUE' => 'Воловский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f82c61761dcce25a125d8f0639c463ce',
    ),
    6 => 
    array (
      'VALUE' => 'Дубенский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'cdf0282f9e826254437333a2d1b3cf33',
    ),
    7 => 
    array (
      'VALUE' => 'Ефремовский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c3140c2f2b55347fe0d964835029e5d9',
    ),
    8 => 
    array (
      'VALUE' => 'Заокский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1bc521861faafbea99b72fa46a84a07f',
    ),
    9 => 
    array (
      'VALUE' => 'Зареченский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1955a86f6c562e3045bbccf3d2a01780',
    ),
    10 => 
    array (
      'VALUE' => 'Каменский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'bd0b8608f49d228ecde79c9426690c92',
    ),
    11 => 
    array (
      'VALUE' => 'Кимовский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1d33b0ab0656dfb89086af1988a81187',
    ),
    12 => 
    array (
      'VALUE' => 'Киреевский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3f1d1eba08631081da0dce13ecfaa80b',
    ),
    13 => 
    array (
      'VALUE' => 'Куркинский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a26bef83734ee5cb66514a9da26260c2',
    ),
    14 => 
    array (
      'VALUE' => 'Ленинский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c4cc83d8c68079b2e229c26c9c57e6ab',
    ),
    15 => 
    array (
      'VALUE' => 'нет района',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '55e0d20d911c2275b7aec33764ed0a82',
    ),
    16 => 
    array (
      'VALUE' => 'Новомосковский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c6976f0427e5fd44bea0ac7e6a53b4fb',
    ),
    17 => 
    array (
      'VALUE' => 'Одоевский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6ea4ea1ac709e6d625b00a0c1eeb6142',
    ),
    18 => 
    array (
      'VALUE' => 'Плавский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '945ef56b302039bbeb6e3617ecfcf059',
    ),
    19 => 
    array (
      'VALUE' => 'Привокзальный р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '9cc98800efe0067df691acbed37eb932',
    ),
    20 => 
    array (
      'VALUE' => 'Пролетарский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1ff2dabedf4e328be15ed3e051c1847c',
    ),
    21 => 
    array (
      'VALUE' => 'Советский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1ef1123a3d7493895cc0ab117b3e645a',
    ),
    22 => 
    array (
      'VALUE' => 'Суворовский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'd879eabe9b19b846d8875cbbd549040d',
    ),
    23 => 
    array (
      'VALUE' => 'Тепло-Огаревский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1c3543a08ecf4d910975302d18c9ffd1',
    ),
    24 => 
    array (
      'VALUE' => 'Тульская обл',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'tulskaya_obl',
    ),
    25 => 
    array (
      'VALUE' => 'Узловский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c21a2291f56828adbd246b25ac541284',
    ),
    26 => 
    array (
      'VALUE' => 'Центральный р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4d86e359d0334e57fcbb27e829fcf67f',
    ),
    27 => 
    array (
      'VALUE' => 'Чернский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6c45789188645fba0fc13f9d186eef3b',
    ),
    28 => 
    array (
      'VALUE' => 'Щекинский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6d7b18676ca3a4e67fc3916e45c8532d',
    ),
    29 => 
    array (
      'VALUE' => 'Ясногорский р-н',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '630ca11669a29c2868dadc7899a76c94',
    ),
  ),
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Населенный пункт',
  'ACTIVE' => 'Y',
  'SORT' => '2',
  'CODE' => 'ATT_CITY',
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
      'VALUE' => 'Алексин',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b701eaf330e0860c7c68800d43a68e9b',
    ),
    1 => 
    array (
      'VALUE' => 'Белев',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '384bc88cadc66fd17fda4ba342ba7f02',
    ),
    2 => 
    array (
      'VALUE' => 'Богородицк',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '660a7b89eb4ae351737b12222471d243',
    ),
    3 => 
    array (
      'VALUE' => 'Болохово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c4f488cf8efa008ea194549da4d6c650',
    ),
    4 => 
    array (
      'VALUE' => 'Венев',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c694a6f74dba398542d59b7b3daa2e49',
    ),
    5 => 
    array (
      'VALUE' => 'д. 1-2 Ивановка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0d8ca296f137f6ae3e2dd2abb588a391',
    ),
    6 => 
    array (
      'VALUE' => 'д. Азаровка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1f976e987bdfdfafd8c7aa87483baee6',
    ),
    7 => 
    array (
      'VALUE' => 'д. Азаровка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '31d3391622e7f4e1a1daf0afb55d5cda',
    ),
    8 => 
    array (
      'VALUE' => 'д. Александровка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e12d82f41617059153029673fde58ba2',
    ),
    9 => 
    array (
      'VALUE' => 'д. Алексеевка (Лидинский с/о)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '51999116714b35ffe54f5faa4b452070',
    ),
    10 => 
    array (
      'VALUE' => 'д. Андреевка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '705552197ac5ef1f9ba570ab7f38b92d',
    ),
    11 => 
    array (
      'VALUE' => 'д. Анишино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '23356347ce3a0bcc76e8d911cd46f0b3',
    ),
    12 => 
    array (
      'VALUE' => 'д. Астапово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '7b1ad6a21efe2a8e936a6c4ead7b2436',
    ),
    13 => 
    array (
      'VALUE' => 'д. Бараново',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '726ccada6d4f2a4b1611cb6fb7694a3d',
    ),
    14 => 
    array (
      'VALUE' => 'д. Барсуки',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0a6662e56b1cd80bb7c2b5e82f3e175e',
    ),
    15 => 
    array (
      'VALUE' => 'д. Бибиково',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '429ef849958abcd99df286358155274a',
    ),
    16 => 
    array (
      'VALUE' => 'д. Богданово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3f57280b01119d9895b2ceb8b8bed489',
    ),
    17 => 
    array (
      'VALUE' => 'д. Богово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '13c960a5d1b4788e8fe748f1f855de22',
    ),
    18 => 
    array (
      'VALUE' => 'д. Болтенки',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '677a74ad1c67829166e5ca5b22aecd1a',
    ),
    19 => 
    array (
      'VALUE' => 'д. Большая Еловая',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5497c74aeff11e43b3f9feb821a212b8',
    ),
    20 => 
    array (
      'VALUE' => 'д. Большая Тросна',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e2ab7599e26439d22165811e606c28c8',
    ),
    21 => 
    array (
      'VALUE' => 'д. Большая Уваровка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a7e3db617a0b159e8a61aea422a3f6d4',
    ),
    22 => 
    array (
      'VALUE' => 'д. Большие Калмыки',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0e700358b3e5226e44e551c60a94a14b',
    ),
    23 => 
    array (
      'VALUE' => 'д. Большие Медведки',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'fc0484d7377536a4be9f376dda0ac976',
    ),
    24 => 
    array (
      'VALUE' => 'д. Большие Плоты',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3e7e161f6c5c22dcbdcaa5bf23bd68bc',
    ),
    25 => 
    array (
      'VALUE' => 'д. Большое Минино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '7d759f551fbae40a1b2fcbbfdce7d0f0',
    ),
    26 => 
    array (
      'VALUE' => 'д. Большое Шелепино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'bae282d53292872f46b0d64f250a572c',
    ),
    27 => 
    array (
      'VALUE' => 'д. Ботвиньево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f3a2fb07ba0df8e7531f343649454bc4',
    ),
    28 => 
    array (
      'VALUE' => 'д. Ботня',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '890c09baa6ec609040dddd64f890db15',
    ),
    29 => 
    array (
      'VALUE' => 'д. Брусяновка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2d6e27458ab51793ea2159e2920d8f89',
    ),
    30 => 
    array (
      'VALUE' => 'д. Булычевка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '59e95a6cc6fa714eacb46954b5551a14',
    ),
    31 => 
    array (
      'VALUE' => 'д. Бураково',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '9a6eb228871c1f5b27aa1e5d9715b638',
    ),
    32 => 
    array (
      'VALUE' => 'д. Варваровка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5d29c2e86b793d01682801dddf01f856',
    ),
    33 => 
    array (
      'VALUE' => 'д. Верхнее Гайково',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a87bc20745d06d5a97506dc706d4a554',
    ),
    34 => 
    array (
      'VALUE' => 'д. Веселево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'af09cb9a00f643e0c904e55459d6fa72',
    ),
    35 => 
    array (
      'VALUE' => 'д. Воловниково',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0f8335001975ac7e9b1e6def73a3ec09',
    ),
    36 => 
    array (
      'VALUE' => 'д. Волхонщино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c16dbd07362a83f74c7d57440d4a7226',
    ),
    37 => 
    array (
      'VALUE' => 'д. Воронцовка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5e8bd655852f381f2056c53e3e885764',
    ),
    38 => 
    array (
      'VALUE' => 'д. Выдумки',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '77d477a74f33ffabdc260bc491ac3f4c',
    ),
    39 => 
    array (
      'VALUE' => 'д. Гамовка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '78d00a275f4b7ba8d37a99b9fec07a4f',
    ),
    40 => 
    array (
      'VALUE' => 'д. Георгиевское',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '00a5ccd63ef70e6b3487f748d6009e2b',
    ),
    41 => 
    array (
      'VALUE' => 'д. Горячкино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6403056e7b0ee513d183a0386f6f7b01',
    ),
    42 => 
    array (
      'VALUE' => 'д. Грецовка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '39234e5bd73bd40595bd63d7fc8a0016',
    ),
    43 => 
    array (
      'VALUE' => 'д. Грецовка (Лазаревское МО)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '28fec98864828c1d4f06b6a075e3ec6e',
    ),
    44 => 
    array (
      'VALUE' => 'д. Давыдово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f4abefad509b4788d32e222cd3469ce8',
    ),
    45 => 
    array (
      'VALUE' => 'д. Денисово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4bb7596f71ec65676d102ef990c756e6',
    ),
    46 => 
    array (
      'VALUE' => 'д. Дубна',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '02e77214b92b8200c8151f9a70fc48df',
    ),
    47 => 
    array (
      'VALUE' => 'д. Егнышевка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1bdcf5371179eb5285eacbaad83a6176',
    ),
    48 => 
    array (
      'VALUE' => 'д. Житово-Дедово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5339d181e41561246d3a6027fa4a617f',
    ),
    49 => 
    array (
      'VALUE' => 'д. Зайцево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c3ae7642ef95cc0ad8b486ae04332432',
    ),
    50 => 
    array (
      'VALUE' => 'д. Заречье',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3a15564e0966d348ccd108437edcca74',
    ),
    51 => 
    array (
      'VALUE' => 'д. Захаровка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ac6aad8e1049b42ecbf7fec94cd02236',
    ),
    52 => 
    array (
      'VALUE' => 'д. Збродово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '531e604f25718d4dd9e2d92684bbdaf8',
    ),
    53 => 
    array (
      'VALUE' => 'д. Ивановка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '7ad6b0459d61a272a7cb67c216719fe1',
    ),
    54 => 
    array (
      'VALUE' => 'д. Ивановка (Ивановская волость)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '961fba6d7c96f3e6794a99111d75bbbe',
    ),
    55 => 
    array (
      'VALUE' => 'д. Ильино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '07afa96f52082cfd5cd2bf95889da132',
    ),
    56 => 
    array (
      'VALUE' => 'д. Иноземка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'd98ff9232c5d2b4d4014756d969feed6',
    ),
    57 => 
    array (
      'VALUE' => 'д. Казначеевка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '032183e549fd8f6cc2981628d387c61e',
    ),
    58 => 
    array (
      'VALUE' => 'д. Калиновка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '66416f1672939a7a1687846f064bea29',
    ),
    59 => 
    array (
      'VALUE' => 'д. Кирзино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f532ba9363cc6c176b1af65965888b14',
    ),
    60 => 
    array (
      'VALUE' => 'д. Клешня',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'dc6d428f8704c8953ed7142bc170cb0c',
    ),
    61 => 
    array (
      'VALUE' => 'д. Колычево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c375c6beff3dc6e279acf5dd9599fd71',
    ),
    62 => 
    array (
      'VALUE' => 'д. Кондрово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c614d6a671c2cd1ffde8e1a468e790ef',
    ),
    63 => 
    array (
      'VALUE' => 'д. Коровики',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6106c16d889ed2180e58115305e2ad04',
    ),
    64 => 
    array (
      'VALUE' => 'д. Крапивенская Слобода',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'dc68e7c6d372dd3616bbe9b23e9167f9',
    ),
    65 => 
    array (
      'VALUE' => 'д. Красное Гремячево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '92d86b130eb6bf690cb510b6d7d4ac47',
    ),
    66 => 
    array (
      'VALUE' => 'д. Красный Холм',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'fcc98221eb64e4ef3956eda7cf2a7d01',
    ),
    67 => 
    array (
      'VALUE' => 'д. Крекшино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1b5c63d4b088ef46d1694585f4f8e1b0',
    ),
    68 => 
    array (
      'VALUE' => 'д. Кресты',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1b56fdd4143e3e7cd3ddc17d5d4b9722',
    ),
    69 => 
    array (
      'VALUE' => 'д. Круглики',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'dec6206f7d766fd0dfc56a0f7ddcaa4a',
    ),
    70 => 
    array (
      'VALUE' => 'д. Круглое',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '92b10be92d762d527b9d2b7179084ddc',
    ),
    71 => 
    array (
      'VALUE' => 'д. Крутое (Ильинский с/о)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a3ffe81f8de695616d25c0758cedbb15',
    ),
    72 => 
    array (
      'VALUE' => 'д. Крюковка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '61eef408114d58b9db2f0e7b7fec2c1e',
    ),
    73 => 
    array (
      'VALUE' => 'д. Крюковка 1',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'dc37e7fcc63f6745ebba1f25ec329c2d',
    ),
    74 => 
    array (
      'VALUE' => 'д. Кугушевские Выселки',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4c1d9199f36aed578d2c0ccf4c859355',
    ),
    75 => 
    array (
      'VALUE' => 'д. Кураково',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b9212eb8542825a5dcc50100f12c87b0',
    ),
    76 => 
    array (
      'VALUE' => 'д. Кытино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '60647a7e206c07bad29875ec3bc40458',
    ),
    77 => 
    array (
      'VALUE' => 'д. Ламоново',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a5e7349d39b3a072e43b01fb84e5e584',
    ),
    78 => 
    array (
      'VALUE' => 'д. Львово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a889f6b42f8ba55fd4e6fc99ca7ef026',
    ),
    79 => 
    array (
      'VALUE' => 'д. Любички',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'd96491314c35f77ca892b2b897db8155',
    ),
    80 => 
    array (
      'VALUE' => 'д. Малахово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'caa2b7eec6d18a538a898bfc086bd5d7',
    ),
    81 => 
    array (
      'VALUE' => 'д. Малахово (МО Иншинское)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '99a89595160e780f556c0f4be91ef288',
    ),
    82 => 
    array (
      'VALUE' => 'д. Малая Корчажка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ab60a0473a4935fa6474377c21ad35f1',
    ),
    83 => 
    array (
      'VALUE' => 'д. Малая Корчажка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '29dfce73bc4f2f243fa1c8b0e486c01d',
    ),
    84 => 
    array (
      'VALUE' => 'д. Малая Огаревка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '73dac4ebc01cad8d54733d72f0eb97e0',
    ),
    85 => 
    array (
      'VALUE' => 'д. Малая Хмелевая',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '7125e01ad801c32a03a02a57bb14e43d',
    ),
    86 => 
    array (
      'VALUE' => 'д. Малое Алитово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2c677868c029f9e232b04616ba8a0297',
    ),
    87 => 
    array (
      'VALUE' => 'д. Медвенка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f4ad951ebe3faffae19df140890afb99',
    ),
    88 => 
    array (
      'VALUE' => 'д. Михайловка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'aad75706658e0832b9f2b6488909750e',
    ),
    89 => 
    array (
      'VALUE' => 'д. Мосюковка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'cad9cda99db448355388553b0df3af1d',
    ),
    90 => 
    array (
      'VALUE' => 'д. Мощены',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '7de5d2273009bd06626e6959e2747e25',
    ),
    91 => 
    array (
      'VALUE' => 'д. Мыза',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3482d4a2627664e461059f4c83d8c8a2',
    ),
    92 => 
    array (
      'VALUE' => 'д. Мыза (Иншинский с/о)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1aa32e5230218870cf091b136424b50d',
    ),
    93 => 
    array (
      'VALUE' => 'д. Нечаевские Выселки',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b064b812d64fc24a95ff87b74e844199',
    ),
    94 => 
    array (
      'VALUE' => 'д. Нижнее Елькино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b912d1eec5e93038e08ab2eae2f841b0',
    ),
    95 => 
    array (
      'VALUE' => 'д. Нижние Присады',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3a328a7e3c704a6d6821643e07f25a3f',
    ),
    96 => 
    array (
      'VALUE' => 'д. Николаевка (Медведский с/о)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'd110568d5762d8ca65526d03da0ba192',
    ),
    97 => 
    array (
      'VALUE' => 'д. Николаевка (Ярославский с/о)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '841a35250a4629f9860e8f69a4baa71f',
    ),
    98 => 
    array (
      'VALUE' => 'д. Новая',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '833e6b829276d1280afa0a64bc33f29f',
    ),
    99 => 
    array (
      'VALUE' => 'д. Новая Дмитриевка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6d8f11ce7e77c513e794383948bf2825',
    ),
    100 => 
    array (
      'VALUE' => 'д. Новоселки',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6facb15b0acbd77480d0cd77af632022',
    ),
    101 => 
    array (
      'VALUE' => 'д. Новоселки (Малаховский с/о)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '298c52c07bd76bb6b543aabacb63de62',
    ),
    102 => 
    array (
      'VALUE' => 'д. Огаревка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '44f757ed1d8280fd59cdd5c220d4e428',
    ),
    103 => 
    array (
      'VALUE' => 'д. Озерки',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '92b5d1fd05d64c55e2219fbcb167b7f3',
    ),
    104 => 
    array (
      'VALUE' => 'д. Павлово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '522e6d9f51a1ed7c6cfb3e6cae0b6d00',
    ),
    105 => 
    array (
      'VALUE' => 'д. Панькино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'bec5f5c6119eedbc98873bdd1e8a059f',
    ),
    106 => 
    array (
      'VALUE' => 'д. Пироговка-Ульяновка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0ba8e4e43aeffaa34473fdebf6807a08',
    ),
    107 => 
    array (
      'VALUE' => 'д. Подиваньково',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'feee56f4d7b435d818dbb9f32e722ee1',
    ),
    108 => 
    array (
      'VALUE' => 'д. Подлозинки',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e8bb09c527e2076d1c2f57b750b90c26',
    ),
    109 => 
    array (
      'VALUE' => 'д. Поречье',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '566fdf1103e56973d6aeb9ee39398f3b',
    ),
    110 => 
    array (
      'VALUE' => 'д. Проскурино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a18f19cf31b1c7600b3fa848eba94de7',
    ),
    111 => 
    array (
      'VALUE' => 'д. Прудное',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f67c6838388c4d883145e5af10262db3',
    ),
    112 => 
    array (
      'VALUE' => 'д. Пушкари',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '36a5163f10cf4e72261dd84c7ba14bdb',
    ),
    113 => 
    array (
      'VALUE' => 'д. Рассылкино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '015563c14464e3150e969e717b2806f4',
    ),
    114 => 
    array (
      'VALUE' => 'д. Ровно',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'd83139e373ae84dc041eec7ec3247f9f',
    ),
    115 => 
    array (
      'VALUE' => 'д. Рогачевка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3656974a1d644b63d6ca9c95bfc3b4d8',
    ),
    116 => 
    array (
      'VALUE' => 'д. Рождествено',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '76594c7e0887d71b5367391bee8c0e59',
    ),
    117 => 
    array (
      'VALUE' => 'д. Русятино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'dd80daf98ccd42bc99f3d1d825a607f1',
    ),
    118 => 
    array (
      'VALUE' => 'д. Саратовка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '99fa54cab12482b89cb3cb91ac7b1ab5',
    ),
    119 => 
    array (
      'VALUE' => 'д. Свинская',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a7f6465f2510eee762f86ee890f1b1a9',
    ),
    120 => 
    array (
      'VALUE' => 'д. Свобода',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '347a14eff0441510d90b7cf3cada1daa',
    ),
    121 => 
    array (
      'VALUE' => 'д. Сеженские Выселки',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'eb655ad9d72ae0fbd80ce512ebc18bc1',
    ),
    122 => 
    array (
      'VALUE' => 'д. Слобода',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3e020d1a2fc634176838e9add0ec49d7',
    ),
    123 => 
    array (
      'VALUE' => 'д. Слобода(Слободский с/о)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'bf9c2a15d665e3beeb00bcd490928159',
    ),
    124 => 
    array (
      'VALUE' => 'д. Слобода(Таратухинский с/о)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'dc9c63f8549a57d957f20ee61cba5d5f',
    ),
    125 => 
    array (
      'VALUE' => 'д. Слободка(Суходольский с/о)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e8fa0a79cb97e253d67c55958579aae6',
    ),
    126 => 
    array (
      'VALUE' => 'д. Соколовка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e6645ac6c3b2bb4efa8dd53e2d94daca',
    ),
    127 => 
    array (
      'VALUE' => 'д. Сретенка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '779bcf06ac311698cb6abe9734ef2055',
    ),
    128 => 
    array (
      'VALUE' => 'д. Стрельцы',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f7c62a2688a71bd226e36e53b1a7a058',
    ),
    129 => 
    array (
      'VALUE' => 'д. Судаково',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '285cbcd37bfa35ed37efa4e2bf08f29f',
    ),
    130 => 
    array (
      'VALUE' => 'д. Тайдаково',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '734b391a88a0d6170b6a03a6fd5da5e9',
    ),
    131 => 
    array (
      'VALUE' => 'д. Тележенка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3092c2b6392fbe2a62053279991cce06',
    ),
    132 => 
    array (
      'VALUE' => 'д. Телятинки',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0364390a9e6c05f14a9f45e1b6393a0f',
    ),
    133 => 
    array (
      'VALUE' => 'д. Темьянь',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5a456ceb4971d337ed394f3a4f38a847',
    ),
    134 => 
    array (
      'VALUE' => 'д. Теряевка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'd038483267d0f09cc753d876ed581a4f',
    ),
    135 => 
    array (
      'VALUE' => 'д. Теряево 1-е',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e9f8feb611360b2b7035aada0ea87b27',
    ),
    136 => 
    array (
      'VALUE' => 'д. Теряево 2-е',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'bc6c2e425e3bd13fd919ef38edace610',
    ),
    137 => 
    array (
      'VALUE' => 'д. Торбеевка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4fae631c7da066fceda859f98a68d134',
    ),
    138 => 
    array (
      'VALUE' => 'д. Торбеевка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '80efb218bc3131fac3954ead56d024bc',
    ),
    139 => 
    array (
      'VALUE' => 'д. Турдей (Двориковское МО)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '48bac1ad2c6881b21c35e96117df0f46',
    ),
    140 => 
    array (
      'VALUE' => 'д. Фатьяново',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e56b89a7c38d43a8792507dfc07c488d',
    ),
    141 => 
    array (
      'VALUE' => 'д. Федоровка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '39b5c15c2abaee895edae9b40d00e49f',
    ),
    142 => 
    array (
      'VALUE' => 'д. Фроловка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '47a29b44b95fd12d5457457985f18991',
    ),
    143 => 
    array (
      'VALUE' => 'д. Харино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '410fa7fb739729dec2cf7ed7ffed4fa5',
    ),
    144 => 
    array (
      'VALUE' => 'д. Харино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ee5c218026ee19ba1d3a98705dd7b21b',
    ),
    145 => 
    array (
      'VALUE' => 'д. Хвошня',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0d528ce58269cf431f4af7306702d64b',
    ),
    146 => 
    array (
      'VALUE' => 'д. Хопилово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'd62ec45796dbd6dc4e64668c04cb656a',
    ),
    147 => 
    array (
      'VALUE' => 'д. Хрипково',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'bc57ef271e9ff7922a64e9272a088998',
    ),
    148 => 
    array (
      'VALUE' => 'д. Цыгановка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'df0b74d9f9cc47d6e94576b6dd00700f',
    ),
    149 => 
    array (
      'VALUE' => 'д. Чегодаево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '38d3c10bd9bd093345df41355d294dd1',
    ),
    150 => 
    array (
      'VALUE' => 'д. Черная Грязь',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4273ce75c91d02dec8f460e7be6cc547',
    ),
    151 => 
    array (
      'VALUE' => 'д. Чернятино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0c5295859480f828307775ee9fc58b34',
    ),
    152 => 
    array (
      'VALUE' => 'д. Шаховское',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a220e05adc2923927d0ae00d46ea3237',
    ),
    153 => 
    array (
      'VALUE' => 'д. Шевелевка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '408423ea10b668a8966b58fba8c12fc8',
    ),
    154 => 
    array (
      'VALUE' => 'д. Шкилевка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '341cde378b363c811c9cc627e24f7230',
    ),
    155 => 
    array (
      'VALUE' => 'д. Юрьево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3fda8a205985511626971fb3d4ce69db',
    ),
    156 => 
    array (
      'VALUE' => 'д. Янчерево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '42011d53a773ddff8bfc24759a0e814f',
    ),
    157 => 
    array (
      'VALUE' => 'д. Ярославка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c619e929fe063cff8a504c308e626aae',
    ),
    158 => 
    array (
      'VALUE' => 'д. Ясеновая',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4e114a675d248a7e962260fec71df9fb',
    ),
    159 => 
    array (
      'VALUE' => 'д. Ясная Поляна',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c04ccf2d1a3464afa1450ba9138fb099',
    ),
    160 => 
    array (
      'VALUE' => 'Донской',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '894d47490b69f25019cba7aea23f64f3',
    ),
    161 => 
    array (
      'VALUE' => 'Ефремов',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '175ad098470012944617d04b9c523b74',
    ),
    162 => 
    array (
      'VALUE' => 'Кимовск',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '44805b711f014403c5b4ba883404fa57',
    ),
    163 => 
    array (
      'VALUE' => 'Киреевск',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '570d9f08224e0f5e33a18c6693c32293',
    ),
    164 => 
    array (
      'VALUE' => 'Липки',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '053912683ee512ecfcccd986ab91495f',
    ),
    165 => 
    array (
      'VALUE' => 'нет города',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f7f383b80b514737f1f6db42b3d78090',
    ),
    166 => 
    array (
      'VALUE' => 'Новомосковск',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a962d0daa9f4e65cd361347b1dc997b4',
    ),
    167 => 
    array (
      'VALUE' => 'п. 342 км',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '485f2133f1127ce18d9e2b9115699243',
    ),
    168 => 
    array (
      'VALUE' => 'п. Авангард',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'cc44a3af09fd360da01b1ca807d0a874',
    ),
    169 => 
    array (
      'VALUE' => 'п. Барсуки',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '569c49409acad6b1a7e638724051287c',
    ),
    170 => 
    array (
      'VALUE' => 'п. Бегичевский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'd6a51a2ae20102776bc1b8630fd8618a',
    ),
    171 => 
    array (
      'VALUE' => 'п. Бельковский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '60b6ed1d7f15d574399c7565a9aef474',
    ),
    172 => 
    array (
      'VALUE' => 'п. Березовский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'cff3480c041f571ffa41c306a0e402a3',
    ),
    173 => 
    array (
      'VALUE' => 'п. Бобрики',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0db5faf287586aa3cbdc274e59d5664b',
    ),
    174 => 
    array (
      'VALUE' => 'п. Боровковский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e776d996ee8bec34ef8b4f34ffc83995',
    ),
    175 => 
    array (
      'VALUE' => 'п. Бородинский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2687e690b2adbdadac78c5c68f860d52',
    ),
    176 => 
    array (
      'VALUE' => 'п. Брусянский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b3fff853d7841776a3ec838d0afa9bf1',
    ),
    177 => 
    array (
      'VALUE' => 'п. Буревестник',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '57dde310987d0ed07c9da0dcf3b0ae75',
    ),
    178 => 
    array (
      'VALUE' => 'п. Бутиково',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e2254ce8c7a1aafe5745ed1a3dbd6fe9',
    ),
    179 => 
    array (
      'VALUE' => 'п. Волово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '46a3af3fba716970f3f70b37f577048f',
    ),
    180 => 
    array (
      'VALUE' => 'п. Восточный',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ecb30719bc06b27ebde534ad3cb7d166',
    ),
    181 => 
    array (
      'VALUE' => 'п. Гвардейский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '29c15acf31061781463f15c320170ac5',
    ),
    182 => 
    array (
      'VALUE' => 'п. Гигант',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '602d33e4586fbe54afed58649887f524',
    ),
    183 => 
    array (
      'VALUE' => 'п. Головеньковский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '43adca96250b4bc90e2060013df03bf1',
    ),
    184 => 
    array (
      'VALUE' => 'п. Головлинский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'df9deffe4054d67ed22e26c338569009',
    ),
    185 => 
    array (
      'VALUE' => 'п. Горбачево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b40db665ef2ee280f8329f93748a992c',
    ),
    186 => 
    array (
      'VALUE' => 'п. Горный',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '60396c0b4d2ba5e78093708275e8b03d',
    ),
    187 => 
    array (
      'VALUE' => 'п. Горьковский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '126dbc41206b1e1dc8c53acee40bb9df',
    ),
    188 => 
    array (
      'VALUE' => 'п. Грибоедово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0b20c407ae6838e8ca251b88a7096134',
    ),
    189 => 
    array (
      'VALUE' => 'п. Грицовский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'facb7d28c15bc036be1ecd3c126bee33',
    ),
    190 => 
    array (
      'VALUE' => 'п. д/о Велегож',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '28c3fb228ca865540445148dc019f310',
    ),
    191 => 
    array (
      'VALUE' => 'п. Дубна',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '42292dec333c1a117faf9647eec6fb73',
    ),
    192 => 
    array (
      'VALUE' => 'п. Дубовка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2734dfb5bd4424ffada304f11c366e61',
    ),
    193 => 
    array (
      'VALUE' => 'п. ж/д Шульгино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '43bf988448da2e015c2021a08bb91666',
    ),
    194 => 
    array (
      'VALUE' => 'п. Ильинка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '9317a29ae7d0a32573ab25c335765fd4',
    ),
    195 => 
    array (
      'VALUE' => 'п. Иншинский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a3003f18131322f85a49275cc7b3826f',
    ),
    196 => 
    array (
      'VALUE' => 'п. Казачка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c58925520b119c2d98e02a4fa7d5944d',
    ),
    197 => 
    array (
      'VALUE' => 'п. Каменецкий',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '8910f03bf78356ebf8f07716ac4e63a6',
    ),
    198 => 
    array (
      'VALUE' => 'п. Кировский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '7623c53c1c0ac8860b322a2332a74452',
    ),
    199 => 
    array (
      'VALUE' => 'п. Козьминский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e97d0207cd8dde3aba2bb1d865ba8860',
    ),
    200 => 
    array (
      'VALUE' => 'п. Комсомольский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2fcaec3fd9388925cb977ea87a8c0748',
    ),
    201 => 
    array (
      'VALUE' => 'п. Красивый',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0901fbdba12ed9c1cf9d17366048cf9a',
    ),
    202 => 
    array (
      'VALUE' => 'п. Красина',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e79ec2f1dae7b810fddbdfb160b2d926',
    ),
    203 => 
    array (
      'VALUE' => 'п. Красногвардеец',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c96e9a2cf266297844a5c961fa912d73',
    ),
    204 => 
    array (
      'VALUE' => 'п. Красный Яр',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '8e4e67e8af88490ffe1dcd108ed5c862',
    ),
    205 => 
    array (
      'VALUE' => 'п. Лазарево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'fd04a3efbeaeb7df8974cffd7cdc0286',
    ),
    206 => 
    array (
      'VALUE' => 'п. Ланьшинский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '553f69e25e8ec225ce007a218eca4670',
    ),
    207 => 
    array (
      'VALUE' => 'п. Липицы',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '7551e557fa41524037f2abeaaa55e1f1',
    ),
    208 => 
    array (
      'VALUE' => 'п. Ломинцевский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2b653293c3e5cb8bebca292ea8f1cc61',
    ),
    209 => 
    array (
      'VALUE' => 'п. Лужковский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '9f542b1e472a4cf05924ae8bb3696ea5',
    ),
    210 => 
    array (
      'VALUE' => 'п. Майский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'dc6e73bfda94c8f24758fa85d68e0385',
    ),
    211 => 
    array (
      'VALUE' => 'п. Маяк',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '491627ae1439543ac305059327b70f0a',
    ),
    212 => 
    array (
      'VALUE' => 'п. Метростроевский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2596b67d24fff4b1d843e1b77942fdab',
    ),
    213 => 
    array (
      'VALUE' => 'п. Механизаторов',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c06cf73ff9f72b433b2abfe60ec8e68b',
    ),
    214 => 
    array (
      'VALUE' => 'п. Мирный',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '502e6a11626ade24beb145dce53ef458',
    ),
    215 => 
    array (
      'VALUE' => 'п. Миротинский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '90815bf0706e4c4b44bcbf6ae66a1197',
    ),
    216 => 
    array (
      'VALUE' => 'п. Мичуринский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e655771b07bf287ca9bf7e551fdd0f60',
    ),
    217 => 
    array (
      'VALUE' => 'п. Молодежный',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c98e885b23e510b28bb2b97218de3b53',
    ),
    218 => 
    array (
      'VALUE' => 'п. Молочные Дворы',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f3bd305a9a953a3cf716ce5e847b0299',
    ),
    219 => 
    array (
      'VALUE' => 'п. Мордвес',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '378635d0bf244a19e939f8fb0456cada',
    ),
    220 => 
    array (
      'VALUE' => 'п. Новая Мыза',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4787f6dd3bb39171866f96a95f3e3c42',
    ),
    221 => 
    array (
      'VALUE' => 'п. Ново-Ревякинский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5a09d8b65ca420daf8ad875a30aae796',
    ),
    222 => 
    array (
      'VALUE' => 'п. Новопетровский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2ee3bcc15ad52c45a38dddf6b854986b',
    ),
    223 => 
    array (
      'VALUE' => 'п. Обидимо',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '791fd9c11ec3341cae4d18fdee2efad1',
    ),
    224 => 
    array (
      'VALUE' => 'п. Огаревка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a0ef616f9b714f71cad265dc8239ee80',
    ),
    225 => 
    array (
      'VALUE' => 'п. Октябрьский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4601f611757158de72976bd84112b7a3',
    ),
    226 => 
    array (
      'VALUE' => 'п. Октябрьский (Зареченский)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '15b188d722504f48d661b4e6835cdba8',
    ),
    227 => 
    array (
      'VALUE' => 'п. Пахомово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0ff1878a54ad41b03db921a19e75a1bb',
    ),
    228 => 
    array (
      'VALUE' => 'п. Первомайский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0250a8b756e9c3afda9aabf1ffdf1a91',
    ),
    229 => 
    array (
      'VALUE' => 'п. Петелино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '217f9a75838cf64e2b8b17c8b653a634',
    ),
    230 => 
    array (
      'VALUE' => 'п. Петровский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '62fd2cc41a8d9d9575d96cc1835c79a7',
    ),
    231 => 
    array (
      'VALUE' => 'п. Победа',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '244abeb90c2b8b5c6682970d051d178b',
    ),
    232 => 
    array (
      'VALUE' => 'п. Победа',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e078d7a87632494b688e5e065300b8d1',
    ),
    233 => 
    array (
      'VALUE' => 'п. Полевой',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3a25ad183900b5b4e53fb3725a6684f5',
    ),
    234 => 
    array (
      'VALUE' => 'п. Пригородный',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '003ea74ad7397daea61588adc0368bd1',
    ),
    235 => 
    array (
      'VALUE' => 'п. Придонье',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '68a2c4348c97936f18e753b8de9516ca',
    ),
    236 => 
    array (
      'VALUE' => 'п. Прилепы',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b23545154b05a9ca572ab7851d2773cd',
    ),
    237 => 
    array (
      'VALUE' => 'п. Приокский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '16d81bae64882c1173e85a90db488e6e',
    ),
    238 => 
    array (
      'VALUE' => 'п. Пристанционный',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e9dfd29946811f3d2ac4c9c3f85f8226',
    ),
    239 => 
    array (
      'VALUE' => 'п. Приупский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'cfb183c988817569e2a455995b8195b6',
    ),
    240 => 
    array (
      'VALUE' => 'п. Прогресс',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2814f4b1814ac2e901c46bfced935524',
    ),
    241 => 
    array (
      'VALUE' => 'п. Пронь',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e775289214e772ac0f8517860621c05b',
    ),
    242 => 
    array (
      'VALUE' => 'п. Раздолье',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2bc9b8d6668894da6f8fffd47c971669',
    ),
    243 => 
    array (
      'VALUE' => 'п. Рассвет',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'fd3234952974845d573844d73ad22f98',
    ),
    244 => 
    array (
      'VALUE' => 'п. Ревякино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '9c00fd3753cba62a9b17399e9784ace3',
    ),
    245 => 
    array (
      'VALUE' => 'п. Ровно',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2657cd2f53e71ee499a3d650fdc10efd',
    ),
    246 => 
    array (
      'VALUE' => 'п. Рождественский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '977556824269a608605011698998253e',
    ),
    247 => 
    array (
      'VALUE' => 'п. Садовый',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0c5efabf35d5992c065b101cba9cf066',
    ),
    248 => 
    array (
      'VALUE' => 'п. Самарский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '499b77af763e17c90bd5f3c9990ccbaf',
    ),
    249 => 
    array (
      'VALUE' => 'п. Санталовский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0433990e86e87d3b4c90ac9b1d54ac70',
    ),
    250 => 
    array (
      'VALUE' => 'п. Северный',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ffc4f47cea417ba277fa04e700743253',
    ),
    251 => 
    array (
      'VALUE' => 'п. Серебряные Ключи',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '267a5041f0dca30e57df7a936c6db352',
    ),
    252 => 
    array (
      'VALUE' => 'п. Сестрики',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3c4a9569fda0d635cb2d7e0e671016b0',
    ),
    253 => 
    array (
      'VALUE' => 'п. Советский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5a9da7390390e678dd9ac0d9f591b5cf',
    ),
    254 => 
    array (
      'VALUE' => 'п. Совхозный',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '62e8f8cfd3f135751146cdea5a6aefb0',
    ),
    255 => 
    array (
      'VALUE' => 'п. Сосновый',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c2d7226ba4d54eabf8b0fc08f7c55815',
    ),
    256 => 
    array (
      'VALUE' => 'п. Социалистический',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2bd2f916b9d99f7cc51752e563f002af',
    ),
    257 => 
    array (
      'VALUE' => 'п. Станция Скуратово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4047b92b762dcbc77d20bcad708a22ae',
    ),
    258 => 
    array (
      'VALUE' => 'п. Степной',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '26d9f014348c853d27f77b9fbba10260',
    ),
    259 => 
    array (
      'VALUE' => 'п. Стрелецкий',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'acdcdefa043e3a2df3a5ca3d47f1a90a',
    ),
    260 => 
    array (
      'VALUE' => 'п. Строительный',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '06ea732dbe0bac4810aa6c7ebfdf0bf0',
    ),
    261 => 
    array (
      'VALUE' => 'п. Стройка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2939c6f8c3641413310a640efc6793f5',
    ),
    262 => 
    array (
      'VALUE' => 'п. Товарковский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '894fee21dcf7c273c8c4c47b04adc314',
    ),
    263 => 
    array (
      'VALUE' => 'п. Торхово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '7d9ee0d1e283feb4739184f468c7fb8f',
    ),
    264 => 
    array (
      'VALUE' => 'п. Туркомплекс "Велегож"',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '67260d34051e652866779c3e2a16cfac',
    ),
    265 => 
    array (
      'VALUE' => 'п. Угольный',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '8dc3849ae00f4ab70597e078a0e9f8ef',
    ),
    266 => 
    array (
      'VALUE' => 'п. Центральный',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0c307a3dd8cf13c4ea1732761962a817',
    ),
    267 => 
    array (
      'VALUE' => 'п. Шатск',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '091664a116bc89ca5a13ef3b8515bb3f',
    ),
    268 => 
    array (
      'VALUE' => 'п. Шахтерский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a58d00b0cee181688f569414d103737a',
    ),
    269 => 
    array (
      'VALUE' => 'п. Шахты 21',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2219f57b7ef25fc18277f50aedbcf6bf',
    ),
    270 => 
    array (
      'VALUE' => 'п. Шахты 24',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ecb1b581e3246f43662fa372e582a22a',
    ),
    271 => 
    array (
      'VALUE' => 'п. Шварцевский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2c2ad680b8597a25f2a357edba839c9d',
    ),
    272 => 
    array (
      'VALUE' => 'п. Шеверняево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f7f4cd0b57f5256a3e91bcae946a9c7d',
    ),
    273 => 
    array (
      'VALUE' => 'п. Ширинский',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'bcaf887d476d4b10e1e852e95afbbf75',
    ),
    274 => 
    array (
      'VALUE' => 'п. Юбилейный',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f40ff3e01ecd1fd00c54a25a9d1f682b',
    ),
    275 => 
    array (
      'VALUE' => 'Плавск',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6ec075d066c82838d560ecc287971957',
    ),
    276 => 
    array (
      'VALUE' => 'с. Алешня',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '9db0c6d0483cf52b6918e7566961a505',
    ),
    277 => 
    array (
      'VALUE' => 'с. Андреевка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '92cce7494fe52c835aa92161ef5b009e',
    ),
    278 => 
    array (
      'VALUE' => 'с. Апухтино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2296bfabe54f5485063385382007b2bb',
    ),
    279 => 
    array (
      'VALUE' => 'с. Архангельское',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '8e96ce22e261af82a86e518ec4996645',
    ),
    280 => 
    array (
      'VALUE' => 'с. Богдановка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '33e3fe24ea59b559cdbc830757a71db2',
    ),
    281 => 
    array (
      'VALUE' => 'с. Болото',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '284afc70aa74fc87ef686765892fc9d4',
    ),
    282 => 
    array (
      'VALUE' => 'с. Бунырево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1de6c9af4270256d5c6f18eadf8c6c92',
    ),
    283 => 
    array (
      'VALUE' => 'с. Бучалки',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '55e458047c48866f537b4054f02dddef',
    ),
    284 => 
    array (
      'VALUE' => 'с. Велегож',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6f8bd3b2beb4ec6777b8b81321e3b9ce',
    ),
    285 => 
    array (
      'VALUE' => 'с. Венев-Монастырь',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3daa7548dde06b2f841f112f7f3ad997',
    ),
    286 => 
    array (
      'VALUE' => 'с. Верхнее Красино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f7d9648ca60792ced9102f50f89ba920',
    ),
    287 => 
    array (
      'VALUE' => 'с. Верхоупье',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'cf09553ff4c77b88c8a1683823f56865',
    ),
    288 => 
    array (
      'VALUE' => 'с. Волово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2d321a231863e9718eb6d08bfdec84bd',
    ),
    289 => 
    array (
      'VALUE' => 'с. Волчья Дубрава',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '50034b6567b7fa2512afc69a270525a4',
    ),
    290 => 
    array (
      'VALUE' => 'с. Воскресенское',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e0d1898d1c1c96414adc6e3a9d468dcd',
    ),
    291 => 
    array (
      'VALUE' => 'с. Вязово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '837f34ee144a93e51c727d6522e6c5a1',
    ),
    292 => 
    array (
      'VALUE' => 'с. Глухие Поляны',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6bfe4810650e2f0aa0057f69f3419cb5',
    ),
    293 => 
    array (
      'VALUE' => 'с. Горшково',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3d157ce6e873d82425240cf0565a1f7f',
    ),
    294 => 
    array (
      'VALUE' => 'с. Дедилово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ecb1a04ac0cee509e2c10d0dd269c731',
    ),
    295 => 
    array (
      'VALUE' => 'с. Денисово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '64287056d46c1a012402941f765952fa',
    ),
    296 => 
    array (
      'VALUE' => 'с. Дмитриевское',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6cff6670c32882e22cf8ccc8fbba2f10',
    ),
    297 => 
    array (
      'VALUE' => 'с. Доброе',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '51f5148bc6d3d6e056be0847ed5ff07e',
    ),
    298 => 
    array (
      'VALUE' => 'с. Долгие Лески',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b14e1037247e5895641a4a0611a865e5',
    ),
    299 => 
    array (
      'VALUE' => 'с. Домнино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '9ee94bb4cd9ff39dabfb599763716b7b',
    ),
    300 => 
    array (
      'VALUE' => 'с. Ержино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'efe5b427a0739c306e6d31edd6c36b8c',
    ),
    301 => 
    array (
      'VALUE' => 'с. Жуково',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '147139778b9f31d6cb31c52b2294977f',
    ),
    302 => 
    array (
      'VALUE' => 'с. Зайцево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ebd5e70ca25e1bcd2ccdd2bac7c537e9',
    ),
    303 => 
    array (
      'VALUE' => 'с. Закопы',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e776e0033bf49f014d31795d0b7701a7',
    ),
    304 => 
    array (
      'VALUE' => 'с. Знаменское',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '957143db6baab3c02feb30f37d7969af',
    ),
    305 => 
    array (
      'VALUE' => 'с. Знаменское',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f712b260bc05429fd9f5ebb22c723304',
    ),
    306 => 
    array (
      'VALUE' => 'с. Иевлево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '43efae41e905ad62566888cf6bdae9ac',
    ),
    307 => 
    array (
      'VALUE' => 'с. Кадное',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '76cc0416fde924ceb98d2046d9c53225',
    ),
    308 => 
    array (
      'VALUE' => 'с. Казначеево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1e806e6a3a3e75671810afc208b2f7b4',
    ),
    309 => 
    array (
      'VALUE' => 'с. Каменка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '58841491900d5730c7b467c2cbee2ee6',
    ),
    310 => 
    array (
      'VALUE' => 'с. Карамышево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '144a421ba0b7eab57a623835950a8e84',
    ),
    311 => 
    array (
      'VALUE' => 'с. Колюпаново',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'fa88705a387a7ab93bd799f94c10298b',
    ),
    312 => 
    array (
      'VALUE' => 'с. Костомарово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2790ea3a80e43668b263dbd9c0a8e0d8',
    ),
    313 => 
    array (
      'VALUE' => 'с. Кочкино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1810ecf39374712cc559a1a25d5f8944',
    ),
    314 => 
    array (
      'VALUE' => 'с. Крапивна',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '32baaf7d8d75bf1bfb0175a7f34b3a21',
    ),
    315 => 
    array (
      'VALUE' => 'с. Красное',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '183af201e5bbf05aba26e0a26a590c37',
    ),
    316 => 
    array (
      'VALUE' => 'с. Краснополье',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'eebaf1198854f52585f493c84213a0c1',
    ),
    317 => 
    array (
      'VALUE' => 'с. Кресты',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '886aa5f8e9020f55246ecf39fab33c16',
    ),
    318 => 
    array (
      'VALUE' => 'с. Кузнецово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '57c78e5e092be6106e307f08118c0b31',
    ),
    319 => 
    array (
      'VALUE' => 'с. Ленино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3bf5bf469717de2fabd65dde40fe9fc8',
    ),
    320 => 
    array (
      'VALUE' => 'с. Лобаново',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6eda710c01649c0e4209972f92f2ab88',
    ),
    321 => 
    array (
      'VALUE' => 'с. Лужны',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'fc8bb34fefd36c7214feb0438278df14',
    ),
    322 => 
    array (
      'VALUE' => 'с. Лутовиново',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'd96fe64a8741ef5a0c7e66afaf2ba86b',
    ),
    323 => 
    array (
      'VALUE' => 'с. Малое Скуратово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b94e13946820e1d6f069a15e4bace2bf',
    ),
    324 => 
    array (
      'VALUE' => 'с. Малое Скуратово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e9d39e195708682c4e4e865c4e207218',
    ),
    325 => 
    array (
      'VALUE' => 'с. Мартемьяново',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '71b33f266ac5c147ff3abf1bda135c54',
    ),
    326 => 
    array (
      'VALUE' => 'с. Маслово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4df620071bbf49b66389aae84679ead0',
    ),
    327 => 
    array (
      'VALUE' => 'с. Меркулово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1b1de8522ff45c58061e702251aec0dd',
    ),
    328 => 
    array (
      'VALUE' => 'с. Мечнянка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0957403b972cfb65b98c923a932a422f',
    ),
    329 => 
    array (
      'VALUE' => 'с. Мясоедово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '138844b80082d1098a8f45dda73d0f41',
    ),
    330 => 
    array (
      'VALUE' => 'с. Ненашево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '38e0c487f6ba8ea515cd94945ab7f746',
    ),
    331 => 
    array (
      'VALUE' => 'с. Непрядва',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c8d8c7aa180725e786ad821af3e65ad6',
    ),
    332 => 
    array (
      'VALUE' => 'с. Николо-Гастунь',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5a241d407a1eaf4823c30ee11a309fcb',
    ),
    333 => 
    array (
      'VALUE' => 'с. Николо-Жупань',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2bdac59ac132d15562ad12fb02704ded',
    ),
    334 => 
    array (
      'VALUE' => 'с. Новое Покровское',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '94e48f56de4ef88d48ba857a6107f606',
    ),
    335 => 
    array (
      'VALUE' => 'с. Новокрасивое',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a2c4c714d3fc0bb1642ce05dc86c3590',
    ),
    336 => 
    array (
      'VALUE' => 'с. Новоселебное',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '722de1be9e907149bfb28f04284a58df',
    ),
    337 => 
    array (
      'VALUE' => 'с. Осиновая Гора',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '93aa9f791858659cad19591a86ef3add',
    ),
    338 => 
    array (
      'VALUE' => 'с. Осиново',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'bef1960a7dc160302ebac25acffa3516',
    ),
    339 => 
    array (
      'VALUE' => 'с. Острецово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6e28c53b2a68738688022537fe465144',
    ),
    340 => 
    array (
      'VALUE' => 'с. Павлов Хутор',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e7af8f2f28c84817a5fca60e41acc0e9',
    ),
    341 => 
    array (
      'VALUE' => 'с. Першино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3e11aa6b7d84e5bcfe19f856aa26ce00',
    ),
    342 => 
    array (
      'VALUE' => 'с. Пожилино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4c4d2f44ab274bb83edd7124c8fa3ac7',
    ),
    343 => 
    array (
      'VALUE' => 'с. Поповка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e48e88a1c5ca4f3d00b29ddab81fcb44',
    ),
    344 => 
    array (
      'VALUE' => 'с. Пришня',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '9c4e075cb7e84311b10154a4fb67713b',
    ),
    345 => 
    array (
      'VALUE' => 'с. Протасово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '82ef52193ca1b3e9c9c1a50a3776787b',
    ),
    346 => 
    array (
      'VALUE' => 'с. Пятницкое',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c1647cd05b3a6a835d6eeb74bbe256a8',
    ),
    347 => 
    array (
      'VALUE' => 'с. Рылево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6c9ad83be859a90925dc14ad30acd283',
    ),
    348 => 
    array (
      'VALUE' => 'с. Селезнево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '9a29eb484cf8f602763c59ba89cb0e71',
    ),
    349 => 
    array (
      'VALUE' => 'с. Селиваново',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '395b7325e6bcc5f30bb50b7d26ed2702',
    ),
    350 => 
    array (
      'VALUE' => 'с. Сенево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6f20b8129577d32cb7a8ceec09ef8d8f',
    ),
    351 => 
    array (
      'VALUE' => 'с. Симоново',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ba1427dc84eea9d384b5be38ebb93e12',
    ),
    352 => 
    array (
      'VALUE' => 'с. Скоморошки',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '8316bdceedeb6996f289153e918f7f07',
    ),
    353 => 
    array (
      'VALUE' => 'с. Слободка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '9515a6f834ef1fc349be3f704ce0aba8',
    ),
    354 => 
    array (
      'VALUE' => 'с. Сныхово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '40c7c30e4f12031a9b19e476ba9a0af9',
    ),
    355 => 
    array (
      'VALUE' => 'с. Сомово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5513ae8dbedc1c2ccc6840bd727e8cad',
    ),
    356 => 
    array (
      'VALUE' => 'с. Сорочинка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'd86fa4c0b332d880dfa3e67f858488b0',
    ),
    357 => 
    array (
      'VALUE' => 'с. Спас-Конино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'd5ad4f05175a76ea23e0b694c4aebeef',
    ),
    358 => 
    array (
      'VALUE' => 'с. Спасское',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '4c7cb4e4cebc192f90e02aecc5730214',
    ),
    359 => 
    array (
      'VALUE' => 'с. Старая Колпна',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f2aba9990374b20bd097278a61858555',
    ),
    360 => 
    array (
      'VALUE' => 'с. Сторожа',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e9ea9c8f5a82fa57c2ae22b329c12676',
    ),
    361 => 
    array (
      'VALUE' => 'с. Страхово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '499dc2dcb05c4f88752e7a77fb451755',
    ),
    362 => 
    array (
      'VALUE' => 'с. Сумароково',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a3e15fcda1a405cc4623985806f0cbb4',
    ),
    363 => 
    array (
      'VALUE' => 'с. Теляково (Теляковская с/т)',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f624872010060d53ffc3f1599bf68f84',
    ),
    364 => 
    array (
      'VALUE' => 'с. Теплое',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '32d2403982767083229ec7da339ef038',
    ),
    365 => 
    array (
      'VALUE' => 'с. Трухачевка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '9c65dca1e794b19f925866874b42b8e6',
    ),
    366 => 
    array (
      'VALUE' => 'с. Урусово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '66b7ac80092a04536bcbccf7081994c1',
    ),
    367 => 
    array (
      'VALUE' => 'с. Ушаково',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '7e2446d1c1ee35607a83bf5cebae337d',
    ),
    368 => 
    array (
      'VALUE' => 'с. Фалдино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '003e11785a09233f531ec14d419f34f8',
    ),
    369 => 
    array (
      'VALUE' => 'с. Федоровка',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ef96fe57b486ad6aba8b1ac76121ae4f',
    ),
    370 => 
    array (
      'VALUE' => 'с. Фомищево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '35ed2d4d71e40ca6d39c3a7c44460883',
    ),
    371 => 
    array (
      'VALUE' => 'с. Хитровщина',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3393913b2c9ca73116e6d73f0a93214c',
    ),
    372 => 
    array (
      'VALUE' => 'с. Хотушь',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'ffaf96fd1d8012a991366edeb8cca206',
    ),
    373 => 
    array (
      'VALUE' => 'с. Хрущево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '7fa7b35b9b3d348266799c40aa6e0555',
    ),
    374 => 
    array (
      'VALUE' => 'с. Царево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3c6f5d02d7a8975798cda89a2816463b',
    ),
    375 => 
    array (
      'VALUE' => 'с. Частое',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2fcef46656db8a268936132361d6bdc7',
    ),
    376 => 
    array (
      'VALUE' => 'с. Шилово',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a92267a3b44360a45c0f58ad3cb103b3',
    ),
    377 => 
    array (
      'VALUE' => 'с. Шульгино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'c237126868058b8472534bcbdd2bbb05',
    ),
    378 => 
    array (
      'VALUE' => 'с. Языково',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '190157d9e555c657ab0e4bd66757c211',
    ),
    379 => 
    array (
      'VALUE' => 'с. Яковлево',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'efb0aeb4bf48c85262a49a5ced3a5da3',
    ),
    380 => 
    array (
      'VALUE' => 'Советск',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '6df60a8484a047e592ea15f0a341bc3e',
    ),
    381 => 
    array (
      'VALUE' => 'Суворов',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '96148afa8c1778934b03a1fecaa15f04',
    ),
    382 => 
    array (
      'VALUE' => 'Тула',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'bf0831defe2acb2bffee05d3f6970a0a',
    ),
    383 => 
    array (
      'VALUE' => 'Узловая',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '98fedf0767a5d3f08bacbdc28b357bd6',
    ),
    384 => 
    array (
      'VALUE' => 'Щекино',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f5228f2b74add42d7fd8e2442d6a2e46',
    ),
    385 => 
    array (
      'VALUE' => 'Ясногорск',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'f1cdf749c0a89b2a8852c0a71238e8da',
    ),
  ),
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Дата внесения данных',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_DATE_ADD_DATA',
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
  'NAME' => '__check_sum',
  'ACTIVE' => 'Y',
  'SORT' => '5000',
  'CODE' => 'ATT_CHECK_SUM',
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
