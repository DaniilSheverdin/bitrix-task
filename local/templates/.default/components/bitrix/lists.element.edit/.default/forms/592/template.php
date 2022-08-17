<?php

use Bitrix\Main\Loader;
use Sprint\Migration\Helpers\IblockHelper;
use \Citto\Integration\Source1C;

define('HAS_VALID_SIGN_DA', "7ac04da6b38ca6e3bb79f695fae02939");
define('BP_POLUCHAET_LICHNO_DA', "7296da8e18e0538517e5fffd09ec57cf");
define('ZAYAVITEL_RUKOVODITEL', "d5463629c45bee6d0fb86c348b458ea8");
$template2Folder = $templateFolder . "/forms/" . $arResult['IBLOCK']['ID'];

Loader::includeModule('sprint.migration');

$this->addExternalCss('/bitrix/templates/.default/bootstrap.min.css');
$this->addExternalCss($template2Folder . '/style.css');
$this->addExternalJS('/bitrix/templates/.default/jquery.min.js');
$this->addExternalJS('/bitrix/templates/.default/bootstrap.min.js');
$this->addExternalJS(
    '/local/components/citto/filesigner/templates/.default/js/cadesplugin_api.js'
);
if (defined('DEV_SERVER')) {
    $this->addExternalJS('https://unpkg.com/react@16/umd/react.development.js');
    $this->addExternalJS(
        'https://unpkg.com/react-dom@16/umd/react-dom.development.js'
    );
} else {
    $this->addExternalJS($templateFolder . "/react.production.min.js");
    $this->addExternalJS($templateFolder . "/react-dom.production.min.js");
}
$this->addExternalJS($templateFolder . "/forms/common/form.js");
$this->addExternalJs($template2Folder . '/scripts.js');

$helper = new IblockHelper();
$iblockIdOrg = $helper->getIblockId('departments', 'structure');

$getOrganization = function ($currSect, $lvlCount) use ($iblockIdOrg) {
    $lvl = $lvlCount - 2;
    $lvl = $lvl > 0 ? $lvl : 1;

    $prevSectData = [];
    $setSect = $currSect;

    while ($lvl > 0) {
        if (isset($arSectData)) {
            $prevSectData = $arSectData;
        }

        $arSectData = CIBlockSection::getList(
            ['SORT' => 'ASC'],
            [
                'ID' => $setSect,
                'GLOBAL_ACTIVE' => 'Y',
                'IBLOCK_ID' => $iblockIdOrg
            ],
            false,
            ['UF_HIDEDEP']
        )->GetNext();

        if ($lvl <= 1 && $arSectData['UF_HIDEDEP'] == '1') {
            $arSectData = $prevSectData;
        }

        if (isset($arSectData)) {
            $setSect = $arSectData['IBLOCK_SECTION_ID'];
        } else {
            break;
        }

        $lvl--;
    }

    return $arSectData;
};

$objConnect1C = Source1C::Connect1C();

$fields = [
    'sessid' => [
        'title' => "",
        'name' => "sessid",
        'id' => "sessid",
        'type' => "hidden",
        'show' => true,
        'value' => bitrix_sessid(),
        'values' => null,
        'placeholder' => "",
        'custom' => "",
        'description' => ""
    ],
    'iblock_id' => [
        'title' => "",
        'name' => "IBLOCK_ID",
        'id' => "iblock_id",
        'type' => "hidden",
        'show' => true,
        'value' => $arResult['IBLOCK']['ID'],
        'values' => null,
        'placeholder' => "",
        'custom' => "",
        'description' => ""
    ],
    'save' => [
        'title' => "",
        'name' => "save",
        'id' => "save",
        'type' => "hidden",
        'show' => true,
        'value' => "Сохранить",
        'values' => null,
        'placeholder' => "",
        'custom' => "",
        'description' => ""
    ]
];

