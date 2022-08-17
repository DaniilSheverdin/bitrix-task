<?php

namespace Sprint\Migration;


class EDU_FIN_20211208152503 extends Version
{

    protected $description = "Группы для распределения ролей финансирования";

    public function up() {
        $helper = new HelperManager();

                $helper->UserGroup()->saveGroup('operator-edu',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '100',
  'ANONYMOUS' => 'N',
  'NAME' => 'Оператор(edu)',
  'DESCRIPTION' => '',
  'SECURITY_POLICY' => 
  array (
  ),
));
                $helper->UserGroup()->saveGroup('kurator-edu',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '100',
  'ANONYMOUS' => 'N',
  'NAME' => 'Куратор(edu)',
  'DESCRIPTION' => '',
  'SECURITY_POLICY' => 
  array (
  ),
));
                $helper->UserGroup()->saveGroup('tehnadzor-edu',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '100',
  'ANONYMOUS' => 'N',
  'NAME' => 'Технадзор(edu)',
  'DESCRIPTION' => '',
  'SECURITY_POLICY' => 
  array (
  ),
));
                $helper->UserGroup()->saveGroup('finance-edu',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '100',
  'ANONYMOUS' => 'N',
  'NAME' => 'Финансист(edu)',
  'DESCRIPTION' => '',
  'SECURITY_POLICY' => 
  array (
  ),
));
                $helper->UserGroup()->saveGroup('admin-edu',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '600',
  'ANONYMOUS' => 'N',
  'NAME' => 'Админ(edu)',
  'DESCRIPTION' => '',
  'SECURITY_POLICY' => 
  array (
  ),
));
            }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
