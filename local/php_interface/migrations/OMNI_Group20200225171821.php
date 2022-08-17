<?php

namespace Sprint\Migration;


class OMNI_Group20200225171821 extends Version
{

    protected $description = "Группа пользователей для редактирования статистики ОМНИ трекера";

    public function up() {
        $helper = new HelperManager();

                $helper->UserGroup()->saveGroup('omni_tracker_admins',array (
  'ACTIVE' => 'Y',
  'C_SORT' => '100',
  'ANONYMOUS' => 'N',
  'NAME' => 'ОМНИ трекер - администраторы',
  'DESCRIPTION' => 'Группа пользователей для редактирования статистики ОМНИ трекера',
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