foreach ($arTabs[0]['fields'] as $field) {
    $field_props = $arResult['FIELDS'][preg_replace("!\[.*?\]!", '', $field['id'])] ?? [];

    $field_code = mb_strtolower($field_props['CODE'] ?? $field['id']);

    $fields[$field_code] = [
        'title' => $field['name'],
        'name' => $field['id'],
        'id' => $field_code,
        'type' => $field_props['PROPERTY_TYPE'] == "L" ? "list" : "text",
        'show' => !in_array($field_code, ['']),
        'value' => !empty($field_props['VALUES'])
            ? current($field_props['VALUES'])['ID']
            : ($field_props['DEFAULT_VALUE'] ?? ""),
        'values' => $field_props['VALUES'] ?? null,
        'placeholder' => "",
        'custom' => $field['type'] == "custom" ? $field['value'] : '',
        'description' => ''
    ];
}

$fields['name']['type'] = "hidden";
//$fields['poluchenie_lichno']['show'] = false;
$fields['name']['value'] = $arResult['IBLOCK']['NAME'];
$fields['rukovoditel']['type'] = 'user';
$fields['ep_doverennoe_litso']['type'] = 'user';

$fields['zayavka']['type'] = "hidden";
/*
$fields['zayavka_file'] = array_merge(
    $fields['zayavka'],
    [
        'name' => "zayavka_file",
        'id' => "zayavka_file",
        'description' => '
             <a href="https://corp.tularegion.ru/~rlFX8">скачать шаблон заявки</a>
        ',
        'type' => "file",
    ]
);
*/

$fields['doverennost']['type'] = "hidden";
/*
$fields['doverennost_file'] = array_merge(
    $fields['doverennost'],
    [
        'name' => "doverennost_file",
        'id' => "doverennost_file",
        'description' => '
             <a href="https://corp.tularegion.ru/~A6X6m">скачать шаблон доверенности</a>
        ',
        'type' => "file",
    ]
);
*/

$fields['doverennost_upolnomochennogo']['type'] = "hidden";
/*
$fields['doverennost_upolnomochennogo_file'] = array_merge(
    $fields['doverennost_upolnomochennogo'],
    [
        'name' => "doverennost_upolnomochennogo_file",
        'id' => "doverennost_upolnomochennogo_file",
        'description' => '
          <a href="https://corp.tularegion.ru/~8JGZg">скачать шаблон доверенности</a>
        ',
        'type' => "file",
    ]
);
*/
$fields['ep_zapros_na_sertifikat']['type'] = "hidden";

$fields['descr_noep'] = [
    'title' => "",
    'name' => "descr_noep",
    'id' => "descr_noep",
    'type' => "textblock",
    'show' => true,
    'value' => '',
    'values' => null,
    'placeholder' => "",
    'custom' => "",
    'description' => '<div>Для получения Электронной подписи Вам необходимо явиться в УЦ и иметь при себе:</div>
        <div>Для получения Электронной подписи Вам необходимо явиться в УЦ и иметь при себе:</div>
        <div><strong>Заполненный и подписанный пакет документов:</strong></div>
        <div>- Заявление - <a href="https://corp.tularegion.ru/~rlFX8"><strong>скачать шаблон</strong></a></div>
        <div>- Доверенность на осуществление действий от имени юр. Лица (или приказ)  - <a href="https://corp.tularegion.ru/~A6X6m"><strong>скачать шаблон</strong></a></div>
    '
];

$fields['descr_noep_pol_lichno'] = [
    'title' => "",
    'name' => "descr_noep_pol_lichno",
    'id' => "descr_noep_pol_lichno",
    'type' => "textblock",
    'show' => true,
    'value' => '',
    'values' => null,
    'placeholder' => "",
    'custom' => "",
    'description' => '
        <div>- Паспорт оригинал</div>
        <div>- СНИЛС оригинал</div>
    '
];
$fields['descr_noep_ne_pol_lichno'] = [
    'title' => "",
    'name' => "descr_noep_ne_pol_lichno",
    'id' => "descr_noep_ne_pol_lichno",
    'type' => "textblock",
    'show' => true,
    'value' => '',
    'values' => null,
    'placeholder' => "",
    'custom' => "",
    'description' => '
        <div>- Паспорт копия</div>
        <div>- СНИЛС копия</div>
    
        <div>- Доверенность уполномоченного пользователя - <a href="https://corp.tularegion.ru/~8JGZg"><strong>скачать шаблон</strong></a></div>
        <div>- Паспорт копия</div>
        <div>- СНИЛС копия</div>
    '
];

