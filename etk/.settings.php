<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$arNeed = [
    [
        'IDS' => [53],
        'SHTAT' => 11,
        'FACT' => 10,
        'NAME' => 'Правительство Тульской области',
        'DATE' => '22.05.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([1711], CIntranetUtils::GetDeparmentsTree(1711, true)),
        'SHTAT' => 37,
        'FACT' => 36,
        'NAME' => 'Аппарат ПТО \ Секретариаты, советники',
        'DATE' => '22.05.2020',
        'USERS' => []
    ],
    [
        'IDS' => [305],
        'SHTAT' => 10,
        'FACT' => 10,
        'NAME' => 'Аппарат ПТО \ Управление пресс-службы',
        'DATE' => '22.05.2020',
        'USERS' => []
    ],
    [
        'IDS' => [452],
        'SHTAT' => 8,
        'FACT' => 8,
        'NAME' => 'Аппарат ПТО \ Управление протокола',
        'DATE' => '22.05.2020',
        'USERS' => []
    ],
    [
        'IDS' => [449],
        'SHTAT' => 3,
        'FACT' => 3,
        'NAME' => 'Аппарат ПТО \ Отдел специальной документальной связи',
        'DATE' => '22.05.2020',
        'USERS' => []
    ],
    [
        'IDS' => [2199],
        'SHTAT' => 5,
        'FACT' => 5,
        'NAME' => 'Аппарат ПТО \ Управление секретного делопроизводства',
        'DATE' => '22.05.2020',
        'USERS' => []
    ],
    [
        'IDS' => [453, 1721, 1722],
        'SHTAT' => 25,
        'FACT' => 25,
        'NAME' => 'Аппарат ПТО \ Главное управление государственной службы и кадров',
        'DATE' => '22.05.2020',
        'USERS' => []
    ],
    [
        'IDS' => [451, 1725, 1726],
        'SHTAT' => 29,
        'FACT' => 29,
        'NAME' => 'Аппарат ПТО \ Управление по делопроизводству и работе с обращениями граждан',
        'DATE' => '22.05.2020',
        'USERS' => []
    ],
    [
        'IDS' => [448, 1723, 1724],
        'SHTAT' => 17,
        'FACT' => 16,
        'NAME' => 'Аппарат ПТО \ Управление делами',
        'DATE' => '22.05.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([463], CIntranetUtils::GetDeparmentsTree(463, true)),
        'SKIP' => [2234],
        'SHTAT' => 34,
        'FACT' => 33,
        'NAME' => 'ОИВ \ Министерство образования Тульской области',
        'DATE' => '22.05.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([461], CIntranetUtils::GetDeparmentsTree(461, true)),
        'SKIP' => [],
        'SHTAT' => 178,
        'FACT' => 168,
        'NAME' => 'ОИВ \ Министерство труда и социальной защиты Тульской области',
        'DATE' => '29.05.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([1961], CIntranetUtils::GetDeparmentsTree(1961, true)),
        'SKIP' => [],
        'SHTAT' => 16,
        'FACT' => 15,
        'NAME' => 'ОИВ \ Министерство промышленности и науки Тульской области',
        'DATE' => '29.05.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([457], CIntranetUtils::GetDeparmentsTree(457, true)),
        'SKIP' => [],
        'SHTAT' => 20,
        'FACT' => 19,
        'NAME' => 'ОИВ \ Министерство культуры Тульской области',
        'DATE' => '29.05.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([458], CIntranetUtils::GetDeparmentsTree(458, true)),
        'SKIP' => array_merge([2182], CIntranetUtils::GetDeparmentsTree(2182, true)),
        'SHTAT' => 70,
        'FACT' => 65,
        'NAME' => 'ОИВ \ Министерство здравоохранения Тульской области',
        'DATE' => '29.05.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([455], CIntranetUtils::GetDeparmentsTree(455, true)),
        'SHTAT' => 38,
        'FACT' => 37,
        'NAME' => 'ОИВ \ Министерство экономического развития Тульской области',
        'DATE' => '29.05.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([464], CIntranetUtils::GetDeparmentsTree(464, true)),
        'SHTAT' => 66,
        'FACT' => 63,
        'NAME' => 'ОИВ \ Министерство имущественных и земельных отношений Тульской области',
        'DATE' => '05.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([467], CIntranetUtils::GetDeparmentsTree(467, true)),
        'SKIP' => array_merge([2233, 1665], CIntranetUtils::GetDeparmentsTree(1665, true)),
        'SHTAT' => 92,
        'FACT' => 89,
        'NAME' => 'ОИВ \ Министерство финансов Тульской области',
        'DATE' => '05.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([1965], CIntranetUtils::GetDeparmentsTree(1965, true)),
        'SHTAT' => 23,
        'FACT' => 21,
        'NAME' => 'ОИВ \ Министерство строительства Тульской области',
        'DATE' => '05.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([1970], CIntranetUtils::GetDeparmentsTree(1970, true)),
        'SHTAT' => 33,
        'FACT' => 28,
        'NAME' => 'ОИВ \ Министерство жилищно-коммунального хозяйства Тульской области',
        'DATE' => '05.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([460], CIntranetUtils::GetDeparmentsTree(460, true)),
        'SHTAT' => 27,
        'FACT' => 26,
        'NAME' => 'ОИВ \ Министерство транспорта и дорожного хозяйства Тульской области',
        'DATE' => '05.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([454], CIntranetUtils::GetDeparmentsTree(454, true)),
        'SHTAT' => 30,
        'FACT' => 28,
        'NAME' => 'ОИВ \ Министерство сельского хозяйства Тульской области',
        'DATE' => '05.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([466], CIntranetUtils::GetDeparmentsTree(466, true)),
        'SHTAT' => 99,
        'FACT' => 96,
        'NAME' => 'ОИВ \ Министерство природных ресурсов и экологии Тульской области',
        'DATE' => '15.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([205], CIntranetUtils::GetDeparmentsTree(205, true)),
        'SHTAT' => 21,
        'FACT' => 17,
        'NAME' => 'ОИВ \ Министерство внутренней политики и развития местного самоуправления в Тульской области',
        'DATE' => '15.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([459], CIntranetUtils::GetDeparmentsTree(459, true)),
        'SHTAT' => 12,
        'FACT' => 12,
        'NAME' => 'ОИВ \ Министерство молодежной политики Тульской области',
        'DATE' => '15.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([204], CIntranetUtils::GetDeparmentsTree(204, true)),
        'SKIP' => array_merge(
            [57, 58],
            CIntranetUtils::GetDeparmentsTree(57, true),
            CIntranetUtils::GetDeparmentsTree(58, true)
        ),
        'SHTAT' => 16,
        'FACT' => 16,
        'NAME' => 'ОИВ \ Министерство по информатизации, связи и вопросам открытого управления Тульской области',
        'DATE' => '15.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([456], CIntranetUtils::GetDeparmentsTree(456, true)),
        'SHTAT' => 32,
        'FACT' => 32,
        'NAME' => 'ОИВ \ Министерство по контролю и профилактике коррупционных нарушений в Тульской области',
        'DATE' => '15.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([474], CIntranetUtils::GetDeparmentsTree(474, true)),
        'SKIP' => array_merge([2229], CIntranetUtils::GetDeparmentsTree(2229, true)),
        'SHTAT' => 20,
        'FACT' => 20,
        'NAME' => 'ОИВ \ Комитет по делам записи актов гражданского состояния и обеспечению деятельности мировых судей в Тульской области',
        'DATE' => '22.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([477], CIntranetUtils::GetDeparmentsTree(477, true)),
        'SHTAT' => 14,
        'FACT' => 12,
        'NAME' => 'ОИВ \ Комитет Тульской области по спорту',
        'DATE' => '22.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([387], CIntranetUtils::GetDeparmentsTree(387, true)),
        'SHTAT' => 12,
        'FACT' => 11,
        'NAME' => 'ОИВ \ Комитет Тульской области по развитию туризма',
        'DATE' => '22.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([1308], CIntranetUtils::GetDeparmentsTree(1308, true)),
        'SHTAT' => 22,
        'FACT' => 22,
        'NAME' => 'ОИВ \ Комитет Тульской области по тарифам',
        'DATE' => '22.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([1616], CIntranetUtils::GetDeparmentsTree(1616, true)),
        'SHTAT' => 7,
        'FACT' => 7,
        'NAME' => 'ОИВ \ Комитет Тульской области по печати и массовым коммуникациям',
        'DATE' => '22.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([476], CIntranetUtils::GetDeparmentsTree(476, true)),
        'SHTAT' => 22,
        'FACT' => 21,
        'NAME' => 'ОИВ \ Комитет Тульской области по предпринимательству и потребительскому рынку',
        'DATE' => '22.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([473], CIntranetUtils::GetDeparmentsTree(473, true)),
        'SHTAT' => 13,
        'FACT' => 10,
        'NAME' => 'ОИВ \ Комитет ветеринарии Тульской области',
        'DATE' => '22.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([1976], CIntranetUtils::GetDeparmentsTree(1976, true)),
        'SHTAT' => 7,
        'FACT' => 7,
        'NAME' => 'ОИВ \ Комитет Тульской области по мобилизационной подготовке',
        'DATE' => '22.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([1977], CIntranetUtils::GetDeparmentsTree(1977, true)),
        'SHTAT' => 25,
        'FACT' => 23,
        'NAME' => 'ОИВ \ Комитет Тульской области по региональной безопасности',
        'DATE' => '22.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([469], CIntranetUtils::GetDeparmentsTree(469, true)),
        'SHTAT' => 51,
        'FACT' => 48,
        'NAME' => 'ОИВ \ Государственно-правовой комитет Тульской области',
        'DATE' => '26.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([468], CIntranetUtils::GetDeparmentsTree(468, true)),
        'SHTAT' => 36,
        'FACT' => 34,
        'NAME' => 'ОИВ \ Государственная жилищная инспекция Тульской области',
        'DATE' => '26.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([1890], CIntranetUtils::GetDeparmentsTree(1890, true)),
        'SHTAT' => 36,
        'FACT' => 35,
        'NAME' => 'ОИВ \ Инспекция Тульской области по государственному надзору за техническим состоянием самоходных машин и других видов техники',
        'DATE' => '26.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([471], CIntranetUtils::GetDeparmentsTree(471, true)),
        'SHTAT' => 29,
        'FACT' => 29,
        'NAME' => 'ОИВ \ Инспекция Тульской области по государственному архитектурно-строительному надзору',
        'DATE' => '26.06.2020',
        'USERS' => []
    ],
    [
        'IDS' => array_merge([470], CIntranetUtils::GetDeparmentsTree(470, true)),
        'SHTAT' => 9,
        'FACT' => 9,
        'NAME' => 'ОИВ \ Инспекция Тульской области по государственной охране объектов культурного наследия',
        'DATE' => '26.06.2020',
        'USERS' => []
    ],
];
