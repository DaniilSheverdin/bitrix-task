<?php

namespace Sprint\Migration;


class getAddressForViolators_20200425025609 extends Version
{

    protected $description = "Агент для добавления адреса нарушителям";

    public function up() {
        $helper = new HelperManager();

                $helper->Agent()->saveAgent(array (
  'MODULE_ID' => 'main',
  'USER_ID' => '1',
  'SORT' => '0',
  'NAME' => 'getAddressForViolators',
  'ACTIVE' => 'Y',
  'NEXT_EXEC' => '25.04.2020 12:00:00',
  'AGENT_INTERVAL' => '43200',
  'IS_PERIOD' => 'N',
));
            }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