$fields['descr_ep'] = [
    'title' => "",
    'name' => "descr_ep",
    'id' => "descr_ep",
    'type' => "textblock",
    'show' => true,
    'value' => '',
    'values' => null,
    'placeholder' => "",
    'custom' => "",
    'description' => '
        <div>Для получения Электронной подписи Вам необходимо явиться в УЦ и иметь при себе:</div>
        <div>- Носитель</div>
    '
];
$fields['descr_ep_pol_lichno'] = [
    'title' => "",
    'name' => "descr_ep_pol_lichno",
    'id' => "descr_ep_pol_lichno",
    'type' => "textblock",
    'show' => true,
    'value' => '',
    'values' => null,
    'placeholder' => "",
    'custom' => "",
    'description' => '<div>- Паспорт оригинал</div>
    '
];
$fields['descr_ep_ne_pol_lichno'] = [
    'title' => "",
    'name' => "descr_ep_ne_pol_lichno",
    'id' => "descr_ep_ne_pol_lichno",
    'type' => "textblock",
    'show' => true,
    'value' => '',
    'values' => null,
    'placeholder' => "",
    'custom' => "",
    'description' => '
        <div>- Паспорт копия</div>
    '
];

$arSOTRUDNIK = $GLOBALS['userFields']($USER->GetId());

//$arSOTRUDNIK = $GLOBALS['userFields'](338); // @todo: mockery
$arSOTRUDNIK['DEPARTMENT_ID'] = array_shift($arSOTRUDNIK['UF_DEPARTMENT']);

$arrSectData = $getOrganization(
    $arSOTRUDNIK['DEPARTMENT_ID'],
    count($arSOTRUDNIK['DEPARTMENTS'])
);

if (!empty($arSOTRUDNIK['UF_SID'])) {
    ob_start();
    $arResC1 = Source1C::GetArray(
        $objConnect1C,
        'PersonalData',
        ['EmployeeID' => $arSOTRUDNIK['UF_SID']]
    );
    ob_end_clean();
}

$userDepName = $arSOTRUDNIK['PODRAZDELENIE'][count($arSOTRUDNIK['PODRAZDELENIE']) - 1];
$dep = CIntranetRestService::departmentGet(['NAME' => $userDepName]);
$dep = array_shift($dep);

