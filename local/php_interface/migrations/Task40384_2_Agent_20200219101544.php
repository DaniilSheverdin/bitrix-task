<?php

namespace Sprint\Migration;


class Task40384_2_Agent_20200219101544 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

                $helper->Agent()->saveAgent(array (
  'MODULE_ID' => 'citto.integration',
  'USER_ID' => NULL,
  'SORT' => '0',
  'NAME' => '\\Citto\\Integration\\Delo\\Users::sync();',
  'ACTIVE' => 'Y',
  'NEXT_EXEC' => '19.02.2020 10:14:00',
  'AGENT_INTERVAL' => '86400',
  'IS_PERIOD' => 'N',
));
            }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
