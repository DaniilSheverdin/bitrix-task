<?php

namespace Sprint\Migration;


class VAC_GROUP_20210622235645 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

                $helper->UserGroup()->saveGroup('vaccination',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '100',
  'ANONYMOUS' => 'N',
  'NAME' => 'Вакцинация',
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