if (!empty($arrSectData)) {
    $arrSectDataExt = CIBlockSection::GetList(
        ['SORT' => 'ASC'],
        [
            'ID' => $dep['ID'],
            'GLOBAL_ACTIVE' => 'Y',
            'IBLOCK_ID' => $iblockIdOrg
        ],
        false,
        [
            'UF_HEAD',
            'UF_ORG_LEGAL_BASIS',
            'UF_ORG_CITY',
            'UF_ORG_REGION',
            'UF_ORG_EMAIL',
            'UF_ORG_INN',
            'UF_ORG_OGRN',
            'UF_ORG_LEGAL_ADDRESS'
        ]
    )->GetNext();

    $strORGANIZATION_NAME = $arSOTRUDNIK['PODRAZDELENIE'][count($arSOTRUDNIK['PODRAZDELENIE']) - 1];
    $strORGANIZATION_RUC = '';
    $strORGANIZATION_RUC_DOLZHNOST = '';
    $strORGANIZATION_SNILS = '';
    $strORGANIZATION_PASSPORT_SERIA_NUMBER = '';
    $strORGANIZATION_PASSPORT_KEM = '';
    $strORGANIZATION_PASSPORT_KOGDA = '';
    $strORGANIZATION_ADDRESS_LIVE = '';

    $strORGANIZATION_LEGAL_BASIS = $arrSectDataExt['UF_ORG_LEGAL_BASIS'] ?? '';

    if (isset($arrSectDataExt['UF_HEAD'])) {
        $arRucData = $GLOBALS['userFields']($arrSectDataExt['UF_HEAD']);
        $strORGANIZATION_RUC = $arRucData['FIO'];
        $strORGANIZATION_RUC_DOLZHNOST
            = empty($arRucData['WORK_POSITION_CLEAR']) ? $arRucData['DOLJNOST_CLEAR'] : $arRucData['WORK_POSITION_CLEAR'];

        $fields['ep_glava_organizatsii_short'] = [
            'title' => "",
            'name' => "GLAVA_ORGANIZATSII_SHORT",
            'id' => "ep_glava_organizatsii_short",
            'type' => "hidden",
            'show' => true,
            'value' => empty($arRucData['FIO_INIC_REV']) ? $arRucData['FIO_INIC_DAT_REV'] : $arRucData['FIO_INIC_REV'],
            'values' => null,
            'placeholder' => "",
            'custom' => "",
            'description' => ""
        ];
    }

    if (isset($arResC1['Data']['PersonalData']['SNILS'])) {
        $strORGANIZATION_SNILS = join(
            '',
            array_map(
                function ($item, $index) {
                    if ($index % 3 == 0) {
                        $item .= '-';
                    }
                    return $item;
                },
                str_split($arResC1['Data']['PersonalData']['SNILS']),
                range(1, mb_strlen($arResC1['Data']['PersonalData']['SNILS']))
            )
        );

        $strORGANIZATION_PASSPORT_SERIA_NUMBER
            = $arResC1['Data']['PersonalData']['Passport']['Series']
            . ' ' . $arResC1['Data']['PersonalData']['Passport']['Number'];
        $strORGANIZATION_PASSPORT_KEM
            = $arResC1['Data']['PersonalData']['Passport']['IssuedBy'];
        $strORGANIZATION_PASSPORT_KOGDA
            = ((new DateTime($arResC1['Data']['PersonalData']['Passport']['DateOfIssue']))
            ->format("d.m.Y"));
    }

    if (isset($arResC1['Data']['PersonalData']['AddressOfResidence'])) {
        $strORGANIZATION_ADDRESS_LIVE
            = $arResC1['Data']['PersonalData']['AddressOfResidence'];
    }

    $strORGANIZATION_DOLZHNOST_ZAYAVITELA
        = empty($arSOTRUDNIK['WORK_POSITION_CLEAR']) ? $arSOTRUDNIK['DOLJNOST_CLEAR'] : $arSOTRUDNIK['WORK_POSITION_CLEAR'];

    $strORGANIZATION_FIO_ZAYAVITELA = $arSOTRUDNIK['FIO'];

    $fields['ep_familiya_imya_otchestvo_short'] = [
        'title' => "",
        'name' => "FAMILIYA_IMYA_OTCHESTVO_SHORT",
        'id' => "ep_familiya_imya_otchestvo_short",
        'type' => "hidden",
        'show' => true,
        'value' => empty($arSOTRUDNIK['FIO_INIC_REV']) ? $arSOTRUDNIK['FIO_INIC_DAT_REV'] : $arSOTRUDNIK['FIO_INIC_REV'],
        'values' => null,
        'placeholder' => "",
        'custom' => "",
        'description' => ""
    ];

    $strORGANIZATION_NAZVANIE_PODRAZDELENIYA
        = (isset($arSOTRUDNIK['DEPARTMENTS'][0])) ? $arSOTRUDNIK['DEPARTMENTS'][0] : (isset($arSOTRUDNIK['PODRAZDELENIE'][0]) ? $arSOTRUDNIK['PODRAZDELENIE'][0] : '');

    $strORGANIZATION_GOROD = $arrSectDataExt['UF_ORG_CITY'] ?? '';
    $strORGANIZATION_OBLAST = $arrSectDataExt['UF_ORG_REGION'] ?? '';
    $strORGANIZATION_EMAIL = $arrSectDataExt['UF_ORG_EMAIL'] ?? '';
    $strORGANIZATION_INN = $arrSectDataExt['UF_ORG_INN'] ?? '';
    $strORGANIZATION_OGRN = $arrSectDataExt['UF_ORG_OGRN'] ?? '';
    $strORGANIZATION_LEGAL_ADDRESS = $arrSectDataExt['UF_ORG_LEGAL_ADDRESS'] ?? '';

    $fields['ep_naimenovanie_organizatsii']['value'] = $strORGANIZATION_NAME;
    $fields['ep_glava_organizatsii']['value'] = $strORGANIZATION_RUC;
    $fields['ep_dolzhnost_glavy_organizatsii']['value']
        = $strORGANIZATION_RUC_DOLZHNOST;
    $fields['ep_osnovanie_deystviya_organizatsii']['value']
        = $strORGANIZATION_LEGAL_BASIS;
    $fields['ep_dolzhnost_zayavitelya']['value']
        = $strORGANIZATION_DOLZHNOST_ZAYAVITELA;
    $fields['ep_familiya_imya_otchestvo']['value'] = $strORGANIZATION_FIO_ZAYAVITELA;
    $fields['ep_naimenovanie_podrazdeleniya']['value']
        = $strORGANIZATION_NAZVANIE_PODRAZDELENIYA;

    $fields['ep_gorod']['value'] = $strORGANIZATION_GOROD;
    $fields['oblast']['value'] = $strORGANIZATION_OBLAST;
    $fields['ep_adres_elektronnoy_pochty']['value'] = $strORGANIZATION_EMAIL;
    $fields['ep_inn_yuridicheskogo_litsa']['value'] = $strORGANIZATION_INN;
    $fields['ep_inn_yuridicheskogo_litsa']['maxlength'] = 12;
    $fields['ep_ogrn_yuridicheskogo_litsa']['value'] = $strORGANIZATION_OGRN;
    $fields['ep_ogrn_yuridicheskogo_litsa']['maxlength'] = 13;

    $fields['ep_yuridicheskiy_adres_organizatsii']['value']
        = $strORGANIZATION_LEGAL_ADDRESS;
    $fields['ep_snils_vladeltsa_sertifikata']['value']
        = $strORGANIZATION_SNILS;
    $fields['ep_snils_vladeltsa_sertifikata']['value']
        = $strORGANIZATION_SNILS;
    $fields['ep_seriya_i_nomer_pasporta']['value']
        = $strORGANIZATION_PASSPORT_SERIA_NUMBER;
    $fields['ep_kem_vydan']['value']
        = $strORGANIZATION_PASSPORT_KEM;
    $fields['ep_kogda_vydan']['value']
        = $strORGANIZATION_PASSPORT_KOGDA;

    $fields['ep_adres_prozhivaniya_zayavitelya']['value'] = $strORGANIZATION_ADDRESS_LIVE;

    $fields['ep_oblast_primeneniya_klyucha_ep']['description']
        = "Для выбора нескольких пунктов используйте Shift или Ctrl + клик левой кнопкой мыши";
    $fields['ep_drugie_oblasti_v_kotorykh_nuzhna_podpis']['description']
        = "Укажите дополнительно, для чего вам еще нужна электронная подпись. Можно оставить поле пустым.";

    $fields['ep_na_rabochem_meste_polzovatelya_ustanovleno']['description']
        = "Можно узнать, посмотрев тип текущей подписи";

    $fields['izgotovlenie_klyuchey_ep_na_sredstvakh_uts']['not_props'] = true;

    $fields['data_rozhdeniya']['type'] = 'date';

    $fields['ep_kogda_vydan']['type'] = 'date';
    //dd($fields);

    $fields['ep_drugie_oblasti_v_kotorykh_nuzhna_podpis']['show'] = false;

    $fields['ep_soglashenie'] = [
        'title' => "Согласие на обработку персональных данных",
        'name' => "SOGLASHENIE",
        'id' => "ep_soglashenie",
        'type' => "checkbox",
        'show' => true,
        'value' => 1,
        'values' => null,
        'placeholder' => "",
        'custom' => "",
        'description' => ""
    ];
}
?>
<div id="lists_element_add_form"></div>
<div class="ajax-background">
    <div class="spinner-border text-primary"></div>
