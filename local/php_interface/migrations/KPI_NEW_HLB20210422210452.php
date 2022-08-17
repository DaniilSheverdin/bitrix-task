<?php

namespace Sprint\Migration;


class KPI_NEW_HLB20210422210452 extends Version
{

    protected $description = "HLB для ретро данных";

    public function up() {
        $helper = new HelperManager();

        
        $helper->Hlblock()->saveHlblock(array (
  'NAME' => 'KPIRetroData',
  'TABLE_NAME' => 'kpi_retro_data',
  'LANG' => 
  array (
    'en' => 
    array (
      'NAME' => 'Ретро-данные KPI',
    ),
    'ru' => 
    array (
      'NAME' => 'Ретро-данные KPI',
    ),
  ),
));

                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_KPIRetroData',
  'FIELD_NAME' => 'UF_HL_KPI_DEPARTMENT',
  'USER_TYPE_ID' => 'iblock_section',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'DISPLAY' => 'LIST',
    'LIST_HEIGHT' => 10,
    'IBLOCK_ID' => 5,
    'DEFAULT_VALUE' => '',
    'ACTIVE_FILTER' => 'N',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Отдел',
    'ru' => 'Отдел',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Отдел',
    'ru' => 'Отдел',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Отдел',
    'ru' => 'Отдел',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Отдел',
    'ru' => 'Отдел',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Отдел',
    'ru' => 'Отдел',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_KPIRetroData',
  'FIELD_NAME' => 'UF_HL_KPI_FE',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Функциональная единица',
    'ru' => 'Функциональная единица',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Функциональная единица',
    'ru' => 'Функциональная единица',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Функциональная единица',
    'ru' => 'Функциональная единица',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Функциональная единица',
    'ru' => 'Функциональная единица',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Функциональная единица',
    'ru' => 'Функциональная единица',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_KPIRetroData',
  'FIELD_NAME' => 'UF_HL_KPI_FE_FORMULA',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Формула функциональной единицы',
    'ru' => 'Формула функциональной единицы',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Формула функциональной единицы',
    'ru' => 'Формула функциональной единицы',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Формула функциональной единицы',
    'ru' => 'Формула функциональной единицы',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Формула функциональной единицы',
    'ru' => 'Формула функциональной единицы',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Формула функциональной единицы',
    'ru' => 'Формула функциональной единицы',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_KPIRetroData',
  'FIELD_NAME' => 'UF_HL_KPI_KPIS',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'Y',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Значения KPI',
    'ru' => 'Значения KPI',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Значения KPI',
    'ru' => 'Значения KPI',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Значения KPI',
    'ru' => 'Значения KPI',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Значения KPI',
    'ru' => 'Значения KPI',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Значения KPI',
    'ru' => 'Значения KPI',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_KPIRetroData',
  'FIELD_NAME' => 'UF_HL_KPI_RATE',
  'USER_TYPE_ID' => 'double',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'PRECISION' => 4,
    'SIZE' => 20,
    'MIN_VALUE' => 0.0,
    'MAX_VALUE' => 0.0,
    'DEFAULT_VALUE' => 0.0,
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Ставка',
    'ru' => 'Ставка',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Ставка',
    'ru' => 'Ставка',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Ставка',
    'ru' => 'Ставка',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Ставка',
    'ru' => 'Ставка',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Ставка',
    'ru' => 'Ставка',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_KPIRetroData',
  'FIELD_NAME' => 'UF_HL_KPI_RESULT',
  'USER_TYPE_ID' => 'double',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'PRECISION' => 12,
    'SIZE' => 20,
    'MIN_VALUE' => 0.0,
    'MAX_VALUE' => 0.0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Итоговое значение',
    'ru' => 'Итоговое значение',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Итоговое значение',
    'ru' => 'Итоговое значение',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Итоговое значение',
    'ru' => 'Итоговое значение',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Итоговое значение',
    'ru' => 'Итоговое значение',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Итоговое значение',
    'ru' => 'Итоговое значение',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_KPIRetroData',
  'FIELD_NAME' => 'UF_HL_KPI_COMMENT',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Комментарий',
    'ru' => 'Комментарий',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Комментарий',
    'ru' => 'Комментарий',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Комментарий',
    'ru' => 'Комментарий',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Комментарий',
    'ru' => 'Комментарий',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Комментарий',
    'ru' => 'Комментарий',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_KPIRetroData',
  'FIELD_NAME' => 'UF_HL_KPI_USER_ID',
  'USER_TYPE_ID' => 'employee',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Сотрудник',
    'ru' => 'Сотрудник',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Сотрудник',
    'ru' => 'Сотрудник',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Сотрудник',
    'ru' => 'Сотрудник',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Сотрудник',
    'ru' => 'Сотрудник',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Сотрудник',
    'ru' => 'Сотрудник',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_KPIRetroData',
  'FIELD_NAME' => 'UF_HL_KPI_DATE',
  'USER_TYPE_ID' => 'date',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'N',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'DEFAULT_VALUE' => 
    array (
      'TYPE' => 'NONE',
      'VALUE' => '',
    ),
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Дата',
    'ru' => 'Дата',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Дата',
    'ru' => 'Дата',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Дата',
    'ru' => 'Дата',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Дата',
    'ru' => 'Дата',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Дата',
    'ru' => 'Дата',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_KPIRetroData',
  'FIELD_NAME' => 'UF_HL_KPI_PROGRESS',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'KPI прогресса',
    'ru' => 'KPI прогресса',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'KPI прогресса',
    'ru' => 'KPI прогресса',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'KPI прогресса',
    'ru' => 'KPI прогресса',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'KPI прогресса',
    'ru' => 'KPI прогресса',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'KPI прогресса',
    'ru' => 'KPI прогресса',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_KPIRetroData',
  'FIELD_NAME' => 'UF_HL_KPI_CRITICAL',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Критический KPI',
    'ru' => 'Критический KPI',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Критический KPI',
    'ru' => 'Критический KPI',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Критический KPI',
    'ru' => 'Критический KPI',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Критический KPI',
    'ru' => 'Критический KPI',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Критический KPI',
    'ru' => 'Критический KPI',
  ),
));
        
            }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
