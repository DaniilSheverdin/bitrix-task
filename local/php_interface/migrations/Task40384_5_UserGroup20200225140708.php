<?php

namespace Sprint\Migration;


class Task40384_5_UserGroup20200225140708 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

                $helper->UserGroup()->saveGroup('contr_protocol',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '303',
  'ANONYMOUS' => 'N',
  'NAME' => 'Контроль поручений – Управление протокола',
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
