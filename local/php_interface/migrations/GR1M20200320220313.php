<?php

namespace Sprint\Migration;


class GR1M20200320220313 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

                $helper->UserGroup()->saveGroup('krpn',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '100',
  'ANONYMOUS' => 'N',
  'NAME' => 'Карантин Роспотребнадзор',
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