</div>
<script>
    $(document).ready(function () {
        function textValidSign() {
            let sMakeKeys = "Требуется";
            let sText = $('[data-id="has_valid_sign"] option:selected').text();
            if (sText == 'Да') {
                sMakeKeys = "Не требуется";
            } else if (sText == 'Нет') {
                sMakeKeys = "Требуется";
            }

            const makeKeysOption = $('[data-id="izgotovlenie_klyuchey_ep_na_sredstvakh_uts"] option:contains(' + sMakeKeys + ')');
            makeKeysOption.prop('selected', true);
            makeKeysOption.siblings('option').prop('disabled', true);

            if (makeKeysOption.prop('selected')) {
              makeKeysOption.prop('disabled', false);
            }

            return sText;
        }

        textValidSign();
        $('body').on('change', '[data-id="has_valid_sign"]', function () {
            textValidSign();
            // var someElement = document.querySelector('[data-id="izgotovlenie_klyuchey_ep_na_sredstvakh_uts"]');
            // someElement.value = 1620
        });

        $('input[data-id="ep_soglashenie"]').after(`<div class="hide js-soglashenie">В соответствии со статьей 428 ГК Российской Федерации полностью и безусловно присоединяюсь к Регламенту УЦ ГАУ ТО «ЦИТ» и обязуюсь соблюдать все положения указанного документа.
                    Даю свое согласие ГАУ ТО «ЦИТ» (300041, г. Тула, пр. Ленина, д. 2) на обработку (сбор, запись, систематизация, накопление, хранение, уточнение, извлечение, использование, блокирование, удаление, уничтожение) смешанным способом моих персональных данных, содержащихся в данном заявлении, с целью изготовления ключа электронной подписи, ключа проверки электронной подписи и сертификата ключа проверки электронной подписи на срок действия указанных ключей и сертификата.
                    Мои персональные данные (Ф.И.О., должность, СНИЛС, место работы, адрес электронной почты), содержащиеся в сертификате, считать общедоступными.
                    Настоящее согласие может быть отозвано мной путем подачи заявления в письменном виде в адрес ГАУ ТО «ЦИТ».</div>`);

        let obLabelSoglasie = $('input[data-id="ep_soglashenie"]').parent().find('label');

        obLabelSoglasie.css({
            'color': '#0b66c3'
        });

        obLabelSoglasie.hover(function () {
            $(this).css({
                'cursor': 'pointer'
            });
        });

        obLabelSoglasie.click(function () {
            $('.js-soglashenie').slideToggle('hide');
        });

        $('[data-id="poluchenie_lichno"]').prop('hidden', true);
        $('[data-id="poluchenie_lichno"]').parent().hide();
    });

    var bpFields = <?=json_encode($fields)?>;

    bpFields.rukovoditel.show = function () {
        return this.getValueXmlId('zayavitel_yavlyaetsya_rukovoditelem') != "<?=ZAYAVITEL_RUKOVODITEL?>";
    };

    bpFields.ep_doverennoe_litso.show = function () {
        return this.getValueXmlId('poluchenie_lichno') != "<?=BP_POLUCHAET_LICHNO_DA?>";
    };

    bpFields.ep_seriya_i_nomer_pasporta_dov_litsa.show = function () {
        return this.getValueXmlId('poluchenie_lichno') != "<?=BP_POLUCHAET_LICHNO_DA?>";
    };

    bpFields.ep_kem_vydan_pasport_dov_litsa.show = function () {
        return this.getValueXmlId('poluchenie_lichno') != "<?=BP_POLUCHAET_LICHNO_DA?>";
    };

    bpFields.ep_kogda_vydan_pasport_dov_litsa.show = function () {
        return this.getValueXmlId('poluchenie_lichno') != "<?=BP_POLUCHAET_LICHNO_DA?>";
    };

    bpFields.ep_adres_fakticheskogo_prozhivaniya_doverennogo_li.show = function () {
        return this.getValueXmlId('poluchenie_lichno') != "<?=BP_POLUCHAET_LICHNO_DA?>";
    };

    bpFields.ep_naimenovanie_organizatsii.setval = function () {
        return true;
    }

    bpFields.ep_glava_organizatsii.setval = function () {
        return true;
    }

    bpFields.ep_glava_organizatsii_short.setval = function () {
        return true;
    }

    bpFields.ep_dolzhnost_glavy_organizatsii.setval = function () {
        return true;
    }

    bpFields.ep_osnovanie_deystviya_organizatsii.setval = function () {
        return true;
    }

    bpFields.ep_seriya_i_nomer_pasporta_dov_litsa.setval = function () {
        return true;
    }

    bpFields.ep_kem_vydan_pasport_dov_litsa.setval = function () {
        return true;
    }

    bpFields.ep_kogda_vydan_pasport_dov_litsa.setval = function () {
        return true;
    }

    bpFields.ep_adres_fakticheskogo_prozhivaniya_doverennogo_li.setval = function () {
        return true;
    }

    bpFields.ep_soglashenie.setval = function () {
        return true;
    }

    bpFields.descr_noep.show = function () {
        return this.getValueXmlId('has_valid_sign') != "<?=HAS_VALID_SIGN_DA?>";
    };
    bpFields.descr_noep_pol_lichno.show = function () {
        return this.getValueXmlId('has_valid_sign') != "<?=HAS_VALID_SIGN_DA?>" && this.getValueXmlId('poluchenie_lichno') == "<?=BP_POLUCHAET_LICHNO_DA?>";
    };
    bpFields.descr_noep_ne_pol_lichno.show = function () {
        return this.getValueXmlId('has_valid_sign') != "<?=HAS_VALID_SIGN_DA?>" && this.getValueXmlId('poluchenie_lichno') != "<?=BP_POLUCHAET_LICHNO_DA?>";
    };
    bpFields.descr_ep.show = function () {
        return this.getValueXmlId('has_valid_sign') == "<?=HAS_VALID_SIGN_DA?>";
    };
    bpFields.descr_ep_pol_lichno.show = function () {
        return this.getValueXmlId('has_valid_sign') == "<?=HAS_VALID_SIGN_DA?>" && this.getValueXmlId('poluchenie_lichno') == "<?=BP_POLUCHAET_LICHNO_DA?>";
    };
    bpFields.descr_ep_ne_pol_lichno.show = function () {
        return this.getValueXmlId('has_valid_sign') == "<?=HAS_VALID_SIGN_DA?>" && this.getValueXmlId('poluchenie_lichno') != "<?=BP_POLUCHAET_LICHNO_DA?>";
    };

    var baseDirBP = '<?=$template2Folder?>';

    document.addEventListener('DOMContentLoaded', function (event) {
        ReactDOM.render(React.createElement(BPForm, {
            fields: bpFields,
            formName: "<?=$arResult["FORM_ID"]?>",
            formAction: "<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0",
            formAjax: "<?=$template2Folder . '/ajax.php'?>",
            formSignRequestAjax: "<?=$template2Folder . '/sign-request.php'?>",
            submitText: "Сформировать задачу на получение электронной подписи в отдел УЦ",
            submitDataType: "multipart/form-data"
        }), document.querySelector('#lists_element_add_form'));
    });
</script>